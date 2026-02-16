<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Actividades</title>
    <style>
        @page { margin: 1cm; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            color: #333;
            line-height: 1.4;
        }

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
            padding: 8px 5px;
            font-size: 8pt;
            border: 1px solid #00A651;
            text-align: left;
            text-transform: uppercase;
        }

        .styled-table td {
            padding: 6px 4px;
            border: 1px solid #dee2e6;
            font-size: 8pt;
            word-wrap: break-word;
            vertical-align: middle;
        }

        .styled-table tbody tr:nth-child(even) {
            background-color: #f2f9f4;
        }

        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }

        .badge {
            padding: 2px 5px;
            border-radius: 4px;
            background-color: #e9ecef;
            color: #333;
            font-weight: bold;
            font-size: 7pt;
            border: 1px solid #ced4da;
            display: inline-block;
        }

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
    <h1>Reporte de Actividades</h1>
    <p>SISTEMA EJIDAL</p>
</div>

<div class="info-bar">
    <strong>Fecha de generación:</strong> {{ now()->format('d/m/Y H:i A') }} |
    <strong>Total de registros:</strong> {{ count($data) }}
</div>

<table class="styled-table">
    <thead>
    <tr>
        <th style="width: 70px;">Tipo</th>
        <th>Descripción</th>
        <th style="width: 60px;">Inicio</th>
        <th style="width: 60px;">Fin</th>
        <th style="width: 70px;">Estado</th>
        <th style="width: 60px;">Nueva F.</th>
        <th style="width: 60px;">Realizado</th>
    </tr>
    </thead>
    <tbody>
    @forelse($data as $a)
        <tr>
            <td class="text-bold text-center">{{ $a->Tipo }}</td>
            <td>{{ $a->Descripcion }}</td>
            <td class="text-center">{{ $a->FechaInicio }}</td>
            <td class="text-center">{{ $a->FechaFin }}</td>
            <td class="text-center">
                <span class="badge">{{ $a->Estado_Actividad }}</span>
            </td>
            <td class="text-center">{{ $a->Nueva_Fecha ?? 'N/A' }}</td>
            <td class="text-center">{{ $a->Fecha_Realizo ?? 'Pendiente' }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="7" class="text-center" style="padding: 20px;">
                No hay actividades registradas.
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
