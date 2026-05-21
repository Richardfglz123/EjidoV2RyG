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
        // Usamos 2026 como año fijo para asegurar consistencia con tus capturas
        $anoActual = 2026;

        // 1. Obtener costos directamente de la tabla catalogo
        $costoAsamblea = DB::table('Catalogo_Multa')->where('Año', $anoActual)->where('Tipo', 'Asamblea')->value('Costo') ?? 0;
        $costoFaena = DB::table('Catalogo_Multa')->where('Año', $anoActual)->where('Tipo', 'Faena')->value('Costo') ?? 0;

        // 2. Obtener sesiones con una consulta más flexible
        $sesionesAsambleasIds = DB::table('Sesion')
            ->join('Evento', 'Sesion.Id_Referencia', '=', 'Evento.Id_Evento')
            ->join('Categoria_Evento', 'Evento.Id_Categoria_Evento', '=', 'Categoria_Evento.Id_Categoria_Evento')
            ->where('Categoria_Evento.Clave_Categoria', 'LIKE', 'asamblea%')
            ->whereYear('Sesion.Fecha', $anoActual)
            ->pluck('Sesion.Id_Sesion')
            ->toArray();

        return DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->select('e.Id_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno')
            ->get()
            ->map(function ($ejid) use ($sesionesAsambleasIds, $costoAsamblea) {

                // Cálculo exacto de Deuda R1: Suma Préstamos - Suma Abonos
                $prestamoR1 = DB::table('Prestamo')
                    ->where('Id_Ejidatario', $ejid->Id_Ejidatario)
                    ->where('Id_Utilidad', 1)
                    ->sum('Cantidad') ?? 0;

                $abonoR1 = DB::table('Abono')
                    ->join('Prestamo', 'Abono.Id_Prestamo', '=', 'Prestamo.Id_Prestamo')
                    ->where('Prestamo.Id_Ejidatario', $ejid->Id_Ejidatario)
                    ->where('Prestamo.Id_Utilidad', 1)
                    ->sum('Abono.Monto') ?? 0;

                $deudaR1 = max(0, $prestamoR1 - $abonoR1);

                // Cálculo de faltas de asamblea basado en PaseLista
                $asistencias = DB::table('PaseLista')
                    ->where('Id_Ejidatario', $ejid->Id_Ejidatario)
                    ->where('Asistencia', 1)
                    ->pluck('Id_Sesion')
                    ->toArray();

                $faltas = count(array_diff($sesionesAsambleasIds, $asistencias));
                $totalMultasAsamblea = $faltas * $costoAsamblea;

                // Monto R2 base
                $montoR2 = 3000;

                return [
                    'No' => $ejid->Id_Ejidatario,
                    'Nombre' => "{$ejid->Nombres} {$ejid->Apellido_Paterno} {$ejid->Apellido_Materno}",
                    'Situacion' => 'Activo',
                    'Desc_Asamblea' => $totalMultasAsamblea > 0 ? $totalMultasAsamblea : 0,
                    'Prestamo_R1' => $deudaR1 > 0 ? $deudaR1 : 0,
                    'Total_Pagar' => $montoR2 - ($totalMultasAsamblea + $deudaR1)
                ];
            });
    }

    public function headings(): array
    {
        return [['No.', 'NOMBRE', 'SITUACION', 'DESC. ASAMBLEA', 'PRESTAMO R1', 'TOTAL A PAGAR']];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => 'center']
        ]);
    }
}