<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Models\Usuario;

class ApiController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $usuario = Usuario::where('email', $request->email)->first();

        if (! $usuario || ! Hash::check($request->password, $usuario->password)) {
            return response()->json([
                'ok' => false,
                'error' => 'Credenciales incorrectas'
            ], 401);
        }

        $code = rand(100000, 999999);

        Cache::put('2fa_'.$usuario->email, $code, now()->addMinutes(10));

        Mail::raw("Tu código es: {$code}", function ($mail) use ($usuario) {
            $mail->to($usuario->email)->subject('Código de acceso');
        });

        return response()->json([
            'ok' => true,
            'two_factor' => true
        ]);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required'
        ]);

        $stored = Cache::get('2fa_'.$request->email);

        if (! $stored || $stored != $request->code) {
            return response()->json([
                'ok' => false,
                'error' => 'Código inválido'
            ], 401);
        }

        Cache::forget('2fa_'.$request->email);

        $usuario = Usuario::where('email', $request->email)->first();

        return response()->json([
            'ok' => true,
            'token' => $usuario->createToken('ios')->plainTextToken,
            'user' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'email' => $usuario->email
            ]
        ]);
    }
}
