<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación 2FA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        /* ======== FONDO GENERAL ======== */
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to bottom right, #d3e8d3, #ffffff);
            margin: 0;
            padding: 0;
        }

        /* ======== NAVBAR ======== */
        .navbar-ejidal {
            background: url(https://www.lamudi.com.mx/journal/wp-content/uploads/2021/08/shutterstock_1773455270-1.jpg)
            center/cover no-repeat;
            height: 50px;
            margin-left: auto;
            margin-right: auto;
            display: flex;
            align-items: center;
            padding: 0 20px;
        }

        .navbar-ejidal .navbar-brand {
            color: white;
            font-weight: bold;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
        }

        /* ======== LOGIN CARD ======== */
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 35px;
            background-color: #ffffffcc;
            backdrop-filter: blur(6px);
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-container h2 {
            text-align: center;
            color: #2c5e1a;
            font-weight: 700;
            margin-bottom: 25px;
        }

        /* ======== INPUTS ======== */
        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #b6b6b6;
            border-radius: 8px;
            font-size: 16px;
            letter-spacing: 6px;
            text-align: center;
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s;
        }

        input[type="text"]:focus {
            border-color: #2c5e1a;
            outline: none;
            box-shadow: 0 0 5px #2c5e1a66;
        }

        /* ======== BOTÓN ======== */
        button {
            width: 100%;
            background-color: #2c5e1a;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s, background-color 0.3s ease;
        }

        button:hover {
            background-color: #214715;
            transform: scale(1.03);
        }

        button:active {
            transform: scale(0.98);
        }

        /* ======== MENSAJES DE ERROR ======== */
        .messages {
            list-style: none;
            padding: 0;
            color: red;
            font-size: 14px;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="navbar-ejidal">
    <span class="navbar-brand">Sistema Ejidal</span>
</div>

<div class="login-container">
    <h2>Verificación</h2>

    <form method="POST" action="{{ route('2fa.check') }}">
        @csrf

        <div class="form-group">
            <label for="code">Código de verificación</label>
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

        <button type="submit">Verificar</button>
    </form>
</div>

</body>
</html>
