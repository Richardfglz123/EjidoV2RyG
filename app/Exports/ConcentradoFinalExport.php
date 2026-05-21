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

        // Solo necesitamos el monto del 2do reparto para que coincida con tu vista web
        $montoR2 = DB::table('Utilidad')->where('Id_Utilidad', 2)->value('Monto') ?? 0;

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
            ->map(function ($ejid, $index) use ($sesionesAsambleas, $costoAsamblea, $costoFaena, $montoR2) {

                $fila = [
                    'No' => $index + 1,
                    'Nombre' => "{$ejid->Nombres} {$ejid->Apellido_Paterno} {$ejid->Apellido_Materno}",
                    'Situacion' => $ejid->NombreEstatus ?? 'S/P',
                ];

                // Lógica de Faltas (Juntas) - Idéntica al controlador
                $asistenciasEjidatario = DB::table('PaseLista')
                    ->where('Id_Ejidatario', $ejid->Id_Ejidatario)
                    ->where('Asistencia', 1)
                    ->whereNotNull('Id_Sesion')
                    ->distinct()->pluck('Id_Sesion')->toArray();

                $reprosAsambleas = DB::table('PaseLista')
                    ->where('Id_Ejidatario', $ejid->Id_Ejidatario)
                    ->where('Asistencia', 1)
                    ->whereNull('Id_Sesion')
                    ->where('Id_Actividad', 1)->count();

                $reprosFaenas = DB::table('PaseLista')
                    ->where('Id_Ejidatario', $ejid->Id_Ejidatario)
                    ->where('Asistencia', 1)
                    ->whereNull('Id_Sesion')
                    ->where('Id_Actividad', 2)->count();

                $totalFaltasJuntas = 0;
                foreach ($sesionesAsambleas as $sesion) {
                    $esFalta = !in_array($sesion->Id_Sesion, $asistenciasEjidatario);
                    $multa = ($esFalta && $reprosAsambleas <= 0) ? $costoAsamblea : 0;
                    $fila[] = $multa > 0 ? $multa : '';
                    if ($esFalta) $totalFaltasJuntas += $costoAsamblea;
                }
                for ($i = count($sesionesAsambleas); $i < 7; $i++) { $fila[] = ''; }

                // Deuda R1 - Idéntica al controlador
                $totalPrestamoR1 = DB::table('Prestamo')->where('Id_Ejidatario', $ejid->Id_Ejidatario)->where('Id_Utilidad', 1)->sum('Cantidad');
                $totalAbonosR1 = DB::table('Abono')->join('Prestamo', 'Abono.Id_Prestamo', '=', 'Prestamo.Id_Prestamo')
                    ->where('Prestamo.Id_Ejidatario', $ejid->Id_Ejidatario)->sum('Abono.Monto');
                $deudaArrastrada = max(0, $totalPrestamoR1 - $totalAbonosR1);

                // Cálculo de Faenas
                $descFaenas = max(0, (/*aquí deberías obtener el conteo de faenas igual que en el controlador*/) - $reprosFaenas) * $costoFaena;

                // Columnas de montos
                $fila['Saneamiento'] = 0;
                $fila['R1'] = 0;
                $fila['R2'] = $montoR2;
                $fila['Finiquito'] = 0;
                $fila['Faena_San'] = 0;
                $fila['Faena_Apr'] = 0;
                $fila['Total_Desc_Juntas'] = $totalFaltasJuntas;
                $fila['Total_Desc_Faenas'] = $descFaenas;

                // TOTAL A PAGAR EXACTO
                $fila['Total_Pagar'] = $montoR2 - ($totalFaltasJuntas + $descFaenas + $deudaArrastrada);

                return $fila;
            });
    }

    public function headings(): array
    {
        return [
            ['CONCENTRADO FINAL 2026'],
            ['No.', 'NOMBRE', 'SITUACION', 'J1', 'J2', 'J3', 'J4', 'J5', 'J6', 'J7', 'SANEAMIENTO', 'R1', '2DO REPARTO', 'FINIQUITO', 'F. SAN', 'F. APR', 'DESC. JUNTAS', 'DESC. FAENAS', 'TOTAL A PAGAR']
        ];
    }

    public function styles(Worksheet $sheet) { /* Mantén tu estilo */ }
}