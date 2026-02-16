<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermiso
{
    public function handle($request, Closure $next, $permiso)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login.form')->withErrors(['session' => 'Sesión expirada o no encontrada.']);
        }

        $user = session('usuario');

        if (isset($user['rol']) && $user['rol'] === 'Administrador') {
            return $next($request);
        }

        $permisos = $user['permisos'] ?? [];
        $permisoBuscado = strtolower($permiso);
        $misPermisos = array_map('strtolower', (array)$permisos);

        if (in_array($permisoBuscado, $misPermisos)) {
            return $next($request);
        }

        abort(403, "Acceso denegado. Se requiere el permiso: " . strtoupper($permisoBuscado));
    }
}