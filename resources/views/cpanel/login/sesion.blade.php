<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Acceso - Sistema Ejidal')</title>
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
            margin-bottom: 2rem;
        }

        .form-label {
            font-weight: 600;
            color: #444;
            font-size: 0.9rem;
        }

        .input-group-password {
            position: relative;
        }

        .input-group-password input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ced4da;
            border-radius: 8px;
            padding-right: 45px;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            z-index: 10;
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
            margin-top: 1rem;
        }

        .btn-ejidal:hover {
            background-color: #157347;
            transform: translateY(-1px);
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
            color: #888;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .divider::before, .divider::after { content: ''; flex: 1; border-bottom: 1px solid #eee; }
        .divider:not(:empty)::before { margin-right: .5em; }
        .divider:not(:empty)::after { margin-left: .5em; }

        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            padding: 0.7rem;
            border-radius: 8px;
            border: 1px solid #ddd;
            background: #fff;
            color: #333;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 10px;
            transition: background 0.2s;
        }
        .social-btn:hover { background: #f9f9f9; border-color: #ccc; color: #000; }
        .btn-apple { background: #000; color: #fff; border: none; }
        .btn-apple:hover { background: #333; color: #fff; }


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
    <div class="container-fluid px-lg-5"> <a class="navbar-brand" href="{{ route('login') }}">
            <i class="fas fa-tractor me-3"></i>
            <span>SISTEMA EJIDAL SAN RAFAEL IXTAPALUCAN</span>
        </a>
    </div>
</nav>

<div class="main-content">
    <div class="login-card">

        @if (session('success'))
            <div class="alert alert-success text-center mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            </div>
        @endif

        @hasSection('content')
            @yield('content')
        @else
            <h2>Iniciar Sesión</h2>

            @if ($errors->any())
                <ul class="error-list">
                    @foreach ($errors->all() as $error)
                        <li><i class="fas fa-times-circle me-2"></i>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Usuario o Correo</label>
                    <div class="input-group-password">
                        <input type="text" name="username" value="{{ old('username') }}" placeholder="Ingresa tu usuario" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <div class="input-group-password">
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                        <i class="fas fa-eye toggle-password" id="btnToggle"></i>
                    </div>
                </div>

                <button type="submit" class="btn-ejidal">Acceder</button>
            </form>

            <div class="text-center mt-3">
                <a href="{{ route('password.forgot') }}" class="text-decoration-none small text-muted">¿Olvidaste tu contraseña?</a>
            </div>

            <div class="divider">Continuar con</div>

            <div class="social-auth">
                <a href="#" class="social-btn">
                    <img src="https://www.gstatic.com/images/branding/product/1x/gsa_512dp.png" width="18" alt="Google">
                    Google
                </a>
                <a href="#" class="social-btn btn-apple">
                    <i class="fab fa-apple fa-lg"></i>
                    Apple ID
                </a>
            </div>
        @endif
    </div>
</div>

<footer class="footer-ejidal">
    <div class="container-fluid px-lg-5 d-flex justify-content-between">
        <span>Sistema de Gestión Ejidal &copy; 2026</span>
        <span class="d-none d-sm-inline">Versión 1.4.1</span>
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