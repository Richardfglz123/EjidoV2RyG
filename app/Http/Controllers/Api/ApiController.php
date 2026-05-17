<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\usuario;
use Exception;

class ApiController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validamos los datos que llegan desde Swift
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // 2. Buscamos por la columna 'Correo' (según tu modelo usuario)
        $usuario = \App\Models\usuario::where('Correo', $request->email)->first();

        // 3. Verificamos contra la columna 'Contraseña'
        if (!$usuario || !\Hash::check($request->password, $usuario->Contraseña)) {
            return response()->json([
                'ok' => false,
                'error' => 'Credenciales incorrectas'
            ], 401);
        }

        $code = rand(100000, 999999);

        // Usamos el Log para ver el código en la Mac por si el mail falla
        \Log::info("Código generado para {$usuario->Correo}: {$code}");

        \Cache::put('2fa_'.$usuario->Correo, $code, now()->addMinutes(10));

        try {
            \Mail::raw("Tu código es: {$code}", function ($mail) use ($usuario) {
                $mail->to($usuario->Correo)->subject('Código de acceso');
            });
        } catch (\Exception $e) {
            // Si el mail falla, NO rompemos la respuesta, solo avisamos
            \Log::error("Error SMTP: " . $e->getMessage());
        }

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

        // NORMALIZACIÓN: Evita errores por espacios o mayúsculas
        $email = strtolower(trim($request->email));
        $stored = Cache::get('2fa_'.$email);

        // LOG DE SEGURIDAD: Revisa esto en tu terminal (storage/logs/laravel.log)
        \Log::info("Intento 2FA - Email: $email | Enviado: {$request->code} | En Cache: $stored");

        if (!$stored || $stored != $request->code) {
            return response()->json([
                'ok' => false,
                'error' => 'Código inválido o expirado'
            ], 401);
        }

        $usuario = usuario::where('Correo', $email)->first();

        if (!$usuario) {
            return response()->json(['ok' => false, 'error' => 'usuario no encontrado'], 404);
        }

        // GENERACIÓN DE TOKEN (Sanctum)
        // Asegúrate de tener HasApiTokens en tu modelo usuario
        $token = $usuario->createToken('ios-device')->plainTextToken;

        // COMENTA ESTA LÍNEA TEMPORALMENTE
        // Cache::forget('2fa_'.$email);

        return response()->json([
            'ok' => true,
            'token' => $token, // Importante enviarlo para que Swift lo guarde
            'user' => [
                'id' => $usuario->getKey(),
                'nombre' => $usuario->Nombres,
                'email' => $usuario->Correo
            ]
        ]);
    }
}