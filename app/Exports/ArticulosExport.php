<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithCustomStartCell, WithEvents};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ArticulosExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithCustomStartCell, WithEvents
{
    public function startCell(): string { return 'A4'; }

    public function collection() {
        return DB::table('Articulos')->select('Descripcion', 'Cantidad', 'Estado', 'Medida', 'fecha_registro')->get();
    }

    public function headings(): array {
        return ['Descripción del Artículo', 'Stock Actual', 'Estado', 'Unidad de Medida', 'Fecha de Registro'];
    }

    public function registerEvents(): array {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->mergeCells('A1:E1');
                $sheet->setCellValue('A1', 'SISTEMA EJIDAL - INVENTARIO DE ARTÍCULOS');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '00A651']],
                    'alignment' => ['horizontal' => 'center']
                ]);
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
