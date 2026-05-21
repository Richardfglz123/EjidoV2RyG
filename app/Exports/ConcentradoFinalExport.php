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
        $anoActual = now()->year; // Detección automática del año

        $montoR2 = DB::table('Utilidad')->where('Id_Utilidad', 2)->value('Monto') ?? 0;
        $costoAsamblea = DB::table('Catalogo_Multa')->where('Año', $anoActual)->where('Tipo', 'Asamblea')->value('Costo') ?? 0;
        $costoFaena = DB::table('Catalogo_Multa')->where('Año', $anoActual)->where('Tipo', 'Faena')->value('Costo') ?? 0;

        // Obtener eventos tipo asamblea del año actual
        $eventosAsamblea = DB::table('Evento')
            ->join('Categoria_Evento', 'Evento.Id_Categoria_Evento', '=', 'Categoria_Evento.Id_Categoria_Evento')
            ->where('Categoria_Evento.Clave_Categoria', 'LIKE', 'asamblea%')
            ->whereYear('Evento.Fecha_Creo', $anoActual)
            ->get();

        return DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->leftJoin('Estatus as es', 'e.Id_Estatus', '=', 'es.Id_Estatus')
            ->select('e.Id_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno', 'es.Estatus as NombreEstatus')
            ->get()
            ->map(function ($ejid) use ($eventosAsamblea, $costoAsamblea, $costoFaena, $montoR2, $anoActual) {

                $fila = [
                    'No' => $ejid->Id_Ejidatario,
                    'Nombre' => "{$ejid->Nombres} {$ejid->Apellido_Paterno} {$ejid->Apellido_Materno}",
                    'Situacion' => $ejid->NombreEstatus ?? 'S/P',
                ];

                $totalDescJuntas = 0;
                foreach ($eventosAsamblea as $evento) {
                    $sesion = DB::table('Sesion')->where('Id_Referencia', $evento->Id_Evento)->first();
                    $asistio = $sesion ? DB::table('PaseLista')->where('Id_Sesion', $sesion->Id_Sesion)->where('Id_Ejidatario', $ejid->Id_Ejidatario)->where('Asistencia', 1)->exists() : false;

                    $multa = (!$asistio) ? $costoAsamblea : 0;
                    $fila[] = $multa > 0 ? $multa : '';
                    $totalDescJuntas += $multa;
                }

                // Cálculo de deuda R1 igual al controlador
                $prestamoR1 = DB::table('Prestamo')->where('Id_Ejidatario', $ejid->Id_Ejidatario)->where('Id_Utilidad', 1)->sum('Cantidad');
                $abonoR1 = DB::table('Abono')->join('Prestamo', 'Abono.Id_Prestamo', '=', 'Prestamo.Id_Prestamo')->where('Prestamo.Id_Ejidatario', $ejid->Id_Ejidatario)->sum('Abono.Monto');
                $deudaR1 = max(0, $prestamoR1 - $abonoR1);

                $fila['Saneamiento'] = 0;
                $fila['R1'] = 0;
                $fila['R2'] = $montoR2;
                $fila['Finiquito'] = 0;
                $fila['F_San'] = 0;
                $fila['F_Apr'] = 0;
                $fila['Desc_Juntas'] = $totalDescJuntas;
                $fila['Desc_Faenas'] = 0;
                $fila['Total_Pagar'] = $montoR2 - ($totalDescJuntas + $deudaR1);

                return $fila;
            });
    }

    public function headings(): array
    {
        return [
            ['CONCENTRADO FINAL DE APORTACIONES Y DESCUENTOS ' . now()->year],
            ['No.', 'NOMBRE DE EJIDATARIO', 'SITUACION', 'ASAMBLEA ELECCION 30/10/24', 'ASAMBLEA EXTRAORDINARIA 20/11/24', 'ASAMBLEA 18 DICIEMBRE', 'ASAMBLEA ENERO', 'ASAMBLEA MARZO', 'ASAMBLEA JUNIO', 'ASAMBLEA SEPTIEMBRE CORTE DE CAJA', 'REPARTO DE FINIQUITO DEL SANEAMIENTO', '1ER. REPARTO DE UTILIDAD', '2do. REPARTO DE UTILIDAD', 'FINIQUITO DE UTILIDAD', 'FAENAS SANEAMIENTO', 'FAENAS APROVECHAMIENTO', 'DESCUENTO POR JUNTAS', 'DESCUENTO DE FAENAS', 'TOTAL A PAGAR']
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