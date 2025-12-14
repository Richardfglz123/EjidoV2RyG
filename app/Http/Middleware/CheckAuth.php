<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAuth
{
    public function handle(Request $request, Closure $next)
{
    if (!session()->has('user_id')) {
        return redirect()->route('login.form');
    }

    return $next($request);
}
}
