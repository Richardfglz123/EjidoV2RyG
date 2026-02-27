<?php

namespace App\Http\Controllers;

use App\Models\Utilidad;
use App\Models\CatalogoMulta;

class MenuController extends Controller
{
    public function index()
    {
        $data = [
            'finiquito_saneamiento'     => Utilidad::where('SegundoReparto', 'reparto_finiquito')->first(),
            'primer_reparto'            => Utilidad::where('SegundoReparto', 'primer_reparto')->first(),
            'segundo_reparto'           => Utilidad::where('SegundoReparto', 'segundo_reparto')->first(),
            'finiquito_utilidades'      => Utilidad::where('SegundoReparto', 'finiquito_utilidades')->first(),
            'reparto_finiquito_nuevo'   => Utilidad::where('SegundoReparto', 'reparto_finiquito_nuevo')->first(),

            // Multas desde el catálogo
            'descuento_saneamiento'     => CatalogoMulta::where('tipo', 'LIKE', '%saneamient%')->first(),
            'descuento_aprovechamiento' => CatalogoMulta::where('tipo', 'LIKE', '%aprovecham%')->first(),
            'descuento_asambleas'       => CatalogoMulta::where('tipo', 'LIKE', '%asambleas%')->first(),
        ];

        return view('cpanel.monto.menu', $data);
    }
}