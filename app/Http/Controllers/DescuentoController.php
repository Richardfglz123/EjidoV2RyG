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
        $ejidatarios = Ejidatario::with(['usuario', 'descuentos'])->paginate(15);
        $catalogoFaenas = CatalogoMulta::where('Tipo', 'LIKE', '%Faena%')->get();

        return view('cpanel.Descuentos.faenas', compact('ejidatarios', 'catalogoFaenas'));
    }

    public function descuento(Request $request)
    {
        $saneamiento = CatalogoMulta::where('Tipo', 'LIKE', '%saneamient%')->first();
        $aprovechamiento = CatalogoMulta::where('Tipo', 'LIKE', '%aprovecham%')->first();
        $asamblea = CatalogoMulta::where('Tipo', 'LIKE', '%ASAMBLEA%')->first();
        $descuentos = collect();
        if ($saneamiento) $descuentos->push($saneamiento);
        if ($aprovechamiento) $descuentos->push($aprovechamiento);
        if ($asamblea) $descuentos->push($asamblea);

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

        $asambleaElegida = CatalogoMulta::findOrFail($request->id_multa_c);
        $montoMaestro = CatalogoMulta::where('Tipo', 'ASAMBLEAS')->first();
        $costoReal = $montoMaestro ? $montoMaestro->Costo : 0;

        DB::table('Descuentos')->updateOrInsert(
            [
                'Id_Ejidatario' => $request->id_ejidatario,
                'Id_MultaC'     => $request->id_multa_c
            ],
            [
                'tipo'          => $asambleaElegida->Tipo,
                'Descuento'     => $costoReal,
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
        $multaOriginal = CatalogoMulta::findOrFail($id);
        $nuevoCosto = $request->Costo;

        if (stripos($multaOriginal->Tipo, 'ASAMBLEA') !== false) {
            CatalogoMulta::where('Tipo', 'LIKE', '%ASAMBLEA%')->update([
                'Costo' => $nuevoCosto,
                'Id_Modificado' => session('usuario.nombre') ?? 'Admin',
                'Fecha_Modificado' => now()
            ]);
        } else {
            $multaOriginal->update([
                'Costo' => $nuevoCosto,
                'Id_Modificado' => session('usuario.nombre') ?? 'Admin',
                'Fecha_Modificado' => now()
            ]);
        }

        return redirect()->route('menu')->with('success', 'Precio actualizado globalmente.');
    }
}