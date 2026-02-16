<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Mail\CodigoVerificacionMail;
use App\Mail\ResetPasswordMail;

class UsuariosController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('Usuario as u')
            ->leftJoin('Relacion_Ejidatario as re', 'u.Id_Usuario', '=', 're.Id_Usuario')
            ->select('u.*');
        if ($request->filled('nombre')) { $query->where('u.Nombres', 'like', '%' . $request->nombre . '%'); }
        if ($request->filled('apellido')) {
            $query->where(function ($q) use ($request) {
                $q->where('u.Apellido_Paterno', 'like', '%' . $request->apellido . '%')
                    ->orWhere('u.Apellido_Materno', 'like', '%' . $request->apellido . '%');
            });
        }
        $data = $query->paginate(10)->withQueryString();
        return view('cpanel.usuarios.indexUsuario', compact('data'));
    }

    public function create() { return view('cpanel.usuarios.formUsuario'); }

    public function store(Request $request)
    {
        $request->validate([
            'Usuario' => 'required|unique:Usuario,Usuario',
            'Correo' => 'required|email|unique:Usuario,Correo',
            'Contraseña' => ['required', 'confirmed', 'min:8', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
            'Telefono' => 'required|numeric',
            'Nombres' => 'required',
            'Apellido_Paterno' => 'required',
            'Apellido_Materno' => 'required',
        ]);

        // Buscamos el rol predeterminado
        $rol = DB::table('Roles')->where('Tipo_Rol', 'Ejidatario')->first(); // Cambiado a Ejidatario como default
        $rolId = $rol ? $rol->Id_Rol : DB::table('Roles')->insertGetId(['Tipo_Rol' => 'Ejidatario', 'Fecha_Creo' => now()]);

        $idUsuario = DB::table('Usuario')->insertGetId([
            'Nombres' => $request->Nombres,
            'Apellido_Paterno' => $request->Apellido_Paterno,
            'Apellido_Materno' => $request->Apellido_Materno,
            'Usuario' => $request->Usuario,
            'Correo' => $request->Correo,
            'Contraseña' => Hash::make($request->Contraseña),
            'Telefono' => $request->Telefono,
            'Fecha_Creo' => now(),
        ]);

        // Insertamos la relación SIN la columna Permisos (ya que ahora va en Roles)
        DB::table('Relacion_Ejidatario')->insert([
            'Id_Rol' => $rolId,
            'Id_Usuario' => $idUsuario,
            'Fecha_Creo' => now()
        ]);

        return redirect()->route('Usuarios.index')->with('success', 'Usuario registrado');
    }

    public function edit($id)
    {
        $fila = DB::table('Usuario')->where('Id_Usuario', $id)->first();
        abort_if(!$fila, 404);
        return view('cpanel.usuarios.editUsuario', compact('fila'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'Nombres' => 'required',
            'Apellido_Paterno' => 'required',
            'Apellido_Materno' => 'required',
            'Usuario' => 'required|unique:Usuario,Usuario,' . $id . ',Id_Usuario',
            'Correo' => 'required|email|unique:Usuario,Correo,' . $id . ',Id_Usuario',
            'Telefono' => 'required|numeric',
        ]);

        $data = [
            'Nombres' => $request->Nombres,
            'Apellido_Paterno' => $request->Apellido_Paterno,
            'Apellido_Materno' => $request->Apellido_Materno,
            'Usuario' => $request->Usuario,
            'Correo' => $request->Correo,
            'Telefono' => $request->Telefono,
            'Fecha_Modificado' => now()
        ];
        if ($request->filled('Contraseña')) { $data['Contraseña'] = Hash::make($request->Contraseña); }

        DB::table('Usuario')->where('Id_Usuario', $id)->update($data);
        return redirect()->route('Usuarios.index')->with('success', 'Actualizado');
    }

    public function destroy($id)
    {
        DB::table('Relacion_Ejidatario')->where('Id_Usuario', $id)->delete();
        DB::table('Usuario')->where('Id_Usuario', $id)->delete();
        return redirect()->route('Usuarios.index')->with('success', 'Eliminado');
    }

    public function login(Request $request)
    {
        $request->validate(['username' => 'required', 'password' => 'required']);

        $user = DB::table('Usuario')
            ->where('Usuario', $request->username)
            ->orWhere('Correo', $request->username)
            ->first();

        if (!$user || !Hash::check($request->password, $user->Contraseña)) {
            return back()->withErrors(['login' => 'Credenciales incorrectas']);
        }

        $acceso = DB::table('Relacion_Ejidatario as re')
            ->join('Roles as r', 're.Id_Rol', '=', 'r.Id_Rol')
            ->where('re.Id_Usuario', $user->Id_Usuario)
            ->select('r.Tipo_Rol', 'r.Permisos', 'r.Id_Rol')
            ->first();

        $code = rand(100000, 999999);

        session([
            '2fa_code' => $code,
            '2fa_user' => [
                'id' => $user->Id_Usuario,
                'username' => $user->Usuario,
                'email' => $user->Correo,
                'nombre_completo' => ($user->Nombres ?? '') . ' ' . ($user->Apellido_Paterno ?? ''),
                'id_rol' => $acceso ? $acceso->Id_Rol : null,
                'rol' => $acceso ? $acceso->Tipo_Rol : 'Invitado',
                'permisos' => ($acceso && $acceso->Permisos) ? json_decode($acceso->Permisos, true) : []
            ]
        ]);

        Mail::to($user->Correo)->send(new CodigoVerificacionMail(session('2fa_user')['nombre_completo'], $code));
        return redirect()->route('2fa.form');
    }

    public function buscar(Request $request)
    {
        $query = DB::table('Usuario as u')->select('u.*');
        if ($request->filled('nombre')) { $query->where('u.Nombres', 'like', '%' . $request->nombre . '%'); }
        if ($request->filled('apellido')) {
            $query->where(function ($q) use ($request) {
                $q->where('u.Apellido_Paterno', 'like', '%' . $request->apellido . '%')
                    ->orWhere('u.Apellido_Materno', 'like', '%' . $request->apellido . '%');
            });
        }
        $usuarios = $query->paginate(10)->withQueryString();
        return view('cpanel.usuarios.BuscarUsuarios', compact('usuarios'));
    }

    public function forgotForm() { return view('cpanel.login.forgot-password'); }
    public function sendResetCode(Request $request)
    {
        $request->validate(['username' => 'required']);
        $user = DB::table('Usuario')->where('Correo', $request->username)->orWhere('Usuario', $request->username)->first();
        if (!$user) { return back()->withErrors(['username' => 'No encontrado']); }
        $code = rand(100000, 999999);
        DB::table('password_resets')->updateOrInsert(['email' => $user->Correo], ['token' => $code, 'expires_at' => now()->addMinutes(10), 'created_at' => now()]);
        Mail::to($user->Correo)->send(new ResetPasswordMail($user->Nombres, $code));
        session(['reset_email' => $user->Correo]);
        return redirect()->route('password.reset.form')->with('success', 'Código enviado');
    }

    public function resetForm() { abort_if(!session('reset_email'), 403); return view('cpanel.login.reset-password'); }
    public function resetPassword(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
            'password' => ['required', 'confirmed', 'min:8', 'regex:/[A-Z]/', 'regex:/[0-9]/']
        ]);

        $record = DB::table('password_resets')
            ->where('email', session('reset_email'))
            ->where('token', $request->code)
            ->where('expires_at', '>=', now())
            ->first();

        if (!$record) {
            return back()->withErrors(['code' => 'Código inválido o expirado']);
        }

        DB::table('Usuario')
            ->where('Correo', session('reset_email'))
            ->update([
                'Contraseña' => Hash::make($request->password),
                'Fecha_Modificado' => now()
            ]);

        DB::table('password_resets')->where('email', session('reset_email'))->delete();

        session()->forget('reset_email');
        return redirect()->route('login')->with('success', '¡Contraseña actualizada con éxito! Ya puedes iniciar sesión.');
    }
}