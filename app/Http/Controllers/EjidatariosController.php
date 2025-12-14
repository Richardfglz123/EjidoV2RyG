<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EjidatariosController extends Controller
{
    public function index()
    {
        $data = DB::table('Ejidatario as e')
            ->join('Usuario as u', 'e.Id_Usuario', '=', 'u.Id_Usuario')
            ->select(
                'e.*',
                DB::raw("CONCAT(u.Nombres,' ',u.Apellido_Paterno,' ',u.Apellido_Materno) as Usuario")
            )
            ->get();

        return view('cpanel/ejidatarios/indexEjidatario', compact('data'));
    }

    public function create(Request $request)
    {
        $usuarios = [];

        if ($request->filled('buscar_usuario')) {
            $buscar = $request->buscar_usuario;

            $usuarios = DB::table('Usuario')
                ->where(DB::raw("CONCAT(Nombres,' ',Apellido_Paterno,' ',Apellido_Materno)"), 'like', "%$buscar%")
                ->limit(20)
                ->get();
        }

        return view('cpanel/ejidatarios/CrearEjidatario', compact('usuarios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'Num_Ejidatario' => 'required|numeric|unique:Ejidatario,Num_Ejidatario',
            'Direccion'      => 'required',
            'No_Parcela'     => 'required',
            'Id_Usuario'     => 'required|exists:Usuario,Id_Usuario'
        ]);

        DB::table('Ejidatario')->insert([
            'Num_Ejidatario' => $request->Num_Ejidatario,
            'Direccion'      => $request->Direccion,
            'No_Parcela'     => $request->No_Parcela,
            'Id_Usuario'     => $request->Id_Usuario,
            'Fecha_Creo'     => now(),
        ]);

        return redirect()->route('Ejidatarios.index')
            ->with('success', 'Ejidatario registrado correctamente');
    }

    public function edit($id)
    {
        $fila = DB::table('Ejidatario')
            ->where('Id_Ejidatario', $id)
            ->first();

        abort_if(!$fila, 404);

        $usuarios = DB::table('Usuario')->get();

        return view('cpanel/ejidatarios/editEjidatarios', compact('fila', 'usuarios'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'Num_Ejidatario' => 'required|numeric|unique:Ejidatario,Num_Ejidatario,' . $id . ',Id_Ejidatario',
            'Direccion'      => 'required',
            'No_Parcela'     => 'required',
            'Id_Usuario'     => 'required|exists:Usuario,Id_Usuario'
        ]);

        DB::table('Ejidatario')
            ->where('Id_Ejidatario', $id)
            ->update([
                'Num_Ejidatario' => $request->Num_Ejidatario,
                'Direccion'      => $request->Direccion,
                'No_Parcela'     => $request->No_Parcela,
                'Id_Usuario'     => $request->Id_Usuario,
                'Fecha_Modificado' => now(),
            ]);

        return redirect()->route('Ejidatarios.index')
            ->with('success', 'Ejidatario actualizado correctamente');
    }

    public function destroy($id)
    {
        DB::table('Ejidatario')->where('Id_Ejidatario', $id)->delete();

        return redirect()->route('Ejidatarios.index')
            ->with('success', 'Ejidatario eliminado');
    }

}
