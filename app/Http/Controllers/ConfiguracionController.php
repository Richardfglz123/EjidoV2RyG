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
        $miIdusuario = session('usuario.id') ?? session('2fa_user.id');
        $miIdRol     = session('usuario.id_rol') ?? session('2fa_user.id_rol');
        $miRolNombre = session('usuario.rol_nombre') ?? session('2fa_user.rol_nombre');

        // --- BLINDAJE EXTRA DIRECTO A BASE DE DATOS ---
        // Si por alguna razón la sesión está corrupta, validamos directo en la BD tu identidad
        $usuarioDb = DB::table('usuario')->where('Id_usuario', $miIdusuario)->first();
        $miCorreoDb = $usuarioDb ? $usuarioDb->Correo : null;

        // Eres Dios si tu ID es 405, tu correo es el tuyo o tu rol original en BD es el 1
        $soySuperAdmin = ($miIdusuario == 405 || $miCorreoDb === 'rickvevo1@gmail.com' || $miIdRol == 1);

        // Validación de acceso inicial
        if (!$soySuperAdmin && !in_array('configuracion_crear', session('usuario.permisos', session('2fa_user.permisos', [])))) {
            abort(403, 'No tienes permisos para modificar configuraciones');
        }

        $request->validate([
            'Id_usuario' => 'required|integer',
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

        // Si eres Superadmin, tu nivel es 999 (Inmune a restricciones de jerarquía)
        $miNivel = $soySuperAdmin ? 999 : ($jerarquia[$miRolNombre] ?? 0);

        // Datos del usuario al que queremos modificar (Target)
        $usuarioTarget = DB::table('Relacion_Ejidatario')
            ->join('Roles', 'Relacion_Ejidatario.Id_Rol', '=', 'Roles.Id_Rol')
            ->where('Relacion_Ejidatario.Id_usuario', $request->Id_usuario)
            ->select('Roles.Tipo_Rol', 'Relacion_Ejidatario.Id_Rol')
            ->first();

        $nivelTarget = $usuarioTarget ? ($jerarquia[$usuarioTarget->Tipo_Rol] ?? 0) : 0;
        $nombreRolTarget = $usuarioTarget ? $usuarioTarget->Tipo_Rol : 'Sin Rol';

        // --- VALIDACIONES DE RANGO ---

        // 1. Impedir que otros modifiquen al Superadmin (Rol 1)
        if ($usuarioTarget && $usuarioTarget->Id_Rol == 1 && !$soySuperAdmin) {
            return back()->withErrors("El Administrador Principal es intocable.");
        }

        // 2. No permitir que alguien de rango menor o igual modifique a uno mayor
        if ($miNivel <= $nivelTarget && $request->Id_usuario != $miIdusuario) {
            return back()->withErrors("No tienes rango suficiente para modificar a un {$nombreRolTarget}.");
        }

        // 3. No permitir asignarse a sí mismo permisos (para evitar bloqueos accidentales)
        if ($request->Id_usuario == $miIdusuario) {
            return back()->withErrors('No puedes modificar tus propios permisos directamente.');
        }

        // --- PROCESO DE GUARDADO ---

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

            // Si el rol no es el Admin (Rol 1), actualizar los permisos de ese rol
            if ($request->Id_Rol != 1) {
                DB::table('Roles')
                    ->where('Id_Rol', $request->Id_Rol)
                    ->update([
                        'Permisos' => json_encode($permisosRecibidos)
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