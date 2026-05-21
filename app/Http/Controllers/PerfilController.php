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
        // 1. DEPURACIÓN: Registro de qué está llegando al servidor
        // Revisa storage/logs/laravel.log después de intentar guardar
        \Log::info('Intento de update API', [
            'headers' => $request->headers->all(),
            'has_bearer' => $request->hasHeader('Authorization'),
            'user_id_sanctum' => $request->user() ? $request->user()->id : 'null'
        ]);

        // 2. RECUPERACIÓN DE USUARIO (Versión Robusta)
        $userId = null;

        // Intento A: Vía Sanctum estándar
        if ($request->user()) {
            $userId = $request->user()->Id_usuario ?? $request->user()->id;
        }

        // Intento B: Vía Token manual (Si Sanctum falla por configuración de modelo)
        if (!$userId) {
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

        // Si aún no tenemos ID, devolvemos el error con detalle
        if (!$userId) {
            return response()->json([
                'ok' => false,
                'error' => 'No autorizado: Token inválido o ausente.',
                'debug' => 'Revisa que envíes el Header "Authorization: Bearer TU_TOKEN"'
            ], 401);
        }

        // 3. EXTRACCIÓN DE DATOS (Más segura)
        // Usamos $request->get() para capturar datos tanto de JSON como de Form-Data
        $correo = trim($request->get('Correo') ?? $request->get('correo') ?? '');
        $telefono = trim($request->get('Telefono') ?? $request->get('telefono') ?? '');
        $nombreCompleto = trim($request->get('nombre') ?? $request->get('Nombre') ?? '');

        // 4. VALIDACIÓN
        $validator = Validator::make([
            'nombre' => $nombreCompleto,
            'Telefono' => $telefono,
            'Correo' => $correo,
        ], [
            'nombre' => 'required|string|max:255',
            'Telefono' => 'required|numeric',
            'Correo' => ['required', 'email', Rule::unique('usuario', 'Correo')->ignore($userId, 'Id_usuario')],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'error' => 'Datos inválidos.',
                'detalles' => $validator->errors()
            ], 422);
        }

        // 5. PROCESAMIENTO
        $partesNombre = explode(' ', $nombreCompleto);
        $datosActualizar = [
            'Nombres' => $partesNombre[0],
            'Apellido_Paterno' => $partesNombre[1] ?? '',
            'Apellido_Materno' => isset($partesNombre[2]) ? implode(' ', array_slice($partesNombre, 2)) : '',
            'Correo' => $correo,
            'Telefono' => $telefono,
            'Fecha_Modificado' => now(),
        ];

        // Subida de imagen
        if ($request->hasFile('foto_perfil')) {
            $file = $request->file('foto_perfil');
            // Eliminar anterior
            $userRecord = DB::table('usuario')->where('Id_usuario', $userId)->first();
            if ($userRecord && !empty($userRecord->foto)) {
                Storage::disk('public')->delete($userRecord->foto);
            }
            $path = $file->store('perfiles', 'public');
            $datosActualizar['foto'] = $path;
        }

        // 6. ACTUALIZACIÓN
        $actualizado = DB::table('usuario')->where('Id_usuario', $userId)->update($datosActualizar);

        return response()->json([
            'ok' => true,
            'message' => 'Perfil actualizado correctamente.',
            'debug_actualizado' => $actualizado // Para saber si realmente cambió algo en la BD
        ]);
    }
}