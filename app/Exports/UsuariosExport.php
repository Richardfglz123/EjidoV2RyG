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
        // Se eliminó 'Contraseña' de la selección
        return DB::table('Usuario')->select(
            'Nombres',
            'Apellido_Paterno',
            'Apellido_Materno',
            'Usuario',
            'Correo',
            'Telefono'
        )->get();
    }

    public function headings(): array
    {
        // Se eliminó 'Contraseña' de los encabezados
        return [
            'Nombres',
            'Apellido Paterno',
            'Apellido Materno',
            'Usuario',
            'Correo',
            'Teléfono'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Ajustado el rango a la columna F (ya que ahora son 6 columnas en lugar de 7)
        $sheet->getStyle('A1:F1')->applyFromArray([
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

        $lastRow = $sheet->getHighestRow();

        // Ajustado el rango de bordes a la columna F
        $sheet->getStyle("A1:F{$lastRow}")->applyFromArray([
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
