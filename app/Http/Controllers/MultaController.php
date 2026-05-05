<?php

namespace App\Http\Controllers;

use App\Models\CatalogoMulta;
use Illuminate\Http\Request;

class MultaController extends Controller
{
    public function index(Request $request) {
        $anio = $request->get('anio');
        $data = CatalogoMulta::when($anio, function ($q, $anio) {
            return $q->where('Año', $anio);
        })
            ->get()
            ->groupBy('Año');

        return view('cpanel.Multa.indexMulta', compact('data'));
    }

    public function create()
    {
        return view('cpanel.Multa.formMulta');
    }


    public function edit($id)
    {
        $multaReferencia = CatalogoMulta::findOrFail($id);
        $registrosDelAnio = CatalogoMulta::where('Año', $multaReferencia->Año)->get();
        $asamblea = $registrosDelAnio->where('Tipo', 'Asamblea')->first();
        $faena = $registrosDelAnio->where('Tipo', 'Faena')->first();

        return view('cpanel.Multa.editMulta', compact('asamblea', 'faena', 'multaReferencia'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nuevo_anio'     => 'required',
            'costo_asamblea' => 'required|numeric',
            'costo_falta'    => 'required|numeric',
        ]);

        $multaReferencia = CatalogoMulta::findOrFail($id);
        $anioAnterior = $multaReferencia->Año;

        CatalogoMulta::updateOrCreate(
            ['Año' => $anioAnterior, 'Tipo' => 'Asamblea'],
            ['Año' => $request->nuevo_anio, 'Costo' => $request->costo_asamblea]
        );

        CatalogoMulta::updateOrCreate(
            ['Año' => $anioAnterior, 'Tipo' => 'Faena'],
            ['Año' => $request->nuevo_anio, 'Costo' => $request->costo_falta]
        );

        return redirect()->route('multas.index')->with('success', 'Configuración actualizada');
    }

    public function destroy($id)
    {
        $multa = CatalogoMulta::findOrFail($id);
        CatalogoMulta::where('Año', $multa->Año)->delete();

        return redirect()->route('multas.index')->with('success', 'Registros eliminados.');
    }

    public function store(Request $request)
    {
        $existeAsamblea = CatalogoMulta::where('Año', $request->anio_asamblea)->where('Tipo', 'Asamblea')->exists();
        $existeFaena = CatalogoMulta::where('Año', $request->anio_falta)->where('Tipo', 'Faena')->exists();

        if ($existeAsamblea || $existeFaena) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['anio_asamblea' => 'Este año ya tiene multas registradas. Use la opción de editar.']);
        }

        CatalogoMulta::create([
            'Año' => $request->anio_asamblea,
            'Tipo' => 'Asamblea',
            'Costo' => $request->costo_asamblea,
        ]);

        CatalogoMulta::create([
            'Año' => $request->anio_falta,
            'Tipo' => 'Faena',
            'Costo' => $request->costo_falta,
        ]);

        return redirect()->route('multas.index')->with('success', 'Año registrado con éxito.');
    }
}