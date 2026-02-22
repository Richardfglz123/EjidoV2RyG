<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ticket de Préstamo #{{ $prestamo->id_prestamo }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
        }
        .container {
            border: 1px solid #000;
            padding: 25px;
            width: 600px;
            margin: auto;
        }
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
        }
        .content table {
            width: 100%;
            border-collapse: collapse;
        }
        .content td {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .content .label {
            font-weight: bold;
            width: 200px;
        }
        .signatures {
            margin-top: 80px;
        }
        .signature-box {
            text-align: center;
            width: 45%;
            display: inline-block;
            margin: 0 2%;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 50px;
            padding-top: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Ticket de Préstamo - Primer Reparto</h1>
        <p>Fecha de Entrega: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <div class="content">
        <table>
            <tr>
                <td class="label">Nombre Ejidatario:</td>
                <td>{{ $prestamo->ejidatario->usuario->nombre_completo ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Monto Préstamo:</td>
                <td>${{ number_format($prestamo->cantidad, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Descripción :</td>
                <td>{{ $prestamo->motivo }}</td>
            </tr>


            <tr>
                <td class="label" style="font-weight: bold;">Saldo Neto Restante (del Reparto):</td>
                <td style="font-weight: bold; font-size: 14px;">${{ number_format($saldoNetoRestante, 2) }}</td>
            </tr>

            <tr>
                <td class="label">Fecha/Hora:</td>

                <td>{{ date('d/m/Y H:i:s') }}</td>
            </tr>
        </table>
    </div>

    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line">
                Nombre y Firma Ejidatario
            </div>
            <p style="margin-top: 5px;">{{ $prestamo->ejidatario->usuario->nombre_completo ?? 'N/A' }}</p>
        </div>
        <div class="signature-box">
            <div class="signature-line">
                Nombre y Firma de Quien Entrega
            </div>

            <p style="margin-top: 5px;">.</p>
        </div>
    </div>
</div>
</body>
</html>