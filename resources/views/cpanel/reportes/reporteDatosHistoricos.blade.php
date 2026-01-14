<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Datos Históricos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #222;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
            letter-spacing: 1px;
        }

        .header p {
            margin: 5px 0 0;
            font-size: 12px;
        }

        .info {
            margin-bottom: 15px;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #1f2933;
            color: #fff;
            padding: 6px;
            font-size: 11px;
            border: 1px solid #000;
        }

        td {
            padding: 6px;
            border: 1px solid #000;
            font-size: 10px;
            vertical-align: top;
        }

        .footer {
            position: fixed;
            bottom: 10px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #555;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Reporte de Datos Históricos</h1>
    <p>Sistema Ejidal</p>
</div>

<div class="info">
    <strong>Fecha de generación:</strong> {{ now()->format('d/m/Y H:i') }} <br>
    <strong>Total de registros:</strong> {{ count($data) }}
</div>

<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Título</th>
        <th>Descripción</th>
        <th>Fecha</th>
    </tr>
    </thead>
    <tbody>
    @forelse($data as $i => $r)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $r->Titulo }}</td>
            <td>{{ $r->Descripcion }}</td>
            <td>{{ \Carbon\Carbon::parse($r->Fecha)->format('d/m/Y') }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="4" style="text-align:center">
                No hay registros para los filtros seleccionados
            </td>
        </tr>
    @endforelse
    </tbody>
</table>

<div class="footer">
    Sistema Ejidal — Reporte generado automáticamente
</div>

</body>
</html>
