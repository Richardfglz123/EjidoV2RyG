<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DatosHistoricosExport;
use Illuminate\Support\Str;

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
            'Evidencia' => 'required|array|max:20',
            'Evidencia.*' => 'mimes:jpg,jpeg,png,pdf|max:10240' // Acepta PDF y hasta 10MB
        ]);

        $rutasArchivos = [];
        if ($request->hasFile('Evidencia')) {
            foreach ($request->file('Evidencia') as $archivo) {
                $rutasArchivos[] = $archivo->store('datos_historicos', 'public');
            }
        }

        DB::table('Datos_Historicos')->insert([
            'Titulo' => $request->Titulo,
            'Descripcion' => $request->Descripcion,
            'Fecha' => $request->Fecha,
            'Evidencia' => json_encode($rutasArchivos),
            'Id_Creo' => session('user_id'),
            'Fecha_Creo' => now()
        ]);

        return redirect()->route('datos_historicos.index')
            ->with('success', 'Registro creado con éxito.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'Titulo' => 'required|max:255',
            'Descripcion' => 'required',
            'Fecha' => 'required|date',
            'Evidencia' => 'nullable|array|max:20',
            'Evidencia.*' => 'mimes:jpg,jpeg,png,pdf|max:10240'
        ]);

        $registro = DB::table('Datos_Historicos')->where('Id_DatosH', $id)->first();
        $fotosExistentes = json_decode($registro->Evidencia, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $fotosExistentes = $registro->Evidencia ? [$registro->Evidencia] : [];
        }
        $fotosExistentes = is_array($fotosExistentes) ? $fotosExistentes : [];

        if ($request->hasFile('Evidencia')) {
            foreach ($request->file('Evidencia') as $archivo) {
                $fotosExistentes[] = $archivo->store('datos_historicos', 'public');
            }
        }

        DB::table('Datos_Historicos')
            ->where('Id_DatosH', $id)
            ->update([
                'Titulo' => $request->Titulo,
                'Descripcion' => $request->Descripcion,
                'Fecha' => $request->Fecha,
                'Evidencia' => json_encode(array_values($fotosExistentes)),
                'Id_Modificado' => session('user_id'),
                'Fecha_Modificado' => now()
            ]);

        return redirect()->route('datos_historicos.index')
            ->with('success', 'Registro actualizado correctamente.');
    }

    public function eliminarFoto(Request $request, $id)
    {
        $registro = DB::table('Datos_Historicos')->where('Id_DatosH', $id)->first();
        $fotos = json_decode($registro->Evidencia, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $fotos = [$registro->Evidencia];
        }

        $fotoAEliminar = $request->query('foto');

        if (($key = array_search($fotoAEliminar, $fotos)) !== false) {
            unset($fotos[$key]);
            Storage::disk('public')->delete($fotoAEliminar);
        }

        DB::table('Datos_Historicos')
            ->where('Id_DatosH', $id)
            ->update(['Evidencia' => json_encode(array_values($fotos))]);

        return back()->with('success', 'Archivo eliminado correctamente');
    }

    public function edit($id)
    {
        $registro = DB::table('Datos_Historicos')
            ->where('Id_DatosH', $id)
            ->first();

        return view('cpanel.DatosHistoricos.EditarRegistro', compact('registro'));
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

    private function filtrar(Request $request)
    {
        $q = DB::table('Datos_Historicos')
            ->whereNull('Fecha_Eliminado');

        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $q->whereBetween('Fecha', [
                $request->fecha_inicio,
                $request->fecha_fin
            ]);
        }

        if ($request->filled('mes')) {
            $q->whereMonth('Fecha', $request->mes);
        }

        if ($request->filled('anio')) {
            $q->whereYear('Fecha', $request->anio);
        }

        return $q->orderBy('Fecha', 'desc')->get();
    }

    public function reportePDF(Request $request)
    {
        $data = $this->filtrar($request);
        $pdf = Pdf::loadView('cpanel.reportes.reporteDatosHistoricos', compact('data'));
        return $pdf->stream('datos_historicos.pdf');
    }

    public function reporteExcel(Request $request)
    {
        return Excel::download(
            new DatosHistoricosExport($request),
            'datos_historicos.xlsx'
        );
    }
}