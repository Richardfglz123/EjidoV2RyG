<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; // <-- Cambiamos Cache por DB
use Illuminate\Support\Facades\Mail;
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

        // --- NUEVA LÓGICA DE CÓDIGO ---
        $code = rand(100000, 999999);

        // Borramos códigos viejos y guardamos el nuevo en la BD
        DB::table('codigos_verificacion')->where('correo', $emailLimpio)->delete();
        DB::table('codigos_verificacion')->insert([
            'correo' => $emailLimpio,
            'codigo' => $code,
            'created_at' => now()
        ]);

        try {
            $html = "
            <div style='background-color: #000; padding: 40px; font-family: -apple-system; text-align: center;'>
                <div style='max-width: 450px; margin: 0 auto; background-color: #1c1c1e; padding: 40px; border-radius: 20px; border: 1px solid #38383a;'>
                    <h1 style='color: #fff; font-size: 24px;'>Verificación</h1>
                    <p style='color: #8e8e93;'>Hola, {$usuario->Nombres}. Usa este código:</p>
                    <div style='background-color: #2c2c2e; border-radius: 12px; padding: 20px; margin: 25px 0;'>
                        <span style='color: #0a84ff; font-size: 38px; font-weight: bold; letter-spacing: 10px;'>{$code}</span>
                    </div>
                </div>
            </div>";

            Mail::html($html, function ($mail) use ($usuario) {
                $mail->to($usuario->Correo)->subject('Tu código de seguridad');
            });
        } catch (Exception $e) { \Log::error($e->getMessage()); }

        return response()->json(['ok' => true, 'two_factor' => true]);
    }

    public function verifyCode(Request $request)
    {
        $request->validate(['email' => 'required|email', 'code' => 'required']);

        $email = strtolower(trim($request->email));
        // Forzamos a que el código sea tratado como una cadena de texto plana
        $codigoRecibido = strval(trim($request->code));

        // LOG PARA DEPURAR (Búscalo en storage/logs/laravel.log)
        \Log::info("Intento de validación - Email: $email | Código Recibido: '$codigoRecibido'");

        $registro = DB::table('codigos_verificacion')
            ->where('correo', $email)
            ->where('codigo', $codigoRecibido) // La BD lo comparará como texto
            ->where('created_at', '>=', now()->subMinutes(15))
            ->first();

        if (!$registro) {
            // Si no lo encuentra, buscamos si el email existe para dar un error más claro
            $existeEmail = DB::table('codigos_verificacion')->where('correo', $email)->exists();
            $errorMsg = $existeEmail ? 'Código incorrecto' : 'El código expiró o no se generó';

            return response()->json(['ok' => false, 'error' => $errorMsg], 401);
        }

        $usuario = Usuario::where('Correo', $email)->first();
        $token = $usuario->createToken('ios-device')->plainTextToken;

        // Limpiamos solo tras el éxito
        DB::table('codigos_verificacion')->where('correo', $email)->delete();

        return response()->json([
            'ok' => true,
            'token' => $token,
            'user' => ['id' => $usuario->Id_usuario, 'nombre' => $usuario->Nombres]
        ]);
    }
}