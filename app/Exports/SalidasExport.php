<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalidasExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    ShouldAutoSize,
    WithCustomStartCell,
    WithEvents
{
    public function startCell(): string
    {
        return 'A4';
    }

    public function collection()
    {
        // Usamos los nombres exactos de tu CREATE TABLE
        return DB::table('Salida')
            ->join('Articulos', 'Salida.Id_Articulo', '=', 'Articulos.Id_Articulo')
            ->select(
                'Articulos.descripcion as articulo', // Verifica si en Articulos es 'descripcion' o 'Descripcion'
                'Salida.Cantidad',
                'Salida.Tipo_Salida',
                'Salida.Fecha', // <--- Antes decía fecha_salida, por eso fallaba
                'Salida.Responsable'
            )->get();
    }

    public function headings(): array
    {
        return [
            'Artículo / Material',
            'Cantidad',
            'Tipo de Salida',
            'Fecha de Registro',
            'Responsable'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // 1. Título del Reporte
                $sheet->mergeCells('A1:E1');
                $sheet->setCellValue('A1', 'SISTEMA EJIDAL - REPORTE DE SALIDAS DE INVENTARIO');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '00A651']],
                    'alignment' => ['horizontal' => 'center']
                ]);

                // 2. Información de fecha y total
                $totalRegistros = $sheet->getHighestRow() - 4;
                $sheet->mergeCells('A2:E2');
                $sheet->setCellValue('A2', 'Generado el: ' . date('d/m/Y H:i A') . ' | Total movimientos: ' . $totalRegistros);
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 10],
                    'alignment' => ['horizontal' => 'center']
                ]);

                // 3. Pie de página
                $lastRow = $sheet->getHighestRow();
                $footerRow = $lastRow + 2;
                $sheet->mergeCells("A{$footerRow}:E{$footerRow}");
                $sheet->setCellValue("A{$footerRow}", 'Sistema Ejidal — Reporte de control de inventario generado automáticamente');
                $sheet->getStyle("A{$footerRow}")->applyFromArray([
                    'font' => ['italic' => true, 'color' => ['rgb' => '777777']],
                    'alignment' => ['horizontal' => 'center']
                ]);
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        // Estilo del Encabezado (Verde Institucional)
        $sheet->getStyle('A4:E4')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => '00A651']
            ],
            'alignment' => ['horizontal' => 'center']
        ]);

        // Bordes y fuente para los datos
        $sheet->getStyle("A4:E{$lastRow}")->applyFromArray([
            'font' => ['name' => 'Arial', 'size' => 11],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ]
            ]
        ]);

        // Centrar Cantidad y Fecha
        $sheet->getStyle("B5:B{$lastRow}")->getAlignment()->setHorizontal('center');
        $sheet->getStyle("D5:D{$lastRow}")->getAlignment()->setHorizontal('center');

        return $sheet;
    }
}
