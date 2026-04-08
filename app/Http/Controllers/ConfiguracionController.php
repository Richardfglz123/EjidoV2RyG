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
            ->select('Relacion_Ejidatario.Id_Rol', 'Roles.Permisos', 'Roles.Tipo_Rol')
            ->first();

        // Si es Administrador, devolvemos un array con permisos totales para la interfaz
        $permisos = json_decode($datos->Permisos ?? '[]', true);
        if (($datos->Tipo_Rol ?? '') === 'Administrador' && empty($permisos)) {
            $permisos = ['usuarios_ver', 'ejidatarios_ver', 'actividades_ver', 'configuracion_ver']; // etc
        }

        return response()->json([
            'Id_Rol'   => $datos->Id_Rol ?? null,
            'permisos' => $permisos
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
        // LLAVE MAESTRA: Si soy Admin, salto la validación de in_array para no bloquearme a mí mismo
        $esAdmin = (session('usuario.rol_nombre') === 'Administrador' || session('usuario.id_rol') == 1);

        if (!$esAdmin) {
            if (!in_array('configuracion_crear', session('usuario.permisos', []))) {
                abort(403, 'No tienes permisos para modificar configuraciones');
            }
        }

        $request->validate([
            'Id_Usuario' => 'required|integer',
            'Id_Rol'     => 'required|integer',
            'permisos'   => 'array'
        ]);

        // Jerarquía basada en los nombres exactos de tu tabla Roles
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

        // Usuario al que queremos modificar
        $usuarioTarget = DB::table('Relacion_Ejidatario')
            ->join('Roles', 'Relacion_Ejidatario.Id_Rol', '=', 'Roles.Id_Rol')
            ->where('Relacion_Ejidatario.Id_Usuario', $request->Id_Usuario)
            ->select('Roles.Tipo_Rol', 'Relacion_Ejidatario.Id_Rol')
            ->first();

        if (!$usuarioTarget) {
            return back()->withErrors("El usuario destino no tiene un rol asignado.");
        }

        $nivelTarget = $jerarquia[$usuarioTarget->Tipo_Rol] ?? 0;

        // 1. Un Admin no puede tocar a otro Admin (a menos que sea él mismo, pero abajo lo bloqueamos)
        if ($usuarioTarget->Tipo_Rol === 'Administrador' && $request->Id_Usuario != session('usuario.id')) {
            return back()->withErrors("Seguridad: Un Administrador no puede modificar a otro.");
        }

        // 2. No puedes modificar a alguien de mayor o igual rango que tú
        if ($miNivel <= $nivelTarget && $request->Id_Usuario != session('usuario.id')) {
            return back()->withErrors("Rango insuficiente para modificar a un {$usuarioTarget->Tipo_Rol}.");
        }

        // 3. No puedes modificarte a ti mismo (para no quitarte el acceso por error)
        if ($request->Id_Usuario == session('usuario.id')) {
            return back()->withErrors('No puedes modificar tus propios permisos por seguridad.');
        }

        if (!$request->has('confirmacion_global')) {
            return back()->withErrors('Debes marcar la casilla de confirmación.');
        }

        $permisosRecibidos = $request->permisos ?? [];

        DB::beginTransaction();
        try {
            // ACTUALIZAR EL ROL DEL USUARIO
            DB::table('Relacion_Ejidatario')
                ->where('Id_Usuario', $request->Id_Usuario)
                ->update([
                    'Id_Rol' => $request->Id_Rol
                ]);

            // ACTUALIZAR LOS PERMISOS DEL ROL (Si el rol no es Administrador)
            // Según tu BD, Administrador es Id_Rol = 1
            if ($request->Id_Rol != 1) {
                DB::table('Roles')
                    ->where('Id_Rol', $request->Id_Rol)
                    ->update([
                        'Permisos' => json_encode($permisosRecibidos)
                    ]);
            }

            DB::commit();
            return back()->with('success', 'Configuración actualizada correctamente.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors('Error: ' . $e->getMessage());
        }
    }
}