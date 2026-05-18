<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Acceso - Sistema Ejidal')</title>
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

        /* HEADER SUPERIOR */
        .navbar-ejidal {
            background-color: #1a1d20 !important;
            padding: 0.7rem 1rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-bottom: 4px solid var(--primary-green);
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
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.05);
        }

        h2 {
            color: var(--text-main);
            font-weight: 800;
            text-align: center;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 0.4rem;
            font-size: 0.85rem;
        }

        .input-group-password { position: relative; display: block; }
        .input-group-password input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #edf2f7;
            border-radius: 12px;
            background: #f8fafc;
            transition: all 0.3s ease;
            outline: none;
            padding-right: 45px;
        }

        .input-group-password input:focus {
            border-color: var(--primary-green);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(25, 135, 84, 0.1);
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #a0aec0;
            z-index: 5;
        }

        .btn-ejidal {
            width: 100%;
            padding: 0.8rem;
            background: var(--primary-green);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .btn-ejidal:hover {
            background: var(--dark-green);
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(25, 135, 84, 0.2);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: #b2bec3;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .divider::before, .divider::after { content: ''; flex: 1; border-bottom: 1px solid #ebf0f5; }
        .divider:not(:empty)::before { margin-right: 1rem; }
        .divider:not(:empty)::after { margin-left: 1rem; }

        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 0.7rem;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #4a5568;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }

        .social-btn:hover { background: #f8fafc; border-color: #cbd5e0; }

        /* FOOTER */
        .footer-ejidal {
            background-color: #1a1d20;
            color: #adb5bd;
            padding: 1.5rem 0;
            font-size: 0.85rem;
            text-align: center;
            border-top: 3px solid var(--primary-green);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-ejidal navbar-dark">
    <div class="container justify-content-center justify-content-md-start">
        <a class="navbar-brand m-0" href="/">
            <img src="{{ asset('SnRafael.png') }}" alt="Logo" height="40" class="me-2">
            <span class="d-none d-sm-inline">Sistema Ejidal San Rafael</span>
        </a>
    </div>
</nav>

<div class="main-content">
    <div class="login-card">

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm text-center mb-3 py-2" style="border-radius: 10px; font-size: 0.9rem;">
                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm text-center mb-3 py-2" style="border-radius: 10px; font-size: 0.85rem;">
                <i class="fas fa-exclamation-triangle me-1"></i> {{ session('error') }}
            </div>
        @endif

        @hasSection('content')
            @yield('content')
        @else
            <h2>Iniciar Sesión</h2>

            @if ($errors->any())
                <div class="alert alert-danger py-2" style="border-radius: 10px; font-size: 0.8rem;">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Usuario o Correo</label>
                    <div class="input-group-password">
                        <input type="text" name="username" value="{{ old('username') }}" placeholder="Nombre de usuario" required autofocus>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label">Contraseña</label>
                    <div class="input-group-password">
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                        <i class="fas fa-eye toggle-password" id="btnToggle"></i>
                    </div>
                </div>

                <div class="text-end mb-4">
                    <a href="{{ route('password.forgot') }}" class="text-decoration-none small" style="color: var(--primary-green); font-weight: 600;">¿Olvidaste tu contraseña?</a>
                </div>

                <button type="submit" class="btn-ejidal">Entrar al Sistema</button>
            </form>

            <div class="divider">O entrar con</div>

            <a href="{{ route('google.redirect') }}" class="social-btn shadow-sm">
                <i class="fab fa-google" style="color: #DB4437; font-size: 1.1rem;"></i>
                <span>Google Account</span>
            </a>
        @endif
    </div>
</div>

<footer class="footer-ejidal">
    <div class="container">
        <p class="mb-1 fw-bold text-white">Ejido San Rafael Ixtapalucan</p>
        <p class="mb-0">© {{ date('Y') }} - Sistema de Gestión Ejidal v1.4.1</p>
    </div>
</footer>

<script>
    const btnToggle = document.querySelector('#btnToggle');
    const passwordInput = document.querySelector('#password');

    btnToggle.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>