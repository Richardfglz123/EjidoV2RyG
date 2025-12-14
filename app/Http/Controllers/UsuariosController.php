<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Mail\CodigoVerificacionMail;

class UsuariosController extends Controller
{
    /* =========================
       LISTADO DE USUARIOS
    ========================= */
    public function index()
    {
        $data = DB::table('Usuario as u')
            ->leftJoin('Relacion_Ejidatario as re', 'u.Id_Usuario', '=', 're.Id_Usuario')
            ->leftJoin('Roles as r', 're.Id_Rol', '=', 'r.Id_Rol')
            ->select(
                'u.*',
                'r.Tipo_Rol'
            )
            ->get();

        return view('cpanel.usuarios.indexUsuario', compact('data'));
    }

    /* =========================
       FORM CREAR USUARIO
    ========================= */
    public function create()
    {
        $roles = DB::table('Roles')->get();
        return view('cpanel.usuarios.CrearUsuario', compact('roles'));
    }

    /* =========================
       GUARDAR USUARIO
    ========================= */
    public function store(Request $request)
    {
        $request->validate([
            'Usuario'           => 'required|unique:Usuario,Usuario',
            'Correo'            => 'required|email|unique:Usuario,Correo',
            'Contraseña'        => 'required|min:6|confirmed',
            'Telefono'          => 'required',
            'Nombres'           => 'required',
            'Apellido_Paterno'  => 'required',
            'Apellido_Materno'  => 'required',
            'Id_Rol'            => 'required|exists:Roles,Id_Rol',
        ]);

        $idUsuario = DB::table('Usuario')->insertGetId([
            'Nombres'           => $request->Nombres,
            'Apellido_Paterno'  => $request->Apellido_Paterno,
            'Apellido_Materno'  => $request->Apellido_Materno,
            'Usuario'           => $request->Usuario,
            'Correo'            => $request->Correo,
            'Contraseña'        => Hash::make($request->Contraseña),
            'Telefono'          => $request->Telefono,
            'Fecha_Creo'        => now(),
        ]);

        DB::table('Relacion_Ejidatario')->insert([
            'Id_Usuario' => $idUsuario,
            'Id_Rol'     => $request->Id_Rol,
            'Fecha_Creo' => now()
        ]);

        return redirect()->route('Usuarios.index')
            ->with('success', 'Usuario registrado correctamente');
    }

    /* =========================
       FORM EDITAR USUARIO
    ========================= */
    public function edit($id)
    {
        $fila = DB::table('Usuario as u')
            ->leftJoin('Relacion_Ejidatario as re', 'u.Id_Usuario', '=', 're.Id_Usuario')
            ->select(
                'u.*',
                're.Id_Rol'
            )
            ->where('u.Id_Usuario', $id)
            ->first();

        abort_if(!$fila, 404);

        $roles = DB::table('Roles')->get();

        return view('cpanel.usuarios.editUsuario', compact('fila', 'roles'));
    }

    /* =========================
       ACTUALIZAR USUARIO
    ========================= */
    public function update(Request $request, $id)
    {
        $request->validate([
            'Nombres'           => 'required',
            'Apellido_Paterno'  => 'required',
            'Apellido_Materno'  => 'required',
            'Usuario'           => 'required|unique:Usuario,Usuario,' . $id . ',Id_Usuario',
            'Correo'            => 'required|email|unique:Usuario,Correo,' . $id . ',Id_Usuario',
            'Telefono'          => 'required',
            'Id_Rol'            => 'required|exists:Roles,Id_Rol',
            'Contraseña'        => 'nullable|min:6|confirmed',
        ]);

        $data = [
            'Nombres'           => $request->Nombres,
            'Apellido_Paterno'  => $request->Apellido_Paterno,
            'Apellido_Materno'  => $request->Apellido_Materno,
            'Usuario'           => $request->Usuario,
            'Correo'            => $request->Correo,
            'Telefono'          => $request->Telefono,
        ];

        if ($request->filled('Contraseña')) {
            $data['Contraseña'] = Hash::make($request->Contraseña);
        }

        DB::table('Usuario')
            ->where('Id_Usuario', $id)
            ->update($data);

        DB::table('Relacion_Ejidatario')
            ->where('Id_Usuario', $id)
            ->update([
                'Id_Rol' => $request->Id_Rol
            ]);

        return redirect()->route('Usuarios.index')
            ->with('success', 'Usuario actualizado correctamente');
    }

    /* =========================
       ELIMINAR USUARIO
    ========================= */
    public function destroy($id)
    {
        DB::table('Relacion_Ejidatario')->where('Id_Usuario', $id)->delete();
        DB::table('Usuario')->where('Id_Usuario', $id)->delete();

        return redirect()->route('Usuarios.index')
            ->with('success', 'Usuario eliminado');
    }

    /* =========================
       LOGIN + 2FA
    ========================= */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $user = DB::table('Usuario as u')
            ->leftJoin('Relacion_Ejidatario as re', 'u.Id_Usuario', '=', 're.Id_Usuario')
            ->leftJoin('Roles as r', 're.Id_Rol', '=', 'r.Id_Rol')
            ->where(function ($q) use ($request) {
                $q->where('u.Usuario', $request->username)
                    ->orWhere('u.Correo', $request->username);
            })
            ->select(
                'u.*',
                'r.Tipo_Rol'
            )
            ->first();

        if (!$user || !Hash::check($request->password, $user->Contraseña)) {
            return back()->withErrors(['login' => 'Credenciales incorrectas']);
        }

        $code = rand(100000, 999999);

        session([
            '2fa_code' => $code,
            '2fa_user' => [
                'id'               => $user->Id_Usuario,
                'username'         => $user->Usuario,
                'email'            => $user->Correo,
                'nombre_completo'  => trim(
                    $user->Nombres . ' ' .
                    $user->Apellido_Paterno . ' ' .
                    $user->Apellido_Materno
                ),
                'rol'              => $user->Tipo_Rol
            ]
        ]);

        Mail::to($user->Correo)->send(new CodigoVerificacionMail(session('2fa_user')['nombre_completo'], $code));

        return redirect()->route('2fa.form');
    }
}
