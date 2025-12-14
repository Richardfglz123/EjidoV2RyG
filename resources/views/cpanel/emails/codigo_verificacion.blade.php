<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de Verificación - Sistema Ejidal</title>
    <style>
        /* Estilos generales para clientes que lo soporten */
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f7f6; /* Fondo más claro */
        }
        a {
            color: #1a73e8;
            text-decoration: none;
        }
        /* Estilo para el código que no puede ser inline por el width: fit-content */
        .codigo-box {
            background-color: #ffffff; /* Fondo claro para el código */
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px 30px;
            margin: 30px auto;
            width: fit-content;
            text-align: center;
        }
        .codigo-text {
            font-size: 36px;
            font-weight: 700;
            color: #333333; /* Texto oscuro para contraste */
            letter-spacing: 6px;
            display: inline-block;
        }

        /* Media Queries para mejor visualización en móvil */
        @media only screen and (max-width: 600px) {
            .container {
                width: 100% !important;
                padding: 0 !important;
            }
            .content-table {
                width: 90% !important;
            }
            .codigo-text {
                font-size: 30px !important;
                padding: 10px 20px !important;
            }
        }
    </style>
</head>
<body style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; margin: 0; padding: 0; background-color: #f4f7f6;">

<table class="container" role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f4f7f6;">
    <tr>
        <td align="center" style="padding: 40px 0;">
            <table class="content-table" role="presentation" border="0" cellpadding="0" cellspacing="0" width="550" style="background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-top: 5px solid #1a73e8;">

                <tr>
                    <td style="padding: 30px 30px 15px 30px; text-align: center;">
                        <h1 style="color: #1a73e8; font-size: 28px; margin: 0 0 10px 0;">Sistema Ejidal</h1>
                        <h2 style="color: #333333; font-size: 22px; margin: 0; padding-bottom: 10px; border-bottom: 1px solid #eeeeee;">¡Hola {{ $nombre }}!</h2>
                    </td>
                </tr>

                <tr>
                    <td style="padding: 0 30px 20px 30px; text-align: center;">
                        <p style="color: #555555; font-size: 16px; margin: 0 0 20px 0;">Has solicitado acceder a tu cuenta. Por favor, utiliza el siguiente código de verificación de un solo uso:</p>

                        <div class="codigo-box">
                            <span class="codigo-text">{{ $codigo }}</span>
                        </div>

                        <p style="color: #555555; font-size: 16px; margin: 30px 0;">
                            Introduce este código en el campo de verificación de la aplicación para completar tu inicio de sesión.
                        </p>

                        <p style="color: #555555; font-size: 14px; margin: 0;">
                            **Este código expirará en 10 minutos.**
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding: 25px 30px; text-align: center; border-top: 1px solid #eeeeee;">
                        <p style="font-size: 12px; color: #999999; margin: 0 0 10px 0;">
                            Este es un correo automático de seguridad.
                            <br>Si no solicitaste este código, por favor, ignora este mensaje.
                        </p>
                        <p style="font-size: 11px; color: #aaaaaa; margin: 0;">
                            Gracias, <span style="font-weight: bold;">El equipo de Sistema Ejidal</span>
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
