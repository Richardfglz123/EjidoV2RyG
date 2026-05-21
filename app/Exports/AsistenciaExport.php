<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AsistenciaExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize
{
    protected $id_sesion;

    public function __construct($id_sesion)
    {
        $this->id_sesion = $id_sesion;
    }

    public function collection()
    {
        return DB::table('Ejidatario')
            ->join('usuario', 'Ejidatario.Id_Usuario', '=', 'usuario.Id_Usuario')
            ->select(
                'Ejidatario.Id_Ejidatario',
                'Ejidatario.Num_Ejidatario',
                'usuario.Nombres',
                'usuario.Apellido_Paterno',
                'usuario.Apellido_Materno'
            )
            ->get();
    }

    public function headings(): array
    {
        return [
            [
                'NÚM. EJIDATARIO',
                'NOMBRE COMPLETO',
                'ESTATUS'
            ]
        ];
    }

    public function map($ejidatario): array
    {
        $asistio = DB::table('asistencia_sesion')
            ->where('Id_Sesion', $this->id_sesion)
            ->where('Id_Ejidatario', $ejidatario->Id_Ejidatario)
            ->exists();

        $nombreCompleto = trim(
            $ejidatario->Nombres . ' ' .
            $ejidatario->Apellido_Paterno . ' ' .
            $ejidatario->Apellido_Materno
        );

        return [
            $ejidatario->Num_Ejidatario,
            strtoupper($nombreCompleto),
            $asistio ? 'ASISTIÓ' : 'FALTA'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $ultimaFila = $sheet->getHighestRow();

        // Encabezados
        $sheet->getStyle('A1:C1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => [
                    'rgb' => 'FFFFFF'
                ]
            ],

            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '198754'
                ]
            ],

            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],

            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ]
            ]
        ]);

        $sheet->getStyle("A1:C{$ultimaFila}")
            ->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => [
                            'rgb' => 'D3D3D3'
                        ]
                    ]
                ]
            ]);

        $sheet->getStyle("A1:A{$ultimaFila}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("C1:C{$ultimaFila}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getRowDimension(1)->setRowHeight(25);

        return [];
    }
}