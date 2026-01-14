<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ActividadesExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $q = DB::table('Actividad');

        if ($this->request->filled('fecha_inicio') && $this->request->filled('fecha_fin')) {
            $q->whereBetween('FechaInicio', [
                $this->request->fecha_inicio,
                $this->request->fecha_fin
            ]);
        }

        if ($this->request->filled('mes')) {
            $q->whereMonth('FechaInicio', $this->request->mes);
        }

        if ($this->request->filled('anio')) {
            $q->whereYear('FechaInicio', $this->request->anio);
        }

        return $q->get([
            'Tipo',
            'Descripcion',
            'FechaInicio',
            'FechaFin',
            'Estado_Actividad',
            'Registro_Original',
            'Nueva_Fecha',
            'Fecha_Realizo'
        ]);
    }

    public function headings(): array
    {
        return [
            'Tipo',
            'Descripción',
            'Fecha Inicio',
            'Fecha Fin',
            'Estado',
            'Registro',
            'Nueva Fecha',
            'Realizado'
        ];
    }
}
