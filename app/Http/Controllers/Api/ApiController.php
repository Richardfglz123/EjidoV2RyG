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
        // 1. Validamos los datos. 'password' ya no es obligatorio si viene de un login biométrico
        $request->validate([
            'email' => 'required|email',
            'password' => 'required_unless:login_biometrico,true'
        ]);

        $emailLimpio = strtolower(trim($request->email));

        // 2. Buscamos al usuario por su columna 'Correo'
        $usuario = usuario::where('Correo', $emailLimpio)->first();

        if (!$usuario) {
            return response()->json([
                'ok' => false,
                'error' => 'El usuario no existe'
            ], 401);
        }

        // ==========================================
        // CASO A: INICIO DE SESIÓN CON FACE ID
        // ==========================================
        if ($request->login_biometrico === 'true') {
            // El usuario ya se autenticó localmente en su iPhone.
            // Generamos directamente su Token de Sanctum para saltar el 2FA.
            $token = $usuario->createToken('ios-device-biometric')->plainTextToken;

            \Log::info("Login Biométrico exitoso por Face ID para: {$usuario->Correo}");

            return response()->json([
                'ok' => true,
                'two_factor' => false, // Bypass de 2FA directo a la app principal
                'token' => $token
            ]);
        }

        // ==========================================
        // CASO B: INICIO DE SESIÓN TRADICIONAL (Contraseña)
        // ==========================================
        if (!Hash::check($request->password, $usuario->Contraseña)) {
            return response()->json([
                'ok' => false,
                'error' => 'Credenciales incorrectas'
            ], 401);
        }

        // Generamos el código numérico para el segundo factor
        $code = rand(100000, 999999);

        // Registro en Log por si falla el envío de correo en Hostinger
        Log::info("Código generado para {$usuario->Correo}: {$code}");

        Cache::put('2fa_'.$usuario->Correo, $code, now()->addMinutes(10));

        try {
            Mail::raw("Tu código de acceso al Sistema Ejidal es: {$code}", function ($mail) use ($usuario) {
                $mail->to($usuario->Correo)->subject('Código de acceso');
            });
        } catch (Exception $e) {
            Log::error("Error SMTP al enviar 2FA: " . $e->getMessage());
        }

        return response()->json([
            'ok' => true,
            'two_factor' => true, // Avisa a Swift que muestre la pantalla del código
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

        Log::info("Intento 2FA - Email: $email | Enviado: {$request->code} | En Cache: $stored");

        if (!$stored || $stored != $request->code) {
            return response()->json([
                'ok' => false,
                'error' => 'Código inválido o expirado'
            ], 401);
        }

        $usuario = usuario::where('Correo', $email)->first();

        if (!$usuario) {
            return response()->json(['ok' => false, 'error' => 'Usuario no encontrado'], 404);
        }

        // Generación de Token tras validar exitosamente el código de correo
        $token = $usuario->createToken('ios-device')->plainTextToken;

        // Limpiamos el caché para que el código expire inmediatamente tras usarse
        Cache::forget('2fa_'.$email);

        return response()->json([
            'ok' => true,
            'token' => $token,
            'user' => [
                'id' => $usuario->getKey(),
                'nombre' => $usuario->Nombres,
                'email' => $usuario->Correo
            ]
        ]);
    }
}