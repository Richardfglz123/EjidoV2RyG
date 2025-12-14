<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PerfilController extends Controller
{
    public function index()
    {
        if (!session()->has('user_id')) {
            abort(403);
        }

        $usuario = DB::table('Usuario as u')
            ->leftJoin('Relacion_Ejidatario as re', 'u.Id_Usuario', '=', 're.Id_Usuario')
            ->leftJoin('Roles as r', 're.Id_Rol', '=', 'r.Id_Rol')
            ->leftJoin('Ejidatario as e', 'u.Id_Usuario', '=', 'e.Id_Usuario')
            ->select(
                'u.*',
                'r.Tipo_Rol',
                'e.Id_Ejidatario',
                'e.Num_Ejidatario',
                'e.No_Parcela'
            )
            ->where('u.Id_Usuario', session('user_id'))
            ->first();

        abort_if(!$usuario, 404);

        return view('cpanel.perfil.indexperfil', compact('usuario'));
    }

    public function update(Request $request)
    {
        if (!session()->has('user_id')) {
            abort(403);
        }

        $request->validate([
            'Telefono'   => 'required',
            'Usuario'    => 'required|unique:Usuario,Usuario,' . session('user_id') . ',Id_Usuario',
            'Correo'     => 'required|email|unique:Usuario,Correo,' . session('user_id') . ',Id_Usuario',
            'Contraseña' => 'nullable|min:6|confirmed',
        ]);

        $data = [
            'Telefono' => $request->Telefono,
            'Usuario'  => $request->Usuario,
            'Correo'   => $request->Correo,
        ];

        if ($request->filled('Contraseña')) {
            $data['Contraseña'] = Hash::make($request->Contraseña);
        }

        DB::table('Usuario')
            ->where('Id_Usuario', session('user_id'))
            ->update($data);

        return back()->with('success', 'Perfil actualizado correctamente');
    }
}
