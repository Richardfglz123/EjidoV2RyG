<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EjidatariosController extends Controller
{
    private function checkPermission($permission)
    {
        $sesion = session('usuario', session('2fa_user', []));
        $permisos = $sesion['permisos'] ?? [];
        $rol = strtolower(trim($sesion['rol'] ?? ''));

        if ($rol === 'administrador' || ($sesion['id_rol'] ?? null) == 2) {
            return true;
        }

        if (!in_array($permission, $permisos)) {
            abort(403, 'No tienes permiso para gestionar ejidatarios.');
        }
    }

    public function index()
    {
        $ejidatarios = DB::table('Ejidatario as e')
            ->join('Usuario as u', 'e.Id_Usuario', '=', 'u.Id_Usuario')
            ->join('Estatus as es', 'e.Id_Estatus', '=', 'es.Id_Estatus')
            ->select(
                'e.*',
                'u.Nombres',
                'u.Apellido_Paterno',
                'es.Estatus as NombreEstatus'
            )
            ->get();

        return view('cpanel/ejidatarios/indexEjidatario', [
            'data' => $ejidatarios
        ]);
    }

    public function create()
    {
        $this->checkPermission('usuarios_crear');

        $usuarios = DB::table('Usuario')->get();
        $estatus  = DB::table('Estatus')->get();

        return view('cpanel.ejidatarios.CrearEjidatario', [
            'usuarios' => $usuarios,
            'estatus'  => $estatus
        ]);
    }

    public function store(Request $request)
    {
        $this->checkPermission('usuarios_crear');

        $request->validate([
            'Num_Ejidatario'     => 'required|integer|unique:Ejidatario,Num_Ejidatario',
            'Calle'              => 'required|string|max:100',
            'Num_Exterior'       => 'required|string|max:10',
            'Colonia'            => 'required|string|max:100',
            'Municipio'          => 'required|string|max:100',
            'Estado'             => 'required|string|max:100',
            'Codigo_Postal'      => 'required|string|max:10',
            'Fecha_Nacimiento'   => 'required|date',
            'CURP'               => 'required|string|max:20|unique:Ejidatario,CURP',
            'RFC'                => 'required|string|max:15',
            'Clave_Elector'      => 'required|string|max:20',
            'Fecha_Ingreso'      => 'required|date',
            'Id_Estatus'         => 'required|exists:Estatus,Id_Estatus',
            'Id_Usuario'         => 'required|exists:Usuario,Id_Usuario',
        ]);

        DB::table('Ejidatario')->insert([
            'Num_Ejidatario'   => $request->Num_Ejidatario,
            'Calle'            => $request->Calle,
            'Num_Exterior'     => $request->Num_Exterior,
            'Num_Interior'     => $request->Num_Interior,
            'Colonia'          => $request->Colonia,
            'Municipio'        => $request->Municipio,
            'Estado'           => $request->Estado,
            'Codigo_Postal'    => $request->Codigo_Postal,
            'Fecha_Nacimiento' => $request->Fecha_Nacimiento,
            'CURP'             => $request->CURP,
            'RFC'              => $request->RFC,
            'Clave_Elector'    => $request->Clave_Elector,
            'Fecha_Ingreso'    => $request->Fecha_Ingreso,
            'Id_Estatus'       => $request->Id_Estatus,
            'Id_Usuario'       => $request->Id_Usuario,
            'Fecha_Creo'       => now(),
            'Id_Creo'          => session('usuario.username', 'admin')
        ]);

        return redirect()->route('Ejidatarios.index')->with('success', 'Ejidatario registrado');
    }

    public function edit($id)
    {
        $this->checkPermission('usuarios_editar');

        $fila = DB::table('Ejidatario')->where('Id_Ejidatario', $id)->first();
        abort_if(!$fila, 404);

        $usuarios = DB::table('Usuario')->get();
        $estatus  = DB::table('Estatus')->get();

        return view('cpanel/ejidatarios/editEjidatarios', compact('fila', 'usuarios', 'estatus'));
    }

    public function update(Request $request, $id)
    {
        $this->checkPermission('usuarios_editar');

        $request->validate([
            'Num_Ejidatario'   => 'required|integer|unique:Ejidatario,Num_Ejidatario,' . $id . ',Id_Ejidatario',
            'CURP'             => 'required|string|max:20|unique:Ejidatario,CURP,' . $id . ',Id_Ejidatario',
        ]);

        DB::table('Ejidatario')->where('Id_Ejidatario', $id)->update([
            'Num_Ejidatario'   => $request->Num_Ejidatario,
            'Calle'            => $request->Calle,
            'Num_Exterior'     => $request->Num_Exterior,
            'Num_Interior'     => $request->Num_Interior,
            'Colonia'          => $request->Colonia,
            'Municipio'        => $request->Municipio,
            'Estado'           => $request->Estado,
            'Codigo_Postal'    => $request->Codigo_Postal,
            'Fecha_Nacimiento' => $request->Fecha_Nacimiento,
            'CURP'             => $request->CURP,
            'RFC'              => $request->RFC,
            'Clave_Elector'    => $request->Clave_Elector,
            'Fecha_Ingreso'    => $request->Fecha_Ingreso,
            'Id_Estatus'       => $request->Id_Estatus,
            'Id_Usuario'       => $request->Id_Usuario,
            'Fecha_Modificado' => now(),
            'Id_Modificado'    => session('usuario.username', 'admin')
        ]);

        return redirect()->route('Ejidatarios.index')->with('success', 'Ejidatario actualizado');
    }

    public function destroy($id)
    {
        $sesion = session('usuario', session('2fa_user', []));
        $miId = $sesion['id'] ?? null;
        $miRol = strtolower(trim($sesion['rol'] ?? ''));
        $esAdmin = ($miRol === 'administrador' || ($sesion['id_rol'] ?? null) == 2);

        if (!$esAdmin && !in_array('usuarios_eliminar', $sesion['permisos'] ?? [])) {
            abort(403, 'No tienes permiso para eliminar.');
        }

        // Buscar ejidatario real
        $fila = DB::table('Ejidatario')
            ->where('Id_Ejidatario', $id)
            ->first();

        if (!$fila) {
            return back()->withErrors('Ejidatario no encontrado.');
        }

        // evitar eliminar tu propio registro
        if ($miId == $fila->Id_Usuario) {
            return back()->withErrors('No puedes eliminar tu propio registro de usuario/ejidatario.');
        }

        try {

            DB::transaction(function () use ($fila) {
                DB::table('Ejidatario')
                    ->where('Id_Ejidatario', $fila->Id_Ejidatario)
                    ->delete();

            });

            return back()->with('success', 'Registro de Ejidatario eliminado correctamente.');

        } catch (\Exception $e) {

            return back()->withErrors('Error al eliminar: ' . $e->getMessage());

        }
    }

    public function show($id)
    {
        $monto = DB::table('Reparto')->where('id_ejidatario', $id)->value('monto') ?? 0;
        return response()->json(['saldo_disponible' => $monto]);
    }
}