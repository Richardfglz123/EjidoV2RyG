<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ejidatario;
use App\Models\Utilidad;
use App\Models\CatalogoMulta;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ConcentradoFinalExport;

class ConcentradoController extends Controller
{
    public function exportarExcel()
    {
        return Excel::download(new ConcentradoFinalExport, 'CONCENTRADO_FINAL_2025.xlsx');
    }
}