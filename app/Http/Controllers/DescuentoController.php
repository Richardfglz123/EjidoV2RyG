<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ejidatario;
use App\Models\Descuento;
use App\Models\CatalogoMulta;
use Illuminate\Support\Facades\DB;

class DescuentoController extends Controller
{

    public function index(Request $request)
    {
        $query = Ejidatario::with(['usuario', 'descuentos']);

        if ($request->filled('query')) {
            $search = $request->get('query');
            $query->whereHas('usuario', function($q) use ($search) {
                $q->where('Nombres', 'LIKE', "%$search%")
                    ->orWhere('Apellido_Paterno', 'LIKE', "%$search%");
            });
        }

        $ejidatarios = $query->paginate(15);

        $catalogoMultas = CatalogoMulta::where('Tipo', 'LIKE', '%ASAMBLEA%')
            ->orderBy('Tipo', 'ASC')
            ->get();

        return view('cpanel.Descuentos.index', compact('ejidatarios', 'catalogoMultas'));
    }

    public function faenas()
    {
        $catalogoFaenas = CatalogoMulta::where('Tipo', 'LIKE', '%Faena%')->get();
        $costoSaneamiento = CatalogoMulta::where('Tipo', 'LIKE', '%saneamient%')->first()?->Costo ?? 0;
        $costoAprovechamiento = CatalogoMulta::where('Tipo', 'LIKE', '%aprovecham%')->first()?->Costo ?? 0;
        $ejidatarios = Ejidatario::with(['usuario', 'descuentos', 'pasesLista'])
            ->paginate(10);

        return view('cpanel.Descuentos.faenas', compact(
            'ejidatarios',
            'catalogoFaenas',
            'costoSaneamiento',
            'costoAprovechamiento'
        ));
    }

    public function descuento(Request $request)
    {
        // Traemos TODOS los registros para ver qué es lo que realmente tenemos
        $descuentos = CatalogoMulta::orderBy('Tipo', 'ASC')->get();

        $descuentoSeleccionado = null;
        if ($request->filled('id_multa_c')) {
            $descuentoSeleccionado = CatalogoMulta::find($request->id_multa_c);
        }

        return view('cpanel.Descuento.descuento', compact('descuentos', 'descuentoSeleccionado'));
    }

    public function store(Request $request)
    {
        if ($request->accion === 'eliminar') {
            Descuento::where('Id_Ejidatario', $request->id_ejidatario)
                ->where('Id_MultaC', $request->id_multa_c)
                ->delete();
            return response()->json(['success' => true]);
        }

        $tipoFaena = CatalogoMulta::findOrFail($request->id_multa_c);
        $montoConfigurado = CatalogoMulta::where('Tipo', 'LIKE', '%' . $request->concepto_monto . '%')->first();
        $precioAAplicar = $montoConfigurado ? $montoConfigurado->Costo : 0;

        DB::table('Descuentos')->updateOrInsert(
            [
                'Id_Ejidatario' => $request->id_ejidatario,
                'Id_MultaC'     => $request->id_multa_c
            ],
            [
                'tipo'          => $tipoFaena->Tipo, // Nombre de la faena (columna)
                'Descuento'     => $precioAAplicar,   // El precio que "jaló" de configuración
                'Id_Creo'       => session('usuario.nombre') ?? 'Admin',
                'Fecha_Creo'    => now()
            ]
        );

        return response()->json(['success' => true]);
    }

    public function buscar(Request $request)
    {
        $term = $request->q;
        $ejidatarios = Ejidatario::whereHas('usuario', function($query) use ($term) {
            $query->where('Nombres', 'LIKE', "%$term%")
                ->orWhere('Apellido_Paterno', 'LIKE', "%$term%");
        })->with('usuario')->get();

        return $ejidatarios->map(function($e) {
            return [
                'id' => $e->Id_Ejidatario,
                'text' => ($e->usuario->Nombres ?? '') . ' ' . ($e->usuario->Apellido_Paterno ?? '')
            ];
        });
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'Costo' => 'required|numeric|min:0',
        ]);

        $multaOriginal = CatalogoMulta::findOrFail($id);
        $nuevoCosto = $request->Costo;
        if (stripos($multaOriginal->Tipo, 'ASAMBLEA') !== false) {
            CatalogoMulta::where('Tipo', 'LIKE', '%ASAMBLEA%')->update([
                'Costo' => $nuevoCosto
            ]);
        } else {
            $multaOriginal->Costo = $nuevoCosto;
            $multaOriginal->save();
        }

        return redirect()->route('menu')->with('success', 'Precio actualizado correctamente.');
    }
}