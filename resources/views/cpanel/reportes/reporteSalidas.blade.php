<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Salidas de Inventario</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; margin: 0; padding: 0; font-size: 9pt; color: #333; }
        .header { text-align: center; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 2px solid #00A651; }
        .header h1 { font-size: 16pt; color: #00A651; margin: 5px 0; text-transform: uppercase; }
        .header p { font-size: 8pt; color: #666; margin: 2px 0; }
        .info-bar { margin-bottom: 10px; text-align: right; font-size: 8pt; color: #444; }
        .styled-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .styled-table thead tr { background-color: #00A651; color: #ffffff; text-align: left; }
        .styled-table th, .styled-table td { padding: 10px 12px; border: 1px solid #dee2e6; vertical-align: middle; }
        .styled-table tbody tr:nth-of-type(even) { background-color: #f2f9f4; }
        .styled-table tbody tr:last-of-type { border-bottom: 2px solid #00A651; }
        .footer { position: fixed; bottom: -30px; left: 0; right: 0; text-align: center; font-size: 8pt; color: #999; border-top: 1px solid #eee; padding-top: 5px; }
        .text-bold { font-weight: bold; }
    </style>
</head>
<body>

<div class="header">
    <h1>Reporte de Salidas de Inventario</h1>
    <p>SISTEMA EJIDAL - SAN RAFAEL IXTAPALUCAN</p>
</div>

<div class="info-bar">
    <strong>Total de registros:</strong> {{ count($salidas) }} |
    <strong>Fecha de impresión:</strong> {{ date('d/m/Y H:i A') }}
</div>

<table class="styled-table">
    <thead>
    <tr>
        <th>Artículo</th>
        <th>Cantidad</th>
        <th>Tipo de Salida</th>
        <th>Fecha</th>
        <th>Responsable</th>
    </tr>
    </thead>
    <tbody>
    @foreach($salidas as $s)
        <tr>
            <td class="text-bold">{{ $s->articulo->descripcion }}</td>
            <td style="text-align: center;">{{ $s->cantidad }}</td>
            <td>{{ $s->tipo_salida }}</td>
            <td>{{ \Carbon\Carbon::parse($s->fecha_salida)->format('d/m/Y') }}</td>
            <td>{{ $s->responsable }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="footer">
    Sistema Ejidal — Reporte generado automáticamente el {{ date('d/m/Y') }}
</div>

</body>
</html>
