<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { size: 54mm 86mm; margin: 0; }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 5mm;
            text-align: center;
        }
        .gafete {
            border: 2px solid #198754;
            border-radius: 10px;
            padding: 10px;
            height: 76mm;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .header { font-size: 14px; font-weight: bold; color: #198754; margin-bottom: 10px; }
        .qr-container { margin: 10px 0; }
        .nombre { font-size: 12px; font-weight: bold; text-transform: uppercase; margin: 10px 0; }
        .num { font-size: 10px; color: #666; }
    </style>
</head>
<body>
<div class="gafete">
    <div class="header">EJIDO SAN RAFAEL<br>IXTAPALUCAN</div>

    <div class="qr-container">
        <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(150)->generate($fila->qr_payload)) !!} ">
    </div>

    <div class="nombre">{{ $fila->Nombres }} {{ $fila->Apellido_Paterno }} {{ $fila->Apellido_Materno }}</div>
    <div class="num">N. E.: {{ $fila->Num_Ejidatario }}</div>
</div>
</body>
</html>