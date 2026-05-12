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
            font-size: 12px;
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
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 16px;
            margin: 0;
            text-transform: uppercase;
        }
        .info-section {
            margin-bottom: 10px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
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
        }
        .table td {
            padding: 5px 0;
            font-size: 11px;
        }
        .total-section {
            margin-top: 10px;
            border-top: 1px double #000;
            padding-top: 5px;
            text-align: right;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
        }
        .signature-box {
            margin-top: 40px;
            border-top: 1px solid #333;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
            padding-top: 5px;
        }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
    </style>
</head>
<body>

<div class="ticket-wrapper">
    <div class="header">
        <h1>EJIDO {{ env('APP_NAME', 'SISTEMA DE CONTROL') }}</h1>
        <p><strong>COMPROBANTE DE PRÉSTAMO</strong><br>
            Folio: #{{ str_pad($prestamo->Id_Prestamo, 5, '0', STR_PAD_LEFT) }}</p>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="label">Fecha:</span>
            <span>{{ \Carbon\Carbon::parse($prestamo->Fecha)->format('d/m/Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="label">Ejidatario:</span><br>
            <span class="fw-bold">{{ $prestamo->ejidatario->usuario->Nombres }} {{ $prestamo->ejidatario->usuario->Apellido_Paterno }}</span>
        </div>
    </div>

    <table class="table">
        <thead>
        <tr>
            <th>DESCRIPCIÓN</th>
            <th style="text-align: right;">MONTO</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>{{ $prestamo->Motivo }}</td>
            <td style="text-align: right;">${{ number_format($prestamo->Cantidad, 2) }}</td>
        </tr>
        </tbody>
    </table>

    <div class="total-section">
        <span class="fw-bold">DEUDA REGISTRADA: ${{ number_format($prestamo->Cantidad, 2) }}</span>
    </div>

    <div class="info-section" style="margin-top: 15px; font-size: 10px; background: #f9f9f9; padding: 5px;">
        {{-- Aquí usamos la variable $montoReparto1 que agregamos al controlador --}}
        <div class="info-row">
            <span>Fondo del Reparto:</span>
            <span>${{ number_format($montoReparto1, 2) }}</span>
        </div>
        <div class="info-row">
            <span class="fw-bold">Saldo Estimado Restante:</span>
            <span class="fw-bold">${{ number_format($montoReparto1 - $prestamo->Cantidad, 2) }}</span>
        </div>
    </div>

    <div class="footer">
        <div class="signature-box">
            Firma del Ejidatario
        </div>
        <p style="margin-top: 20px;">Este documento es un comprobante interno de control de préstamos para el Primer Reparto</p>
    </div>
</div>

</body>
</html>