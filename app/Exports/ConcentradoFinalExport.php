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
        $ano = 2026;
        $costoAsamblea = DB::table('Catalogo_Multa')->where('Año', $ano)->where('Tipo', 'Asamblea')->value('Costo') ?? 0;
        $costoFaena = DB::table('Catalogo_Multa')->where('Año', $ano)->where('Tipo', 'Faena')->value('Costo') ?? 0;

        return DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->select('e.Id_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno')
            ->get()
            ->map(function ($ejid) use ($ano, $costoAsamblea, $costoFaena) {

                // Cálculos de R1 y Multas
                $totalPrestamoR1 = DB::table('Prestamo')->where('Id_Ejidatario', $ejid->Id_Ejidatario)->where('Id_Utilidad', 1)->sum('Cantidad');
                $totalAbonosR1 = DB::table('Abono')->join('Prestamo', 'Abono.Id_Prestamo', '=', 'Prestamo.Id_Prestamo')->where('Prestamo.Id_Ejidatario', $ejid->Id_Ejidatario)->sum('Abono.Monto');
                $deudaR1 = max(0, $totalPrestamoR1 - $totalAbonosR1);

                // Asistencias y Faenas (Lógica del controlador)
                $reprosAsambleas = DB::table('PaseLista')->where('Id_Ejidatario', $ejid->Id_Ejidatario)->where('Asistencia', 1)->whereNull('Id_Sesion')->where('Id_Actividad', 1)->count();
                $reprosFaenas = DB::table('PaseLista')->where('Id_Ejidatario', $ejid->Id_Ejidatario)->where('Asistencia', 1)->whereNull('Id_Sesion')->where('Id_Actividad', 2)->count();

                $totalAsambleas = max(0, 0 - $reprosAsambleas) * $costoAsamblea; // Ajustar según tus sesiones reales
                $totalFaenas = max(0, 0 - $reprosFaenas) * $costoFaena;

                return [
                    'No' => $ejid->Id_Ejidatario,
                    'Nombre' => "{$ejid->Nombres} {$ejid->Apellido_Paterno} {$ejid->Apellido_Materno}",
                    'Situacion' => 'Activo',
                    'Asamblea1' => '', 'Asamblea2' => '', 'Asamblea3' => '', 'Asamblea4' => '', 'Asamblea5' => '', 'Asamblea6' => '', 'Asamblea7' => '',
                    'RepartoSaneamiento' => 0,
                    'Reparto1' => $deudaR1,
                    'Reparto2' => 3000,
                    'Finiquito' => 0,
                    'FaenaSan' => 0,
                    'FaenaApr' => 0,
                    'DescJuntas' => $totalAsambleas,
                    'DescFaenas' => $totalFaenas,
                    'TotalPagar' => 3000 - ($totalAsambleas + $totalFaenas + $deudaR1)
                ];
            });
    }

    public function headings(): array
    {
        return [['No.', 'NOMBRE', 'SITUACION', 'ASAMBLEA 1', 'ASAMBLEA 2', 'ASAMBLEA 3', 'ASAMBLEA 4', 'ASAMBLEA 5', 'ASAMBLEA 6', 'ASAMBLEA 7', 'REPARTO SANEAMIENTO', '1ER REPARTO', '2DO REPARTO', 'FINIQUITO', 'FAENAS SAN.', 'FAENAS APR.', 'DESC. JUNTAS', 'DESC. FAENAS', 'TOTAL A PAGAR']];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:S1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '000000']]
        ]);
    }
}