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
                'Id_Ejidatario',
                'Num_Ejidatario',
                // Concatenamos las columnas reales de tu DB para formar la "Direccion"
                DB::raw("CONCAT(Calle, ' #', Num_Exterior, ', ', Colonia, ', ', Municipio)"),
                'CURP' // Usamos CURP ya que No_Parcela no existe en tu tabla SQL
            )->get();
    }

    public function headings(): array
    {
        return [
            'ID Ejidatario',
            'Número',
            'Dirección Completa',
            'CURP'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // El rango sigue siendo A1:D1 porque tenemos 4 columnas
        $sheet->getStyle('A1:D1')->applyFromArray([
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

        $sheet->getStyle("A1:D{$lastRow}")->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 11 // Bajé un poco el tamaño para que quepa mejor la dirección
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
