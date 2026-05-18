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
            'usuario'    => 'required|unique:usuario,usuario,' . $userId . ',Id_usuario',
            'Correo'     => 'required|email|unique:usuario,Correo,' . $userId . ',Id_usuario',
            'Telefono'   => 'required|numeric',
            'foto'       => 'nullable|image|mimes:jpg,jpeg|max:2048',
            'Contraseña' => [
                'nullable',
                'confirmed',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
            ],
        ]);

        $data = [
            'usuario'          => $request->usuario,
            'Correo'           => $request->Correo,
            'Telefono'         => $request->Telefono,
            'Fecha_Modificado' => now(),
        ];

        if ($request->hasFile('foto')) {
            $userRecord = DB::table('usuario')->where('Id_usuario', $userId)->first();

            if ($userRecord && isset($userRecord->foto) && $userRecord->foto) {
                Storage::disk('public')->delete($userRecord->foto);
            }

            $path = $request->file('foto')->store('perfiles', 'public');
            $data['foto'] = $path;

            session(['usuario.foto' => $path]);
        }

        if ($request->filled('Contraseña')) {
            $data['Contraseña'] = Hash::make($request->Contraseña);
        }

        DB::table('usuario')
            ->where('Id_usuario', $userId)
            ->update($data);

        session(['usuario.nombre_completo' => $request->usuario]);

        $request->session()->save();

        return back()->with('success', 'Perfil actualizado correctamente');
    }
    // Dentro de PerfilController.php

    public function getPerfilApi(Request $request)
    {
        // 1. Obtener ID del token
        $token = $request->bearerToken();
        $userId = $token;

        if (!$userId) {
            return response()->json(['ok' => false, 'error' => 'No autorizado'], 401);
        }

        // 2. Buscamos al usuario uniendo con la tabla Ejidatario para tener TODO
        $usuario = DB::table('usuario as u')
            ->leftJoin('Ejidatario as e', 'u.Id_usuario', '=', 'e.Id_usuario')
            ->where('u.Id_usuario', $userId)
            ->select(
                'u.Nombres',
                'u.Apellido_Paterno',
                'u.Apellido_Materno',
                'u.Correo',
                'u.Telefono',
                'u.foto',
                'e.Num_Ejidatario', // Dato extra de ejidatario
                'e.Id_Ejidatario'
            )
            ->first();

        if (!$usuario) {
            return response()->json(['ok' => false, 'error' => 'Usuario no encontrado'], 404);
        }

        // 3. Construimos el nombre real (no el alias de login)
        $nombreReal = trim("{$usuario->Nombres} {$usuario->Apellido_Paterno} {$usuario->Apellido_Materno}");
        if (empty($nombreReal)) { $nombreReal = "Usuario Sin Nombre"; }

        return response()->json([
            'ok' => true,
            'usuario' => [
                'nombre'   => $nombreReal,
                'correo'   => $usuario->Correo ?? '',
                'telefono' => $usuario->Telefono ?? '',
                'num_ejidatario' => $usuario->Num_Ejidatario ?? 'N/A', // Nuevo dato
                'foto_url' => ($usuario->foto) ? asset('storage/' . $usuario->foto) : null,
            ]
        ]);
    }
}