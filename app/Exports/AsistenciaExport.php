<?php

namespace App\Exports;

use App\Models\Ejidatario;
use App\Models\PaseLista;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AsistenciaExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $id_sesion;

    public function __construct($id_sesion)
    {
        $this->id_sesion = $id_sesion;
    }

    public function collection()
    {
        return Ejidatario::all();
    }

    public function headings(): array
    {
        return [
            ['Reporte Detallado de Asistencia'],
            ['ID', 'Nombre Completo', 'Estatus']
        ];
    }

    public function map($ejidatario): array
    {
        $asistio = PaseLista::where('Id_Sesion', $this->id_sesion)
            ->where('Id_Ejidatario', $ejidatario->Id_Ejidatario)
            ->exists();

        return [
            $ejidatario->Id_Ejidatario,
            $ejidatario->Nombre . ' ' . $ejidatario->Apellido_Paterno . ' ' . $ejidatario->Apellido_Materno,
            $asistio ? 'ASISTIÓ' : 'FALTA'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true, 'size' => 14]],
            2    => ['font' => ['bold' => true]],
        ];
    }
}