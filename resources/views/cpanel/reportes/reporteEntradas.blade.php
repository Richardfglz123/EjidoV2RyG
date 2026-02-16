<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Entradas de Inventario</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            font-size: 9pt;
            color: #333;
        }

        /* Encabezado con color institucional */
        .header {
            text-align: center;
            margin-bottom: 10px;
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
            background-color: #f2f9f4; /* Fondo verde muy suave */
        }

        .styled-table tbody tr:last-of-type {
            border-bottom: 2px solid #00A651;
        }

        /* Pie de página */
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
        .badge-success {
            color: #00A651;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Reporte de Entradas de Inventario</h1>
    <p>SISTEMA EJIDAL - SAN RAFAEL IXTAPALUCAN</p>
</div>

<div class="info-bar">
    <strong>Total de movimientos:</strong> {{ count($entradas) }} |
    <strong>Fecha de impresión:</strong> {{ date('d/m/Y H:i A') }}
</div>

<table class="styled-table">
    <thead>
    <tr>
        <th width="35%">Artículo</th>
        <th width="15%" class="text-center">Cantidad</th>
        <th width="20%" class="text-center">Fecha</th>
        <th width="30%">Observaciones</th>
    </tr>
    </thead>

    <tbody>
    @foreach($entradas as $e)
        <tr>
            <td class="text-bold">{{ $e->articulo->descripcion }}</td>
            <td class="text-center badge-success">+ {{ $e->Cantidad }}</td>
            <td class="text-center">
                {{ \Carbon\Carbon::parse($e->Fecha)->format('d/m/Y') }}
            </td>
            <td>
                {{ $e->Observaciones ?? 'N/A' }}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="footer">
    Sistema Ejidal — Reporte de movimientos de entrada generado automáticamente el {{ date('d/m/Y') }}
</div>

</body>
</html>
