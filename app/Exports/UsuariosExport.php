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

class usuariosExport implements
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
        return DB::table('usuario')->select(
            'Nombres',
            DB::raw("CONCAT(Apellido_Paterno, ' ', Apellido_Materno) as Apellidos"),
            'usuario',
            'Correo',
            'Telefono'
        )->get();
    }

    public function headings(): array
    {
        return [
            'Nombres',
            'Apellidos',
            'Nombre de usuario',
            'Correo Electrónico',
            'Teléfono'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:E1');
                $sheet->setCellValue('A1', 'SISTEMA EJIDAL - REPORTE DE usuarioS');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '00A651']],
                    'alignment' => ['horizontal' => 'center']
                ]);

                $sheet->mergeCells('A2:E2');
                $sheet->setCellValue('A2', 'Generado el: ' . date('d/m/Y H:i A') . ' | usuarios registrados: ' . ($sheet->getHighestRow() - 4));
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 10],
                    'alignment' => ['horizontal' => 'center']
                ]);

                $lastRow = $sheet->getHighestRow();
                $footerRow = $lastRow + 2;
                $sheet->mergeCells("A{$footerRow}:E{$footerRow}");
                $sheet->setCellValue("A{$footerRow}", 'Sistema Ejidal — Reporte de control de acceso generado automáticamente');
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

        $sheet->getStyle('A4:E4')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'name' => 'Arial',
                'size' => 12
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => '00A651']
            ],
            'alignment' => [
                'horizontal' => 'center'
            ]
        ]);

        $sheet->getStyle("A4:E{$lastRow}")->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 11
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        $sheet->getStyle("C5:C{$lastRow}")->getAlignment()->setHorizontal('center');
        $sheet->getStyle("E5:E{$lastRow}")->getAlignment()->setHorizontal('center');

        return $sheet;
    }
}
