<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { size: 54mm 86mm; margin: 0; }

        body {
            margin: 0;
            padding: 0;
            width: 54mm;
            height: 86mm;
            overflow: hidden;
            font-family: Arial, sans-serif;
        }

        .gafete {
            position: relative;
            width: 50mm;
            height: 82mm;
            margin: 2mm;
            border: 2px solid #198754;
            border-radius: 5px;
            text-align: center;
        }

        .header { margin-top: 5mm; font-size: 13px; font-weight: bold; color: #198754; }
        .qr-container { margin-top: 5mm; }
        .nombre { margin-top: 5mm; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .num { margin-top: 2mm; font-size: 9px; color: #666; }
    </style>
</head>
<body>
<div class="gafete">
    <div class="header">EJIDO SAN RAFAEL<br>IXTAPALUCAN</div>

    <div class="qr-container">
        <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(140)->generate($fila->qr_payload)) !!}">
    </div>

    <div class="nombre">{{ $fila->Nombres }} {{ $fila->Apellido_Paterno }} {{ $fila->Apellido_Materno }}</div>
    <div class="num">N. E.: {{ $fila->Num_Ejidatario }}</div>
</div>
</body>
</html>