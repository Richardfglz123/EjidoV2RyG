<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

class CheckPermiso
{
    public function handle($request, Closure $next, $permisoRequerido)
    {
        if (!session()->has('usuario.id')) {
            return redirect()->route('login.form');
        }

        $user = DB::table('usuario')
            ->leftJoin('Relacion_Ejidatario', 'usuario.Id_Usuario', '=', 'Relacion_Ejidatario.Id_Usuario')
            ->leftJoin('Roles', 'Relacion_Ejidatario.Id_Rol', '=', 'Roles.Id_Rol')
            ->where('usuario.Id_Usuario', session('usuario.id'))
            ->select('Roles.Tipo_Rol', 'Roles.Permisos', 'usuario.foto', 'usuario.Nombres', 'usuario.Apellido_Paterno')
            ->first();

        if (!$user) {
            return $next($request);
        }

        session([
            'usuario.id' => session('usuario.id'),
            'usuario.rol' => $user->Tipo_Rol,
            'usuario.foto' => $user->foto,
            'usuario.nombre_completo' => $user->Nombres . ' ' . $user->Apellido_Paterno,
            'usuario.permisos' => json_decode($user->Permisos, true) ?? []
        ]);

        if ($user->Tipo_Rol === 'Administrador') {
            return $next($request);
        }

        $misPermisos = array_map('strtolower', (array)session('usuario.permisos'));
        if (in_array(strtolower($permisoRequerido), $misPermisos)) {
            return $next($request);
        }

        abort(403, "No tienes permiso.");
    }
}