<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación 2FA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(to bottom right, #d3e8d3, #ffffff);
            margin: 0;
            padding: 0;
        }

        /* NAVBAR */
        .navbar-ejidal {
            background: url(https://www.lamudi.com.mx/journal/wp-content/uploads/2021/08/shutterstock_1773455270-1.jpg)
            center / cover no-repeat;
            height: 56px;
            display: flex;
            align-items: center;
            padding: 0 24px;
        }

        .navbar-ejidal .navbar-brand {
            color: #ffffff;
            font-weight: 700;
            font-size: 18px;
            letter-spacing: 0.3px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
            text-decoration: none; /* sin subrayado */
        }

        .navbar-ejidal .navbar-brand:hover,
        .navbar-ejidal .navbar-brand:focus {
            text-decoration: none;
            opacity: 0.95;
        }

        /* CONTENEDOR LOGIN */
        .login-container {
            max-width: 420px;
            margin: 90px auto;
            padding: 40px;
            background-color: #ffffffcc;
            backdrop-filter: blur(6px);
            border-radius: 18px;
            box-shadow: 0 14px 35px rgba(0,0,0,0.14);
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-container h2 {
            text-align: center;
            color: #2c5e1a;
            font-weight: 700;
            margin-bottom: 30px;
            letter-spacing: 0.4px;
        }

        /* FORM */
        .form-group {
            margin-bottom: 22px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
            font-size: 14px;
        }

        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #b6b6b6;
            border-radius: 10px;
            font-size: 18px;
            letter-spacing: 6px;
            text-align: center;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        input[type="text"]:focus {
            border-color: #2c5e1a;
            outline: none;
            box-shadow: 0 0 0 3px #2c5e1a33;
        }

        /* BOTÓN */
        button {
            width: 100%;
            background-color: #2c5e1a;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.25s ease, transform 0.15s ease;
        }

        button:hover {
            background-color: #214715;
            transform: translateY(-1px);
        }

        button:active {
            transform: translateY(0);
        }

        /* ERRORES */
        .messages {
            list-style: none;
            padding: 0;
            color: #b00020;
            font-size: 14px;
            margin: 10px 0 18px 0;
            text-align: center;
        }

        /* RESPONSIVE */
        @media (max-width: 480px) {
            .login-container {
                margin: 60px 16px;
                padding: 32px 24px;
            }

            .navbar-ejidal {
                height: auto;
                padding: 12px 16px;
            }

            .navbar-ejidal .navbar-brand {
                font-size: 16px;
            }
        }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-ejidal">
    <div class="container">
        <a class="navbar-brand" href="{{ route('login') }}">
            Sistema Ejidal San Rafael Ixtapalucan
        </a>
    </div>
</nav>

<div class="login-container">
    <h2>Validación para acceso</h2>

    <form method="POST" action="{{ route('2fa.check') }}">
        @csrf

        <div class="form-group">
            <label for="code">Ingresa el código de verificación</label>
            <input
                    type="text"
                    id="code"
                    name="code"
                    maxlength="6"
                    autofocus
                    inputmode="numeric"
                    autocomplete="one-time-code"
            >
        </div>

        @error('code')
        <ul class="messages">
            <li>{{ $message }}</li>
        </ul>
        @enderror

        <button type="submit">Acceder</button>
    </form>
</div>

</body>
</html>