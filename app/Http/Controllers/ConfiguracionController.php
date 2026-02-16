<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConfiguracionController extends Controller
{
    public function permisos()
    {
        $roles = DB::table('Roles')->get();
        return view('cpanel.Configuracion.permisos', compact('roles'));
    }

    public function buscarUsuariosAjax(Request $request)
    {
        $term = $request->get('q');
        if (!$term) {
            return response()->json([]);
        }

        $usuarios = DB::table('Usuario')
            ->where('Usuario', 'LIKE', '%' . $term . '%')
            ->select('Id_Usuario as id', 'Usuario as text') // Formateamos para Select2
            ->limit(10)
            ->get();

        return response()->json($usuarios);
    }

    public function obtenerPermisosUsuario($id)
    {
        $datos = DB::table('Relacion_Ejidatario')
            ->join('Roles', 'Relacion_Ejidatario.Id_Rol', '=', 'Roles.Id_Rol')
            ->where('Relacion_Ejidatario.Id_Usuario', $id)
            ->select('Relacion_Ejidatario.Id_Rol', 'Roles.Permisos')
            ->first();

        if (!$datos) {
            return response()->json(['Id_Rol' => null, 'permisos' => []]);
        }

        return response()->json([
            'Id_Rol'   => $datos->Id_Rol,
            'permisos' => json_decode($datos->Permisos, true) ?? []
        ]);
    }

    public function guardarPermisos(Request $request)
    {
        $request->validate([
            'Id_Rol' => 'required',
            'Id_Usuario' => 'required' // Aseguramos que se seleccionó un usuario
        ]);

        $permisos = $request->input('permisos', []);

        try {
            DB::beginTransaction();
            DB::table('Relacion_Ejidatario')
                ->where('Id_Usuario', $request->Id_Usuario)
                ->update(['Id_Rol' => $request->Id_Rol]);
            DB::table('Roles')
                ->where('Id_Rol', $request->Id_Rol)
                ->update([
                    'Permisos' => json_encode($permisos),
                    'Fecha_Modificado' => now()
                ]);

            DB::commit();

            if (session('usuario.id') == $request->Id_Usuario) {
                session(['usuario.permisos' => $permisos, 'usuario.id_rol' => $request->Id_Rol]);
            }

            return back()->with('success', 'Configuración guardada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
    }
}