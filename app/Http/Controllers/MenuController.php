<?php

namespace App\Http\Controllers;

use App\Models\Utilidad;
use App\Models\CatalogoMulta;
use App\Models\usuario;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $utilidades = Utilidad::all();
        $id_seleccionado = $request->input('id_utilidad');
        $utilidadSeleccionada = null;

        if ($id_seleccionado) {
            $utilidadSeleccionada = Utilidad::find($id_seleccionado);
        }

        $usuarios = usuario::all();

        $data = [
            'utilidades'                => $utilidades,
            'utilidadSeleccionada'      => $utilidadSeleccionada,
            'usuarios'                  => $usuarios,
            'finiquito_saneamiento'     => Utilidad::where('Tipo_Reparto', 'reparto_finiquito')->first(),
            'primer_reparto'            => Utilidad::where('Tipo_Reparto', 'primer_reparto')->first(),
            'segundo_reparto'           => Utilidad::where('Tipo_Reparto', 'segundo_reparto')->first(),
            'finiquito_utilidades'      => Utilidad::where('Tipo_Reparto', 'finiquito_utilidades')->first(),
            'reparto_finiquito_nuevo'   => Utilidad::where('Tipo_Reparto', 'reparto_finiquito_nuevo')->first(),
            // SIN el segundo $data = [
            'descuento_saneamiento'     => CatalogoMulta::where('Tipo', 'Saneamiento')->first(),
            'descuento_aprovechamiento' => CatalogoMulta::where('Tipo', 'Aprovechamiento')->first(),
            'descuento_asambleas'       => CatalogoMulta::where('Tipo', 'Asamblea')->first(),
        ];

        return view('cpanel.monto.menu', $data);
    }

    public function updateDescuento(Request $request)
    {
        $request->validate([
            'tipo' => 'required|string',
            'costo' => 'required|numeric'
        ]);

        // Busca el registro en CatalogoMulta
        $multa = CatalogoMulta::where('Tipo', 'LIKE', '%' . $request->tipo . '%')->first();

        if ($multa) {
            $multa->Costo = $request->costo;
            $multa->save();
            return back()->with('success', 'Descuento de ' . $request->tipo . ' actualizado.');
        }

        return back()->with('error', 'No se encontró el concepto para actualizar.');
    }
}