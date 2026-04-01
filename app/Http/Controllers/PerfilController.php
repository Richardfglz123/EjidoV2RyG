<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
                ->get(); // Traemos todas con ->get()
        }

        return view('cpanel.perfil.indexperfil', compact('usuario', 'parcelas'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'Usuario'    => 'required|unique:Usuario,Usuario,' . session('usuario.id') . ',Id_Usuario',
            'Correo'     => 'required|email|unique:Usuario,Correo,' . session('usuario.id') . ',Id_Usuario',
            'Telefono'   => 'required|numeric',
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

        if ($request->filled('Contraseña')) {
            $data['Contraseña'] = Hash::make($request->Contraseña);
        }

        DB::table('Usuario')
            ->where('Usuario.Id_Usuario', session('usuario.id'))
            ->update($data);

        return back()->with('success', 'Perfil actualizado correctamente');
    }
}
