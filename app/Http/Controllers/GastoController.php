<?php

namespace App\Http\Controllers;

use App\Models\Gasto;
use Illuminate\Http\Request;
use App\Exports\GastosExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;


class GastoController extends Controller
{
    public function index(Request $request)
    {
        $query = Gasto::query();

        if ($request->filled('Responsable')) {
            $query->where('Responsable', 'like', '%' . $request->responsable . '%');
        }

        if ($request->filled('Concepto')) {
            $query->where('Concepto', 'like', '%' . $request->concepto . '%');
        }

        if ($request->filled('Fecha')) {
            $query->whereDate('Fecha', $request->fecha);
        }

        $gastos = $query->orderBy('Fecha', 'desc')->get();

        return view('cpanel.ListViews.consultaGasto', compact('gastos'));
    }



    public function create()
    {
        return view('cpanel.RegisterViews.nuevoGasto');
    }

    public function store(Request $request)
    {
        $request->validate([
            'responsable' => 'required|string|max:50',
            'fecha' => 'required|date',
            'monto' => 'required|numeric',
            'concepto' => 'required|string|max:50',
            'medida' => 'required|string|max:50',
        ]);

        Gasto::create([
            'Responsable' => $request->responsable,
            'Fecha'       => $request->fecha,
            'Monto'       => $request->monto,
            'Concepto'    => $request->concepto,
            'Medida'      => $request->medida,
        ]);

        return redirect()
            ->route('gastos.index')
            ->with('success', 'Gasto registrado correctamente');
    }



    public function edit($id)
    {
        $gasto = Gasto::findOrFail($id);
        return view('cpanel.EditViews.editarGasto', compact('gasto'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'responsable' => 'required|string|max:50',
            'fecha' => 'required|date',
            'monto' => 'required|numeric',
            'concepto' => 'required|string|max:50',
            'medida' => 'required|string|max:50',
        ]);

        $gasto = Gasto::findOrFail($id);

        $gasto->update([
            'Responsable' => $request->responsable,
            'Fecha'       => $request->fecha,
            'Monto'       => $request->monto,
            'Concepto'    => $request->concepto,
            'Medida'      => $request->medida,
        ]);

        return redirect()
            ->route('gastos.index')
            ->with('success', 'Gasto actualizado correctamente');
    }

    public function destroy($id)
    {
        $gasto = Gasto::findOrFail($id);
        $gasto->delete();

        return redirect()->route('gastos.index')->with('success', 'Gasto eliminado correctamente');
    }
    public function generarExcel() {
        return Excel::download(new GastosExport, 'Gastos_Ejidales.xlsx');
    }

    public function generarPdf() {
        $gastos = Gasto::all();
        $pdf = Pdf::loadView('cpanel.reportes.reporteGastos', compact('gastos'));
        return $pdf->stream('Reporte_Gastos.pdf');
    }
}
