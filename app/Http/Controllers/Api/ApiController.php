<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Usuario; // IMPORTANTE: U mayúscula igual que tu modelo
use Exception;

class ApiController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validación flexible para permitir Face ID (sin password)
        $request->validate([
            'email' => 'required|email',
            'password' => 'required_unless:login_biometrico,true'
        ]);

        $emailLimpio = strtolower(trim($request->email));

        // 2. Buscamos al usuario usando el modelo correcto
        $usuario = Usuario::where('Correo', $emailLimpio)->first();

        if (!$usuario) {
            return response()->json([
                'ok' => false,
                'error' => 'El usuario no existe'
            ], 401);
        }

        // --- CASO A: INICIO DE SESIÓN CON FACE ID ---
        if ($request->login_biometrico === 'true') {
            // Generamos token directo (Sanctum)
            $token = $usuario->createToken('ios-device-biometric')->plainTextToken;

            return response()->json([
                'ok' => true,
                'two_factor' => false,
                'token' => $token
            ]);
        }

        // --- CASO B: INICIO DE SESIÓN TRADICIONAL ---
        if (!Hash::check($request->password, $usuario->Contraseña)) {
            return response()->json([
                'ok' => false,
                'error' => 'Credenciales incorrectas'
            ], 401);
        }

        // Generamos código 2FA
        $code = rand(100000, 999999);
        Log::info("Código 2FA para {$usuario->Correo}: {$code}");
        Cache::put('2fa_'.$usuario->Correo, $code, now()->addMinutes(10));

        try {
            Mail::raw("Tu código de acceso al Sistema Ejidal es: {$code}", function ($mail) use ($usuario) {
                $mail->to($usuario->Correo)->subject('Código de acceso');
            });
        } catch (Exception $e) {
            Log::error("Error SMTP: " . $e->getMessage());
        }

        return response()->json([
            'ok' => true,
            'two_factor' => true,
            'token' => null
        ]);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required'
        ]);

        $email = strtolower(trim($request->email));
        $stored = Cache::get('2fa_'.$email);

        if (!$stored || $stored != $request->code) {
            return response()->json(['ok' => false, 'error' => 'Código inválido'], 401);
        }

        $usuario = Usuario::where('Correo', $email)->first();

        if (!$usuario) {
            return response()->json(['ok' => false, 'error' => 'Usuario no encontrado'], 404);
        }

        $token = $usuario->createToken('ios-device')->plainTextToken;
        Cache::forget('2fa_'.$email);

        return response()->json([
            'ok' => true,
            'token' => $token,
            'user' => [
                'id' => $usuario->Id_usuario,
                'nombre' => $usuario->Nombres,
                'email' => $usuario->Correo
            ]
        ]);
    }
}