<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Ejidatarios</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            font-size: 9pt;
            color: #333;
        }
        /* Color Verde Principal */
        .text-verde { color: #00A651; }
        .bg-verde { background-color: #00A651; }

        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 2px solid #00A651;
        }
        .header h1 {
            font-size: 16pt;
            color: #00A651;
            margin: 5px 0 5px 0;
            text-transform: uppercase;
        }
        .header p {
            font-size: 8pt;
            color: #666;
            margin: 2px 0;
        }

        .info-bar {
            margin-bottom: 10px;
            text-align: right;
            font-size: 8pt;
            color: #444;
        }

        .styled-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .styled-table thead tr {
            background-color: #00A651;
            color: #ffffff;
            text-align: left;
        }

        .styled-table th,
        .styled-table td {
            padding: 8px 10px;
            border: 1px solid #dee2e6;
            vertical-align: middle;
        }

        .styled-table tbody tr:nth-of-type(even) {
            background-color: #f2f9f4;
        }

        .badge {
            padding: 3px 7px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: bold;
            background-color: #e9ecef;
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

        .text-bold { font-weight: bold; }
        .small-text { font-size: 8pt; color: #666; }
    </style>
</head>
<body>

<div class="header">
    <h1>Reporte General de Ejidatarios</h1>
    <p>SISTEMA EJIDAL</p>
</div>

<div class="info-bar">
    <strong>Total de registros:</strong> {{ count($data) }} |
    <strong>Fecha de impresión:</strong> {{ date('d/m/Y H:i A') }}
</div>

<table class="styled-table">
    <thead>
    <tr>
        <th width="8%">No.</th>
        <th width="35%">Datos del Ejidatario / Dirección</th>
        <th width="20%">Identificación</th>
        <th width="22%">Ejidatario</th>
        <th width="15%">Estatus</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $fila)
        <tr>
            <td class="text-bold">{{ $fila->Num_Ejidatario }}</td>
            <td>
                <div class="text-bold">Ingreso: {{ $fila->Fecha_Ingreso ? \Carbon\Carbon::parse($fila->Fecha_Ingreso)->format('d/m/Y') : 'N/A' }}</div>
                <div class="small-text">
                    {{ $fila->Calle }} #{{ $fila->Num_Exterior }},
                    Col. {{ $fila->Colonia }}, {{ $fila->Municipio }}
                </div>
            </td>
            <td>
                <span class="text-bold">CURP:</span> {{ $fila->CURP }}<br>
                <span class="small-text">RFC: {{ $fila->RFC }}</span>
            </td>
            <td>
                <div class="text-bold">{{ $fila->Nombres ?? 'No asignado' }}</div>
                <div class="small-text">{{ $fila->Apellido_Paterno ?? '' }}</div>
            </td>
            <td style="text-align: center;">
                <span class="badge">
                    {{ $fila->NombreEstatus ?? 'N/A' }}
                </span>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="footer">
    Sistema Ejidal — Reporte generado automáticamente el {{ date('d/m/Y') }}
</div>

</body>
</html>
