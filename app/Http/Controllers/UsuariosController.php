<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Mail\CodigoVerificacionMail;
use App\Mail\ResetPasswordMail;
use App\Models\Usuario;

class UsuariosController extends Controller
{
    private function validationMessages()
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'unique'   => 'El :attribute ya se encuentra registrado en el sistema.',
            'email'    => 'El :attribute debe ser una dirección de correo válida.',
            'confirmed'=> 'La confirmación de la :attribute no coincide.',
            'min'      => 'El campo :attribute debe tener al menos :min caracteres.',
            'regex'    => 'El campo :attribute debe contener al menos una mayúscula y un número.',
            'numeric'  => 'El campo :attribute debe ser un valor numérico.',
            'digits'   => 'El campo :attribute debe tener :digits dígitos.',
        ];
    }

    private function validationAttributes()
    {
        return [
            'Usuario' => 'usuario',
            'Correo' => 'correo electrónico',
            'Contraseña' => 'contraseña',
            'Telefono' => 'teléfono',
            'Nombres' => 'nombre(s)',
            'Apellido_Paterno' => 'apellido paterno',
            'Apellido_Materno' => 'apellido materno',
            'code' => 'código de verificación',
            'username' => 'nombre de usuario o correo',
            'password' => 'contraseña'
        ];
    }

    private function checkPermission($permission)
    {
        $sesion = session('usuario', session('2fa_user', []));

        if (is_object($sesion)) {
            $permisos = $sesion->permisos ?? [];
            $rol = strtolower(trim($sesion->rol ?? ''));
        } else {
            $permisos = $sesion['permisos'] ?? [];
            $rol = strtolower(trim($sesion['rol'] ?? ''));
        }

        if ($rol === 'administrador') {
            return true;
        }

        if (!in_array($permission, $permisos)) {
            abort(403, 'No tienes permiso para realizar esta acción.');
        }
    }

    public function index(Request $request)
    {
        $query = DB::table('usuario as u')
            ->leftJoin('Relacion_Ejidatario as re', 'u.Id_Usuario', '=', 're.Id_usuario')
            ->leftJoin('Roles as r', 're.Id_Rol', '=', 'r.Id_Rol')
            ->select('u.*', 'r.Tipo_Rol as rol')
            ->whereNull('u.fecha_eliminado');

        if ($request->filled('nombre')) {
            $query->where('u.Nombres', 'like', '%' . $request->nombre . '%');
        }

        if ($request->filled('apellido')) {
            $query->where(function ($q) use ($request) {
                $q->where('u.Apellido_Paterno', 'like', '%' . $request->apellido . '%')
                    ->orWhere('u.Apellido_Materno', 'like', '%' . $request->apellido . '%');
            });
        }

        $data = $query->paginate(10)->withQueryString();
        return view('cpanel.usuarios.indexusuario', compact('data'));
    }

    public function create()
    {
        $this->checkPermission('usuarios_crear');
        return view('cpanel.usuarios.formusuario');
    }

    public function store(Request $request)
    {
        $this->checkPermission('usuarios_crear');

        $request->validate([
            'Usuario' => 'required|unique:usuario,Usuario',
            'Correo' => 'required|email|unique:usuario,Correo',
            'Contraseña' => ['required', 'confirmed', 'min:8', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
            'Telefono' => 'required|numeric',
            'Nombres' => 'required',
            'Apellido_Paterno' => 'required',
            'Apellido_Materno' => 'required',
        ], $this->validationMessages(), $this->validationAttributes());

        $rol = DB::table('Roles')->where('Tipo_Rol', 'Ejidatario')->first();
        $rolId = $rol ? $rol->Id_Rol : DB::table('Roles')->insertGetId(['Tipo_Rol' => 'Ejidatario', 'Fecha_Creo' => now()]);

        $idusuario = DB::table('usuario')->insertGetId([
            'Nombres' => $request->Nombres,
            'Apellido_Paterno' => $request->Apellido_Paterno,
            'Apellido_Materno' => $request->Apellido_Materno,
            'Usuario' => $request->Usuario,
            'Correo' => $request->Correo,
            'Contraseña' => Hash::make($request->Contraseña),
            'Telefono' => $request->Telefono,
            'Fecha_Creo' => now(),
        ]);

        DB::table('Relacion_Ejidatario')->insert([
            'Id_Rol' => $rolId,
            'Id_usuario' => $idusuario,
            'Fecha_Creo' => now()
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario registrado con éxito.');
    }

    public function edit($id)
    {
        $this->checkPermission('usuarios_editar');

        $fila = DB::table('usuario')->where('Id_Usuario', $id)->first();

        if (!$fila) {
            abort(404, "Usuario con ID {$id} no fue encontrado en la base de datos.");
        }

        return view('cpanel.usuarios.editusuario', compact('fila'));
    }

    public function update(Request $request, $id)
    {
        $this->checkPermission('usuarios_editar');

        $sesion = session('usuario', session('2fa_user'));
        $esAdmin = is_object($sesion)
            ? (strtolower($sesion->rol ?? '') === 'administrador')
            : (strtolower($sesion['rol'] ?? '') === 'administrador');

        $reglas = [
            'Nombres' => 'required',
            'Apellido_Paterno' => 'required',
            'Apellido_Materno' => 'required',
            'Telefono' => 'required|numeric',
        ];

        if ($esAdmin && $request->filled('Correo')) {
            $reglas['Correo'] = 'required|email|unique:usuario,Correo,' . $id . ',Id_Usuario';
        }

        $request->validate($reglas, $this->validationMessages(), $this->validationAttributes());

        $data = [
            'Nombres' => $request->Nombres,
            'Apellido_Paterno' => $request->Apellido_Paterno,
            'Apellido_Materno' => $request->Apellido_Materno,
            'Telefono' => $request->Telefono,
            'Fecha_Modificado' => now()
        ];

        if ($esAdmin && $request->filled('Correo')) {
            $data['Correo'] = $request->Correo;
        }

        DB::table('usuario')->where('Id_Usuario', $id)->update($data);

        return redirect('/admon/usuarios')->with('success', 'Usuario actualizado con éxito.');
    }

    public function destroy($id)
    {
        $this->checkPermission('usuarios_eliminar');

        $sesion = session('usuario', session('2fa_user'));

        $authId = is_object($sesion)
            ? ($sesion->Id_Usuario ?? $sesion->id ?? null)
            : ($sesion['Id_Usuario'] ?? $sesion['id'] ?? null);

        if ($authId == $id) {
            return back()->withErrors('No puedes eliminar tu propio usuario.');
        }

        $rolObjetivo = DB::table('Relacion_Ejidatario as re')
            ->join('Roles as r', 're.Id_Rol', '=', 'r.Id_Rol')
            ->where('re.Id_usuario', $id)
            ->value('r.Tipo_Rol');

        if (strtolower($rolObjetivo) === 'administrador') {
            return back()->withErrors('No puedes eliminar a otro administrador.');
        }

        DB::transaction(function () use ($id) {
            DB::table('Relacion_Ejidatario')->where('Id_usuario', $id)->delete();
            DB::table('usuario')->where('Id_Usuario', $id)->delete();
        });

        return back()->with('success', 'Usuario eliminado correctamente.');
    }

    // AUTENTICACIÓN

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ], $this->validationMessages(), $this->validationAttributes());

        $user = DB::table('usuario')
            ->where('Usuario', $request->username)
            ->orWhere('Correo', $request->username)
            ->first();

        if (!$user || !Hash::check($request->password, $user->Contraseña)) {
            return back()
                ->withInput($request->only('username'))
                ->withErrors(['login' => 'Las credenciales introducidas no coinciden con nuestros registros.']);
        }

        $userId = $user->Id_Usuario;
        $usernameStr = $user->Usuario;

        $acceso = DB::table('Relacion_Ejidatario as re')
            ->leftJoin('Roles as r', 're.Id_Rol', '=', 'r.Id_Rol')
            ->where('re.Id_usuario', $userId)
            ->select('r.Tipo_Rol', 'r.Permisos', 'r.Id_Rol')
            ->first();

        $code = rand(100000, 999999);

        $nombreCompleto = trim(($user->Nombres ?? '') . ' ' . ($user->Apellido_Paterno ?? ''));
        if (empty($nombreCompleto)) { $nombreCompleto = $usernameStr; }

        session([
            '2fa_code' => $code,
            '2fa_user' => [
                'Id_Usuario'      => $userId,
                'id'              => $userId,
                'username'        => $usernameStr,
                'email'           => $user->Correo,
                'foto'            => $user->foto ?? null,
                'nombre_completo' => $nombreCompleto,
                'id_rol'          => $acceso ? $acceso->Id_Rol : null,
                'rol'             => $acceso ? ($acceso->Tipo_Rol ?? 'Usuario') : 'Usuario',
                'permisos'        => ($acceso && $acceso->Permisos) ? json_decode($acceso->Permisos, true) : []
            ]
        ]);

        session()->save();

        if (!empty($user->Correo)) {
            try {
                Mail::to($user->Correo)->send(new CodigoVerificacionMail($nombreCompleto, $code));
            } catch (\Exception $e) {
                \Log::error("Error enviando correo 2FA: " . $e->getMessage());
            }
        }

        return redirect()->route('2fa.form');
    }

    public function buscar(Request $request)
    {
        $query = DB::table('usuario as u')->select('u.*')->whereNull('u.fecha_eliminado');
        if ($request->filled('nombre')) { $query->where('u.Nombres', 'like', '%' . $request->nombre . '%'); }
        if ($request->filled('apellido')) {
            $query->where(function ($q) use ($request) {
                $q->where('u.Apellido_Paterno', 'like', '%' . $request->apellido . '%')
                    ->orWhere('u.Apellido_Materno', 'like', '%' . $request->apellido . '%');
            });
        }
        $usuarios = $query->paginate(10)->withQueryString();
        return view('cpanel.usuarios.Buscarusuarios', compact('usuarios'));
    }

    public function forgotForm() { return view('cpanel.login.forgot-password'); }

    public function sendResetCode(Request $request)
    {
        $request->validate(['username' => 'required'], $this->validationMessages(), $this->validationAttributes());
        $user = DB::table('usuario')->where('Correo', $request->username)->orWhere('Usuario', $request->username)->first();
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
        ], $this->validationMessages(), $this->validationAttributes());

        $record = DB::table('password_resets')
            ->where('email', session('reset_email'))
            ->where('token', $request->code)
            ->where('expires_at', '>=', now())
            ->first();

        if (!$record) { return back()->withErrors(['code' => 'Código inválido o expirado']); }

        DB::table('usuario')->where('Correo', session('reset_email'))->update([
            'Contraseña' => Hash::make($request->password),
            'Fecha_Modificado' => now()
        ]);

        DB::table('password_resets')->where('email', session('reset_email'))->delete();
        session()->forget('reset_email');
        return redirect()->route('login')->with('success', '¡Contraseña actualizada con éxito!');
    }

    //MÉTODOS ENDPOINTS API (PARA IOS)


    public function apiIndex() {
        $usuarios = DB::table('usuario')->whereNull('fecha_eliminado')->get();
        return response()->json($usuarios, 200);
    }

    public function apiStore(Request $request) {
        $idusuario = DB::table('usuario')->insertGetId([
            'Nombres'          => $request->input('Nombres'),
            'Apellido_Paterno' => $request->input('Apellido_Paterno'),
            'Apellido_Materno' => $request->input('Apellido_Materno'),
            'Usuario'          => $request->input('Usuario'),
            'Correo'           => $request->input('Correo'),
            'Contraseña'       => Hash::make($request->input('Contraseña', 'Ejido123*')),
            'Telefono'         => $request->input('Telefono'),
            'Fecha_Creo'       => now(),
        ]);

        $rol = DB::table('Roles')->where('Tipo_Rol', 'Ejidatario')->first();
        $rolId = $rol ? $rol->Id_Rol : 1;

        DB::table('Relacion_Ejidatario')->insert([
            'Id_Rol'     => $rolId,
            'Id_usuario' => $idusuario,
            'Fecha_Creo' => now()
        ]);

        return response()->json(['success' => true, 'id' => $idusuario], 201);
    }

    public function apiUpdate(Request $request, $id) {
        $data = [
            'Nombres'          => $request->input('Nombres'),
            'Apellido_Paterno' => $request->input('Apellido_Paterno'),
            'Apellido_Materno' => $request->input('Apellido_Materno'),
            'Telefono'         => $request->input('Telefono'),
            'Fecha_Modificado' => now()
        ];

        if ($request->filled('Correo')) {
            $data['Correo'] = $request->input('Correo');
        }
        if ($request->filled('Contraseña')) {
            $data['Contraseña'] = Hash::make($request->input('Contraseña'));
        }

        $actualizado = DB::table('usuario')->where('Id_Usuario', $id)->update($data);

        return response()->json(['success' => true], 200);
    }

    public function apiDestroy($id) {
        DB::transaction(function () use ($id) {
            DB::table('Relacion_Ejidatario')->where('Id_usuario', $id)->delete();
            DB::table('usuario')->where('Id_Usuario', $id)->delete();
        });

        return response()->json(['success' => true], 200);
    }
}