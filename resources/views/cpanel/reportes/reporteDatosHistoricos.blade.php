<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Datos Históricos</title>
    <style>
        @page { margin: 1cm; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            color: #333;
            line-height: 1.4;
        }

        /* Título unificado en verde */
        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 2px solid #00A651;
        }

        .header h1 {
            margin: 5px 0 0;
            font-size: 16pt;
            color: #00A651;
            text-transform: uppercase;
        }

        .header p {
            margin: 2px 0 0;
            font-size: 9pt;
            font-weight: bold;
            color: #666;
        }

        .info-bar {
            margin-bottom: 10px;
            text-align: right;
            font-size: 8pt;
            color: #444;
        }

        /* Estilos de la Tabla */
        .styled-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .styled-table thead tr {
            background-color: #00A651;
            color: #ffffff;
        }

        .styled-table th {
            padding: 8px 10px;
            font-size: 9pt;
            border: 1px solid #00A651;
            text-align: left;
            text-transform: uppercase;
        }

        .styled-table td {
            padding: 8px 10px;
            border: 1px solid #dee2e6;
            font-size: 9pt;
            word-wrap: break-word;
            vertical-align: top;
        }

        /* Estilo cebra suave */
        .styled-table tbody tr:nth-child(even) {
            background-color: #f2f9f4;
        }

        .text-bold { font-weight: bold; }
        .text-center { text-align: center; }

        .footer {
            position: fixed;
            bottom: -30px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Reporte de Datos Históricos</h1>
    <p>SISTEMA EJIDAL</p>
</div>

<div class="info-bar">
    <strong>Fecha de generación:</strong> {{ now()->format('d/m/Y H:i A') }} |
    <strong>Total de registros:</strong> {{ count($data) }}
</div>

<table class="styled-table">
    <thead>
    <tr>
        <th style="width: 30%;">Título</th>
        <th style="width: 50%;">Descripción</th>
        <th style="width: 20%; text-align: center;">Fecha</th>
    </tr>
    </thead>
    <tbody>
    @forelse($data as $r)
        <tr>
            <td class="text-bold">{{ $r->Titulo }}</td>
            <td>{{ $r->Descripcion }}</td>
            <td class="text-center">
                {{ $r->Fecha ? \Carbon\Carbon::parse($r->Fecha)->format('d/m/Y') : 'N/A' }}
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="3" class="text-center" style="padding: 20px;">
                No hay registros históricos para los filtros seleccionados.
            </td>
        </tr>
    @endforelse
    </tbody>
</table>

<div class="footer">
    Sistema Ejidal — Reporte generado automáticamente el {{ date('d/m/Y') }}
</div>

</body>
</html>
