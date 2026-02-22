<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Utilidad;
use App\Models\CatalogoMulta;

class MenuController extends Controller
{

    public function index()
    {
        // Cambié los nombres de búsqueda para que coincidan con lo que tienes en la BD
        $finiquito_saneamiento = Utilidad::where('SegundoReparto', 'reparto_finiquito')->first();
        $primer_reparto        = Utilidad::where('SegundoReparto', 'primer_reparto')->first();
        $segundo_reparto       = Utilidad::where('SegundoReparto', 'segundo_reparto')->first();
        $finiquito_utilidades  = Utilidad::where('SegundoReparto', 'finiquito_utilidades')->first();

        // Esta es la nueva opción que agregaste
        $reparto_finiquito_nuevo = Utilidad::where('SegundoReparto', 'reparto_finiquito_nuevo')->first();

        $descuento_saneamiento = CatalogoMulta::where('tipo', 'Descuento faenas de saneamient')->first();
        $descuento_aprovechamiento = CatalogoMulta::where('tipo', 'Descuento faenas de aprovecham')->first();
        $descuento_asambleas = CatalogoMulta::where('tipo', 'Descuento asambleas')->first();

        return view('menu', [
            'finiquito_saneamiento'     => $finiquito_saneamiento,
            'primer_reparto'            => $primer_reparto,
            'segundo_reparto'           => $segundo_reparto,
            'finiquito_utilidades'      => $finiquito_utilidades,
            'reparto_finiquito_nuevo'   => $reparto_finiquito_nuevo,
            'descuento_saneamiento'     => $descuento_saneamiento,
            'descuento_aprovechamiento' => $descuento_aprovechamiento,
            'descuento_asambleas'       => $descuento_asambleas,
        ]);
    }
}