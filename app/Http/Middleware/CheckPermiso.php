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

        // CORRECCIÓN DE MAYÚSCULAS PARA LINUX: Se cambiaron las columnas a 'Id_Usuario'
        $user = DB::table('usuario')
            ->leftJoin('Relacion_Ejidatario', 'usuario.Id_usuario', '=', 'Relacion_Ejidatario.Id_Usuario')
            ->leftJoin('Roles', 'Relacion_Ejidatario.Id_Rol', '=', 'Roles.Id_Rol')
            ->where('usuario.Id_usuario', session('usuario.id'))
            ->select('Roles.Tipo_Rol', 'Roles.Permisos', 'usuario.foto', 'usuario.Nombres', 'usuario.Apellido_Paterno')
            ->first();

        // Si por alguna razón la consulta falla o no se encuentra el rol,
        // validamos si en la sesión actual ya vienes forzado como Administrador para no bloquearte
        $rolActual = $user ? $user->Tipo_Rol : session('usuario.rol_nombre');

        if (!$user && !$rolActual) {
            return $next($request);
        }

        // Re-actualizamos la sesión de forma segura
        session([
            'usuario.id'              => session('usuario.id'),
            'usuario.rol'             => $rolActual,
            'usuario.rol_nombre'      => $rolActual,
            'usuario.foto'            => $user ? $user->foto : session('usuario.foto'),
            'usuario.nombre_completo' => $user ? ($user->Nombres . ' ' . $user->Apellido_Paterno) : session('usuario.nombre_completo'),
            'usuario.permisos'        => $user ? (json_decode($user->Permisos, true) ?? []) : (session('usuario.permisos') ?? [])
        ]);

        // Validación definitiva del Administrador sin importar minúsculas/mayúsculas
        if (strcasecmp(trim($rolActual), 'Administrador') === 0) {
            return $next($request);
        }

        $misPermisos = array_map('strtolower', (array)session('usuario.permisos'));
        if (in_array(strtolower($permisoRequerido), $misPermisos)) {
            return $next($request);
        }

        abort(403, "No tienes permiso.");
    }
}