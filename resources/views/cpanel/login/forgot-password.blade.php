@extends('cpanel.login.sesion')

@section('title', 'Validar código - Sistema Ejidal')

@section('content')
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
                <input type="text" id="code" name="code" class="input-2fa shadow-sm" maxlength="6" inputmode="numeric" autocomplete="one-time-code" placeholder="000000" required autofocus>
            </div>
            <button type="submit" id="btn-submit" class="btn-ejidal">
                <i class="fas fa-shield-alt me-2"></i> Verificar y Entrar
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="{{ route('login') }}" class="text-decoration-none small" style="color: #94a3b8; font-weight: 600;">
                <i class="fas fa-arrow-left me-1"></i> Volver al inicio
            </a>
        </div>
    </div>
@endsection