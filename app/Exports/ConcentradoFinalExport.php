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
        // Forzamos los nombres exactos que tú necesitas
        $nombresEventos = [
            'ASAMBLEA ELECCION 30/10/24',
            'ASAMBLEA EXTRAORDINARIA 20/11/24',
            'ASAMBLEA 18 DICIEMBRE',
            'ASAMBLEA ENERO',
            'ASAMBLEA MARZO',
            'ASAMBLEA JUNIO',
            'ASAMBLEA SEPTIEMBRE CORTE DE CAJA'
        ];

        // Obtener datos globales
        $montoR2 = DB::table('Utilidad')->where('Id_Utilidad', 2)->value('Monto') ?? 3000;
        $costoAsamblea = DB::table('Catalogo_Multa')->where('Tipo', 'Asamblea')->orderBy('Año', 'desc')->value('Costo') ?? 0;
        $costoFaena = DB::table('Catalogo_Multa')->where('Tipo', 'Faena')->orderBy('Año', 'desc')->value('Costo') ?? 0;

        // Mapear eventos a IDs de sesión
        $sesionesMap = [];
        foreach ($nombresEventos as $nombre) {
            $sesion = DB::table('Sesion')
                ->join('Evento', 'Sesion.Id_Referencia', '=', 'Evento.Id_Evento')
                ->where('Evento.Nombre_Evento', 'LIKE', '%' . $nombre . '%')
                ->first();
            $sesionesMap[$nombre] = $sesion ? $sesion->Id_Sesion : null;
        }

        return DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->leftJoin('Estatus as es', 'e.Id_Estatus', '=', 'es.Id_Estatus')
            ->select('e.Id_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno', 'es.Estatus as NombreEstatus')
            ->get()
            ->map(function ($ejid) use ($sesionesMap, $costoAsamblea, $montoR2) {

                $fila = [
                    'No' => $ejid->Id_Ejidatario,
                    'Nombre' => "{$ejid->Nombres} {$ejid->Apellido_Paterno} {$ejid->Apellido_Materno}",
                    'Situacion' => $ejid->NombreEstatus ?? 'S/P',
                ];

                $totalDescJuntas = 0;
                foreach ($sesionesMap as $idSesion) {
                    $asistio = $idSesion ? DB::table('PaseLista')
                        ->where('Id_Sesion', $idSesion)
                        ->where('Id_Ejidatario', $ejid->Id_Ejidatario)
                        ->where('Asistencia', 1)
                        ->exists() : false;

                    $multa = (!$asistio && $idSesion) ? $costoAsamblea : 0;
                    $fila[] = $multa > 0 ? $multa : '';
                    $totalDescJuntas += $multa;
                }

                // Cálculo de deuda real
                $prestamoR1 = DB::table('Prestamo')->where('Id_Ejidatario', $ejid->Id_Ejidatario)->where('Id_Utilidad', 1)->sum('Cantidad');
                $abonoR1 = DB::table('Abono')->join('Prestamo', 'Abono.Id_Prestamo', '=', 'Prestamo.Id_Prestamo')->where('Prestamo.Id_Ejidatario', $ejid->Id_Ejidatario)->sum('Abono.Monto');
                $deudaR1 = max(0, $prestamoR1 - $abonoR1);

                $fila['Saneamiento'] = 0;
                $fila['R1'] = $deudaR1; // Deuda R1
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
            ['No.', 'NOMBRE', 'SITUACION', 'ASAMBLEA ELECCION 30/10/24', 'ASAMBLEA EXTRAORDINARIA 20/11/24', 'ASAMBLEA 18 DICIEMBRE', 'ASAMBLEA ENERO', 'ASAMBLEA MARZO', 'ASAMBLEA JUNIO', 'ASAMBLEA SEPTIEMBRE CORTE DE CAJA', 'REPARTO DE FINIQUITO DEL SANEAMIENTO', '1ER. REPARTO DE UTILIDAD', '2do. REPARTO DE UTILIDAD', 'FINIQUITO DE UTILIDAD', 'FAENAS SANEAMIENTO', 'FAENAS APROVECHAMIENTO', 'DESC. JUNTAS', 'DESC. FAENAS', 'TOTAL A PAGAR']
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