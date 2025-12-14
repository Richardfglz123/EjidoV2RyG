<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación 2FA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f2e7, #e0d9c8); /* tonos cálidos */
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            background-color: #fff;
        }
        .form-control {
            letter-spacing: 5px;
            border: 1px solid #ccc;
        }
        .form-control:focus {
            border-color: #4b6b2d; /* verde ejidal */
            box-shadow: 0 0 5px rgba(75,107,45,0.4);
        }
        .btn-primary {
            border-radius: 10px;
            font-weight: 600;
            background-color: #4b6b2d; /* verde ejidal */
            border: none;
        }
        .btn-primary:hover {
            background-color: #3a5322;
        }
        h3 {
            color: #4b6b2d !important;
        }
    </style>
</head>
<body>

<div class="container" style="max-width: 420px;">
    <div class="card p-4">
        <h3 class="text-center mb-3">Verificación en dos pasos</h3>
        <p class="text-center text-muted">Ingresa el código que enviamos a tu correo.</p>

        @if(session('error'))
            <div class="alert alert-danger text-center">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('2fa.check') }}">
            @csrf

            <input type="text" name="code" maxlength="6"
                   class="form-control mb-3 text-center fs-4"
                   placeholder="000000" required>

            <button class="btn btn-primary w-100">Verificar</button>
        </form>
    </div>
</div>

</body>
</html>
