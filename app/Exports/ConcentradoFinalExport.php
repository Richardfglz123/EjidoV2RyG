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
        $anoActual = 2026; // Ajustado según tu captura de 2024

        // Obtenemos los nombres exactos de las sesiones para los encabezados
        $nombresSesiones = [
            'ASAMBLEA ELECCION 30/10/24', 'ASAMBLEA EXTRAORDINARIA 20/11/24', 'ASAMBLEA 18 DICIEMBRE',
            'ASAMBLEA ENERO', 'ASAMBLEA MARZO', 'ASAMBLEA JUNIO', 'ASAMBLEA SEPTIEMBRE CORTE DE CAJA'
        ];

        $montoR2 = DB::table('Utilidad')->where('Id_Utilidad', 2)->value('Monto') ?? 0;
        $costoAsamblea = DB::table('Catalogo_Multa')->where('Año', $anoActual)->where('Tipo', 'Asamblea')->value('Costo') ?? 0;

        return DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->leftJoin('Estatus as es', 'e.Id_Estatus', '=', 'es.Id_Estatus')
            ->select('e.Id_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno', 'es.Estatus as NombreEstatus')
            ->get()
            ->map(function ($ejid, $index) use ($nombresSesiones, $costoAsamblea, $montoR2) {

                $fila = [
                    'No' => $index + 1,
                    'Nombre' => "{$ejid->Nombres} {$ejid->Apellido_Paterno} {$ejid->Apellido_Materno}",
                    'Situacion' => $ejid->NombreEstatus ?? 'S/P',
                ];

                $totalDescJuntas = 0;
                foreach ($nombresSesiones as $nombre) {
                    $sesion = DB::table('Sesion')->where('Nombre_Sesion', $nombre)->first();
                    $asistio = $sesion ? DB::table('PaseLista')->where('Id_Sesion', $sesion->Id_Sesion)->where('Id_Ejidatario', $ejid->Id_Ejidatario)->where('Asistencia', 1)->exists() : false;

                    $multa = (!$asistio) ? $costoAsamblea : 0;
                    $fila[] = $multa > 0 ? $multa : '';
                    $totalDescJuntas += $multa;
                }

                // Agregamos las columnas fijas que solicitaste
                $fila['Saneamiento'] = 0; // Ajusta si tienes valor de utilidad
                $fila['R1'] = 0;
                $fila['R2'] = $montoR2;
                $fila['Finiquito'] = 0;
                $fila['F_San'] = 0;
                $fila['F_Apr'] = 0;
                $fila['Desc_Juntas'] = $totalDescJuntas;
                $fila['Desc_Faenas'] = 0;
                $fila['Total_Pagar'] = $montoR2 - $totalDescJuntas; // Lógica exacta de tu módulo

                return $fila;
            });
    }

    public function headings(): array
    {
        return [
            ['CONCENTRADO FINAL DE APORTACIONES Y DESCUENTOS 2025'], // Título principal
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
            2 => ['font' => ['bold' => true], 'alignment' => ['horizontal' => 'center']],
        ];
    }
}