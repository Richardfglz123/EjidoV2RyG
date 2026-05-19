<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket Segundo Reparto - {{ $ejidatario->Id_Ejidatario }}</title>
    <style>
        @page {
            margin: 0.5cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        .ticket-wrapper {
            width: 100%;
            max-width: 80mm;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 8px;
        }
        .header h1 {
            font-size: 13px;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .header p {
            margin: 3px 0;
        }
        .info-section {
            margin-bottom: 10px;
        }
        /* Reemplazo de flexbox para máxima compatibilidad con PDF */
        .info-table {
            width: 100%;
            margin-bottom: 5px;
        }
        .info-table td {
            padding: 2px 0;
            font-size: 11px;
        }
        .label {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table th {
            border-bottom: 1px solid #000;
            text-align: left;
            font-size: 11px;
            padding-bottom: 3px;
        }
        .table td {
            padding: 5px 0;
            font-size: 11px;
            vertical-align: top;
        }
        .total-section {
            margin-top: 10px;
            border-top: 1px double #000;
            padding-top: 5px;
            text-align: right;
        }
        .footer {
            margin-top: 25px;
            text-align: center;
            font-size: 9px;
        }
        .signature-box {
            margin-top: 35px;
            border-top: 1px solid #333;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
            padding-top: 5px;
            text-transform: uppercase;
            font-weight: bold;
        }
        .fw-bold { font-weight: bold; }
        .text-danger { color: #dc3545; }
        .text-success { color: #1b4b36; }
    </style>
</head>
<body>

<div class="ticket-wrapper">
    <!-- Encabezado corregido sin etiquetas anidadas erróneas -->
    <div class="header">
        <h1>Sistema Ejidal San Rafael Ixtapalucan</h1>
        <p>
            <strong>
                @if($totalAPagar >= 0)
                    LIQUIDACIÓN DE SEGUNDO REPARTO
                @else
                    ESTADO DE ADEUDO — 2DO REPARTO
                @endif
            </strong>
        </p>
        <p><strong>Folio:</strong> #{{ str_pad($prestamo->Id_Prestamo, 5, '0', STR_PAD_LEFT) }}</p>
        <p><strong>Año de Gestión:</strong> {{ now()->year }}</p>
    </div>

    <!-- Sección de Información optimizada para impresión -->
    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Fecha Impresión:</td>
                <td style="text-align: right;">{{ date('d/m/Y H:i') }}</td>
            </tr>
        </table>
        <div class="fw-bold" style="text-transform: uppercase; font-size: 11px; border-top: 1px solid #eee; padding-top: 4px; margin-top: 4px;">
            {{ $ejidatario->Nombres }} {{ $ejidatario->Apellido_Paterno }} {{ $ejidatario->Apellido_Materno }}
        </div>
    </div>

    <!-- Tabla Principal de Conceptos -->
    <table class="table">
        <thead>
        <tr>
            <th>CONCEPTO / DESCRIPCIÓN</th>
            <th style="text-align: right;">MONTO</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Fondo Asignado Base R2</td>
            <td style="text-align: right;" class="text-success">+${{ number_format($montoFijoR2, 2) }}</td>
        </tr>

        <tr>
            <td>(-) Retención Faltas Asambleas</td>
            <td style="text-align: right;" class="{{ $totalAsambleas > 0 ? 'text-danger' : '' }}">
                -${{ number_format($totalAsambleas, 2) }}
            </td>
        </tr>

        <tr>
            <td>(-) Retención Faltas Faenas</td>
            <td style="text-align: right;" class="{{ $totalFaenas > 0 ? 'text-danger' : '' }}">
                -${{ number_format($totalFaenas, 2) }}
            </td>
        </tr>

        @if($deudaArrastrada > 0)
            <tr>
                <td>
                    (-) Saldo Pendiente Préstamo R1<br>
                    <small style="color: #666; font-size: 9px;">Deuda: ${{ number_format($totalPrestamoR1, 2) }} | Abonado: ${{ number_format($totalAbonosR1, 2) }}</small>
                </td>
                <td style="text-align: right;" class="text-danger">-${{ number_format($deudaArrastrada, 2) }}</td>
            </tr>
        @endif
        </tbody>
    </table>

    <hr style="border: none; border-top: 1px dashed #ccc; margin: 12px 0;">

    <!-- Desglose de Abonos -->
    <div class="fw-bold" style="font-size: 10px; margin-bottom: 5px; text-transform: uppercase;">Desglose de Abonos Realizados (R1):</div>
    <table class="table" style="margin-top: 0;">
        <tbody>
        @if($historialAbonos->count() > 0)
            @foreach($historialAbonos as $index => $abono)
                <tr>
                    <td style="font-size: 10px; color: #555;">
                        Abono #{{ $index + 1 }} ({{ \Carbon\Carbon::parse($abono->Fecha)->format('d/m/Y') }})
                    </td>
                    <td style="text-align: right; font-size: 10px; color: #1b4b36;">
                        +${{ number_format($abono->Monto, 2) }}
                    </td>
                </tr>
            @endforeach
            <tr style="border-top: 1px solid #ccc;">
                <td class="fw-bold" style="font-size: 10px; padding-top: 4px;">Total a pagar:</td>
                <td class="fw-bold" style="text-align: right; font-size: 10px; padding-top: 4px;">${{ number_format($totalAbonosR1, 2) }}</td>
            </tr>
        @else
            <tr>
                <td colspan="2" style="font-size: 10px; color: #777; font-style: italic;">Sin abonos registrados en este ciclo.</td>
            </tr>
        @endif
        </tbody>
    </table>

    <!-- Totales Finales -->
    <div class="total-section">
        <div style="font-size: 11px; color: #666;">Monto total R2: ${{ number_format($montoFijoR2, 2) }}</div>
        <div style="font-size: 11px; color: #666;">Total Deducciones Aplicadas: -${{ number_format($totalDeducciones, 2) }}</div>

        <div class="fw-bold" style="font-size: 12px; margin-top: 4px; border-top: 1px dashed #000; padding-top: 4px;">
            @if($totalAPagar >= 0)
                <span class="text-success">NETO A ENTREGAR: ${{ number_format($totalAPagar, 2) }}</span>
            @else
                <span class="text-danger">SALDO DEUDOR EN CAJA: ${{ number_format(abs($totalAPagar), 2) }}</span>
            @endif
        </div>
    </div>

    <!-- Pie de página y Firma -->
    <div class="footer">
        <div class="signature-box">
            Firma de Conformidad
        </div>
        <p style="margin-top: 15px;">Este documento es un comprobante de control interno para el proceso de liquidación del Segundo Reparto.</p>
    </div>
</div>

</body>
</html>