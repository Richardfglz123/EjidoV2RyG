<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Verificación 2FA - Sistema Ejidal')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --primary-green: #198754;
            --dark-green: #146c43;
            --text-main: #2d3436;
        }

        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Inter', 'Segoe UI', Roboto, sans-serif;
            background: radial-gradient(circle at top right, #e8f5e9, #f4f7f6);
        }

        body {
            display: flex;
            flex-direction: column;
        }

        /* Navbar Consistente */
        .navbar-ejidal {
            background-color: #1a1d20 !important;
            padding: 0.7rem 1rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-bottom: 4px solid var(--primary-green);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            font-weight: 800;
            color: #fff !important;
            text-transform: uppercase;
            font-size: 1rem;
        }

        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        /* Card con el mismo estilo que el Login */
        .login-card {
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            padding: 2.5rem 2.2rem;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.05);
            text-align: center;
        }

        h2 {
            color: var(--text-main);
            font-weight: 800;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .instruction-text {
            color: #636e72;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 2rem;
        }

        .form-label {
            font-weight: 700;
            color: #555;
            margin-bottom: 1rem;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Input 2FA Modernizado */
        .input-2fa {
            width: 100%;
            padding: 1rem;
            border: 2px solid #edf2f7;
            border-radius: 12px;
            background: #f8fafc;
            font-size: 1.8rem;
            letter-spacing: 12px;
            text-align: center;
            font-weight: 800;
            color: var(--primary-green);
            transition: all 0.3s ease;
            outline: none;
        }

        .input-2fa:focus {
            border-color: var(--primary-green);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(25, 135, 84, 0.1);
        }

        /* Botón de Acción */
        .btn-ejidal {
            width: 100%;
            padding: 0.9rem;
            background: var(--primary-green);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
            margin-top: 1.5rem;
            box-shadow: 0 8px 15px rgba(25, 135, 84, 0.2);
        }

        .btn-ejidal:hover:not(:disabled) {
            background: var(--dark-green);
            transform: translateY(-2px);
            box-shadow: 0 12px 20px rgba(25, 135, 84, 0.25);
        }

        .btn-ejidal:disabled {
            background-color: #a5c09a;
            box-shadow: none;
            cursor: not-allowed;
        }

        /* Footer Refinado */
        .footer-ejidal {
            background: rgba(255, 255, 255, 0.8);
            padding: 1rem 0;
            border-top: 1px solid #eef2f7;
            color: #94a3b8;
            font-size: 0.8rem;
        }

        /* Estilo de Errores */
        .error-list {
            background-color: #fff5f5;
            border-left: 4px solid #ff7675;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            list-style: none;
            font-size: 0.85rem;
            color: #d63031;
            text-align: left;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark navbar-ejidal">
    <div class="container-fluid px-lg-5">
        <a class="navbar-brand" href="{{ route('login') }}">
            <i class="fas fa-tractor me-2"></i>
            <span>Sistema Ejidal <span class="d-none d-md-inline">San Rafael Ixtapalucan</span></span>
        </a>
    </div>
</nav>

<div class="main-content">
    <div class="login-card">
        <div class="mb-3">
            <i class="fas fa-user-shield fa-3x" style="color: var(--primary-green); opacity: 0.8;"></i>
        </div>
        <h2>Verificación de Acceso</h2>
        <p class="instruction-text">Por seguridad, ingresa el código de 6 dígitos enviado a tu correo electrónico para continuar.</p>

        @if ($errors->any())
            <ul class="error-list shadow-sm">
                @foreach ($errors->all() as $error)
                    <li><i class="fas fa-exclamation-circle me-2"></i>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form id="two-fa-form" method="POST" action="{{ route('2fa.check') }}">
            @csrf
            <div class="mb-4">
                <label class="form-label d-block">Código de Verificación</label>
                <input
                        type="text"
                        id="code"
                        name="code"
                        class="input-2fa shadow-sm"
                        maxlength="6"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        placeholder="000000"
                        required
                        autofocus
                >
            </div>

            <button type="submit" id="btn-submit" class="btn-ejidal">
                <i class="fas fa-shield-alt me-2"></i> Verificar y Entrar
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="{{ route('login') }}" class="text-decoration-none small" style="color: #94a3b8; font-weight: 600; transition: color 0.2s;">
                <i class="fas fa-arrow-left me-1"></i> Volver al inicio
            </a>
        </div>
    </div>
</div>

<footer class="footer-ejidal">
    <div class="container-fluid px-lg-5 d-flex justify-content-between align-items-center">
        <span>&copy; 2026 Gestión Ejidal</span>
        <span class="badge bg-light text-muted border">v1.4.1</span>
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