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
        }

        .bg-dinamico {
            background-size: cover !important;
            background-position: center !important;
            background-attachment: fixed !important;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* HEADER CON IMAGN */
        .navbar-ejidal {
            background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)),
            url('{{ asset("assets/volcan.jpeg") }}') no-repeat center center !important;
            background-size: cover !important;
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
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            border: 1px solid rgba(0,0,0,0.05);
        }

        h2 { color: var(--text-main); font-weight: 800; text-align: center; margin-bottom: 1.5rem; font-size: 1.5rem; }
        .form-label { font-weight: 600; color: #555; margin-bottom: 0.4rem; font-size: 0.85rem; }

        .input-group-password { position: relative; display: block; }
        .input-group-password input {
            width: 100%; padding: 0.75rem 1rem; border: 2px solid #edf2f7;
            border-radius: 12px; background: #f8fafc; transition: all 0.3s ease; outline: none; padding-right: 45px;
        }
        .input-group-password input:focus { border-color: var(--primary-green); background: #fff; box-shadow: 0 0 0 4px rgba(25, 135, 84, 0.1); }
        .toggle-password { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #a0aec0; z-index: 5; }

        .btn-ejidal {
            width: 100%; padding: 0.8rem; background: var(--primary-green); color: white;
            border: none; border-radius: 12px; font-weight: 700; transition: all 0.3s ease; margin-top: 1rem;
        }
        .btn-ejidal:hover { background: var(--dark-green); transform: translateY(-2px); }

        .divider { display: flex; align-items: center; margin: 1.5rem 0; color: #b2bec3; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
        .divider::before, .divider::after { content: ''; flex: 1; border-bottom: 1px solid #ebf0f5; }
        .divider:not(:empty)::before { margin-right: 1rem; }
        .divider:not(:empty)::after { margin-left: 1rem; }

        .social-btn {
            display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%;
            padding: 0.7rem; border-radius: 12px; border: 1px solid #e2e8f0; background: #fff;
            color: #4a5568; text-decoration: none; font-weight: 600;
        }
        .social-btn:hover { background: #f8fafc; }
    </style>
</head>
<?php
$imagenes = ['venado.jpeg', 'lago.jpeg', 'luciernagas.jpeg', 'luciernagas2.jpeg'];
$fondoSeleccionado = $imagenes[rand(0, 3)];
?>
<body class="bg-dinamico" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('{{ asset('assets/' . $fondoSeleccionado) }}');">

<nav class="navbar navbar-ejidal navbar-dark">
    <div class="container-fluid">
        @if(session()->has('usuario') || session()->has('2fa_user'))
            <a class="navbar-brand d-flex align-items-center gap-2 m-0 p-0" href="{{ route('') }}">
                <img src="{{ asset('SnRafael.png') }}" alt="Logo" height="35">
                <span class="fw-bold d-none d-sm-inline" style="font-size: 1rem;">Sistema Ejidal San Rafael Ixtapalucan</span>
            </a>
        @else
            <span class="navbar-brand d-flex align-items-center gap-2 m-0 p-0">
                <img src="{{ asset('SnRafael.png') }}" alt="Logo" height="35">
                <span class="fw-bold d-none d-sm-inline" style="font-size: 1rem;">Sistema Ejidal San Rafael Ixtapalucan</span>
            </span>
        @endif
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
                <span>Google</span>
            </a>
        @endif
    </div>
</div>
<footer class="footer bg-dark text-light py-4 border-top border-primary">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-4 text-center text-md-start">
                <img src="{{ asset('SnRafael.png') }}" alt="Logo" height="50" class="mb-2">
                <h6 class="text-uppercase fw-bold mb-0">Sistema de Gestión Ejidal San Rafael Ixtapalucan</h6>
                <small class="text-secondary">v1.4.1</small>
            </div>
            <div class="col-md-4 text-center my-3 my-md-0">
                <p class="mb-2 small text-secondary">Síguenos en:</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="https://www.facebook.com/vallede.luciernagas/" target="_blank"
                       class="btn btn-outline-light rounded-circle d-flex align-items-center justify-content-center"
                       style="width: 45px; height: 45px; font-size: 1.2rem;" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://www.instagram.com/valle_de_luciernagas_esri/" target="_blank"
                       class="btn btn-outline-light rounded-circle d-flex align-items-center justify-content-center"
                       style="width: 45px; height: 45px; font-size: 1.2rem;" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
            </div>
            <div class="col-md-4 text-center text-md-end">
                <div style="font-size: 0.7rem;" class="text-secondary">
                    <p class="mb-1">&copy; 2026 Todos los Derechos Reservados D.R.A.</p>
                    <p class="mb-0">Prohibida su reproducción total o parcial sin autorización escrita.</p>
                    <p class="mb-0 font-italic text-lowercase">All rights reserved 2026.</p>
                </div>
            </div>
        </div>
    </div>
</footer>

<script>
    const btnToggle = document.querySelector('#btnToggle');
    const passwordInput = document.querySelector('#password');
    if(btnToggle) {
        btnToggle.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>