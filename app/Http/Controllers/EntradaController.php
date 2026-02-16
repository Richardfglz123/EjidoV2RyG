<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Entrada;
use App\Models\Articulo;
use Illuminate\Support\Facades\DB;
use App\Exports\EntradasExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class EntradaController extends Controller
{
    public function index()
    {
        $entradas = Entrada::with('articulo')
            ->orderBy('Fecha', 'desc') // CAMBIO: 'fecha_entrada' -> 'Fecha'
            ->get();

        return view('cpanel.ListViews.listadoEntradas', compact('entradas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'Id_Articulo' => 'required|exists:Articulos,Id_Articulo', // Ajustado a SQL
            'Cantidad'    => 'required|integer|min:1',
            'Fecha'       => 'required|date',
            'Observaciones' => 'nullable|string'
        ]);

        DB::transaction(function () use ($request) {
            Entrada::create([
                'Id_Articulo'   => $request->Id_Articulo,
                'Cantidad'      => $request->Cantidad,
                'Fecha'         => $request->Fecha,
                'Observaciones' => $request->Observaciones
            ]);

            // 2. Sumar al artículo
            DB::table('Articulos')
                ->where('Id_Articulo', $request->Id_Articulo)
                ->increment('Cantidad', $request->Cantidad);
        });

        return redirect()->route('entradas.create')
            ->with('success', 'Entrada registrada correctamente');
    }

    public function create()
    {
        $articulos = Articulo::orderBy('descripcion')->get();
        return view('cpanel.RegisterViews.nuevaEntrada', compact('articulos'));
    }


    public function edit($id)
    {
        $entrada = Entrada::findOrFail($id);
        $articulos = Articulo::orderBy('descripcion')->get();

        return view('cpanel.EditViews.editarEntrada', compact('entrada', 'articulos'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_equipo' => 'required|exists:articulos,id_equipo',
            'cantidad' => 'required|integer|min:1',
            'fecha_entrada' => 'required|date',
            'observaciones' => 'nullable|string'
        ]);

        $entrada = Entrada::findOrFail($id);

        DB::transaction(function () use ($request, $entrada) {

            // revertir cantidad anterior
            $articuloAnterior = Articulo::where('id_equipo', $entrada->id_equipo)->first();
            $articuloAnterior->cantidad -= $entrada->cantidad;
            $articuloAnterior->save();

            // actualizar entrada
            $entrada->update([
                'id_equipo' => $request->id_equipo,
                'cantidad' => $request->cantidad,
                'fecha_entrada' => $request->fecha_entrada,
                'observaciones' => $request->observaciones
            ]);

            // sumar nueva cantidad
            $articuloNuevo = Articulo::where('id_equipo', $request->id_equipo)->first();
            $articuloNuevo->cantidad += $request->cantidad;
            $articuloNuevo->save();
        });

        return redirect()
            ->route('entradas.index')
            ->with('success', 'Entrada actualizada correctamente');
    }

    public function destroy($id)
    {
        $entrada = Entrada::findOrFail($id);

        DB::transaction(function () use ($entrada) {

            // restar la entrada del artículo
            $articulo = Articulo::where('id_equipo', $entrada->id_equipo)->first();
            $articulo->cantidad -= $entrada->cantidad;
            $articulo->save();

            // eliminar entrada
            $entrada->delete();
        });

        return redirect()
            ->route('entradas.index')
            ->with('success', 'Entrada eliminada correctamente');
    }
    public function generarExcel() {
        return Excel::download(new EntradasExport, 'Entradas_Inventario.xlsx');
    }

    public function generarPdf() {
        $entradas = Entrada::with('articulo')->orderBy('Fecha', 'desc')->get();
        $pdf = Pdf::loadView('cpanel.reportes.reporteEntradas', compact('entradas')); // Crea esta vista similar a la de salidas
        return $pdf->stream('Reporte_Entradas.pdf');
    }
}
