<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConfiguracionController extends Controller
{
    public function permisos()
    {
        $roles = DB::table('Roles')->get();
        return view('cpanel.configuracion.permisos', compact('roles'));
    }

    public function buscarUsuariosAjax(Request $request)
    {
        return DB::table('usuario')
            ->where('usuario', 'LIKE', '%' . $request->q . '%')
            ->select('Id_usuario as id', 'usuario as text')
            ->limit(10)
            ->get();
    }

    public function obtenerPermisosUsuario($id)
    {
        $datos = DB::table('Relacion_Ejidatario')
            ->join('Roles', 'Relacion_Ejidatario.Id_Rol', '=', 'Roles.Id_Rol')
            ->where('Relacion_Ejidatario.Id_usuario', $id)
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
        // CUALQUIERA que tenga acceso a esta función va a poder guardar temporalmente.
        // Quitamos TODAS las validaciones de rango para desbloquearte YA.

        $request->validate([
            'Id_usuario' => 'required|integer',
            'Id_Rol'     => 'required|integer',
            'permisos'   => 'array'
        ]);

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
            // Actualizar o Insertar en la tabla de relaciones
            DB::table('Relacion_Ejidatario')->updateOrInsert(
                ['Id_usuario' => $request->Id_usuario],
                [
                    'Id_Rol'           => $request->Id_Rol,
                    'Fecha_Modificado' => now()
                ]
            );

            // Actualizar los permisos globales de ese rol (Se permite para todos temporalmente)
            DB::table('Roles')
                ->where('Id_Rol', $request->Id_Rol)
                ->update([
                    'Permisos' => json_encode($permisosRecibidos)
                ]);

            DB::commit();
            return back()->with('success', '¡Modo Dios activo! Permisos y rol actualizados correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors('Error crítico al guardar: ' . $e->getMessage());
        }
    }
}