<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PerfilController extends Controller
{
    public function index()
    {
        $usuario = DB::table('usuario')
            ->leftJoin('Ejidatario', 'usuario.Id_usuario', '=', 'Ejidatario.Id_usuario')
            ->where('usuario.Id_usuario', session('usuario.id'))
            ->select('usuario.*', 'Ejidatario.Id_Ejidatario', 'Ejidatario.Num_Ejidatario')
            ->first();

        abort_if(!$usuario, 404);

        $parcelas = [];
        if ($usuario->Id_Ejidatario) {
            $parcelas = DB::table('Parcela')
                ->where('Id_Ejidatario', $usuario->Id_Ejidatario)
                ->get();
        }

        return view('cpanel.perfil.indexperfil', compact('usuario', 'parcelas'));
    }

    public function update(Request $request)
    {
        $userId = session('usuario.id');

        $request->validate([
            // Cambiamos a 'Usuario' con mayúscula para que coincida con tu Blade
            'Usuario' => 'required|unique:usuario,usuario,' . $userId . ',Id_usuario',
            'Correo' => 'required|email|unique:usuario,Correo,' . $userId . ',Id_usuario',
            'Telefono' => 'required|numeric',
            'foto' => 'nullable|image',
        ]);

        $data = [
            'usuario' => $request->Usuario, // Coincide con el input del Blade
            'Correo' => $request->Correo,
            'Telefono' => $request->Telefono,
            'Fecha_Modificado' => now(),
        ];

        if ($request->hasFile('foto')) {
            $userRecord = DB::table('usuario')->where('Id_usuario', $userId)->first();
            if ($userRecord && $userRecord->foto) {
                Storage::disk('public')->delete($userRecord->foto);
            }

            // Guardamos y actualizamos sesión
            $path = $request->file('foto')->store('perfiles', 'public');
            $data['foto'] = $path;
            session(['usuario.foto' => $path]);
        }

        if ($request->filled('Contraseña')) {
            $data['Contraseña'] = \Hash::make($request->Contraseña);
        }

        DB::table('usuario')->where('Id_usuario', $userId)->update($data);
        session(['usuario.nombre_completo' => $request->Usuario]);

        return back()->with('success', 'Perfil actualizado correctamente');
    }

    public function getPerfilApi(Request $request)
    {
        // 1. Intentamos la vía normal de Sanctum
        $userId = null;
        if ($request->user()) {
            $userId = $request->user()->Id_usuario ?? $request->user()->id;
        }

        // 2. 🚀 PLAN DE RESCATE: Si Sanctum no devolvió el usuario por incompatibilidad de modelos,
        // leemos el token directamente de la cabecera HTTP "Authorization"
        if (!$userId) {
            $headerToken = $request->bearerToken(); // Extrae la cadena limpia del token (sin el "Bearer ")

            if ($headerToken) {
                // Buscamos el token en la tabla de Sanctum utilizando el hash SHA256 que Laravel usa internamente
                // Nota: Los tokens de Sanctum vienen en formato "id|token_real". Separamos si tiene pipa.
                $tokenActual = str_contains($headerToken, '|') ? explode('|', $headerToken)[1] : $headerToken;

                $accessToken = DB::table('personal_access_tokens')
                    ->where('token', hash('sha256', $tokenActual))
                    ->first();

                if ($accessToken) {
                    // 'tokenable_id' guarda el Id_usuario de la persona que se logueó
                    $userId = $accessToken->tokenable_id;
                }
            }
        }

        // 3. Si de plano no hay ID tras agotar recursos, devolvemos el fallo
        if (!$userId) {
            return response()->json([
                'ok' => false,
                'error' => "Sanctum validó el token, pero no se pudo vincular con un ID de usuario válido."
            ], 401);
        }

        // 4. Tu consulta se mantiene igual, trayendo los datos usando la variable blindada
        $usuario = DB::table('usuario as u')
            ->leftJoin('Ejidatario as e', 'u.Id_usuario', '=', 'e.Id_usuario')
            ->where('u.Id_usuario', '=', $userId)
            ->select('u.*', 'e.Num_Ejidatario')
            ->first();

        if (!$usuario) {
            return response()->json([
                'ok' => false,
                'error' => "El token pertenece al usuario con ID $userId, pero ese registro ya no existe en la tabla 'usuario'"
            ], 404);
        }

        // Mapeo consistente a un array para evitar problemas de mayúsculas/minúsculas del driver
        $uArray = (array) $usuario;

        $nombres = $uArray['Nombres'] ?? $uArray['nombres'] ?? '';
        $paterno = $uArray['Apellido_Paterno'] ?? $uArray['apellido_paterno'] ?? '';
        $materno = $uArray['Apellido_Materno'] ?? $uArray['apellido_materno'] ?? '';
        $nombreCompleto = trim("$nombres $paterno $materno");

        $correo = $uArray['Correo'] ?? $uArray['correo'] ?? '';
        $telefono = $uArray['Telefono'] ?? $uArray['telefono'] ?? '';
        $foto = $uArray['foto'] ?? $uArray['Foto'] ?? null;
        $numEjidatario = $uArray['Num_Ejidatario'] ?? $uArray['num_ejidatario'] ?? null;

        $fotoUrl = null;
        if ($foto) {
            $fotoUrl = str_starts_with($foto, 'http') ? $foto : asset(Storage::url($foto));
        }

        return response()->json([
            'ok' => true,
            'usuario' => [
                'nombre' => $nombreCompleto ?: 'Nombre no disponible',
                'correo' => $correo,
                'telefono' => (string)$telefono,
                'num_ejidatario' => (string)($numEjidatario ?? 'N/A'),
                'foto_url' => $fotoUrl,
            ]
        ]);
    }

    public function updatePerfilApi(Request $request)
    {
        // Recuperamos el ID del usuario con la estrategia blindada que usamos antes
        $userId = null;
        if ($request->user()) {
            $userId = $request->user()->Id_usuario ?? $request->user()->id;
        } else {
            $headerToken = $request->bearerToken();
            if ($headerToken) {
                $tokenActual = str_contains($headerToken, '|') ? explode('|', $headerToken)[1] : $headerToken;
                $accessToken = DB::table('personal_access_tokens')
                    ->where('token', hash('sha256', $tokenActual))
                    ->first();
                if ($accessToken) {
                    $userId = $accessToken->tokenable_id;
                }
            }
        }

        if (!$userId) {
            return response()->json(['ok' => false, 'error' => 'No autorizado'], 401);
        }

        // 🚀 AQUÍ ESTÁ EL TRUCO:
        // Le decimos a la regla 'unique' que ignore el registro donde la columna 'Id_usuario' sea igual a nuestro $userId
        $request->validate([
            'Nombres' => 'required|string|max:255',
            'Apellido_Paterno' => 'required|string|max:255',
            'Apellido_Materno' => 'nullable|string|max:255',
            'Telefono' => 'nullable|string',
            'Correo' => 'required|email|unique:usuario,Correo,' . $userId . ',Id_usuario', // 👈 Excluye tu propio ID
        ]);

        // Tu lógica para actualizar los datos en la base de datos...
        DB::table('usuario')
            ->where('Id_usuario', $userId)
            ->update([
                'Nombres' => $request->Nombres,
                'Apellido_Paterno' => $request->Apellido_Paterno,
                'Apellido_Materno' => $request->Apellido_Materno,
                'Telefono' => $request->Telefono,
                'Correo' => $request->Correo,
            ]);

        return response()->json([
            'ok' => true,
            'message' => 'Perfil actualizado correctamente.'
        ]);
    }
}