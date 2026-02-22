<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckPermiso
{
    public function handle($request, Closure $next, $permisoRequerido)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login.form')->withErrors(['session' => 'Sesión expirada.']);
        }

        $usuarioSesion = session('usuario');

        $datosUsuario = DB::table('Relacion_Ejidatario')
            ->join('Roles', 'Relacion_Ejidatario.Id_Rol', '=', 'Roles.Id_Rol')
            ->where('Relacion_Ejidatario.Id_Usuario', $usuarioSesion['id'])
            ->select('Roles.Tipo_Rol', 'Roles.Permisos')
            ->first();

        if (!$datosUsuario) {
            abort(403, "Usuario sin rol asignado en el sistema.");
        }

        if ($datosUsuario->Tipo_Rol === 'Administrador') {
            return $next($request);
        }

        $permisosArray = json_decode($datosUsuario->Permisos, true) ?? [];

        $permisoBuscado = strtolower($permisoRequerido);
        $misPermisos = array_map('strtolower', (array)$permisosArray);

        if (in_array($permisoBuscado, $misPermisos)) {
            return $next($request);
        }

        abort(403, "Acceso denegado. No cuentas con el permiso: " . strtoupper($permisoRequerido));
    }
}