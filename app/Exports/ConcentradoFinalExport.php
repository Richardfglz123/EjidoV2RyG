<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ConcentradoFinalExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        // 1. Nombres exactos de las asambleas (Deben coincidir con tu BD)
        $nombresAsambleas = [
            'ASAMBLEA ELECCION 30/10/24',
            'ASAMBLEA EXTRAORDINARIA 20/11/24',
            'ASAMBLEA 18 DICIEMBRE',
            'ASAMBLEA ENERO',
            'ASAMBLEA MARZO',
            'ASAMBLEA JUNIO',
            'ASAMBLEA SEPTIEMBRE CORTE DE CAJA'
        ];

        return DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->leftJoin('Estatus as es', 'e.Id_Estatus', '=', 'es.Id_Estatus')
            ->select('e.Id_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno', 'es.Estatus as NombreEstatus')
            ->get()
            ->map(function ($ejid) use ($nombresAsambleas) {

                $montoR2 = 3000;

                // Cálculo de deuda R1 (Prestamo - Abono)
                $totalPrestamoR1 = DB::table('Prestamo')->where('Id_Ejidatario', $ejid->Id_Ejidatario)->where('Id_Utilidad', 1)->sum('Cantidad');
                $totalAbonosR1 = DB::table('Abono')->join('Prestamo', 'Abono.Id_Prestamo', '=', 'Prestamo.Id_Prestamo')->where('Prestamo.Id_Ejidatario', $ejid->Id_Ejidatario)->sum('Abono.Monto');
                $deudaR1 = max(0, $totalPrestamoR1 - $totalAbonosR1);

                $fila = [
                    'No' => $ejid->Id_Ejidatario,
                    'Nombre' => "{$ejid->Nombres} {$ejid->Apellido_Paterno} {$ejid->Apellido_Materno}",
                    'Situacion' => $ejid->NombreEstatus ?? 'S/P',
                ];

                $totalDescJuntas = 0;
                foreach ($nombresAsambleas as $nombre) {
                    $evento = DB::table('Evento')->where('Nombre_Evento', 'LIKE', '%' . $nombre . '%')->first();
                    $multa = 0;
                    if ($evento) {
                        $sesion = DB::table('Sesion')->where('Id_Referencia', $evento->Id_Evento)->first();
                        $asistio = $sesion ? DB::table('PaseLista')->where('Id_Sesion', $sesion->Id_Sesion)->where('Id_Ejidatario', $ejid->Id_Ejidatario)->where('Asistencia', 1)->exists() : false;
                        if (!$asistio) {
                            $multa = DB::table('Catalogo_Multa')->where('Tipo', 'Asamblea')->orderBy('Año', 'desc')->value('Costo') ?? 0;
                        }
                    }
                    $fila[] = $multa > 0 ? $multa : '';
                    $totalDescJuntas += $multa;
                }

                $fila['RepartoSaneamiento'] = 0;
                $fila['R1'] = $deudaR1;
                $fila['R2'] = $montoR2;
                $fila['Finiquito'] = 0;
                $fila['FaenaSan'] = 0;
                $fila['FaenaApr'] = 0;
                $fila['DescJuntas'] = $totalDescJuntas;
                $fila['DescFaenas'] = 0;
                $fila['TotalPagar'] = $montoR2 - ($totalDescJuntas + $deudaR1);

                return $fila;
            });
    }

    public function headings(): array
    {
        return [
            ['CONCENTRADO FINAL DE APORTACIONES Y DESCUENTOS ' . now()->year],
            ['No.', 'NOMBRE DE EJIDATARIO', 'SITUACION', 'ASAMBLEA ELECCION 30/10/24', 'ASAMBLEA EXTRAORDINARIA 20/11/24', 'ASAMBLEA 18 DICIEMBRE', 'ASAMBLEA ENERO', 'ASAMBLEA MARZO', 'ASAMBLEA JUNIO', 'ASAMBLEA SEPTIEMBRE CORTE DE CAJA', 'REPARTO DE FINIQUITO DEL SANEAMIENTO', '1ER. REPARTO DE UTILIDAD', '2do. REPARTO DE UTILIDAD', 'FINIQUITO DE UTILIDAD', 'FAENAS SANEAMIENTO', 'FAENAS APROVECHAMIENTO', 'DESC. JUNTAS', 'DESC. FAENAS', 'TOTAL A PAGAR']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:S1');
        return [
            1 => ['font' => ['bold' => true, 'size' => 16], 'alignment' => ['horizontal' => 'center']],
            2 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '000000']],
                'alignment' => ['horizontal' => 'center']
            ],
        ];
    }
}