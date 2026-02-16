<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Inicio de sesión - Sistema Ejidal San Rafael Ixtapalucan')</title>
    <link rel="stylesheet" href="{{ asset('assets/login.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-ejidal">
    <div class="container">
        <a class="navbar-brand" href="{{ route('login') }}">Sistema Ejidal San Rafael Ixtapalucan</a>
    </div>
</nav>

<div class="login-container">

    {{-- MENSAJE DE ÉXITO: Se mostrará aquí para cualquier vista (Login, Olvidé, Reset) --}}
    @if (session('success'))
        <div class="alert alert-success text-center mb-4 shadow-sm" role="alert" style="border-left: 5px solid #198754;">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    @hasSection('content')
        @yield('content')
    @else
        <h2>Inicio de Sesión</h2>

        @if ($errors->any())
            <ul class="messages">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="username">Usuario o Correo:</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Acceder</button>
        </form>

        <div class="extra-options">
            <a href="{{ route('password.forgot') }}">¿Olvidaste tu contraseña?</a>
        </div>
    @endif
</div>

<footer class="footer bg-ejidal-light">
    <div class="footer-container container">
        <div class="footer-left"><span>Sistema de Gestión Ejidal &copy; 2026</span></div>
        <div class="footer-right"><span>Versión 1.3.1</span></div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>