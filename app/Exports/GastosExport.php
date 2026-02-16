<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithCustomStartCell, WithEvents};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GastosExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithCustomStartCell, WithEvents
{
    public function startCell(): string { return 'A4'; }

    public function collection() {
        return DB::table('Gastos')->select('Responsable', 'Fecha', 'Monto', 'Concepto', 'Medida')->get();
    }

    public function headings(): array {
        return ['Responsable', 'Fecha de Gasto', 'Monto ($)', 'Concepto', 'Medida'];
    }

    public function registerEvents(): array {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                // Título
                $sheet->mergeCells('A1:E1');
                $sheet->setCellValue('A1', 'SISTEMA EJIDAL - REPORTE DE EGRESOS');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '00A651']],
                    'alignment' => ['horizontal' => 'center']
                ]);

                $totalRow = $lastRow + 1;
                $sheet->setCellValue("B{$totalRow}", 'TOTAL GENERAL:');
                $sheet->setCellValue("C{$totalRow}", "=SUM(C5:C{$lastRow})");
                $sheet->getStyle("B{$totalRow}:C{$totalRow}")->getFont()->setBold(true);
            },
        ];
    }

    public function styles(Worksheet $sheet) {
        $sheet->getStyle('A4:E4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '00A651']]
        ]);
        return $sheet;
    }
}
