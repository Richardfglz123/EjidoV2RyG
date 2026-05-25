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

        $sesionesAsambleasIds = $this->obtenerSesiones('asamblea%');
        $sesionesFaenasIds = $this->obtenerSesiones('faena%');

        return DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->select('e.Id_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno')
            ->get()
            ->map(function ($ejid) use ($sesionesAsambleasIds, $sesionesFaenasIds, $costoAsamblea, $costoFaena, $montoFijoR2) {

                // Cálculos de Deuda
                $deudaR1 = $this->calcularDeudaR1($ejid->Id_Ejidatario);

                $asistencias = DB::table('PaseLista')->where('Id_Ejidatario', $ejid->Id_Ejidatario)->where('Asistencia', 1)->whereNotNull('Id_Sesion')->pluck('Id_Sesion')->toArray();
                $reprosAs = DB::table('PaseLista')->where('Id_Ejidatario', $ejid->Id_Ejidatario)->where('Asistencia', 1)->whereNull('Id_Sesion')->where('Id_Actividad', 1)->count();
                $reprosFa = DB::table('PaseLista')->where('Id_Ejidatario', $ejid->Id_Ejidatario)->where('Asistencia', 1)->whereNull('Id_Sesion')->where('Id_Actividad', 2)->count();

                $totalDescJuntas = max(0, count(array_diff($sesionesAsambleasIds, $asistencias)) - $reprosAs) * $costoAsamblea;
                $totalDescFaenas = max(0, count(array_diff($sesionesFaenasIds, $asistencias)) - $reprosFa) * $costoFaena;

                // Cálculo de Total a Pagar asegurando no negativo
                $totalPagar = max(0, $montoFijoR2 - ($totalDescJuntas + $totalDescFaenas + $deudaR1));

                return [
                    'No' => $ejid->Id_Ejidatario,
                    'Nombre' => "{$ejid->Nombres} {$ejid->Apellido_Paterno} {$ejid->Apellido_Materno}",
                    'Situacion' => 'Activo',
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
                    'TotalPagar' => $totalPagar
                ];
            });
    }

    private function obtenerSesiones($like) {
        return DB::table('Sesion')->join('Evento as e', 'Sesion.Id_Referencia', '=', 'e.Id_Evento')
            ->join('Categoria_Evento as c', 'e.Id_Categoria_Evento', '=', 'c.Id_Categoria_Evento')
            ->where('c.Clave_Categoria', 'LIKE', $like)->pluck('Sesion.Id_Sesion')->toArray();
    }

    private function calcularDeudaR1($id) {
        $prestamo = DB::table('Prestamo')->where('Id_Ejidatario', $id)->where('Id_Utilidad', 1)->sum('Cantidad') ?? 0;
        $abonos = DB::table('Abono')->join('Prestamo', 'Abono.Id_Prestamo', '=', 'Prestamo.Id_Prestamo')->where('Prestamo.Id_Ejidatario', $id)->sum('Abono.Monto') ?? 0;
        return max(0, $prestamo - $abonos);
    }

    public function headings(): array {
        return [['Sistema Ejidal San Rafael Ixtapalucan'], ['No.', 'NOMBRE', 'SITUACION', '1ra Asamblea', 'Asamblea extraordinaria', 'Diciembre', 'Enero', 'Marzo', 'Junio', 'REP. SANEAMIENTO', '1ER REPARTO', '2DO REPARTO', 'FINIQUITO', 'FAENA SAN.', 'FAENA APR.', 'TOTAL DESC. JUNTAS', 'TOTAL DESC. FAENAS', 'TOTAL A PAGAR']];
    }

    public function columnFormats(): array {
        return ['K' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, 'L' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, 'P' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, 'Q' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, 'R' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE];
    }

    public function styles(Worksheet $sheet) {
        $sheet->mergeCells('A1:R1');
        return [
            1 => ['font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '006400']]],
            2 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2E8B57']]]
        ];
    }
}