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
        // 🔑 FORMA ULTRA-SEGURA PARA MODELOS PERSONALIZADOS:
        // Si $request->user() no tiene la propiedad, la extraemos del token autenticado por Sanctum
        $tokenUser = $request->user();

        if ($tokenUser) {
            $userId = $tokenUser->Id_usuario ?? $tokenUser->id ?? null;
        } else {
            // Fallback: Intentar obtenerlo mediante el ID del token directamente
            $userId = auth('sanctum')->id();
        }

        // Si aún así no se encuentra (por configuración del Auth provider)
        if (!$userId) {
            return response()->json([
                'ok' => false,
                'error' => "Sanctum no pudo recuperar el ID del usuario autenticado."
            ], 401);
        }

        // Continuamos con tu consulta...
        $usuario = DB::table('usuario as u')
            ->leftJoin('Ejidatario as e', 'u.Id_usuario', '=', 'e.Id_usuario')
            ->where('u.Id_usuario', '=', $userId)
            ->select('u.*', 'e.Num_Ejidatario')
            ->first();

        if (!$usuario) {
            return response()->json([
                'ok' => false,
                'error' => "El usuario con ID $userId no existe en la tabla de la BD"
            ], 404);
        }

        // Convertimos a array para mitigar problemas de mayúsculas/minúsculas del driver de la BD
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