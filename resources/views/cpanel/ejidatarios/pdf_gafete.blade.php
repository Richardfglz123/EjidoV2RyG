<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gafete - {{ $fila->Num_Ejidatario }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            text-align: center;
        }
        .gafete-container {
            border: 2px solid #198754;
            padding: 20px;
            border-radius: 10px;
            width: 200px;
            margin: auto;
        }
        .header {
            font-size: 14px;
            font-weight: bold;
            color: #198754;
            margin-bottom: 10px;
        }
        .qr-area {
            margin: 15px 0;
        }
        .nombre-ejidatario {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
            display: block;
        }
        .num-ejidatario {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>

<div class="gafete-container">
    <div class="header">EJIDO SAN RAFAEL<br>IXTAPALUCAN</div>

    <div class="qr-area">
        {!! QrCode::size(150)->generate($fila->qr_payload) !!}
    </div>

    <div class="nombre-ejidatario">
        {{ $fila->Nombres }} {{ $fila->Apellido_Paterno }} {{ $fila->Apellido_Materno }}
    </div>

    <div class="num-ejidatario">
        # {{ $fila->Num_Ejidatario }}
    </div>
</div>

</body>
</html>