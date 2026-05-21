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
        $anoActual = 2024;

        $nombresEventos = [
            'ASAMBLEA ELECCION 30/10/24', 'ASAMBLEA EXTRAORDINARIA 20/11/24', 'ASAMBLEA 18 DICIEMBRE',
            'ASAMBLEA ENERO', 'ASAMBLEA MARZO', 'ASAMBLEA JUNIO', 'ASAMBLEA SEPTIEMBRE CORTE DE CAJA'
        ];

        $montoR2 = DB::table('Utilidad')->where('Id_Utilidad', 2)->value('Monto') ?? 0;
        $costoAsamblea = DB::table('Catalogo_Multa')->where('Año', $anoActual)->where('Tipo', 'Asamblea')->value('Costo') ?? 0;

        // Mapear nombres a IDs de Sesión
        $sesionesIds = [];
        foreach ($nombresEventos as $nombre) {
            $sesion = DB::table('Sesion')
                ->join('Evento', 'Sesion.Id_Referencia', '=', 'Evento.Id_Evento')
                ->where('Evento.Nombre_Evento', $nombre)
                ->first();
            $sesionesIds[] = $sesion ? $sesion->Id_Sesion : null;
        }

        return DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->leftJoin('Estatus as es', 'e.Id_Estatus', '=', 'es.Id_Estatus')
            ->select('e.Id_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno', 'es.Estatus as NombreEstatus')
            ->get()
            ->map(function ($ejid, $index) use ($sesionesIds, $costoAsamblea, $montoR2) {

                $fila = [
                    'No' => $index + 1,
                    'Nombre' => "{$ejid->Nombres} {$ejid->Apellido_Paterno} {$ejid->Apellido_Materno}",
                    'Situacion' => $ejid->NombreEstatus ?? 'S/P',
                ];

                $totalDescJuntas = 0;
                foreach ($sesionesIds as $idSesion) {
                    $asistio = $idSesion ? DB::table('PaseLista')
                        ->where('Id_Sesion', $idSesion)
                        ->where('Id_Ejidatario', $ejid->Id_Ejidatario)
                        ->where('Asistencia', 1)
                        ->exists() : false;

                    $multa = (!$asistio && $idSesion) ? $costoAsamblea : 0;
                    $fila[] = $multa > 0 ? $multa : '';
                    $totalDescJuntas += $multa;
                }

                $fila['Saneamiento'] = 0;
                $fila['R1'] = 0;
                $fila['R2'] = $montoR2;
                $fila['Finiquito'] = 0;
                $fila['F_San'] = 0;
                $fila['F_Apr'] = 0;
                $fila['Desc_Juntas'] = $totalDescJuntas;
                $fila['Desc_Faenas'] = 0; // Ajustar si tienes lógica de faenas
                $fila['Total_Pagar'] = $montoR2 - $totalDescJuntas;

                return $fila;
            });
    }

    public function headings(): array
    {
        return [
            ['CONCENTRADO FINAL DE APORTACIONES Y DESCUENTOS 2025'],
            [
                'No.', 'NOMBRE DE EJIDATARIO', 'SITUACION',
                'ASAMBLEA ELECCION 30/10/24', 'ASAMBLEA EXTRAORDINARIA 20/11/24', 'ASAMBLEA 18 DICIEMBRE',
                'ASAMBLEA ENERO', 'ASAMBLEA MARZO', 'ASAMBLEA JUNIO', 'ASAMBLEA SEPTIEMBRE CORTE DE CAJA',
                'REPARTO DE FINIQUITO DEL SANEAMIENTO', '1ER. REPARTO DE UTILIDAD', '2do. REPARTO DE UTILIDAD',
                'FINIQUITO DE UTILIDAD', 'FAENAS SANEAMIENTO', 'FAENAS APROVECHAMIENTO',
                'DESCUENTO POR JUNTAS', 'DESCUENTO DE FAENAS', 'TOTAL A PAGAR'
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:S1');
        return [
            1 => ['font' => ['bold' => true, 'size' => 16], 'alignment' => ['horizontal' => 'center']],
            2 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D3D3D3']]],
        ];
    }
}