<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use PDF;
use App\Exports\EjidatariosExport;

class ReportesEController extends Controller
{
    public function GenerarPDF()
    {
        // Esta es la parte CLAVE: los JOINS para traer los nombres reales
        $ejidatarios = DB::table('Ejidatario')
            ->join('Usuario', 'Ejidatario.Id_Usuario', '=', 'Usuario.Id_Usuario')
            ->join('Estatus', 'Ejidatario.Id_Estatus', '=', 'Estatus.Id_Estatus')
            ->select(
                'Ejidatario.*',
                'Usuario.Nombres',
                'Usuario.Apellido_Paterno',
                'Estatus.Estatus as NombreEstatus'
            )
            ->get();

        // Asegúrate de que la ruta de la vista sea la correcta
        $pdf = Pdf::loadView('cpanel.reportes.reporteE', ['data' => $ejidatarios]);

        return $pdf->stream('Reporte_Ejidatarios.pdf');
    }

    public function GenerarExcel()
    {
        return Excel::download(new EjidatariosExport, 'reporteEjidatarios.xlsx');
    }
}
