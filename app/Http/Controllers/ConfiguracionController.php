<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConfiguracionController extends Controller
{
    public function permisos()
    {
        $roles = DB::table('Roles')->get();
        return view('cpanel.Configuracion.permisos', compact('roles'));
    }

    public function buscarUsuariosAjax(Request $request)
    {
        return DB::table('Usuario')
            ->where('Usuario', 'LIKE', '%' . $request->q . '%')
            ->select('Id_Usuario as id', 'Usuario as text')
            ->limit(10)
            ->get();
    }

    public function obtenerPermisosUsuario($id)
    {
        $datos = DB::table('Relacion_Ejidatario')
            ->join('Roles', 'Relacion_Ejidatario.Id_Rol', '=', 'Roles.Id_Rol')
            ->where('Relacion_Ejidatario.Id_Usuario', $id)
            ->select('Relacion_Ejidatario.Id_Rol', 'Roles.Permisos')
            ->first();

        return response()->json([
            'Id_Rol'   => $datos->Id_Rol ?? null,
            'permisos' => json_decode($datos->Permisos ?? '[]', true)
        ]);
    }
    public function obtenerPermisosRol($id) {
        $rol = DB::table('Roles')->where('Id_Rol', $id)->first();

        if (!$rol) {
            return response()->json(['permisos' => []]);
        }

        $permisosRaw = $rol->Permisos;
        $permisosDecodificados = is_string($permisosRaw)
            ? json_decode($permisosRaw, true)
            : $permisosRaw;

        return response()->json([
            'permisos' => $permisosDecodificados ?? []
        ]);
    }

    public function guardarPermisos(Request $request)
    {
        if (!in_array('configuracion_crear', session('usuario.permisos', []))) {
            abort(403, 'No tienes permisos para modificar configuraciones');
        }

        $request->validate([
            'Id_Usuario' => 'required|integer',
            'Id_Rol'     => 'required|integer',
            'permisos'   => 'array'
        ]);

        $jerarquia = [
            'Administrador'         => 10,
            'Secretaria'            => 8,
            'Comisario ejidal'      => 6,
            'Comité de vigilancia'  => 5,
            'Ejidatario'            => 3,
            'Usuario'               => 1
        ];

        $miRolNombre = session('usuario.rol_nombre');
        $miNivel = $jerarquia[$miRolNombre] ?? 0;

        $usuarioTarget = DB::table('Relacion_Ejidatario')
            ->join('Roles', 'Relacion_Ejidatario.Id_Rol', '=', 'Roles.Id_Rol')
            ->where('Relacion_Ejidatario.Id_Usuario', $request->Id_Usuario)
            ->select('Roles.Tipo_Rol', 'Relacion_Ejidatario.Id_Rol')
            ->first();

        $nivelTarget = ($usuarioTarget) ? ($jerarquia[$usuarioTarget->Tipo_Rol] ?? 0) : 0;

        if ($miNivel === 0) {
            return back()->withErrors("Error de sistema: No se pudo identificar tu nivel de rango. Reintenta iniciando sesión.");
        }

        if ($miNivel < $nivelTarget) {
            return back()->withErrors("No tienes rango suficiente para modificar a un {$usuarioTarget->Tipo_Rol}.");
        }

        $nuevoRolNombre = DB::table('Roles')->where('Id_Rol', $request->Id_Rol)->value('Tipo_Rol');
        if (($jerarquia[$nuevoRolNombre] ?? 0) > $miNivel) {
            return back()->withErrors("No puedes asignar el rango de {$nuevoRolNombre} porque es superior al tuyo.");
        }

        if (!$request->has('confirmacion_global')) {
            return back()->withErrors('Debes confirmar que entiendes el impacto del cambio.');
        }

        if ($request->Id_Usuario == session('usuario.id')) {
            return back()->withErrors('No puedes modificar tus propios permisos directamente para evitar bloqueos accidentales.');
        }

        if ($usuarioTarget && $usuarioTarget->Id_Rol == 2 && $request->Id_Rol != 2) {
            return back()->withErrors('Por seguridad, no se puede degradar el rango de un Administrador.');
        }

        $permisosPermitidos = [
            'usuarios_ver','usuarios_crear','usuarios_eliminar',
            'ejidatarios_ver','ejidatarios_crear',
            'actividades_ver','actividades_crear',
            'gestion_ver','gestion_crear',
            'asambleas_ver','asambleas_crear',
            'asistencia_ver','asistencia_crear',
            'expedientes_ver','expedientes_crear',
            'parcelas_ver','parcelas_crear',
            'utilidades_ver','utilidades_crear',
            'gastos_ver','gastos_crear',
            'inventario_ver','inventario_crear',
            'apoyos_ver','apoyos_crear',
            'historicos_ver','historicos_crear',
            'respaldo_ver','respaldo_crear',
            'configuracion_ver','configuracion_crear'
        ];

        $permisosRecibidos = $request->permisos ?? [];

        if (array_diff($permisosRecibidos, $permisosPermitidos)) {
            return back()->withErrors('Se detectaron permisos inválidos.');
        }

        DB::beginTransaction();
        try {
            // Actualizar el rol del usuario
            DB::table('Relacion_Ejidatario')
                ->where('Id_Usuario', $request->Id_Usuario)
                ->update([
                    'Id_Rol'           => $request->Id_Rol,
                    'Fecha_Modificado' => now()
                ]);

            if ($miRolNombre === 'Administrador') {
                DB::table('Roles')
                    ->where('Id_Rol', $request->Id_Rol)
                    ->update([
                        'Permisos'         => json_encode($permisosRecibidos),
                        'Fecha_Modificado' => now()
                    ]);
            }

            DB::commit();
            return back()->with('success', 'Permisos y rol actualizados correctamente');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors('Error al guardar: ' . $e->getMessage());
        }
    }
}