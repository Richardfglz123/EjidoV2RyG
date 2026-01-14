<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Actividades</title>
    <style>
        @page {
            margin: 1cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }

        /* Título con estilo Ejidal */
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #1f2933; /* Color ejidal oscuro */
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 22px;
            color: #1f2933;
            text-transform: uppercase;
        }

        .header p {
            margin: 5px 0 0;
            font-size: 13px;
            font-weight: bold;
            color: #555;
        }

        .info {
            margin-bottom: 15px;
            font-size: 10px;
            width: 100%;
        }

        /* Tabla con colores Ejidales */
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* Ayuda a que no se desborde */
        }

        th {
            background-color: #1f2933; /* Mismo color que card-header-ejidal */
            color: #ffffff;
            padding: 8px 5px;
            font-size: 10px;
            border: 1px solid #1f2933;
            text-align: left;
            text-transform: uppercase;
        }

        td {
            padding: 6px 4px;
            border: 1px solid #dee2e6;
            font-size: 9px;
            word-wrap: break-word; /* Ajusta texto largo */
        }

        /* Estilo cebra (striped) como en tu web */
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            padding: 2px 5px;
            border-radius: 4px;
            background-color: #e9ecef;
            color: #333;
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: -10px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #777;
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

<table class="info" style="border: none;">
    <tr>
        <td style="border: none; width: 50%;">
            <strong>Fecha de generación:</strong> {{ now()->format('d/m/Y H:i') }}
        </td>
        <td style="border: none; width: 50%; text-align: right;">
            <strong>Total de registros:</strong> {{ count($data) }}
        </td>
    </tr>
</table>

<table>
    <thead>
    <tr>
        <th style="width: 25px;">#</th>
        <th style="width: 70px;">Tipo</th>
        <th>Descripción</th>
        <th style="width: 60px;">Inicio</th>
        <th style="width: 60px;">Fin</th>
        <th style="width: 70px;">Estado</th>
        <th style="width: 60px;">Nueva Fecha</th>
        <th style="width: 60px;">Realizado</th>
    </tr>
    </thead>
    <tbody>
    @forelse($data as $i => $a)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td><strong>{{ $a->Tipo }}</strong></td>
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
            <td colspan="8" class="text-center" style="padding: 20px;">
                No hay actividades registradas para los filtros seleccionados.
            </td>
        </tr>
    @endforelse
    </tbody>
</table>

<div class="footer">
    Sistema Ejidal — Reporte oficial generado el {{ date('d/m/Y') }} — Página 1
</div>

</body>
</html>
