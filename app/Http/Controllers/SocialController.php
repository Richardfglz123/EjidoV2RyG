<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\DB;

class SocialController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->stateless()
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $usuario = DB::table('usuario')
                ->where('google_id', $googleUser->id)
                ->orWhere('Correo', $googleUser->email)
                ->first();

            if (!$usuario) {
                return redirect()->route('login.form')->with('error', 'El correo ' . $googleUser->email . ' no está registrado en el sistema Ejidal.');
            }

            $acceso = DB::table('Relacion_Ejidatario as re')
                ->leftJoin('Roles as r', 're.Id_Rol', '=', 'r.Id_Rol')
                ->where('re.Id_usuario', $usuario->Id_usuario)
                ->select('r.Tipo_Rol', 'r.Permisos', 'r.Id_Rol')
                ->first();

            session([
                'authenticated' => true,
                'usuario' => [
                    'id'              => $usuario->Id_usuario,
                    'username'        => $usuario->usuario,
                    'email'           => $usuario->Correo,
                    'nombre_completo' => $usuario->Nombres . ' ' . $usuario->Apellido_Paterno,
                    'rol'             => $acceso ? $acceso->Tipo_Rol : 'usuario',
                    'permisos'        => ($acceso && $acceso->Permisos) ? json_decode($acceso->Permisos, true) : []
                ]
            ]);

            session()->save();

            if (empty($usuario->google_id)) {
                DB::table('usuario')->where('Id_usuario', $usuario->Id_usuario)->update(['google_id' => $googleUser->id]);
            }

            return redirect()->route('inicio');

        } catch (\Exception $e) {
            return redirect()->route('login.form')->with('error', 'Error en la conexión con Google: ' . $e->getMessage());
        }
    }

    private function crearSesionusuario($usuario) {
        session([
            'usuario.id' => $usuario->Id_usuario,
            'usuario.nombre_completo' => $usuario->usuario,
            'usuario.foto' => $usuario->foto,
            'usuario.rol' => $usuario->Rol ?? null
        ]);
    }

    public function unlink($provider)
    {
        $userId = session('usuario.id');
        if (!$userId) return redirect()->route('login');

        if ($provider === 'google') {
            DB::table('usuario')->where('Id_usuario', $userId)->update(['google_id' => null]);
            return redirect()->route('perfil.index')->with('success', 'Cuenta desvinculada.');
        }
        return back();
    }
}