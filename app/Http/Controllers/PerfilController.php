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
    public function getPerfilApi(Request $request)
    {
        // El token que manda el iPhone (que actualmente es el ID 405)
        $userId = $request->bearerToken();

        if (!$userId) {
            return response()->json(['ok' => false, 'error' => 'No hay ID en el token'], 401);
        }

        $usuario = DB::table('usuario as u')
            ->leftJoin('Ejidatario as e', 'u.Id_usuario', '=', 'e.Id_usuario')
            ->where('u.Id_usuario', $userId) // Aquí buscará el 405
            ->select('u.*', 'e.Num_Ejidatario')
            ->first();

        if (!$usuario) {
            return response()->json(['ok' => false, 'error' => 'Usuario no encontrado'], 404);
        }

        // Unimos tu nombre: Ricardo Flores Gonzalez
        $nombreReal = trim("{$usuario->Nombres} {$usuario->Apellido_Paterno} {$usuario->Apellido_Materno}");

        return response()->json([
            'ok' => true,
            'usuario' => [
                'nombre'   => $nombreReal,
                'correo'   => $usuario->Correo,
                'telefono' => (string)$usuario->Telefono, // Lo mandamos como String para Swift
                'num_ejidatario' => (string)($usuario->Num_Ejidatario ?? 'Sin número'), // String para evitar error
                'foto_url' => $usuario->foto ? asset('storage/' . $usuario->foto) : null,
            ]
        ]);
    }
}