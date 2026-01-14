<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EjidatariosExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    ShouldAutoSize
{
    public function collection()
    {
        return DB::table('Ejidatario')
            ->select(
            // Se eliminó 'Id_Ejidatario'
                'Num_Ejidatario',
                DB::raw("CONCAT(Calle, ' #', Num_Exterior, ', ', Colonia, ', ', Municipio)"),
                'CURP'
            )->get();
    }

    public function headings(): array
    {
        return [
            // Se eliminó 'ID Ejidatario'
            'Número',
            'Dirección Completa',
            'CURP'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // El rango se ajustó a A1:C1 (3 columnas)
        $sheet->getStyle('A1:C1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'name' => 'Arial',
                'size' => 12
            ],
            'fill' => [
                'fillType' => 'solid',
                'color' => ['rgb' => '00A651']
            ],
            'alignment' => [
                'horizontal' => 'center'
            ]
        ]);

        $lastRow = $sheet->getHighestRow();

        // Se ajustó el rango de bordes a A1:C{$lastRow}
        $sheet->getStyle("A1:C{$lastRow}")->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 11
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin',
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);
    }
}
