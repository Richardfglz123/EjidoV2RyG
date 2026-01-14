<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DatosHistoricosExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $q = DB::table('Datos_Historicos')
            ->whereNull('Fecha_Eliminado');

        if ($this->request->filled('fecha_inicio') && $this->request->filled('fecha_fin')) {
            $q->whereBetween('Fecha', [
                $this->request->fecha_inicio,
                $this->request->fecha_fin
            ]);
        }

        if ($this->request->filled('mes')) {
            $q->whereMonth('Fecha', $this->request->mes);
        }

        if ($this->request->filled('anio')) {
            $q->whereYear('Fecha', $this->request->anio);
        }

        return $q->orderBy('Fecha', 'desc')->get([
            'Titulo',
            'Descripcion',
            'Fecha'
        ]);
    }

    public function headings(): array
    {
        return ['Título', 'Descripción', 'Fecha'];
    }
}
