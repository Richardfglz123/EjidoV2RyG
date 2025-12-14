<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsuariosExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    ShouldAutoSize
{
    public function collection()
    {
        return DB::table('Usuario')->select(
            'Nombres',
            'Apellido_Paterno',
            'Apellido_Materno',
            'Usuario',
            'Correo',
            'Contraseña',
            'Telefono'
        )->get();
    }

    public function headings(): array
    {
        return [
            'Nombres',
            'Apellido Paterno',
            'Apellido Materno',
            'Usuario',
            'Correo',
            'Contraseña',
            'Teléfono'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Encabezados con estilo
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'name' => 'Arial',
                'size' => 12
            ],
            'fill' => [
                'fillType' => 'solid',
                'color' => ['rgb' => '00A651'] // VERDE
            ],
            'alignment' => [
                'horizontal' => 'center'
            ]
        ]);

        // Bordes
        $lastRow = $sheet->getHighestRow();

        $sheet->getStyle("A1:G{$lastRow}")->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 12
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
