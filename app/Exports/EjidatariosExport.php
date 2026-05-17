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

class EjidatariosExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    ShouldAutoSize,
    WithCustomStartCell,
    WithEvents
{
    // Iniciamos la tabla en la fila 4 para dejar espacio al título
    public function startCell(): string
    {
        return 'A4';
    }

    public function collection()
    {
        return DB::table('Ejidatario')
            ->join('usuario', 'Ejidatario.Id_usuario', '=', 'usuario.Id_usuario')
            ->join('Estatus', 'Ejidatario.Id_Estatus', '=', 'Estatus.Id_Estatus')
            ->select(
                'Ejidatario.Num_Ejidatario',
                DB::raw("CONCAT(Ejidatario.Calle, ' #', Ejidatario.Num_Exterior, ', ', Ejidatario.Colonia, ', ', Ejidatario.Municipio) as Direccion"),
                'Ejidatario.CURP',
                DB::raw("CONCAT(usuario.Nombres, ' ', usuario.Apellido_Paterno) as Responsable"),
                'Estatus.Estatus as NombreEstatus'
            )->get();
    }

    public function headings(): array
    {
        return [
            'Número',
            'Dirección Completa',
            'CURP',
            'Responsable',
            'Estatus'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // 1. Título del Reporte (Combinado de A a E)
                $sheet->mergeCells('A1:E1');
                $sheet->setCellValue('A1', 'SISTEMA EJIDAL - REPORTE DE EJIDATARIOS');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '00A651']],
                    'alignment' => ['horizontal' => 'center']
                ]);

                // 2. Subtítulo con info
                $sheet->mergeCells('A2:E2');
                $sheet->setCellValue('A2', 'Generado el: ' . date('d/m/Y H:i A') . ' | Registros totales: ' . ($sheet->getHighestRow() - 4));
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 10],
                    'alignment' => ['horizontal' => 'center']
                ]);

                // 3. Pie de página
                $lastRow = $sheet->getHighestRow();
                $footerRow = $lastRow + 2;
                $sheet->mergeCells("A{$footerRow}:E{$footerRow}");
                $sheet->setCellValue("A{$footerRow}", 'Sistema Ejidal — Documento de control generado automáticamente');
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

        // Estilo del Encabezado de Tabla (Fila 4)
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

        // Bordes y fuente para los datos
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

        // Centrar columnas específicas
        $sheet->getStyle("A5:A{$lastRow}")->getAlignment()->setHorizontal('center'); // Número
        $sheet->getStyle("E5:E{$lastRow}")->getAlignment()->setHorizontal('center'); // Estatus
    }
}
