<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\UsuariosExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use PDF;

class ReportesUController extends Controller
{
    public function GenerarPDF(){
        $usuario = DB::table("usuario");
        $fila = $usuario->get();
        $pdf = PDF::loadView('cpanel/reportes/reporte',['data' => $fila]);
        return $pdf->stream('Reportesusuarios.pdf');
    }
    public function GenerarExcel()
    {
        return Excel::download(new usuariosExport, 'reporteusuarios.xlsx');
    }

}
