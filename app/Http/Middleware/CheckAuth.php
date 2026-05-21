<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAuth
{
    public function handle(Request $request, Closure $next)
    {
        $rutasPublicas = [
            'login.form',
            'login',
            'google.redirect',
            'google.callback'
        ];

        if ($request->routeIs($rutasPublicas)) {
            return $next($request);
        }

        if (!session()->has('authenticated') && !session()->has('usuario')) {
            return redirect()->route('login.form');
        }

        return $next($request);
    }
}