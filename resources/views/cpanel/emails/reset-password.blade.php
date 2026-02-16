<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de reestablecimiento de contraseña - Sistema Ejidal San Rafael Ixtapalucan </title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Helvetica Neue', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background: radial-gradient(circle at top, #1b1b1f 0%, #0b0b0e 60%);
            color: #eaeaea;
        }

        .content-table {
            background: linear-gradient(180deg, #141418 0%, #0f0f13 100%);
            border-radius: 18px;
            overflow: hidden;
            box-shadow:
                0 20px 40px rgba(0,0,0,0.6),
                inset 0 0 0 1px rgba(255,255,255,0.04);
            border-top: 6px solid #ff9f43;
        }

        .codigo-box {
            background: linear-gradient(145deg, #2a1a0f, #1a110b);
            border: 2px solid rgba(255,159,67,0.5);
            border-radius: 16px;
            padding: 20px 40px;
            margin: 36px auto;
            width: fit-content;
            text-align: center;
            box-shadow:
                0 10px 25px rgba(0,0,0,0.6),
                inset 0 0 0 1px rgba(255,159,67,0.15);
        }

        .codigo-text {
            font-size: 36px;
            font-weight: 900;
            color: #ff9f43;
            letter-spacing: 8px;
            font-family: "SF Mono", Menlo, Consolas, monospace;
            text-shadow: 0 0 12px rgba(255,159,67,0.45);
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

<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" style="padding: 50px 0;">
            <table class="content-table" width="550" cellpadding="0" cellspacing="0">

                <tr>
                    <td style="padding: 36px 30px 18px; text-align: center;">
                        <h1 style="color: #ff9f43; font-size: 30px; margin-bottom: 8px;">
                            Sistema Ejidal
                        </h1>
                        <h2 style="color: #eaeaea; font-size: 22px; margin: 0; padding-bottom: 14px; border-bottom: 1px solid rgba(255,255,255,0.08);">
                            ¡Hola {{ $nombre }}!
                        </h2>
                    </td>
                </tr>

                <tr>
                    <td style="padding: 10px 30px 24px; text-align: center;">
                        <p style="color: #cfcfcf; font-size: 16px; margin-bottom: 22px;">
                            Recibimos una solicitud para <strong>restablecer la contraseña</strong> de tu cuenta.
                        </p>

                        <p style="color: #cfcfcf; font-size: 16px;">
                            Usa el siguiente código para continuar con el proceso:
                        </p>

                        <div class="codigo-box">
                            <span class="codigo-text">{{ $codigo }}</span>
                        </div>

                        <p style="color: #cfcfcf; font-size: 16px; margin-top: 28px;">
                            Ingresa este código en la pantalla de restablecimiento para definir una nueva contraseña.
                        </p>

                        <p style="color: #9a9a9a; font-size: 14px;">
                            Este código expirará en 10 minutos.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding: 26px 30px; text-align: center; border-top: 1px solid rgba(255,255,255,0.06); background: #0b0b0e;">
                        <p style="font-size: 12px; color: #8a8a8a;">
                            Si tú no solicitaste restablecer tu contraseña,
                            <br>ignora este correo tranquilamente.
                        </p>
                        <p style="font-size: 11px; color: #6f6f6f;">
                            — Equipo del Sistema Ejidal
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
