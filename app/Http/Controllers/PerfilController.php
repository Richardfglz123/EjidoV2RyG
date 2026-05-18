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

        // 1. Validación sin límite de tamaño (quitamos max:2048)
        $request->validate([
            'usuario'    => 'required|unique:usuario,usuario,' . $userId . ',Id_usuario',
            'Correo'     => 'required|email|unique:usuario,Correo,' . $userId . ',Id_usuario',
            'Telefono'   => 'required|numeric',
            'foto'       => 'nullable|image|mimes:jpg,jpeg,png', // Sin límite de peso
            'Contraseña' => [
                'nullable',
                'confirmed',
                'min:8',
            ],
        ]);

        $data = [
            'usuario'          => $request->usuario,
            'Correo'           => $request->Correo,
            'Telefono'         => $request->Telefono,
            'Fecha_Modificado' => now(),
        ];

        // 2. Manejo de la Foto
        if ($request->hasFile('foto')) {
            $userRecord = DB::table('usuario')->where('Id_usuario', $userId)->first();

            // Borrar foto física anterior para no llenar el servidor de basura
            if ($userRecord && !empty($userRecord->foto)) {
                Storage::disk('public')->delete($userRecord->foto);
            }

            // Guardar con un nombre único basado en tiempo para evitar conflictos de caché
            $file = $request->file('foto');
            $nombreFoto = time() . '_' . $file->getClientOriginalName();

            // Guardamos en la carpeta 'perfiles' dentro del disco 'public'
            $path = $file->storeAs('perfiles', $nombreFoto, 'public');

            $data['foto'] = $path;

            // Actualizar la sesión para que el cambio sea instantáneo en la interfaz
            session(['usuario.foto' => $path]);
        }

        // 3. Manejo de Contraseña
        if ($request->filled('Contraseña')) {
            $data['Contraseña'] = Hash::make($request->Contraseña);
        }

        // 4. Actualización en Base de Datos
        DB::table('usuario')
            ->where('Id_usuario', $userId)
            ->update($data);

        session(['usuario.nombre_completo' => $request->usuario]);
        $request->session()->save();

        return back()->with('success', 'Perfil actualizado correctamente');
    }

    public function getPerfilApi(Request $request)
    {
        // Obtenemos la cabecera Authorization
        $authHeader = $request->header('Authorization');

        if (!$authHeader) {
            return response()->json(['ok' => false, 'error' => 'Cabecera Authorization ausente'], 401);
        }

        // Limpiamos la palabra 'Bearer ' y removemos cualquier espacio en blanco o salto de línea residual
        $cleanId = trim(str_replace('Bearer ', '', $authHeader));

        // Forzamos que sea un número entero para evitar que la base de datos se confunda o devuelva registros incorrectos
        $userId = intval($cleanId);

        // Si el iPhone mandó un ID vacío, nulo o que tras la conversión no es un número válido válido mayor a 0
        if ($userId <= 0) {
            return response()->json(['ok' => false, 'error' => 'ID de usuario no válido o no recibido'], 401);
        }

        $usuario = DB::table('usuario as u')
            ->leftJoin('Ejidatario as e', 'u.Id_usuario', '=', 'e.Id_usuario')
            ->where('u.Id_usuario', '=', $userId) // <--- FILTRO ESTRICTO SEGURO
            ->select('u.*', 'e.Num_Ejidatario')
            ->first();

        if (!$usuario) {
            return response()->json([
                'ok' => false,
                'error' => "El usuario con ID $userId no existe en la BD"
            ], 404);
        }

        $nombreCompleto = trim($usuario->Nombres . ' ' . $usuario->Apellido_Paterno . ' ' . $usuario->Apellido_Materno);

        // Usamos Storage::url() que es más consistente para resolver las rutas públicas configuradas en producción
        $fotoUrl = $usuario->foto ? asset(Storage::url($usuario->foto)) : null;

        return response()->json([
            'ok' => true,
            'usuario' => [
                'nombre'         => $nombreCompleto ?: 'Nombre no disponible',
                'correo'         => $usuario->Correo,
                'telefono'       => (string)$usuario->Telefono,
                'num_ejidatario' => (string)($usuario->Num_Ejidatario ?? 'N/A'),
                'foto_url'       => $fotoUrl,
            ]
        ]);
    }
}