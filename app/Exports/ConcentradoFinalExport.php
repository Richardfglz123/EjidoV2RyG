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
        $anoActual = now()->year;

        $montoR1 = DB::table('Utilidad')->where('Id_Utilidad', 1)->value('Monto') ?? 0;
        $montoR2 = DB::table('Utilidad')->where('Id_Utilidad', 2)->value('Monto') ?? 0;
        $montoSaneamiento = DB::table('Utilidad')->where('Tipo_Reparto', 'REPARTO FINIQUITO')->value('Monto') ?? 0;
        $montoFiniquitoU = DB::table('Utilidad')->where('Tipo_Reparto', 'FINIQUITO UTILIDADES')->value('Monto') ?? 0;

        $costoAsamblea = DB::table('Catalogo_Multa')->where('Año', $anoActual)->where('Tipo', 'Asamblea')->value('Costo') ?? 0;
        $costoFaena = DB::table('Catalogo_Multa')->where('Año', $anoActual)->where('Tipo', 'Faena')->value('Costo') ?? 0;

        $sesionesAsambleas = DB::table('Sesion')
            ->join('Evento', 'Sesion.Id_Referencia', '=', 'Evento.Id_Evento')
            ->where('Evento.Id_Categoria_Evento', 1)
            ->whereYear('Sesion.Fecha', $anoActual)
            ->orderBy('Sesion.Fecha', 'asc')
            ->take(7)
            ->get();

        return DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->leftJoin('Estatus as es', 'e.Id_Estatus', '=', 'es.Id_Estatus')
            ->select('e.Id_Ejidatario', 'e.Id_Estatus', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno', 'es.Estatus as NombreEstatus')
            ->get()
            ->map(function ($ejid, $index) use ($sesionesAsambleas, $costoAsamblea, $costoFaena, $montoR1, $montoR2, $montoSaneamiento, $montoFiniquitoU) {

                // Definir si es vigente (ID 1 o nombre VIGENTE)
                $esVigente = ($ejid->Id_Estatus == 1);

                $fila = [
                    'No' => $index + 1,
                    'Nombre' => "{$ejid->Nombres} {$ejid->Apellido_Paterno} {$ejid->Apellido_Materno}",
                    'Situacion' => $ejid->NombreEstatus ?? 'S/P',
                ];

                $totalFaltasJuntas = 0;
                foreach ($sesionesAsambleas as $sesion) {
                    $asistio = DB::table('PaseLista')
                        ->where('Id_Sesion', $sesion->Id_Sesion)
                        ->where('Id_Ejidatario', $ejid->Id_Ejidatario)
                        ->where('Asistencia', 1)
                        ->exists();

                    $multa = $asistio ? 0 : $costoAsamblea;
                    $fila[] = $multa > 0 ? $multa : '';
                    if(!$asistio) $totalFaltasJuntas += $costoAsamblea;
                }

                for ($i = count($sesionesAsambleas); $i < 7; $i++) { $fila[] = ''; }

                $prestamosR1 = DB::table('Prestamo')->where('Id_Ejidatario', $ejid->Id_Ejidatario)->where('Id_Utilidad', 1)->sum('Cantidad');
                $abonosR1 = DB::table('Abono')->join('Prestamo', 'Abono.Id_Prestamo', '=', 'Prestamo.Id_Prestamo')
                    ->where('Prestamo.Id_Ejidatario', $ejid->Id_Ejidatario)->sum('Abono.Monto');
                $deudaArrastrada = max(0, $prestamosR1 - $abonosR1);

                $faltasFaenas = DB::table('Sesion')
                    ->join('Evento', 'Sesion.Id_Referencia', '=', 'Evento.Id_Evento')
                    ->where('Evento.Id_Categoria_Evento', '!=', 1)
                    ->whereNotExists(function ($query) use ($ejid) {
                        $query->select(DB::raw(1))
                            ->from('PaseLista')
                            ->whereColumn('PaseLista.Id_Sesion', 'Sesion.Id_Sesion')
                            ->where('PaseLista.Id_Ejidatario', $ejid->Id_Ejidatario)
                            ->where('Asistencia', 1);
                    })->count();
                $descFaenas = $faltasFaenas * $costoFaena;

                // Si no es vigente, los montos de reparto son 0
                $fila['Saneamiento'] = $esVigente ? $montoSaneamiento : 0;
                $fila['R1'] = $esVigente ? $montoR1 : 0;
                $fila['R2'] = $esVigente ? $montoR2 : 0;
                $fila['Finiquito'] = $esVigente ? $montoFiniquitoU : 0;
                $fila['Faena_San'] = 0;
                $fila['Faena_Apr'] = 0;
                $fila['Total_Desc_Juntas'] = $totalFaltasJuntas;
                $fila['Total_Desc_Faenas'] = $descFaenas;

                $sumaRepartos = $esVigente ? ($montoR1 + $montoR2 + $montoSaneamiento + $montoFiniquitoU) : 0;
                $fila['Total_Pagar'] = $sumaRepartos - ($totalFaltasJuntas + $descFaenas + $deudaArrastrada);

                return $fila;
            });
    }

    public function headings(): array
    {
        return [
            ['CONCENTRADO FINAL DE APORTACIONES Y DESCUENTOS 2026'],
            ['No.', 'NOMBRE DE EJIDATARIO', 'SITUACION', '1ER. ASAMBLEA 30/10/24', 'ASAMBLEA EXTRA. 20/11/24', 'J3', 'J4', 'J5', 'J6', 'J7', 'REPARTO DE FINIQUITO DEL SANEAMIENTO', '1ER REPARTO', '2DO REPARTO', 'FINIQUITO UTILIDADES', 'F. SAN', 'F. APR', 'DESC. JUNTAS', 'DESC. FAENAS', 'TOTAL A PAGAR']
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