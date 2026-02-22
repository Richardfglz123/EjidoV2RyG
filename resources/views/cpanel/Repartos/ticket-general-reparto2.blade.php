<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ticket de Pago - 2do Reparto</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 12px; color: #333; }
        .container { border: 1px solid #000; padding: 20px; width: 300px; margin: auto; }
        .header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 10px; margin-bottom: 15px; }
        .header h2 { margin: 0; font-size: 16px; }
        .info-group { margin-bottom: 10px; }
        .info-label { font-weight: bold; display: block; }

        .summary-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .summary-table td { padding: 5px 0; border-bottom: 1px solid #eee; }
        .amount { text-align: right; }
        .total-row { font-weight: bold; font-size: 14px; border-top: 2px solid #000; }
        .signatures { margin-top: 50px; text-align: center; }
        .line { border-top: 1px solid #000; width: 80%; margin: 0 auto; margin-top: 40px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>TICKET DE PAGO</h2>
        <h3>Segundo Reparto</h3>
        <p>Fecha: {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="info-group">
        <span class="info-label">Ejidatario:</span>
        <span>{{ $ejidatario->usuario->nombre_completo ?? 'N/A' }}</span>
    </div>

    <table class="summary-table">

        <tr>
            <td>Monto 2do Reparto:</td>
            <td class="amount" style="color: #198754;">+ ${{ number_format($montoReparto2, 2) }}</td>
        </tr>


        @if($totalAsambleas > 0)
            <tr>
                <td>(-) Desc. Asambleas:</td>
                <td class="amount" style="color: #dc3545;">- ${{ number_format($totalAsambleas, 2) }}</td>
            </tr>
        @endif

        @if($totalFaenas > 0)
            <tr>
                <td>(-) Desc. Faenas:</td>
                <td class="amount" style="color: #dc3545;">- ${{ number_format($totalFaenas, 2) }}</td>
            </tr>
        @endif

        @if($totalPrestamos > 0)
            <tr>
                <td>(-) Préstamos (Total):</td>
                <td class="amount" style="color: #dc3545;">- ${{ number_format($totalPrestamos, 2) }}</td>
            </tr>
        @endif


        <tr class="total-row">
            <td style="padding-top: 10px;">TOTAL A PAGAR:</td>
            <td class="amount" style="padding-top: 10px;">${{ number_format($totalAPagar, 2) }}</td>
        </tr>
    </table>

    <div class="signatures">
        <div class="line"></div>
        <p>Firma de Conformidad</p>
    </div>
</div>
</body>
</html>