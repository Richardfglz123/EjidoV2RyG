<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Ejidatario;
use App\Models\Descuento;
use App\Models\CatalogoMulta;

class DescuentoController extends Controller
{
    public function index()
    {
        $ejidatarios = Ejidatario::with(['usuario', 'descuentos'])->paginate(10);

        $asambleas = Descuento::whereNotNull('tipo')
            ->distinct()
            ->pluck('tipo')
            ->toArray();

        $catalogoMultas = CatalogoMulta::all();

        return view('cpanel.Descuentos.index', compact(
            'ejidatarios',
            'asambleas',
            'catalogoMultas'
        ));
    }

    public function update(Request $request, $id)
    {
        // Es mejor validar antes de buscar el registro
        $data = $request->validate([
            'monto' => 'required|numeric|min:0',
            'anio' => 'required|numeric',
            'fecha_registro' => 'required|date',
        ]);

        $descuento = CatalogoMulta::findOrFail($id);

        // Usar $data asegura que solo guardas lo que ya pasó la validación
        $descuento->update($data);

        return redirect()->route('cpanel.Descuento.descuento', ['id_multa_c' => $id])
            ->with('success', '¡Descuento actualizado exitosamente!');
    }

    public function buscar(Request $request)
    {
        $query = $request->get('query');

        $usuarios = Usuario::where('nombre_completo', 'like', "%{$query}%")
            ->with('ejidatario')
            ->limit(10)
            ->get();

        return response()->json($usuarios);
    }
    public function faenas()
    {
        $ejidatarios = Ejidatario::with(['usuario', 'descuentos'])->paginate(10);

        $faenas = Descuento::whereNotNull('tipo')
            ->where('tipo', 'like', '%FAENA%')
            ->distinct()
            ->pluck('tipo')
            ->toArray();

        $catalogoFaenas = CatalogoMulta::all(); // ← cambia el nombre aquí

        return view('cpanel.Descuentos.faenas', compact(
            'ejidatarios',
            'faenas',
            'catalogoFaenas' // ← y aquí también
        ));
    }
    public function descuento(Request $request)
    {
        $id_multa_c = $request->query('id_multa_c');

        $descuentos = CatalogoMulta::all();
        $descuentoSeleccionado = $id_multa_c ? CatalogoMulta::find($id_multa_c) : null;

        return view('cpanel.descuento.descuento', compact('descuentos', 'descuentoSeleccionado'));
    }
}