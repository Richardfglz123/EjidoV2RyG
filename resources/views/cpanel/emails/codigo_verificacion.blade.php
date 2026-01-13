<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de Verificación - Sistema Ejidal</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Helvetica Neue', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background: radial-gradient(circle at top, #1b1b1f 0%, #0b0b0e 60%);
            color: #eaeaea;
        }

        a {
            color: #4da3ff;
            text-decoration: none;
        }

        .content-table {
            background: linear-gradient(180deg, #141418 0%, #0f0f13 100%);
            border-radius: 18px;
            overflow: hidden;
            box-shadow:
                0 20px 40px rgba(0,0,0,0.6),
                inset 0 0 0 1px rgba(255,255,255,0.04);
            border-top: 6px solid #4da3ff;
        }

        .codigo-box {
            background: linear-gradient(145deg, #0f1b2a, #0b111a);
            border: 2px solid rgba(77,163,255,0.4);
            border-radius: 16px;
            padding: 20px 40px;
            margin: 36px auto;
            width: fit-content;
            text-align: center;
            box-shadow:
                0 10px 25px rgba(0,0,0,0.6),
                inset 0 0 0 1px rgba(77,163,255,0.15);
        }

        .codigo-text {
            font-size: 36px;
            font-weight: 900;
            color: #4da3ff;
            letter-spacing: 8px;
            display: inline-block;
            font-family: "SF Mono", Menlo, Consolas, monospace;
            text-shadow: 0 0 12px rgba(77,163,255,0.45);
        }

        h1 {
            letter-spacing: 1px;
        }

        h2 {
            font-weight: 600;
        }

        @media only screen and (max-width: 600px) {
            .content-table {
                width: 92% !important;
            }
            .codigo-text {
                font-size: 30px !important;
                letter-spacing: 6px !important;
            }
        }
    </style>
</head>
<body>

<table class="container" role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td align="center" style="padding: 50px 0;">
            <table class="content-table" role="presentation" border="0" cellpadding="0" cellspacing="0" width="550">

                <tr>
                    <td style="padding: 36px 30px 18px 30px; text-align: center;">
                        <h1 style="color: #4da3ff; font-size: 30px; margin: 0 0 8px 0;">Sistema Ejidal</h1>
                        <h2 style="color: #eaeaea; font-size: 22px; margin: 0; padding-bottom: 14px; border-bottom: 1px solid rgba(255,255,255,0.08);">
                            ¡Hola {{ $nombre }}!
                        </h2>
                    </td>
                </tr>

                <tr>
                    <td style="padding: 10px 30px 24px 30px; text-align: center;">
                        <p style="color: #cfcfcf; font-size: 16px; margin: 0 0 22px 0;">
                            Has solicitado acceder a tu cuenta. Por favor, utiliza el siguiente código de verificación de un solo uso:
                        </p>

                        <div class="codigo-box">
                            <span class="codigo-text">{{ $codigo }}</span>
                        </div>

                        <p style="color: #cfcfcf; font-size: 16px; margin: 28px 0 6px 0;">
                            Introduce este código en el campo de verificación de la aplicación para completar tu inicio de sesión
                        </p>

                        <p style="color: #9a9a9a; font-size: 14px; margin: 0;">
                            Este código expirará en 10 minutos
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding: 26px 30px; text-align: center; border-top: 1px solid rgba(255,255,255,0.06); background: #0b0b0e;">
                        <p style="font-size: 12px; color: #8a8a8a; margin: 0 0 10px 0;">
                            Este es un correo automático de seguridad
                            <br>Si no solicitaste este código, por favor, ignora este mensaje
                        </p>
                        <p style="font-size: 11px; color: #6f6f6f; margin: 0;">
                            Gracias, <span style="font-weight: bold;">El equipo dl Sistema Ejidal</span>
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
