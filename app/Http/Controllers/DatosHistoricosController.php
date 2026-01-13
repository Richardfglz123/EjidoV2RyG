<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DatosHistoricosController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('Datos_Historicos')
            ->whereNull('Fecha_Eliminado');

        if ($request->filled('buscar')) {
            $query->where('Titulo', 'LIKE', '%' . $request->buscar . '%');
        }

        $registros = $query
            ->orderBy('Fecha', 'desc')
            ->get();

        return view('cpanel.DatosHistoricos.indexRegistro', compact('registros'));
    }

    public function create()
    {
        return view('cpanel.DatosHistoricos.CrearRegistro');
    }

    public function store(Request $request)
    {
        $request->validate([
            'Titulo' => 'required|max:255',
            'Descripcion' => 'required',
            'Fecha' => 'required|date',
            'Evidencia' => 'required|file|mimes:jpg,jpeg,png,pdf'
        ]);

        $rutaArchivo = null;

        if ($request->hasFile('Evidencia')) {
            $rutaArchivo = $request->file('Evidencia')
                ->store('datos_historicos', 'public');
        }

        DB::table('Datos_Historicos')->insert([
            'Titulo' => $request->Titulo,
            'Descripcion' => $request->Descripcion,
            'Fecha' => $request->Fecha,
            'Evidencia' => $rutaArchivo,
            'Id_Creo' => session('user_id'),
            'Fecha_Creo' => now()
        ]);

        return redirect()->route('datos_historicos.index')
            ->with('success', 'Dato histórico registrado');
    }

    public function edit($id)
    {
        $registro = DB::table('Datos_Historicos')
            ->where('Id_DatosH', $id)
            ->first();

        return view('cpanel.DatosHistoricos.EditarRegistro', compact('registro'));
    }

    public function update(Request $request, $id)
    {
        $registro = DB::table('Datos_Historicos')
            ->where('Id_DatosH', $id)
            ->first();

        $rutaArchivo = $registro->Evidencia;

        if ($request->hasFile('Evidencia')) {
            if ($rutaArchivo) {
                Storage::disk('public')->delete($rutaArchivo);
            }

            $rutaArchivo = $request->file('Evidencia')
                ->store('datos_historicos', 'public');
        }

        DB::table('Datos_Historicos')
            ->where('Id_DatosH', $id)
            ->update([
                'Titulo' => $request->Titulo,
                'Descripcion' => $request->Descripcion,
                'Fecha' => $request->Fecha,
                'Evidencia' => $rutaArchivo,
                'Id_Modificado' => session('user_id'),
                'Fecha_Modificado' => now()
            ]);

        return redirect()->route('datos_historicos.index')
            ->with('success', 'Dato histórico actualizado');
    }

    public function destroy($id)
    {
        DB::table('Datos_Historicos')
            ->where('Id_DatosH', $id)
            ->update([
                'Id_Elimino' => session('user_id'),
                'Fecha_Eliminado' => now()
            ]);

        return back()->with('success', 'Registro eliminado');
    }
}
