<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Mail\CodigoVerificacionMail;

class UsuariosController extends Controller
{
    /* Listado */
    public function index()
    {
        $data = DB::table('Usuario as u')
            ->leftJoin('Relacion_Ejidatario as re', 'u.Id_Usuario', '=', 're.Id_Usuario')
            ->select('u.*')
            ->paginate(10);

        return view('cpanel.usuarios.indexUsuario', compact('data'));
    }

    /* Crear usuarios */
    public function create()
    {
        return view('cpanel.usuarios.formUsuario');
    }

    /* Guardar usuarios */
    public function store(Request $request)
    {
        $request->validate([
            'Usuario'           => 'required|unique:Usuario,Usuario',
            'Correo'            => 'required|email|unique:Usuario,Correo',
            'Contraseña'        => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
            ],
            'Telefono'          => 'required|numeric',
            'Nombres'           => 'required',
            'Apellido_Paterno'  => 'required',
            'Apellido_Materno'  => 'required',
        ]);

        $rol = DB::table('Roles')->first();

        if (!$rol) {
            $rolId = DB::table('Roles')->insertGetId([
                'Tipo_Rol' => 'Usuario',
                'Fecha_Creo' => now()
            ]);
        } else {
            $rolId = $rol->Id_Rol;
        }

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
            'Id_Rol'     => $rolId,
            'Id_Usuario' => $idUsuario,
            'Fecha_Creo' => now()
        ]);

        return redirect()->route('Usuarios.index')->with('success', 'Usuario registrado correctamente');
    }

    /* Editar */
    public function edit($id)
    {
        $fila = DB::table('Usuario')->where('Id_Usuario', $id)->first();
        abort_if(!$fila, 404);
        return view('cpanel.usuarios.editUsuario', compact('fila'));
    }

    /* Actualizar */
    public function update(Request $request, $id)
    {
        $request->validate([
            'Nombres'           => 'required',
            'Apellido_Paterno'  => 'required',
            'Apellido_Materno'  => 'required',
            'Usuario'           => 'required|unique:Usuario,Usuario,' . $id . ',Id_Usuario',
            'Correo'            => 'required|email|unique:Usuario,Correo,' . $id . ',Id_Usuario',
            'Telefono'          => 'required|numeric',
            'Contraseña'        => [
                'nullable',
                'confirmed',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
            ],
        ]);

        $data = [
            'Nombres'           => $request->Nombres,
            'Apellido_Paterno'  => $request->Apellido_Paterno,
            'Apellido_Materno'  => $request->Apellido_Materno,
            'Usuario'           => $request->Usuario,
            'Correo'            => $request->Correo,
            'Telefono'          => $request->Telefono,
            'Fecha_Modificado'  => now()
        ];

        if ($request->filled('Contraseña')) {
            $data['Contraseña'] = Hash::make($request->Contraseña);
        }

        DB::table('Usuario')->where('Id_Usuario', $id)->update($data);

        return redirect()->route('Usuarios.index')->with('success', 'Usuario actualizado correctamente');
    }

    /* Borrar */
    public function destroy($id)
    {
        DB::table('Relacion_Ejidatario')->where('Id_Usuario', $id)->delete();
        DB::table('Usuario')->where('Id_Usuario', $id)->delete();

        return redirect()->route('Usuarios.index')->with('success', 'Usuario eliminado');
    }

    /* Login con 2FA */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $user = DB::table('Usuario')
            ->where('Usuario', $request->username)
            ->orWhere('Correo', $request->username)
            ->first();

        if (!$user || !Hash::check($request->password, $user->Contraseña)) {
            return back()->withErrors(['login' => 'Credenciales incorrectas']);
        }

        $code = rand(100000, 999999);

        session([
            '2fa_code' => $code,
            '2fa_user' => [
                'id' => $user->Id_Usuario,
                'username' => $user->Usuario,
                'email' => $user->Correo,
                'nombre_completo' => $user->Nombres . ' ' . $user->Apellido_Paterno
            ]
        ]);

        Mail::to($user->Correo)
            ->send(new CodigoVerificacionMail(session('2fa_user')['nombre_completo'], $code));

        return redirect()->route('2fa.form');
    }

    /* Buscar usuarios */
    public function buscar(Request $request)
    {
        $query = DB::table('Usuario as u')->select('u.*');

        if ($request->filled('nombre')) {
            $query->where('u.Nombres', 'like', '%' . $request->nombre . '%');
        }

        if ($request->filled('apellido')) {
            $query->where(function ($q) use ($request) {
                $q->where('u.Apellido_Paterno', 'like', '%' . $request->apellido . '%')
                    ->orWhere('u.Apellido_Materno', 'like', '%' . $request->apellido . '%');
            });
        }

        $usuarios = $query->paginate(10)->withQueryString();

        return view('cpanel.usuarios.BuscarUsuarios', compact('usuarios'));
    }
}
