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
        return DB::table('Usuario')
            ->where('Usuario', 'LIKE', '%' . $request->q . '%')
            ->select('Id_Usuario as id', 'Usuario as text')
            ->limit(10)
            ->get();
    }

    public function obtenerPermisosUsuario($id)
    {
        $datos = DB::table('Relacion_Ejidatario')
            ->join('Roles', 'Relacion_Ejidatario.Id_Rol', '=', 'Roles.Id_Rol')
            ->where('Relacion_Ejidatario.Id_Usuario', $id)
            ->select('Relacion_Ejidatario.Id_Rol', 'Roles.Permisos')
            ->first();

        return response()->json([
            'Id_Rol' => $datos->Id_Rol ?? null,
            'permisos' => json_decode($datos->Permisos ?? '[]', true)
        ]);
    }

    public function guardarPermisos(Request $request)
    {
        $request->validate([
            'Id_Usuario' => 'required',
            'Id_Rol' => 'required',
            'permisos' => 'array'
        ]);

        DB::beginTransaction();

        DB::table('Relacion_Ejidatario')
            ->where('Id_Usuario', $request->Id_Usuario)
            ->update(['Id_Rol' => $request->Id_Rol]);

        DB::table('Roles')
            ->where('Id_Rol', $request->Id_Rol)
            ->update([
                'Permisos' => json_encode($request->permisos ?? []),
                'Fecha_Modificado' => now()
            ]);

        DB::commit();

        return back()->with('success', 'Permisos actualizados correctamente');
    }
}
