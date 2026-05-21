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
        $montoFijoR2 = DB::table('Utilidad')->where('Id_Utilidad', 2)->value('Monto') ?? 3000;

        $precios = DB::table('Catalogo_Multa')->where('Año', $anoActual)->get();
        $costoAsamblea = $precios->where('Tipo', 'Asamblea')->first()->Costo ?? 0;
        $costoFaena = $precios->where('Tipo', 'Faena')->first()->Costo ?? 0;

        // Obtener sesiones clave para comparación
        $sesionesAsambleasIds = DB::table('Sesion')
            ->join('Evento', 'Sesion.Id_Referencia', '=', 'Evento.Id_Evento')
            ->join('Categoria_Evento', 'Evento.Id_Categoria_Evento', '=', 'Categoria_Evento.Id_Categoria_Evento')
            ->where('Categoria_Evento.Clave_Categoria', 'LIKE', 'asamblea%')
            ->pluck('Sesion.Id_Sesion')->toArray();

        $sesionesFaenasIds = DB::table('Sesion')
            ->join('Evento', 'Sesion.Id_Referencia', '=', 'Evento.Id_Evento')
            ->join('Categoria_Evento', 'Evento.Id_Categoria_Evento', '=', 'Categoria_Evento.Id_Categoria_Evento')
            ->where('Categoria_Evento.Clave_Categoria', 'LIKE', 'faena%')
            ->pluck('Sesion.Id_Sesion')->toArray();

        return DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->leftJoin('Estatus as es', 'e.Id_Estatus', '=', 'es.Id_Estatus')
            ->select('e.Id_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno', 'es.Estatus')
            ->get()
            ->map(function ($ejid) use ($sesionesAsambleasIds, $sesionesFaenasIds, $costoAsamblea, $costoFaena, $montoFijoR2) {

                // --- CÁLCULO DEUDA R1 ---
                $totalPrestamoR1 = DB::table('Prestamo')->where('Id_Ejidatario', $ejid->Id_Ejidatario)->where('Id_Utilidad', 1)->sum('Cantidad') ?? 0;
                $totalAbonosR1 = DB::table('Abono')->join('Prestamo', 'Abono.Id_Prestamo', '=', 'Prestamo.Id_Prestamo')->where('Prestamo.Id_Ejidatario', $ejid->Id_Ejidatario)->sum('Abono.Monto') ?? 0;
                $deudaR1 = max(0, $totalPrestamoR1 - $totalAbonosR1);

                // --- CÁLCULO DE ASISTENCIAS/REPROS (Lógica del Controlador) ---
                $asistencias = DB::table('PaseLista')->where('Id_Ejidatario', $ejid->Id_Ejidatario)->where('Asistencia', 1)->whereNotNull('Id_Sesion')->pluck('Id_Sesion')->toArray();
                $reprosAsambleas = DB::table('PaseLista')->where('Id_Ejidatario', $ejid->Id_Ejidatario)->where('Asistencia', 1)->whereNull('Id_Sesion')->where('Id_Actividad', 1)->count();
                $reprosFaenas = DB::table('PaseLista')->where('Id_Ejidatario', $ejid->Id_Ejidatario)->where('Asistencia', 1)->whereNull('Id_Sesion')->where('Id_Actividad', 2)->count();

                $faltasAsambleas = max(0, count(array_diff($sesionesAsambleasIds, $asistencias)) - $reprosAsambleas);
                $faltasFaenas = max(0, count(array_diff($sesionesFaenasIds, $asistencias)) - $reprosFaenas);

                $totalDescJuntas = $faltasAsambleas * $costoAsamblea;
                $totalDescFaenas = $faltasFaenas * $costoFaena;

                return [
                    'No' => $ejid->Id_Ejidatario,
                    'Nombre' => "{$ejid->Nombres} {$ejid->Apellido_Paterno} {$ejid->Apellido_Materno}",
                    'Situacion' => $ejid->Estatus ?? 'S/P',
                    'As1' => in_array($sesionesAsambleasIds[0]??0, $asistencias) ? 'Asistió' : 'Falta',
                    'As2' => in_array($sesionesAsambleasIds[1]??0, $asistencias) ? 'Asistió' : 'Falta',
                    'As3' => in_array($sesionesAsambleasIds[2]??0, $asistencias) ? 'Asistió' : 'Falta',
                    'As4' => in_array($sesionesAsambleasIds[3]??0, $asistencias) ? 'Asistió' : 'Falta',
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
        return [['No.', 'NOMBRE', 'SITUACION', 'ASAMB. 1', 'ASAMB. 2', 'ASAMB. 3', 'ASAMB. 4', 'REP. SANEAMIENTO', '1ER REPARTO', '2DO REPARTO', 'FINIQUITO', 'FAENA SAN.', 'FAENA APR.', 'DESC. JUNTAS', 'DESC. FAENAS', 'TOTAL A PAGAR']];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '000000']]]];
    }
}