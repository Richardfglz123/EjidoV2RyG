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

class DatosHistoricosExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    ShouldAutoSize,
    WithCustomStartCell,
    WithEvents
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function startCell(): string
    {
        return 'A4';
    }

    public function collection()
    {
        $q = DB::table('Datos_Historicos')
            ->whereNull('Fecha_Eliminado');

        if ($this->request->filled('fecha_inicio') && $this->request->filled('fecha_fin')) {
            $q->whereBetween('Fecha', [
                $this->request->fecha_inicio,
                $this->request->fecha_fin
            ]);
        }

        if ($this->request->filled('mes')) {
            $q->whereMonth('Fecha', $this->request->mes);
        }

        if ($this->request->filled('anio')) {
            $q->whereYear('Fecha', $this->request->anio);
        }

        return $q->orderBy('Fecha', 'desc')->get([
            'Titulo',
            'Descripcion',
            'Fecha'
        ]);
    }

    public function headings(): array
    {
        return ['Título', 'Descripción', 'Fecha'];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:C1');
                $sheet->setCellValue('A1', 'SISTEMA EJIDAL - REPORTE DE DATOS HISTÓRICOS');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '00A651']],
                    'alignment' => ['horizontal' => 'center']
                ]);

                $sheet->mergeCells('A2:C2');
                $sheet->setCellValue('A2', 'Generado el: ' . date('d/m/Y H:i A') . ' | Registros: ' . ($sheet->getHighestRow() - 3));
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 10],
                    'alignment' => ['horizontal' => 'center']
                ]);

                $lastRow = $sheet->getHighestRow();
                $footerRow = $lastRow + 2;
                $sheet->mergeCells("A{$footerRow}:C{$footerRow}");
                $sheet->setCellValue("A{$footerRow}", 'Sistema Ejidal — Reporte histórico generado automáticamente');
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

        $sheet->getStyle('A4:C4')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => '00A651']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        $sheet->getStyle("A4:C{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        $sheet->getStyle("C5:C{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        return $sheet;
    }
}
