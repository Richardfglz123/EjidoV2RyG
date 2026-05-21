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
        // Traemos a todos los ejidatarios
        return DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->select('e.Id_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno')
            ->get()
            ->map(function ($ejid) {

                // --- 1. LÓGICA DE ASAMBLEAS (Igual a la de tu pantalla) ---
                $multaAsamblea = DB::table('PaseLista')
                    ->where('Id_Ejidatario', $ejid->Id_Ejidatario)
                    ->where('Asistencia', 1)
                    ->whereNull('Id_Sesion')
                    ->where('Id_Actividad', 1)
                    ->count() > 0 ? 0 : 1200; // Ajusta según tu lógica real de multa

                // --- 2. LÓGICA DE FAENAS ---
                $multaFaena = DB::table('PaseLista')
                    ->where('Id_Ejidatario', $ejid->Id_Ejidatario)
                    ->where('Asistencia', 1)
                    ->whereNull('Id_Sesion')
                    ->where('Id_Actividad', 2)
                    ->count() > 0 ? 0 : 400;

                // --- 3. LÓGICA DE PRÉSTAMO R1 ---
                $prestamoR1 = DB::table('Prestamo')
                    ->where('Id_Ejidatario', $ejid->Id_Ejidatario)
                    ->where('Id_Utilidad', 1)
                    ->sum('Cantidad') ?? 0;
                $abonoR1 = DB::table('Abono')
                    ->join('Prestamo', 'Abono.Id_Prestamo', '=', 'Prestamo.Id_Prestamo')
                    ->where('Prestamo.Id_Ejidatario', $ejid->Id_Ejidatario)
                    ->sum('Abono.Monto') ?? 0;
                $deudaR1 = max(0, $prestamoR1 - $abonoR1);

                $montoR2 = 3000;
                $totalPagar = $montoR2 - ($multaAsamblea + $multaFaena + $deudaR1);

                return [
                    'No' => $ejid->Id_Ejidatario,
                    'Nombre' => "{$ejid->Nombres} {$ejid->Apellido_Paterno} {$ejid->Apellido_Materno}",
                    'Situacion' => 'Activo',
                    'Asamb1' => '', 'Asamb2' => '', 'Asamb3' => '', 'Asamb4' => '', 'Asamb5' => '', 'Asamb6' => '', 'Asamb7' => '',
                    'RepSaneamiento' => 0,
                    '1erReparto' => $deudaR1,
                    '2doReparto' => $montoR2,
                    'Finiquito' => 0,
                    'FaenaSan' => 0,
                    'FaenaApr' => 0,
                    'DescJuntas' => $multaAsamblea,
                    'DescFaenas' => $multaFaena,
                    'Total' => $totalPagar
                ];
            });
    }

    public function headings(): array
    {
        return [['No.', 'NOMBRE', 'SITUACION', 'ASAMBLEA 1', 'ASAMBLEA 2', 'ASAMBLEA 3', 'ASAMBLEA 4', 'ASAMBLEA 5', 'ASAMBLEA 6', 'ASAMBLEA 7', 'REP. SANEAMIENTO', '1ER REPARTO', '2DO REPARTO', 'FINIQUITO', 'FAENAS SAN.', 'FAENAS APR.', 'DESC. JUNTAS', 'DESC. FAENAS', 'TOTAL A PAGAR']];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '000000']]]];
    }
}