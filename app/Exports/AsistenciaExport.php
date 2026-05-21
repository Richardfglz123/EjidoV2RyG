<?php

namespace App\Exports;

use App\Models\Ejidatario;
use Illuminate\Support\Facades\DB;
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
        return Ejidatario::with('usuario')->get();
    }

    public function headings(): array
    {
        return [
            ['Reporte Detallado de Asistencia'],
            ['Núm. Ejidatario', 'Nombre Completo', 'Estatus']
        ];
    }

    public function map($ejidatario): array
    {
        $asistio = DB::table('asistencia_sesion')
            ->where('Id_Sesion', $this->id_sesion)
            ->where('Id_Ejidatario', $ejidatario->Id_Ejidatario)
            ->exists();

        $nombreCompleto = trim(
            ($ejidatario->usuario->Nombres ?? '') . ' ' .
            ($ejidatario->usuario->Apellido_Paterno ?? '') . ' ' .
            ($ejidatario->usuario->Apellido_Materno ?? '')
        );

        return [
            $ejidatario->Num_Ejidatario,
            strtoupper($nombreCompleto),
            $asistio ? 'ASISTIÓ' : 'FALTA'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:C1');

        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 14
                ],
                'alignment' => [
                    'horizontal' => 'center'
                ]
            ],

            2 => [
                'font' => [
                    'bold' => true
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => [
                        'rgb' => 'E9ECEF'
                    ]
                ]
            ],
        ];
    }
}