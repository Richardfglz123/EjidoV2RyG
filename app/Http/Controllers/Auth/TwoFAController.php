<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TwoFAController extends Controller
{
    public function showForm()
    {
        if (!session()->has('2fa_code') || !session()->has('2fa_user')) {
            return redirect()->route('login.form');
        }
        return view('cpanel.login.verificar-codigo');
    }

    public function check(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $userSession = session('2fa_user');
        $storedCode = session('2fa_code');

        if (!$userSession || !$storedCode) {
            return redirect()->route('login.form')->withErrors(['error' => 'Sesión expirada.']);
        }

        if (trim($request->code) != trim($storedCode)) {
            return back()->withErrors(['code' => 'Código incorrecto']);
        }

        session([
            'authenticated' => true,
            '2fa_verified' => true,
            'usuario' => [
                'id' => $userSession['id'],
                'nombre_completo' => $userSession['nombre_completo'],
                'rol' => $userSession['rol'],
                'permisos' => $userSession['permisos']
            ],
            'Id_usuario' => $userSession['id'],
            'nombre_completo' => $userSession['nombre_completo']
        ]);
        session()->forget(['2fa_code', '2fa_user']);

        return redirect()->route('inicio');
    }
}