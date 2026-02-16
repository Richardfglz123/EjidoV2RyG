<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Código de verificación</title>
</head>

<body style="margin:0;padding:0;background-color:#0b0b0e;">

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#0b0b0e;">
    <tr>
        <td align="center" style="padding:40px 10px;">

            <!-- CONTENEDOR -->
            <table width="550" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#141418;border-top:6px solid #4da3ff;">

                <!-- HEADER -->
                <tr>
                    <td align="center" style="padding:30px 20px 20px 20px;">
                        <h1 style="margin:0;font-size:28px;color:#4da3ff;font-family:Arial,Helvetica,sans-serif;">
                            Sistema Ejidal
                        </h1>

                        <p style="margin:10px 0 0 0;font-size:20px;color:#eaeaea;font-family:Arial,Helvetica,sans-serif;">
                            ¡Hola {{ $nombre }}!
                        </p>
                    </td>
                </tr>

                <!-- TEXTO -->
                <tr>
                    <td align="center" style="padding:0 25px 20px 25px;">
                        <p style="margin:0;font-size:16px;line-height:24px;color:#cfcfcf;font-family:Arial,Helvetica,sans-serif;">
                            Has solicitado acceder a tu cuenta. Utiliza el siguiente código de verificación:
                        </p>
                    </td>
                </tr>

                <!-- CÓDIGO -->
                <tr>
                    <td align="center" style="padding:20px 0 30px 0;">

                        <table cellpadding="0" cellspacing="0" role="presentation" style="border:2px solid #4da3ff;">
                            <tr>
                                <td align="center" style="padding:18px 40px;">

<span style="
font-size:34px;
letter-spacing:6px;
font-weight:bold;
color:#4da3ff;
font-family:Courier New,monospace;
">
{{ $codigo }}
</span>

                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>

                <!-- TEXTO FINAL -->
                <tr>
                    <td align="center" style="padding:0 25px 20px 25px;">
                        <p style="margin:0;font-size:15px;line-height:22px;color:#cfcfcf;font-family:Arial,Helvetica,sans-serif;">
                            Introduce este código en la aplicación para completar tu inicio de sesión.
                        </p>

                        <p style="margin:8px 0 0 0;font-size:14px;color:#9a9a9a;font-family:Arial,Helvetica,sans-serif;">
                            Este código expirará en 10 minutos.
                        </p>
                    </td>
                </tr>

                <!-- FOOTER -->
                <tr>
                    <td align="center" style="padding:20px;border-top:1px solid #2a2a2a;background-color:#0b0b0e;">
                        <p style="margin:0 0 8px 0;font-size:12px;color:#8a8a8a;font-family:Arial,Helvetica,sans-serif;">
                            Este es un correo automático de seguridad.<br>
                            Si no solicitaste este código, ignora este mensaje.
                        </p>

                        <p style="margin:0;font-size:11px;color:#6f6f6f;font-family:Arial,Helvetica,sans-serif;">
                            Gracias,<br><strong>Equipo del Sistema Ejidal</strong>
                        </p>
                    </td>
                </tr>

            </table>
            <!-- FIN CONTENEDOR -->

        </td>
    </tr>
</table>

</body>
</html>