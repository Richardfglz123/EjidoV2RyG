<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TwoFAController extends Controller
{
    public function showForm()
    {
        // Deben existir AMBOS datos del 2FA
        if (!session()->has('2fa_code') || !session()->has('2fa_user')) {
            return redirect()->route('login.form');
        }

        return view('cpanel.login.verificar-codigo');
    }

    public function check(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6'
        ]);

        // Seguridad extra
        if (!session()->has('2fa_code') || !session()->has('2fa_user')) {
            return redirect()->route('login.form');
        }

        if ($request->code != session('2fa_code')) {
            return back()->withErrors(['code' => 'Código incorrecto']);
        }

        $user = session('2fa_user');

        // Limpieza del 2FA
        session()->forget('2fa_code');
        session()->forget('2fa_user');

        // 🔐 SESIÓN FINAL (ESTO ES LO IMPORTANTE)
        session([
            'authenticated'   => true,
            'user_id'         => $user['id'],
            'username'        => $user['username'],
            'user_email'      => $user['email'],
            'nombre_completo' => $user['nombre_completo'],
            'rol'             => $user['rol'],
        ]);

        return redirect()->route('inicio');
    }
}
