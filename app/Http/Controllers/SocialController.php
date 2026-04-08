<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\DB;

class SocialController extends Controller
{
    // Redirige a Google o Apple
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    // Recibe la respuesta de la red social
    public function handleProviderCallback($provider)
    {
        $socialUser = Socialite::driver($provider)->user();
        $miUsuarioId = session('usuario.id');

        // Guardamos el ID social en el usuario actual
        DB::table('Usuario')
            ->where('Id_Usuario', $miUsuarioId)
            ->update([
                $provider . '_id' => $socialUser->getId(),
                'updated_at' => now()
            ]);

        return redirect()->route('perfil.index')->with('success', "Cuenta de $provider vinculada correctamente.");
    }

    // Para quitar la vinculación
    public function unlink($provider)
    {
        DB::table('Usuario')
            ->where('Id_Usuario', session('usuario.id'))
            ->update([$provider . '_id' => null]);

        return back()->with('success', "Se ha desvinculado la cuenta de $provider.");
    }
}