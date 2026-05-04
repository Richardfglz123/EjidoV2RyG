<?php

namespace App\Http\Controllers;

use App\Models\CatalogoMulta;
use Illuminate\Http\Request;

class MultaController extends Controller
{
    public function index(Request $request)
    {
        // Capturamos los filtros de la URL
        $anio = $request->get('anio');
        $tipo = $request->get('tipo');

        // Construimos la consulta con filtros básicos
        $data = CatalogoMulta::when($anio, function ($query, $anio) {
            return $query->where('anio', $anio);
        })
            // Nota: Si en tu tabla los costos están en filas separadas por tipo,
            // la lógica de tu tabla Blade (que muestra ambos en una fila)
            // podría necesitar un groupBy o ajuste.
            ->paginate(10);

        // Enviamos la variable como 'data'
        return view('cpanel.Multa.indexMulta', compact('data'));
    }

    public function create()
    {
        return view('cpanel.Multa.formMulta');
    }

    public function store(Request $request)
    {
        $request->validate([
            'anio' => 'required|integer',
            'tipo_multa' => 'required|in:Asamblea,Faena',
            'costo' => 'required|numeric',
        ]);

        CatalogoMulta::create([
            'anio' => $request->anio,
            'tipo_multa' => $request->tipo_multa,
            'costo' => $request->costo,
            'Id_Creo' => auth()->user()->username ?? 'lou',
            'Fecha_Creo' => now(),
        ]);

        return redirect()->route('multas.index')->with('success', 'Multa creada');
    }
}