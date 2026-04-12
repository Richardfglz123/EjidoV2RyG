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

    public function obtenerPermisosRol($id)
    {
        $rol = DB::table('Roles')->where('Id_Rol', $id)->first();
        if (!$rol) return response()->json(['permisos' => []]);

        $permisosRaw = $rol->Permisos;
        $permisosDecodificados = is_string($permisosRaw) ? json_decode($permisosRaw, true) : $permisosRaw;

        return response()->json(['permisos' => $permisosDecodificados ?? []]);
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
            'Comisariado Ejidal'    => 6,
            'Comité de vigilancia'  => 5,
            'Ejidatario'            => 3,
            'Invitado'              => 1
        ];

        $miRolNombre = session('usuario.rol_nombre');
        $miNivel = $jerarquia[$miRolNombre] ?? 0;

        $usuarioTarget = DB::table('Relacion_Ejidatario')
            ->join('Roles', 'Relacion_Ejidatario.Id_Rol', '=', 'Roles.Id_Rol')
            ->where('Relacion_Ejidatario.Id_Usuario', $request->Id_Usuario)
            ->select('Roles.Tipo_Rol', 'Relacion_Ejidatario.Id_Rol')
            ->first();

        $nivelTarget = $usuarioTarget ? ($jerarquia[$usuarioTarget->Tipo_Rol] ?? 0) : 0;
        $nombreRolTarget = $usuarioTarget ? $usuarioTarget->Tipo_Rol : 'Sin Rol';

        if ($usuarioTarget && $usuarioTarget->Tipo_Rol === 'Administrador' && $request->Id_Usuario != session('usuario.id')) {
            return back()->withErrors("No está permitido que un Administrador modifique a otro Administrador.");
        }

        if ($miNivel <= $nivelTarget && $request->Id_Usuario != session('usuario.id')) {
            return back()->withErrors("No tienes rango suficiente para modificar a un {$nombreRolTarget}.");
        }

        $nuevoRol = DB::table('Roles')->where('Id_Rol', $request->Id_Rol)->first();
        $nivelNuevoRol = $jerarquia[$nuevoRol->Tipo_Rol] ?? 0;

        if ($nivelNuevoRol >= $miNivel && $miRolNombre !== 'Administrador') {
            return back()->withErrors("No puedes asignar el rango de {$nuevoRol->Tipo_Rol} porque es igual o superior al tuyo.");
        }

        if ($request->Id_Usuario == session('usuario.id')) {
            return back()->withErrors('No puedes modificar tus propios permisos directamente.');
        }

        if (!$request->has('confirmacion_global')) {
            return back()->withErrors('Debes confirmar que entiendes que esto afecta a todos los usuarios con este rol.');
        }

        $permisosPermitidos = [
            'usuarios_ver','usuarios_crear','usuarios_eliminar',
            'ejidatarios_ver','ejidatarios_crear','ejidatarios_eliminar',
            'actividades_ver','actividades_crear','actividades_eliminar',
            'gestion_ver','gestion_crear','gestion_eliminar',
            'asambleas_ver','asambleas_crear','asambleas_eliminar',
            'asistencia_ver','asistencia_crear','asistencia_eliminar',
            'expedientes_ver','expedientes_crear',
            'parcelas_ver','parcelas_crear','parcelas_eliminar',
            'utilidades_ver','utilidades_crear','utilidades_eliminar',
            'gastos_ver','gastos_crear','gastos_eliminar',
            'inventario_ver','inventario_crear','inventario_eliminar',
            'apoyos_ver','apoyos_crear','apoyos_eliminar',
            'historicos_ver','historicos_crear','historicos_eliminar',
            'respaldo_ver','respaldo_crear',
            'configuracion_ver','configuracion_crear'
        ];

        $permisosRecibidos = $request->permisos ?? [];

        if (array_diff($permisosRecibidos, $permisosPermitidos)) {
            return back()->withErrors('Se detectaron permisos inválidos.');
        }

        DB::beginTransaction();
        try {
            DB::table('Relacion_Ejidatario')->updateOrInsert(
                ['Id_Usuario' => $request->Id_Usuario],
                [
                    'Id_Rol'           => $request->Id_Rol,
                    'Fecha_Modificado' => now()
                ]
            );

            if ($request->Id_Rol != 1) {
                DB::table('Roles')
                    ->where('Id_Rol', $request->Id_Rol)
                    ->update([
                        'Permisos'         => json_encode($permisosRecibidos)
                    ]);
            }

            DB::commit();
            return back()->with('success', 'Permisos y rol actualizados correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors('Error al guardar: ' . $e->getMessage());
        }
    }
}