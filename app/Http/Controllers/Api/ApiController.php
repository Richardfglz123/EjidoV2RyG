<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
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

        $code = rand(100000, 999999);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $emailLimpio],
            [
                'token' => $code,
                'expires_at' => now()->addMinutes(15),
                'created_at' => now()
            ]
        );

        try {
            $html = "
            <div style='background-color: #000; padding: 40px; font-family: -apple-system, sans-serif; text-align: center;'>
                <div style='max-width: 450px; margin: 0 auto; background-color: #1c1c1e; padding: 40px; border-radius: 20px; border: 1px solid #38383a;'>
                    <h1 style='color: #fff; font-size: 24px;'>Verificación</h1>
                    <p style='color: #8e8e93;'>Hola, {$usuario->Nombres}. Usa este código para ingresar:</p>
                    <div style='background-color: #2c2c2e; border-radius: 12px; padding: 20px; margin: 25px 0;'>
                        <span style='color: #0a84ff; font-size: 38px; font-weight: bold; letter-spacing: 10px;'>{$code}</span>
                    </div>
                    <p style='color: #636366; font-size: 12px;'>Este código expira en 15 minutos.</p>
                </div>
            </div>";

            Mail::html($html, function ($mail) use ($usuario) {
                $mail->to($usuario->Correo)->subject($usuario->Nombres . ', tu código de seguridad');
            });
        } catch (Exception $e) {
            \Log::error("Error enviando correo: " . $e->getMessage());
        }

        return response()->json(['ok' => true, 'two_factor' => true]);
    }
    public function verifyCode(Request $request)
    {
        $emailLimpio = strtolower(trim($request->email));
        $codigoIngresado = trim($request->code);

        $record = DB::table('password_resets')
            ->where('email', $emailLimpio)
            ->where('token', $codigoIngresado)
            ->first();

        if (!$record) {
            return response()->json(['ok' => false, 'error' => 'Código inválido para este correo'], 401);
        }

        $user = DB::table('usuario')->where('Correo', $emailLimpio)->first();

        if (!$user) {
            return response()->json(['ok' => false, 'error' => 'No existe un usuario con ese correo'], 404);
        }

        DB::table('password_resets')->where('email', $emailLimpio)->delete();

        // 3. RESPUESTA DINÁMICA
        return response()->json([
            'ok' => true,
            'token' => (string)$user->Id_Usuario,
            'user' => [
                'nombre' => $user->Nombres,
                'email' => $user->Correo
            ]
        ]);
    }
}