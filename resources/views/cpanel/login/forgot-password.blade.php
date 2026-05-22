@extends('cpanel/login/sesion')
@section('title', 'Recuperar contraseña - Sistema Ejidal')

@section('content')
    <h2>Recuperar Contraseña</h2>
    <p class="text-center text-muted mb-4" style="font-size: 0.95rem;">
        Ingresa tu <strong>correo o usuario</strong> y te enviaremos un código de verificación para restablecer tu acceso.
    </p>

    @if ($errors->any())
        <ul class="error-list">
            @foreach ($errors->all() as $error)
                <li><i class="fas fa-times-circle me-2"></i>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('password.send') }}">
        @csrf

        <div class="mb-4">
            <label for="username" class="form-label">Correo o Usuario</label>
            <div class="input-group-password">
                <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-control"
                        placeholder="ejemplo@correo.com"
                        value="{{ old('username') }}"
                        required
                        autofocus
                        style="padding: 0.75rem 1rem; border-radius: 8px;"
                >
            </div>
        </div>

        <button type="submit" class="btn-ejidal">
            <i class="fas fa-paper-plane me-2"></i> Enviar código
        </button>
    </form>

    <div class="text-center mt-4">
        <a href="{{ route('login') }}" class="text-decoration-none small fw-bold" style="color: #198754;">
            <i class="fas fa-arrow-left me-1"></i> Volver al inicio de sesión
        </a>
    </div>
@endsection