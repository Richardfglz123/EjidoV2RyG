<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use PDF;
use App\Exports\EjidatariosExport;

class ReportesEController extends Controller
{
    public function GenerarPDF(){
        $Ejidatarios = DB::table("Ejidatario")->get();

        $pdf = PDF::loadView('cpanel/reportes/reporteE', [
            'data' => $Ejidatarios
        ]);

        return $pdf->stream('ReportesEjidatarios.pdf');
    }

    public function GenerarExcel()
    {
        return Excel::download(new EjidatariosExport, 'reporteEjidatarios.xlsx');
    }
}
