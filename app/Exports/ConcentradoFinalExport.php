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
        $anoActual = 2026; // Forzado a 2026 para que coincida con tu vista

        // 1. Obtenemos costos de multas del catálogo
        $costoAsamblea = DB::table('Catalogo_Multa')->where('Año', $anoActual)->where('Tipo', 'Asamblea')->value('Costo') ?? 0;
        $costoFaena = DB::table('Catalogo_Multa')->where('Año', $anoActual)->where('Tipo', 'Faena')->value('Costo') ?? 0;

        // 2. Obtenemos IDs de sesiones (Lógica copiada de tu controlador)
        $sesionesAsambleasIds = DB::table('Sesion')
            ->join('Evento as e', 'Sesion.Id_Referencia', '=', 'e.Id_Evento')
            ->join('Categoria_Evento as c', 'e.Id_Categoria_Evento', '=', 'c.Id_Categoria_Evento')
            ->where('c.Clave_Categoria', 'LIKE', 'asamblea%')
            ->whereYear('Sesion.Fecha', $anoActual)
            ->pluck('Sesion.Id_Sesion')->toArray();

        // 3. Obtenemos Ejidatarios
        return DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->select('e.Id_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno')
            ->get()
            ->map(function ($ejid) use ($sesionesAsambleasIds, $costoAsamblea, $costoFaena) {

                // Cálculo de deuda R1 (Deuda arrastrada)
                $totalPrestamo = DB::table('Prestamo')->where('Id_Ejidatario', $ejid->Id_Ejidatario)->where('Id_Utilidad', 1)->sum('Cantidad') ?? 0;
                $totalAbonos = DB::table('Abono')->join('Prestamo', 'Abono.Id_Prestamo', '=', 'Prestamo.Id_Prestamo')->where('Prestamo.Id_Ejidatario', $ejid->Id_Ejidatario)->sum('Abono.Monto') ?? 0;
                $deudaR1 = max(0, $totalPrestamo - $totalAbonos);

                // Cálculo de Asistencias (Faltas)
                $asistencias = DB::table('PaseLista')->where('Id_Ejidatario', $ejid->Id_Ejidatario)->where('Asistencia', 1)->pluck('Id_Sesion')->toArray();
                $faltasCount = count(array_diff($sesionesAsambleasIds, $asistencias));
                $totalMultas = $faltasCount * $costoAsamblea;

                return [
                    'No' => $ejid->Id_Ejidatario,
                    'Nombre' => "{$ejid->Nombres} {$ejid->Apellido_Paterno} {$ejid->Apellido_Materno}",
                    'Situacion' => 'Activo', // Puedes traer esto de tu tabla Estatus
                    'Multas' => $totalMultas,
                    'DeudaR1' => $deudaR1,
                    'TotalPagar' => 3000 - ($totalMultas + $deudaR1)
                ];
            });
    }

    public function headings(): array
    {
        return [['No.', 'NOMBRE', 'SITUACION', 'DESC. ASAMBLEA', 'PRESTAMO R1', 'TOTAL A PAGAR']];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '000000']]],
        ];
    }
}