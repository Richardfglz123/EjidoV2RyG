<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Verificación 2FA - Sistema Ejidal')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f4f7f6;
        }

        body {
            display: flex;
            flex-direction: column;
        }

        .navbar-ejidal {
            background-color: #212529 !important;
            padding: 0.8rem 1rem;
            width: 100%;
            border-bottom: 3px solid #198754;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            font-weight: 700;
            color: #fff !important;
            margin: 0;
            white-space: nowrap;
        }

        @media (max-width: 768px) {
            .navbar-brand {
                white-space: normal;
                font-size: 0.95rem;
                text-align: center;
                justify-content: center;
                width: 100%;
            }
        }

        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        h2 {
            color: #333;
            font-weight: 700;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .instruction-text {
            text-align: center;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .form-label {
            font-weight: 600;
            color: #444;
            font-size: 0.9rem;
        }

        .input-2fa {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #ced4da;
            border-radius: 8px;
            font-size: 1.5rem;
            letter-spacing: 8px;
            text-align: center;
            font-weight: 700;
            color: #198754;
            transition: border-color 0.3s ease;
        }

        .input-2fa:focus {
            border-color: #198754;
            outline: none;
            box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.1);
        }

        .btn-ejidal {
            width: 100%;
            padding: 0.8rem;
            background-color: #198754;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
            margin-top: 1.5rem;
        }

        .btn-ejidal:hover:not(:disabled) {
            background-color: #157347;
            transform: translateY(-1px);
        }

        .btn-ejidal:disabled {
            background-color: #a5c09a;
            cursor: not-allowed;
        }

        .footer-ejidal {
            background: #fff;
            padding: 1rem 0;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 0.85rem;
        }

        .error-list {
            background-color: #fff5f5;
            border-left: 4px solid #dc3545;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 1.5rem;
            list-style: none;
            font-size: 0.85rem;
            color: #dc3545;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark navbar-ejidal">
    <div class="container-fluid px-lg-5">
        <a class="navbar-brand" href="{{ route('login') }}">
            <i class="fas fa-tractor me-3"></i>
            <span>SISTEMA EJIDAL SAN RAFAEL IXTAPALUCAN</span>
        </a>
    </div>
</nav>

<div class="main-content">
    <div class="login-card">
        <h2>Verificación de Acceso</h2>
        <p class="instruction-text">Por seguridad, ingresa el código de 6 dígitos enviado a tu correp</p>

        @if ($errors->any())
            <ul class="error-list">
                @foreach ($errors->all() as $error)
                    <li><i class="fas fa-times-circle me-2"></i>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form id="two-fa-form" method="POST" action="{{ route('2fa.check') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label d-block text-center mb-3">Código de Verificación</label>
                <input
                        type="text"
                        id="code"
                        name="code"
                        class="input-2fa"
                        maxlength="6"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        required
                        autofocus
                >
            </div>

            <button type="submit" id="btn-submit" class="btn-ejidal">
                <i class="fas fa-shield-alt me-2"></i> Verificar y Entrar
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="{{ route('login') }}" class="text-decoration-none small text-muted">
                <i class="fas fa-arrow-left me-1"></i> Volver al inicio
            </a>
        </div>
    </div>
</div>

<footer class="footer-ejidal">
    <div class="container-fluid px-lg-5 d-flex justify-content-between">
        <span>Sistema de Gestión Ejidal &copy; 2026</span>
        <span class="d-none d-sm-inline">Versión 1.4.1</span>
    </div>
</footer>

<script>
    const codeInput = document.getElementById('code');
    const faForm = document.getElementById('two-fa-form');
    const submitBtn = document.getElementById('btn-submit');

    codeInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');

        if (this.value.length === 6) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Verificando...';
            faForm.submit();
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>