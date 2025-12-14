<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRol
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!session()->has('rol')) {
            abort(403, 'Rol no definido');
        }

        if (!in_array(session('rol'), $roles)) {
            abort(403, 'No tienes permisos para acceder a esta sección');
        }

        return $next($request);
    }
}
