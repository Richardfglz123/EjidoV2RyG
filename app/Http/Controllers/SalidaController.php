<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Entrada;
use App\Models\Salida;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\SalidasExport;
use Maatwebsite\Excel\Facades\Excel;

class SalidaController extends Controller
{
    public function index()
    {
        $salidas = Salida::with('articulo')
            // Cambio: 'fecha_salida' -> 'Fecha'
            ->orderBy('Fecha', 'desc')
            ->get();

        return view('cpanel.ListViews.ListadoSalidas', compact('salidas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            // Cambio: 'id_equipo' -> 'Id_Articulo' (según tu SQL)
            'Id_Articulo' => 'required|exists:Articulos,Id_Articulo',
            'Cantidad' => 'required|integer|min:1',
            'Fecha' => 'required|date',
            'Tipo_Salida' => 'required|string',
            'Responsable' => 'required|string',
            'Observaciones' => 'nullable|string'
        ]);

        DB::transaction(function () use ($request) {
            Salida::create($request->all());

            DB::table('Articulos')
                ->where('Id_Articulo', $request->Id_Articulo)
                ->decrement('Cantidad', $request->Cantidad);
        });

        return redirect(url('salidas'))->with('success', 'Salida registrada');
    }

    public function create()
    {
        $articulos = Articulo::orderBy('descripcion')->get();
        return view('cpanel.RegisterViews.nuevaSalida', compact('articulos'));
    }


    public function edit($id)
    {
        $salida = Salida::findOrFail($id);
        $articulos = Articulo::orderBy('descripcion')->get();

        return view('cpanel.EditViews.EditarSalida', compact('salida', 'articulos'));
    }

    public function update(Request $request, $id)
    {
        $salida = Salida::findOrFail($id);

        $request->validate([
            'id_equipo' => 'required|exists:articulos,id_equipo',
            'cantidad' => 'required|integer|min:1',
            'fecha_salida' => 'required|date',
            'tipo_salida' => 'required|string',
            'responsable' => 'required|string',
            'observaciones' => 'nullable|string'
        ]);

        DB::transaction(function () use ($request, $salida) {

            DB::table('articulos')
                ->where('id_equipo', $salida->id_equipo)
                ->increment('cantidad', $salida->cantidad);

            DB::table('articulos')
                ->where('id_equipo', $request->id_equipo)
                ->decrement('cantidad', $request->cantidad);

            $salida->update($request->all());
        });

        return redirect(url('salidas'))
            ->with('success', 'Salida actualizada correctamente');
    }

    public function destroy($id)
    {
        $salida = Salida::findOrFail($id);

        DB::table('articulos')
            ->where('id_equipo', $salida->id_equipo)
            ->increment('cantidad', $salida->cantidad);

        $salida->delete();

        return redirect(url('salidas'))
            ->with('success', 'Salida eliminada');
    }
    public function reporteEyS()
    {
        // Cambiado: 'fecha_entrada' -> 'Fecha'
        $entradas = Entrada::with('articulo')
            ->orderBy('Fecha', 'desc')
            ->get();

        // Cambiado: 'fecha_salida' -> 'Fecha'
        $salidas = Salida::with('articulo')
            ->orderBy('Fecha', 'desc')
            ->get();

        return view('cpanel.ReportViews.reportesEyS', compact('entradas', 'salidas'));
    }
    public function generarPdf()
    {
        $salidas = Salida::with('articulo')->get(); // Carga los datos

        // Apunta a la vista de reporte que creamos (reporteSalidas.blade.php)
        $pdf = Pdf::loadView('cpanel.reportes.reporteSalidas', compact('salidas'));

        return $pdf->stream('Reporte_Salidas_' . date('d-m-Y') . '.pdf');
    }
    public function generarExcel()
    {
        return Excel::download(new SalidasExport, 'Reporte_Salidas_' . date('d-m-Y') . '.xlsx');
    }
}
