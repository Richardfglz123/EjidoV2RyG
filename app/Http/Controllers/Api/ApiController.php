<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Usuario;
use Exception;

class ApiController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required_unless:login_biometrico,true'
        ]);

        $emailLimpio = strtolower(trim($request->email));
        $usuario = Usuario::where('Correo', $emailLimpio)->first();

        if (!$usuario) {
            return response()->json(['ok' => false, 'error' => 'El usuario no existe'], 401);
        }

        if ($request->login_biometrico === 'true') {
            $token = $usuario->createToken('ios-device-biometric')->plainTextToken;
            return response()->json(['ok' => true, 'two_factor' => false, 'token' => $token]);
        }

        if (!Hash::check($request->password, $usuario->Contraseña)) {
            return response()->json(['ok' => false, 'error' => 'Credenciales incorrectas'], 401);
        }

        $code = rand(100000, 999999);
        Cache::put('2fa_'.$emailLimpio, $code, now()->addMinutes(10));

        try {
            $html = "
            <div style='background-color: #000; padding: 40px; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; text-align: center;'>
                <div style='max-width: 450px; margin: 0 auto; background-color: #1c1c1e; padding: 40px; border-radius: 20px; border: 1px solid #38383a;'>
                    <h1 style='color: #fff; font-size: 24px; font-weight: 600; margin-bottom: 10px;'>Verificación</h1>
                    <p style='color: #8e8e93; font-size: 15px; margin-bottom: 30px;'>Hola, {$usuario->Nombres}. Usa este código para acceder de forma segura.</p>
                    <div style='background-color: #2c2c2e; border-radius: 12px; padding: 20px; margin-bottom: 30px;'>
                        <span style='color: #0a84ff; font-size: 38px; font-weight: 700; letter-spacing: 10px;'>{$code}</span>
                    </div>
                    <p style='color: #48484a; font-size: 12px;'>Este código expira en 10 minutos. Si no solicitaste esto, ignora el mensaje.</p>
                </div>
                <p style='color: #48484a; font-size: 11px; margin-top: 20px;'>SISTEMA EJIDAL SAN RAFAEL IXTAPALUCAN 2026</p>
            </div>";

            Mail::html($html, function ($mail) use ($usuario) {
                $mail->to($usuario->Correo)->subject($usuario->Nombres . ', tu código de seguridad');
            });
        } catch (Exception $e) {
            Log::error("Error SMTP: " . $e->getMessage());
        }

        return response()->json(['ok' => true, 'two_factor' => true]);
    }

    public function verifyCode(Request $request)
    {
        $request->validate(['email' => 'required|email', 'code' => 'required']);
        $email = strtolower(trim($request->email));
        $stored = Cache::get('2fa_'.$email);

        if (!$stored || $stored != $request->code) {
            return response()->json(['ok' => false, 'error' => 'Código inválido o expirado'], 401);
        }

        $usuario = Usuario::where('Correo', $email)->first();
        $token = $usuario->createToken('ios-device')->plainTextToken;
        Cache::forget('2fa_'.$email);

        return response()->json([
            'ok' => true,
            'token' => $token,
            'user' => ['id' => $usuario->Id_usuario, 'nombre' => $usuario->Nombres]
        ]);
    }
}