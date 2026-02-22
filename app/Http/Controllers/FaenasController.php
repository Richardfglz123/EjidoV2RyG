<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ejidatario;
use App\Models\Usuario;
use App\Models\Descuento;
use App\Models\CatalogoMulta;

class FaenasController extends Controller
{

    public function index()
    {

        $faenas = [
            'Descuento faenas de saneamient',
            'Descuento faenas de aprovecham'
        ];


        $ejidatarios = Ejidatario::with(['usuario', 'descuentos'])->paginate(10);


        $catalogoFaenas = CatalogoMulta::where('tipo', 'like', 'Descuento faenas%')->get();


        return view('cpanel.Descuentos.faenas', [
            'ejidatarios' => $ejidatarios,
            'faenas' => $faenas,
            'catalogoFaenas' => $catalogoFaenas
        ]);
    }


    public function aplicarDescuento(Request $request)
    {

        $request->validate([
            'id_ejidatario' => 'required|integer|exists:ejidatario,id_ejidatario',
            'nombre_faena' => 'required|string|max:100',
            'id_multa_c' => 'nullable|integer|exists:catalogo_multa,id_multa_c'
        ]);

        $id_ejidatario = $request->id_ejidatario;
        $nombre_faena = $request->nombre_faena;
        $id_multa = $request->id_multa_c;

        if ($id_multa) {

            $multa = CatalogoMulta::find($id_multa);
            $montoDescuento = $multa->monto;


            Descuento::updateOrCreate(
                [
                    'id_ejidatario' => $id_ejidatario,
                    'tipo' => $nombre_faena
                ],
                [
                    'descuento' => $montoDescuento
                ]
            );
        } else {

            Descuento::where('id_ejidatario', $id_ejidatario)
                ->where('tipo', $nombre_faena)
                ->delete();
        }

        return response()->json(['success' => true, 'message' => 'Descuento actualizado.']);
    }


    public function buscarEjidatarios(Request $request)
    {

        $query = $request->get('query');
        if (empty($query)) {
            return response()->json([]);
        }

        $usuarios = Usuario::whereHas('ejidatario')
            ->where(function($q) use ($query) {
                $q->where('nombre', 'LIKE', '%' . $query . '%')
                    ->orWhere('apellido_paterno', 'LIKE', '%' . $query . '%')
                    ->orWhere('apellido_materno', 'LIKE', '%' . $query . '%');
            })
            ->with('ejidatario')
            ->limit(5)
            ->get();

        return response()->json($usuarios);
    }
}