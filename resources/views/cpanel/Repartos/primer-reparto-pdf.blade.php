<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket de Préstamo - {{ $prestamo->Id_Prestamo }}</title>
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
            font-size: 14px;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .header p {
            margin: 3px 0;
        }
        .info-section {
            margin-bottom: 10px;
        }
        /* Reemplazo de flexbox para máxima compatibilidad con PDF (domPDF) */
        .info-table {
            width: 100%;
            margin-bottom: 2px;
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
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
    </style>
</head>
<body>

<div class="ticket-wrapper">
    <!-- Encabezado corregido -->
    <div class="header">
        <h1>Sistema Ejidal San Rafael Ixtapalucan</h1>
        <p><strong>ESTADO DE CUENTA Y PRÉSTAMO 1ER REPARTO</strong></p>
        <p><strong>Folio:</strong> #{{ str_pad($prestamo->Id_Prestamo, 5, '0', STR_PAD_LEFT) }}</p>
    </div>

    <!-- Sección de Información limpia con el Nombre del Ejidatario visible -->
    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Año de Gestión:</td>
                <td style="text-align: right;">{{ now()->year }}</td>
            </tr>
            <tr>
                <td class="label">Fecha Impresión:</td>
                <td style="text-align: right;">{{ date('d/m/Y H:i') }}</td>
            </tr>
        </table>

        <!-- Nombre del Ejidatario destacado -->
        <div class="fw-bold" style="text-transform: uppercase; font-size: 11px; border-top: 1px solid #eee; padding-top: 4px; margin-top: 4px; color: #000;">
            EJIDATARIO: {{ $ejidatario->Nombres }} {{ $ejidatario->Apellido_Paterno }} {{ $ejidatario->Apellido_Materno }}
        </div>
    </div>

    <!-- SECCIÓN DE DETALLE ORIGINAL -->
    <table class="table">
        <thead>
        <tr>
            <th>DESCRIPCIÓN MOTIVO</th>
            <th style="text-align: right;">MONTO ORIG.</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>{{ $prestamo->Motivo }}</td>
            <td style="text-align: right;">${{ number_format($prestamo->Cantidad, 2) }}</td>
        </tr>
        </tbody>
    </table>

    <hr style="border: none; border-top: 1px dashed #ccc; margin: 12px 0;">

    <!-- HISTORIAL DE MOVIMIENTOS / ABONOS -->
    <div class="fw-bold" style="font-size: 10px; margin-bottom: 5px; text-transform: uppercase;">Historial de Pagos:</div>
    <table class="table" style="margin-top: 0;">
        <tbody>
        @if($prestamo->abonos && $prestamo->abonos->count() > 0)
            @foreach($prestamo->abonos as $index => $abono)
                <tr>
                    <td style="font-size: 10px; color: #555;">
                        Abono #{{ $index + 1 }} ({{ \Carbon\Carbon::parse($abono->Fecha)->format('d/m/Y') }})
                    </td>
                    <td style="text-align: right; font-size: 10px;" class="fw-bold">
                        -${{ number_format($abono->Monto, 2) }}
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="2" style="font-size: 10px; color: #777; font-style: italic;">Sin abonos registrados a la fecha.</td>
            </tr>
        @endif
        </tbody>
    </table>

    <!-- SECCIÓN DE TOTALES ACTUALIZADOS -->
    <div class="total-section">
        <div style="font-size: 11px; color: #555;">Total Prestado: ${{ number_format($prestamo->Cantidad, 2) }}</div>
        <div style="font-size: 11px; color: #555;">Total Abonado: -${{ number_format($totalAbonado, 2) }}</div>
        <div class="fw-bold" style="font-size: 12px; margin-top: 4px; border-top: 1px dashed #000; padding-top: 4px;">
            SALDO PENDIENTE: ${{ number_format($saldoRestante, 2) }}
        </div>
    </div>

    <!-- FONDO DE REPARTO EJIDAL -->
    <div style="margin-top: 15px; font-size: 10px; background: #f9f9f9; padding: 6px; border: 1px solid #eee;">
        <table style="width: 100%;">
            <tr>
                <td style="font-size: 10px; color: #555;">Fondo del Reparto:</td>
                <td style="text-align: right; font-size: 10px;">${{ number_format($montoReparto1, 2) }}</td>
            </tr>
            <tr>
                <td class="fw-bold" style="font-size: 10px; padding-top: 3px;">Saldo Neto Restante en Reparto:</td>
                <td class="fw-bold" style="text-align: right; font-size: 10px; padding-top: 3px;">${{ number_format($montoReparto1 - $saldoRestante, 2) }}</td>
            </tr>
        </table>
    </div>

    <!-- Pie de página y Firma -->
    <div class="footer">
        <div class="signature-box">
            Firma del Ejidatario
        </div>
        <p style="margin-top: 15px;">Este documento es un comprobante interno de control de préstamos para el Primer Reparto</p>
    </div>
</div>

</body>
</html>