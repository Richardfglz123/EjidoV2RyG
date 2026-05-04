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
        $usuario = DB::table('Usuario')
            ->leftJoin('Ejidatario', 'Usuario.Id_Usuario', '=', 'Ejidatario.Id_Usuario')
            ->where('Usuario.Id_Usuario', session('usuario.id'))
            ->select('Usuario.*', 'Ejidatario.Id_Ejidatario', 'Ejidatario.Num_Ejidatario')
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
            'Usuario'    => 'required|unique:Usuario,Usuario,' . $userId . ',Id_Usuario',
            'Correo'     => 'required|email|unique:Usuario,Correo,' . $userId . ',Id_Usuario',
            'Telefono'   => 'required|numeric',
            'foto'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'Contraseña' => [
                'nullable',
                'confirmed',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
            ],
        ]);

        $data = [
            'Usuario'          => $request->Usuario,
            'Correo'           => $request->Correo,
            'Telefono'         => $request->Telefono,
            'Fecha_Modificado' => now(),
        ];

        if ($request->hasFile('foto')) {
            $userRecord = DB::table('Usuario')->where('Id_Usuario', $userId)->first();

            if ($userRecord && isset($userRecord->foto) && $userRecord->foto) {
                Storage::disk('public')->delete($userRecord->foto);
            }

            $path = $request->file('foto')->store('perfiles', 'public');
            $data['foto'] = $path;

            $request->session()->put('usuario.foto', $path);
            $request->session()->save();
        }

        if ($request->filled('Contraseña')) {
            $data['Contraseña'] = Hash::make($request->Contraseña);
        }

        DB::table('Usuario')
            ->where('Id_Usuario', $userId)
            ->update($data);

        session(['usuario.nombre_usuario' => $request->Usuario]);

        return back()->with('success', 'Perfil actualizado correctamente');
    }
}