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

            'descuento_saneamiento'     => CatalogoMulta::where('tipo', 'LIKE', '%Saneamiento%')
                ->orWhere('tipo', 'LIKE', '%saneamient%')->first(),

            'descuento_aprovechamiento' => CatalogoMulta::where('tipo', 'LIKE', '%Aprovechamiento%')
                ->orWhere('tipo', 'LIKE', '%aprovecham%')->first(),

            'descuento_asambleas'       => CatalogoMulta::where('tipo', 'LIKE', '%Asamblea%')
                ->orWhere('tipo', 'LIKE', '%asamble%')->first(),
        ];

        return view('cpanel.monto.menu', $data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'monto'          => 'required|numeric',
            'anio'           => 'required|integer',
            'fecha_registro' => 'nullable|date',
            'responsable'    => 'nullable|string'
        ]);

        $utilidad = Utilidad::findOrFail($id);
        $utilidad->Monto          = $request->monto;
        $utilidad->Año            = $request->anio;
        $utilidad->Fecha_Registro = $request->fecha_registro;
        $utilidad->Id_Modificado  = $request->responsable;
        $utilidad->Fecha_Modificado = Carbon::now();

        $utilidad->save();

        return redirect()->route('monto.index', ['id_utilidad' => $id])
            ->with('success', 'Reparto actualizado correctamente');
    }
}