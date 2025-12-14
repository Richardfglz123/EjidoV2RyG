<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio de sesión - Sistena Ejidal San Rafael Ixtapalucan</title>
    <link rel="stylesheet" href="{{ asset('assets/login.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<!-- Barra de navegación -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-ejidal">
    <div class="container">
        <a class="navbar-brand" href="#">
            Sistema Ejidal San Rafael Ixtapalucan
        </a>
    </div>
</nav>

<!-- Formulario de inicio de sesión -->
<div class="login-container">
    <h2>Inicio de Sesión</h2>

    <!-- 🔥 MOSTRAR ERRORES DEL LOGIN -->
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
            <label for="username">Correo o Usuario:</label>
            <input
                type="text"
                id="username"
                name="username"
                value="{{ old('username') }}"
                required>
        </div>

        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input
                type="password"
                id="password"
                name="password"
                required>
        </div>

        <button type="submit">Ingresar</button>
    </form>

    <div class="extra-options">
        <a href="#"
           onclick="alert('Por favor comunícate con el administrador para recuperar el acceso a tu cuenta'); return false;">
            ¿Olvidaste tu contraseña?
        </a>
    </div>
</div>

<footer class="footer bg-ejidal-light">
    <div class="footer-container container">
        <div class="footer-left">
            <span>Sistema de Gestión Ejidal &copy; 2026</span>
        </div>
        <div class="footer-right">
            <span>Versión 1.0.1</span>
        </div>
    </div>
</footer>

</body>
</html>
