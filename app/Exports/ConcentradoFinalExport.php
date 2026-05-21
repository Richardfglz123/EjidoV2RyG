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
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ConcentradoFinalExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    ShouldAutoSize,
    WithCustomStartCell,
    WithEvents
{
    private $asambleas;
    private $faenas;

    public function __construct()
    {
        $anoActual = now()->year;

        $this->asambleas = DB::table('Sesion')
            ->join('Evento', 'Sesion.Id_Referencia', '=', 'Evento.Id_Evento')
            ->join('Categoria_Evento as c', 'Evento.Id_Categoria_Evento', '=', 'c.Id_Categoria_Evento')
            ->whereIn('Evento.Id_Categoria_Evento', [1,2,3,4,5,6,7,8])
            ->whereYear('Sesion.Fecha', $anoActual)
            ->select(
                'Sesion.Id_Sesion',
                'c.Nombre_Categoria'
            )
            ->distinct()
            ->get();

        $this->faenas = DB::table('Sesion')
            ->join('Evento', 'Sesion.Id_Referencia', '=', 'Evento.Id_Evento')
            ->join('Categoria_Evento as c', 'Evento.Id_Categoria_Evento', '=', 'c.Id_Categoria_Evento')
            ->whereIn('Evento.Id_Categoria_Evento', [9,10])
            ->whereYear('Sesion.Fecha', $anoActual)
            ->select(
                'Sesion.Id_Sesion',
                'c.Nombre_Categoria'
            )
            ->distinct()
            ->get();
    }

    public function startCell(): string
    {
        return 'A4';
    }

    public function collection()
    {
        $anoActual = now()->year;

        $montoR1 = DB::table('Utilidad')->where('Id_Utilidad', 1)->value('Monto') ?? 0;
        $montoR2 = DB::table('Utilidad')->where('Id_Utilidad', 2)->value('Monto') ?? 0;
        $montoSaneamiento = DB::table('Utilidad')
            ->where('Tipo_Reparto', 'REPARTO FINIQUITO')
            ->value('Monto') ?? 0;

        $montoFiniquitoU = DB::table('Utilidad')
            ->where('Tipo_Reparto', 'FINIQUITO UTILIDADES')
            ->value('Monto') ?? 0;

        $costoAsamblea = DB::table('Catalogo_Multa')
            ->where('Año', $anoActual)
            ->where('Tipo', 'Asamblea')
            ->value('Costo') ?? 0;

        $costoFaena = DB::table('Catalogo_Multa')
            ->where('Año', $anoActual)
            ->where('Tipo', 'Faena')
            ->value('Costo') ?? 0;

        return DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->select(
                'e.Id_Ejidatario',
                'e.Id_Estatus',
                'u.Nombres',
                'u.Apellido_Paterno',
                'u.Apellido_Materno'
            )
            ->get()

            ->map(function ($ejid, $index) use (
                $montoR1,
                $montoR2,
                $montoSaneamiento,
                $montoFiniquitoU,
                $costoAsamblea,
                $costoFaena
            ) {

                $fila = [
                    'No' => $index + 1,

                    'Nombre' =>
                        "{$ejid->Nombres} {$ejid->Apellido_Paterno} {$ejid->Apellido_Materno}",

                    'Situacion' =>
                        ($ejid->Id_Estatus == 1)
                            ? 'VIGENTE'
                            : 'S/P',
                ];

                $totalFaltasJuntas = 0;

                foreach ($this->asambleas as $sesion) {

                    $asistio = DB::table('PaseLista')
                        ->where('Id_Sesion', $sesion->Id_Sesion)
                        ->where('Id_Ejidatario', $ejid->Id_Ejidatario)
                        ->where('Asistencia', 1)
                        ->exists();

                    $multa = $asistio ? 0 : $costoAsamblea;

                    $fila[] = $multa > 0 ? $multa : '';

                    if (!$asistio) {
                        $totalFaltasJuntas += $costoAsamblea;
                    }
                }

                $totalFaltasFaenas = 0;

                foreach ($this->faenas as $sesion) {

                    $asistio = DB::table('PaseLista')
                        ->where('Id_Sesion', $sesion->Id_Sesion)
                        ->where('Id_Ejidatario', $ejid->Id_Ejidatario)
                        ->where('Asistencia', 1)
                        ->exists();

                    $multa = $asistio ? 0 : $costoFaena;

                    $fila[] = $multa > 0 ? $multa : '';

                    if (!$asistio) {
                        $totalFaltasFaenas += $costoFaena;
                    }
                }

                $prestamosR1 = DB::table('Prestamo')
                    ->where('Id_Ejidatario', $ejid->Id_Ejidatario)
                    ->where('Id_Utilidad', 1)
                    ->sum('Cantidad');

                $abonosR1 = DB::table('Abono')
                    ->join('Prestamo', 'Abono.Id_Prestamo', '=', 'Prestamo.Id_Prestamo')
                    ->where('Prestamo.Id_Ejidatario', $ejid->Id_Ejidatario)
                    ->sum('Abono.Monto');

                $deudaArrastrada = max(0, $prestamosR1 - $abonosR1);

                $fila['Saneamiento'] = $montoSaneamiento;
                $fila['R1'] = $montoR1;
                $fila['R2'] = $montoR2;
                $fila['Finiquito'] = $montoFiniquitoU;

                $fila['Desc_Juntas'] = $totalFaltasJuntas;
                $fila['Desc_Faenas'] = $totalFaltasFaenas;

                $fila['Total_Pagar'] =
                    (
                        $montoR1 +
                        $montoR2 +
                        $montoSaneamiento +
                        $montoFiniquitoU
                    )
                    -
                    (
                        $totalFaltasJuntas +
                        $totalFaltasFaenas +
                        $deudaArrastrada
                    );

                return $fila;
            });
    }

    public function headings(): array
    {
        $headers = [
            'No.',
            'NOMBRE DE EJIDATARIO',
            'SITUACION',
        ];

        foreach ($this->asambleas as $asamblea) {
            $headers[] = $asamblea->Nombre_Categoria;
        }

        foreach ($this->faenas as $faena) {
            $headers[] = $faena->Nombre_Categoria;
        }

        $headers = array_merge($headers, [
            'SANEAMIENTO',
            '1ER REPARTO',
            '2DO REPARTO',
            'FINIQUITO',
            'DESC. JUNTAS',
            'DESC. FAENAS',
            'TOTAL A PAGAR'
        ]);

        return $headers;
    }

    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function(AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $lastColumn = $sheet->getHighestColumn();
                $lastRow = $sheet->getHighestRow();

                // TITULO
                $sheet->mergeCells("A1:{$lastColumn}1");

                $sheet->setCellValue(
                    'A1',
                    'SISTEMA EJIDAL - CONCENTRADO FINAL DE APORTACIONES Y DESCUENTOS'
                );

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => '00A651']
                    ],
                    'alignment' => [
                        'horizontal' => 'center'
                    ]
                ]);

                // SUBTITULO
                $sheet->mergeCells("A2:{$lastColumn}2");

                $sheet->setCellValue(
                    'A2',
                    'Generado el: '
                    . date('d/m/Y H:i A')
                    . ' | Total de ejidatarios: '
                    . ($lastRow - 4)
                );

                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 10
                    ],
                    'alignment' => [
                        'horizontal' => 'center'
                    ]
                ]);

                // FOOTER
                $footerRow = $lastRow + 2;

                $sheet->mergeCells("A{$footerRow}:{$lastColumn}{$footerRow}");

                $sheet->setCellValue(
                    "A{$footerRow}",
                    'Sistema Ejidal — Reporte generado automáticamente'
                );

                $sheet->getStyle("A{$footerRow}")->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'color' => ['rgb' => '777777']
                    ],
                    'alignment' => [
                        'horizontal' => 'center'
                    ]
                ]);
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();

        // HEADER
        $sheet->getStyle("A4:{$lastColumn}4")->applyFromArray([

            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'name' => 'Arial',
                'size' => 11
            ],

            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '00A651']
            ],

            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
                'wrapText' => true
            ]
        ]);

        // TABLA
        $sheet->getStyle("A4:{$lastColumn}{$lastRow}")
            ->applyFromArray([

                'font' => [
                    'name' => 'Arial',
                    'size' => 10
                ],

                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ]);

        // CENTRAR COLUMNAS
        $sheet->getStyle("A5:{$lastColumn}{$lastRow}")
            ->getAlignment()
            ->setHorizontal('center');

        // ALTURA HEADER
        $sheet->getRowDimension(4)->setRowHeight(35);

        return [];
    }
}