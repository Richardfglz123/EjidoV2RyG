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

class EntradasExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithCustomStartCell, WithEvents
{
    public function startCell(): string { return 'A4'; }

    public function collection()
    {
        return DB::table('Entrada')
            ->join('Articulos', 'Entrada.Id_Articulo', '=', 'Articulos.Id_Articulo')
            ->select(
                'Articulos.descripcion',
                'Entrada.Cantidad',
                'Entrada.Fecha',
                'Entrada.Observaciones'
            )->get();
    }

    public function headings(): array
    {
        return ['Artículo', 'Cantidad Ingresada', 'Fecha de Entrada', 'Observaciones'];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->mergeCells('A1:D1');
                $sheet->setCellValue('A1', 'SISTEMA EJIDAL - REPORTE DE ENTRADAS');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '00A651']],
                    'alignment' => ['horizontal' => 'center']
                ]);
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('A4:D4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '00A651']]
        ]);
        return $sheet;
    }
}
