<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

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
        // 1. Recuperar el ID del usuario de forma segura
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
            return response()->json(['ok' => false, 'error' => 'No autorizado.'], 401);
        }

        // 2. Extraer y limpiar los valores vengan como vengan (mayúsculas o minúsculas de Swift)
        $correo = trim($request->input('Correo') ?? $request->input('correo') ?? '');
        $telefono = trim($request->input('Telefono') ?? $request->input('telefono') ?? '');
        $nombreCompleto = trim($request->input('nombre') ?? $request->input('Nombre') ?? '');

        // 3. Crear un validador manual para controlar el JSON de error en las APIs móviles
        $datosAValidar = [
            'nombre' => $nombreCompleto,
            'Telefono' => $telefono,
            'Correo' => $correo,
        ];

        $reglas = [
            'nombre' => 'required|string|max:255',
            'Telefono' => 'required',
            'Correo' => [
                'required',
                'email',
                Rule::unique('usuario', 'Correo')->ignore($userId, 'Id_usuario')
            ],
        ];

        $validator = Validator::make($datosAValidar, $reglas);

        // 4. 🚀 SI FALLA LA VALIDACIÓN: No redirigimos, devolvemos un JSON directo para que Swift no se congele
        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'error' => 'Error de validación en los datos.',
                'detalles' => $validator->errors() // 👈 Esto le dirá a Xcode exactamente qué pasó
            ], 422);
        }

        // 5. Procesar nombres para tu BD tradicional
        $partesNombre = explode(' ', $nombreCompleto);
        $nombres = $partesNombre[0];
        $paterno = isset($partesNombre[1]) ? $partesNombre[1] : '';
        $materno = isset($partesNombre[2]) ? implode(' ', array_slice($partesNombre, 2)) : '';

        $datosActualizar = [
            'Nombres' => $nombres,
            'Apellido_Paterno' => $paterno,
            'Apellido_Materno' => $materno,
            'Correo' => $correo,
            'Telefono' => $telefono,
            'Fecha_Modificado' => now(),
        ];

        // Subida de foto opcional
        if ($request->hasFile('foto_perfil')) {
            $file = $request->file('foto_perfil');
            $userRecord = DB::table('usuario')->where('Id_usuario', $userId)->first();
            if ($userRecord && $userRecord->foto) {
                Storage::disk('public')->delete($userRecord->foto);
            }
            $nombreArchivo = 'avatar_' . $userId . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('perfiles', $nombreArchivo, 'public');
            $datosActualizar['foto'] = $path;
        }

        // 6. Guardar cambios
        DB::table('usuario')->where('Id_usuario', $userId)->update($datosActualizar);

        return response()->json([
            'ok' => true,
            'message' => 'Perfil actualizado con éxito.'
        ]);
    }
}