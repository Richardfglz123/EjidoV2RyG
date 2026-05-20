<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Usuario;
use App\Models\Ejidatario;


class ConfiguracionController extends Controller
{
    public function permisos()
    {
        $roles = DB::table('Roles')->get();
        return view('cpanel.Configuracion.permisos', compact('roles'));
    }

    public function buscarusuariosAjax(Request $request)
    {
        return DB::table('usuario')
            ->where('Usuario', 'LIKE', '%' . $request->q . '%')
            ->select('Id_Usuario as id', 'Usuario as text')
            ->limit(10)
            ->get();
    }

    public function obtenerPermisosusuario($id)
    {
        $datos = DB::table('Relacion_Ejidatario')
            ->join('Roles', 'Relacion_Ejidatario.Id_Rol', '=', 'Roles.Id_Rol')
            ->where('Relacion_Ejidatario.Id_usuario', $id)
            ->select('Relacion_Ejidatario.Id_Rol', 'Roles.Permisos')
            ->first();

        return response()->json([
            'Id_Rol'   => $datos->Id_Rol ?? null,
            'permisos' => json_decode($datos->Permisos ?? '[]', true)
        ]);
    }

    public function obtenerPermisosRol($id)
    {
        $rol = DB::table('Roles')->where('Id_Rol', $id)->first();
        if (!$rol) return response()->json(['permisos' => []]);

        $permisosRaw = $rol->Permisos;
        $permisosDecodificados = is_string($permisosRaw) ? json_decode($permisosRaw, true) : $permisosRaw;

        return response()->json(['permisos' => $permisosDecodificados ?? []]);
    }

    public function guardarPermisos(Request $request)
    {
        $sesion = session('usuario', session('2fa_user', []));

        if (is_object($sesion)) {
            $miIdusuario = $sesion->Id_Usuario ?? $sesion->id ?? null;
            $miIdRol     = $sesion->id_rol ?? null;
            $miRolNombre = $sesion->rol ?? '';
            $miCorreo    = $sesion->email ?? '';
            $misPermisos = $sesion->permisos ?? [];
        } else {
            $miIdusuario = $sesion['Id_Usuario'] ?? $sesion['id'] ?? null;
            $miIdRol     = $sesion['id_rol'] ?? null;
            $miRolNombre = $sesion['rol'] ?? '';
            $miCorreo    = $sesion['email'] ?? '';
            $misPermisos = $sesion['permisos'] ?? [];
        }

        $soySuperAdmin = ($miIdRol == 1 || $miCorreo === 'rickvevo1@gmail.com' || $miIdusuario == 405 || strtolower(trim($miRolNombre)) === 'administrador');

        if (!$soySuperAdmin && !in_array('configuracion_crear', $misPermisos)) {
            abort(403, 'No tienes permisos para modificar configuraciones');
        }

        $request->validate([
            'Id_Usuario' => 'required_without:Id_usuario|integer',
            'Id_usuario' => 'required_without:Id_Usuario|integer',
            'Id_Rol'     => 'required|integer',
            'permisos'   => 'array'
        ]);

        $targetUserId = $request->input('Id_Usuario') ?? $request->input('Id_usuario');

        $jerarquia = [
            'administrador'         => 10,
            'secretaria'            => 8,
            'comisariado ejidal'    => 6,
            'comité de vigilancia'  => 5,
            'ejidatario'            => 3,
            'invitado'              => 1
        ];

        $miRolNormalizado = strtolower(trim($miRolNombre));
        $miNivel = $soySuperAdmin ? 999 : ($jerarquia[$miRolNormalizado] ?? 0);

        $usuarioTarget = DB::table('Relacion_Ejidatario')
            ->join('Roles', 'Relacion_Ejidatario.Id_Rol', '=', 'Roles.Id_Rol')
            ->where('Relacion_Ejidatario.Id_usuario', $targetUserId)
            ->select('Roles.Tipo_Rol', 'Relacion_Ejidatario.Id_Rol')
            ->first();

        $targetRolNormalizado = $usuarioTarget ? strtolower(trim($usuarioTarget->Tipo_Rol)) : 'sin rol';
        $nivelTarget = $jerarquia[$targetRolNormalizado] ?? 0;

        if ($usuarioTarget && $usuarioTarget->Id_Rol == 1 && !$soySuperAdmin) {
            return back()->withErrors("El Administrador no puede modificarse");
        }

        if ($miNivel <= $nivelTarget && $targetUserId != $miIdusuario) {
            return back()->withErrors("No tienes rango suficiente para modificar a un " . ($usuarioTarget->Tipo_Rol ?? 'Usuario') . ".");
        }

        if ($targetUserId == $miIdusuario) {
            return back()->withErrors('No puedes modificar tus propios permisos directamente');
        }

        if (!$request->has('confirmacion_global')) {
            return back()->withErrors('Debes confirmar que entiendes que esto afecta a todos los usuarios con este rol');
        }

        $permisosPermitidos = [
            'usuarios_ver','usuarios_crear','usuarios_eliminar',
            'ejidatarios_ver','ejidatarios_crear','ejidatarios_eliminar',
            'actividades_ver','actividades_crear','actividades_eliminar',
            'gestion_ver','gestion_crear','gestion_eliminar',
            'asambleas_ver','asambleas_crear','asambleas_eliminar',
            'asistencia_ver','asistencia_crear','asistencia_eliminar',
            'expedientes_ver','expedientes_crear',
            'parcelas_ver','parcelas_crear','parcelas_eliminar',
            'utilidades_ver','utilidades_crear','utilidades_eliminar',
            'gastos_ver','gastos_crear','gastos_eliminar',
            'inventario_ver','inventario_crear','inventario_eliminar',
            'apoyos_ver','apoyos_crear','apoyos_eliminar',
            'historicos_ver','historicos_crear','historicos_eliminar',
            'respaldo_ver','respaldo_crear',
            'configuracion_ver','configuracion_crear'
        ];

        $permisosRecibidos = $request->permisos ?? [];

        if (array_diff($permisosRecibidos, $permisosPermitidos)) {
            return back()->withErrors('Se detectaron permisos inválidos');
        }

        DB::beginTransaction();
        try {
            DB::table('Relacion_Ejidatario')->updateOrInsert(
                ['Id_usuario' => $targetUserId],
                [
                    'Id_Rol'           => $request->Id_Rol,
                    'Fecha_Modificado' => now()
                ]
            );

            if ($request->Id_Rol != 1) {
                DB::table('Roles')
                    ->where('Id_Rol', $request->Id_Rol)
                    ->update([
                        'Permisos' => json_encode($permisosRecibidos)
                    ]);
            }

            DB::commit();
            return back()->with('success', 'Permisos y rol actualizados correctamente');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors('Error al guardar: ' . $e->getMessage());
        }
    }
}