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
        // 🔑 SANCTUM MAGIA: Sacamos el usuario autenticado directamente de la petición protegida.
        // Como en tu BD usas la tabla 'usuario' manual, obtenemos el ID que Sanctum ya validó.
        $userId = $request->user()->Id_usuario ?? $request->user()->id;

        $usuario = DB::table('usuario as u')
            ->leftJoin('Ejidatario as e', 'u.Id_usuario', '=', 'e.Id_usuario')
            ->where('u.Id_usuario', '=', $userId)
            ->select('u.*', 'e.Num_Ejidatario')
            ->first();

        if (!$usuario) {
            return response()->json([
                'ok' => false,
                'error' => "El usuario con ID $userId no existe en la BD"
            ], 404);
        }

        $nombreCompleto = trim($usuario->Nombres . ' ' . $usuario->Apellido_Paterno . ' ' . $usuario->Apellido_Materno);

        // Validamos si guardaste la ruta del archivo o la URL completa
        $fotoUrl = null;
        if ($usuario->foto) {
            $fotoUrl = str_starts_with($usuario->foto, 'http') ? $usuario->foto : asset(Storage::url($usuario->foto));
        }

        return response()->json([
            'ok' => true,
            'usuario' => [
                'nombre' => $nombreCompleto ?: 'Nombre no disponible',
                'correo' => $usuario->Correo,
                'telefono' => (string)$usuario->Telefono,
                'num_ejidatario' => (string)($usuario->Num_Ejidatario ?? 'N/A'),
                'foto_url' => $fotoUrl,
            ]
        ]);
    }

    public function updatePerfilApi(Request $request)
    {
        // 🔑 Obtenemos el ID de forma segura con Sanctum
        $userId = $request->user()->Id_usuario ?? $request->user()->id;

        $request->validate([
            'nombre' => 'required|string|max:255',
            'Correo' => 'required|email|unique:usuario,Correo,' . $userId . ',Id_usuario',
            'Telefono' => 'required|numeric',
            'foto_perfil' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $partesNombre = explode(' ', trim($request->nombre));
        $nombres = $partesNombre[0];
        $paterno = isset($partesNombre[1]) ? $partesNombre[1] : '';
        $materno = isset($partesNombre[2]) ? implode(' ', array_slice($partesNombre, 2)) : '';

        $datosActualizar = [
            'Nombres' => $nombres,
            'Apellido_Paterno' => $paterno,
            'Apellido_Materno' => $materno,
            'Correo' => $request->Correo,
            'Telefono' => $request->Telefono,
            'Fecha_Modificado' => now(),
        ];

        if ($request->hasFile('foto_perfil')) {
            $file = $request->file('foto_perfil');

            // Eliminamos la foto anterior si existe para no llenar el servidor de basura
            $userRecord = DB::table('usuario')->where('Id_usuario', $userId)->first();
            if ($userRecord && $userRecord->foto) {
                Storage::disk('public')->delete($userRecord->foto);
            }

            // Guardamos consistentemente en la columna 'foto' usando el disco public
            $nombreArchivo = 'avatar_' . $userId . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('perfiles', $nombreArchivo, 'public');

            $datosActualizar['foto'] = $path; // ✅ Cambiado a 'foto' para que coincida con getPerfilApi
        }

        DB::table('usuario')->where('Id_usuario', $userId)->update($datosActualizar);

        return response()->json([
            'ok' => true,
            'message' => 'Campos de identidad y foto actualizados con éxito en el sistema.'
        ]);
    }
}