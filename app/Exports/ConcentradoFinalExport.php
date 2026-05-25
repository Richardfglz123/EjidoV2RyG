<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ConcentradoFinalExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    public function collection()
    {
        $anoActual = now()->year;
        $montoFijoR2 = DB::table('Utilidad')->where('Id_Utilidad', 2)->value('Monto') ?? 3000;

        $precios = DB::table('Catalogo_Multa')->where('Año', $anoActual)->get();
        $costoAsamblea = $precios->where('Tipo', 'Asamblea')->first()->Costo ?? 0;
        $costoFaena = $precios->where('Tipo', 'Faena')->first()->Costo ?? 0;

        // Obtener IDs de sesiones dinámicamente, igual que en el Controlador
        $sesionesAsambleasIds = DB::table('Sesion')
            ->join('Evento as e', 'Sesion.Id_Referencia', '=', 'e.Id_Evento')
            ->join('Categoria_Evento as c', 'e.Id_Categoria_Evento', '=', 'c.Id_Categoria_Evento')
            ->where('c.Clave_Categoria', 'LIKE', 'asamblea%')
            ->where('Sesion.Tipo', 'Evento')
            ->pluck('Sesion.Id_Sesion')->toArray();

        $sesionesFaenasIds = DB::table('Sesion')
            ->join('Evento as e', 'Sesion.Id_Referencia', '=', 'e.Id_Evento')
            ->join('Categoria_Evento as c', 'e.Id_Categoria_Evento', '=', 'c.Id_Categoria_Evento')
            ->where('c.Clave_Categoria', 'LIKE', 'faena%')
            ->where('Sesion.Tipo', 'Evento')
            ->pluck('Sesion.Id_Sesion')->toArray();

        return DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->get()
            ->map(function ($ejid) use ($sesionesAsambleasIds, $sesionesFaenasIds, $costoAsamblea, $costoFaena, $montoFijoR2) {

                // Cálculo de Deuda R1
                $totalPrestamo = DB::table('Prestamo')->where('Id_Ejidatario', $ejid->Id_Ejidatario)->where('Id_Utilidad', 1)->sum('Cantidad') ?? 0;
                $totalAbonos = DB::table('Abono')->join('Prestamo', 'Abono.Id_Prestamo', '=', 'Prestamo.Id_Prestamo')->where('Prestamo.Id_Ejidatario', $ejid->Id_Ejidatario)->sum('Abono.Monto') ?? 0;
                $deudaR1 = max(0, $totalPrestamo - $totalAbonos);

                // Cálculo de Asistencias y Faltas (Lógica unificada)
                $asistencias = DB::table('PaseLista')->where('Id_Ejidatario', $ejid->Id_Ejidatario)->where('Asistencia', 1)->whereNotNull('Id_Sesion')->pluck('Id_Sesion')->toArray();
                $reprosAs = DB::table('PaseLista')->where('Id_Ejidatario', $ejid->Id_Ejidatario)->where('Asistencia', 1)->whereNull('Id_Sesion')->where('Id_Actividad', 1)->count();
                $reprosFa = DB::table('PaseLista')->where('Id_Ejidatario', $ejid->Id_Ejidatario)->where('Asistencia', 1)->whereNull('Id_Sesion')->where('Id_Actividad', 2)->count();

                $totalDescJuntas = max(0, count(array_diff($sesionesAsambleasIds, $asistencias)) - $reprosAs) * $costoAsamblea;
                $totalDescFaenas = max(0, count(array_diff($sesionesFaenasIds, $asistencias)) - $reprosFa) * $costoFaena;

                return [
                    'No' => $ejid->Id_Ejidatario,
                    'Nombre' => "{$ejid->Nombres} {$ejid->Apellido_Paterno} {$ejid->Apellido_Materno}",
                    'Situacion' => 'Activo', // Ajustar según tu lógica de Estatus
                    'As1' => in_array($sesionesAsambleasIds[0]??0, $asistencias) ? 'Asistió' : 'Falta',
                    'As2' => in_array($sesionesAsambleasIds[1]??0, $asistencias) ? 'Asistió' : 'Falta',
                    'As3' => in_array($sesionesAsambleasIds[2]??0, $asistencias) ? 'Asistió' : 'Falta',
                    'As4' => in_array($sesionesAsambleasIds[3]??0, $asistencias) ? 'Asistió' : 'Falta',
                    'As5' => in_array($sesionesAsambleasIds[4]??0, $asistencias) ? 'Asistió' : 'Falta',
                    'As6' => in_array($sesionesAsambleasIds[5]??0, $asistencias) ? 'Asistió' : 'Falta',
                    'RepSaneamiento' => 0,
                    'R1' => $deudaR1,
                    'R2' => $montoFijoR2,
                    'Finiquito' => 0,
                    'FaenaSan' => 0,
                    'FaenaApr' => 0,
                    'DescJuntas' => $totalDescJuntas,
                    'DescFaenas' => $totalDescFaenas,
                    'TotalPagar' => $montoFijoR2 - ($totalDescJuntas + $totalDescFaenas + $deudaR1)
                ];
            });
    }

    public function headings(): array
    {
        return [
            ['Sistema Ejidal San Rafael Ixtapalucan'],
            ['No.', 'NOMBRE', 'SITUACION', '1ra Asamblea', 'Asamblea extraordinaria', 'Diciembre', 'Enero', 'Marzo', 'Junio', 'REP. SANEAMIENTO', '1ER REPARTO', '2DO REPARTO', 'FINIQUITO', 'FAENA SAN.', 'FAENA APR.', 'TOTAL DESC. JUNTAS', 'TOTAL DESC. FAENAS', 'TOTAL A PAGAR']
        ];
    }

    public function columnFormats(): array
    {
        // Formato moneda para columnas J, K, L, M, N, O, P, Q, R (índices base 1)
        return [
            'K' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE,
            'L' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE,
            'P' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE,
            'Q' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE,
            'R' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:R1');
        return [
            1 => ['font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '006400']]],
            2 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2E8B57']]]
        ];
    }
}