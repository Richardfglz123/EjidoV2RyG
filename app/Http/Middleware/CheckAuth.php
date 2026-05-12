<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAuth
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Definimos las rutas que NO deben ser protegidas
        $rutasPublicas = [
            'login.form',
            'login',
            'google.redirect',
            'google.callback' // Asegúrate de que este nombre coincida con el ->name() en web.php
        ];

        // 2. Si la ruta actual es una de las públicas, déjalo pasar
        if ($request->routeIs($rutasPublicas)) {
            return $next($request);
        }

        // 3. Si no está autenticado (manual o por Google), al login
        // Nota: En tu SocialController usamos la llave 'usuario', asegúrate de que coincida
        if (!session()->has('authenticated') && !session()->has('usuario')) {
            return redirect()->route('login.form');
        }

        return $next($request);
    }
}