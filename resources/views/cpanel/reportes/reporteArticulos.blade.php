<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario General de Artículos</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            font-size: 9pt;
            color: #333;
        }

        /* Encabezado Institucional */
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #00A651;
        }
        .header h1 {
            font-size: 16pt;
            color: #00A651;
            margin: 5px 0;
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

        /* Tabla Estilizada */
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
            padding: 10px 12px;
            border: 1px solid #dee2e6;
            vertical-align: middle;
        }

        .styled-table tbody tr:nth-of-type(even) {
            background-color: #f2f9f4; /* Verde muy claro para filas pares */
        }

        .styled-table tbody tr:last-of-type {
            border-bottom: 2px solid #00A651;
        }

        /* Pie de página fijo */
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
        .text-center { text-align: center; }

        /* Indicador de stock bajo */
        .low-stock {
            color: #d9534f;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Inventario General de Artículos</h1>
    <p>SISTEMA EJIDAL - SAN RAFAEL IXTAPALUCAN</p>
</div>

<div class="info-bar">
    <strong>Total de artículos:</strong> {{ count($articulos) }} |
    <strong>Fecha de reporte:</strong> {{ date('d/m/Y H:i A') }}
</div>

<table class="styled-table">
    <thead>
    <tr>
        <th width="40%">Descripción</th>
        <th width="10%" class="text-center">Existencia</th>
        <th width="15%">Estado</th>
        <th width="15%">Medida</th>
        <th width="20%">F. Registro</th>
    </tr>
    </thead>

    <tbody>
    @foreach($articulos as $a)
        <tr>
            <td class="text-bold">{{ $a->Descripcion }}</td>
            <td class="text-center {{ $a->Cantidad <= 5 ? 'low-stock' : '' }}">
                {{ $a->Cantidad }}
            </td>
            <td>{{ $a->Estado }}</td>
            <td>{{ $a->Medida }}</td>
            <td>
                {{ \Carbon\Carbon::parse($a->fecha_registro)->format('d/m/Y') }}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="footer">
    Sistema Ejidal — Reporte de existencias generado automáticamente el {{ date('d/m/Y') }}
</div>

</body>
</html>
