<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EjidatariosController extends Controller
{
    // ================================
    // INDEX → Listado
    // ================================
    public function index()
    {
        $ejidatarios = DB::table('Ejidatario')
            ->join('Usuario', 'Ejidatario.Id_Usuario', '=', 'Usuario.Id_Usuario')
            ->join('Estatus', 'Ejidatario.Id_Estatus', '=', 'Estatus.Id_Estatus')
            ->select(
                'Ejidatario.*',
                'Usuario.Nombres',
                'Usuario.Apellido_Paterno',
                'Estatus.Estatus as NombreEstatus'
            )
            ->get();

        return view('cpanel/ejidatarios.indexEjidatario', [
            'data' => $ejidatarios
        ]);
    }

    // ================================
    // CREATE → Formulario
    // ================================
    public function create()
    {
        $usuarios = DB::table('Usuario')->get();
        $estatus  = DB::table('Estatus')->get();

        return view('cpanel/ejidatarios.CrearEjidatario', [
            'usuarios' => $usuarios,
            'estatus'  => $estatus
        ]);
    }

    // ================================
    // STORE → Guardar
    // ================================
    public function store(Request $request)
    {
        $request->validate([
            'Num_Ejidatario'     => 'required|integer|unique:Ejidatario,Num_Ejidatario',
            'Calle'              => 'required|string|max:100',
            'Num_Exterior'       => 'required|string|max:10',
            'Num_Interior'       => 'nullable|string|max:10',
            'Colonia'            => 'required|string|max:100',
            'Municipio'          => 'required|string|max:100',
            'Estado'             => 'required|string|max:100',
            'Codigo_Postal'      => 'required|string|max:10',
            'Fecha_Nacimiento'   => 'required|date',
            'CURP'               => 'required|string|max:20',
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

            // Auditoría
            'Fecha_Creo' => now(),
            'Id_Creo'    => 'admin'
        ]);

        return redirect()->route('ejidatarios.index')
            ->with('success', 'Ejidatario registrado correctamente');
    }

    // ================================
    // EDIT → Formulario edición
    // ================================
    public function edit($id)
    {
        $fila = DB::table('Ejidatario')
            ->where('Id_Ejidatario', $id)
            ->first();

        $usuarios = DB::table('Usuario')->get();
        $estatus  = DB::table('Estatus')->get();

        return view('cpanel/ejidatarios/editEjidatarios', [
            'fila'     => $fila,
            'usuarios' => $usuarios,
            'estatus'  => $estatus
        ]);
    }

    // ================================
    // UPDATE → Actualizar
    // ================================
    public function update(Request $request, $id)
    {
        $request->validate([
            'Calle'            => 'required|string|max:100',
            'Num_Exterior'     => 'required|string|max:10',
            'Num_Interior'     => 'nullable|string|max:10',
            'Colonia'          => 'required|string|max:100',
            'Municipio'        => 'required|string|max:100',
            'Estado'           => 'required|string|max:100',
            'Codigo_Postal'    => 'required|string|max:10',
            'Fecha_Nacimiento' => 'required|date',
            'CURP'             => 'required|string|max:20',
            'RFC'              => 'required|string|max:15',
            'Clave_Elector'    => 'required|string|max:20',
            'Fecha_Ingreso'    => 'required|date',
            'Id_Estatus'       => 'required|exists:Estatus,Id_Estatus',
            'Id_Usuario'       => 'required|exists:Usuario,Id_Usuario',
        ]);

        DB::table('Ejidatario')
            ->where('Id_Ejidatario', $id)
            ->update([
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
                'Id_Modificado'    => 'admin'
            ]);

        return redirect()->route('ejidatarios.index')
            ->with('success', 'Ejidatario actualizado correctamente');
    }

    // ================================
    // DESTROY → Eliminar
    // ================================
    public function destroy($id)
    {
        DB::table('Ejidatario')
            ->where('Id_Ejidatario', $id)
            ->delete();

        return redirect()->route('ejidatarios.index')
            ->with('success', 'Ejidatario eliminado correctamente');
    }
}
