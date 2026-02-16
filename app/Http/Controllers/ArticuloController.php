<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Articulo;
use App\Exports\ArticulosExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ArticuloController extends Controller
{
    public function index()
    {
        $articulos = Articulo::orderBy('Fecha_Registro', 'desc')->get();
        return view('cpanel.ListViews.consultaArticulo', compact('articulos'));
    }

    public function create()
    {
        return view('cpanel.RegisterViews.nuevoArticulo');
    }

    public function store(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string',
            'cantidad' => 'required|numeric',
            'estado' => 'required|string',
            'medida' => 'required|string',
            'fecha_registro' => 'nullable|date'
        ]);

        Articulo::create([
            'Descripcion' => $request->descripcion,
            'Cantidad' => $request->cantidad,
            'Estado' => $request->estado,
            'Medida' => $request->medida,
            'Fecha_Registro' => $request->fecha_registro
        ]);

        return redirect()
            ->back()
            ->with('success', 'Artículo registrado correctamente');
    }


    public function edit($id)
    {
        $articulo = Articulo::findOrFail($id);
        return view('cpanel.editViews.editarArticulo', compact('articulo'));
    }
    public function update(Request $request, Articulo $articulo)
    {
        $request->validate([
            'descripcion' => 'required|string',
            'cantidad' => 'required|numeric',
            'estado' => 'required|string',
            'medida' => 'required|string',
            'fecha_registro' => 'nullable|date'
        ]);

        $articulo->update([
            'Descripcion'    => $request->descripcion,
            'Cantidad'       => $request->cantidad,
            'Estado'         => $request->estado,
            'Medida'         => $request->medida,
            'Fecha_Registro' => $request->fecha_registro
        ]);

        return redirect()
            ->route('articulos.index')
            ->with('success', 'Artículo actualizado correctamente');
    }


    public function destroy($id)
    {
        $articulo = Articulo::findOrFail($id);
        $articulo->delete();

        return redirect()
            ->route('articulos.index')
            ->with('success', 'Artículo eliminado correctamente');
    }

    public function buscar(Request $request)
    {
        $articulos = Articulo::query()

            ->when($request->descripcion, function ($q) use ($request) {
                $q->where('Descripcion', 'like', '%' . $request->descripcion . '%');
            })

            ->when($request->estado, function ($q) use ($request) {
                $q->where('Estado', 'like', '%' . $request->estado . '%');
            })

            ->when($request->fecha_registro, function ($q) use ($request) {
                $q->whereDate('Fecha_Registro', $request->fecha_registro);
            })


            ->orderBy('fecha_registro', 'desc')
            ->get();

        return view('cpanel.ListViews.consultaArticulo', compact('articulos'));
    }

    public function reporte()
    {
        $articulos = Articulo::orderBy('fecha_registro', 'desc')->get();

        return view('cpanel.ReportViews.reporteArticulo', compact('articulos'));
    }

    public function generarExcel() {
        return Excel::download(new ArticulosExport, 'Inventario_Ejidal_' . date('d-m-Y') . '.xlsx');
    }

    public function generarPdf() {
        $articulos = \App\Models\Articulo::all();
        // Reutiliza el diseño CSS de reporteEntradas para mantener la uniformidad
        $pdf = Pdf::loadView('cpanel.reportes.reporteArticulos', compact('articulos'));
        return $pdf->stream('Inventario_General.pdf');
    }

}
