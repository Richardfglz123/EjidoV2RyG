@extends('cpanel.login.sesion')
@section('title', 'Verificación 2FA')

@section('content')
    <div class="login-card">
        <div class="mb-4">
            <i class="fas fa-user-shield fa-4x" style="color: var(--primary-green);"></i>
        </div>
        <h2>Verificación de Acceso</h2>
        <p class="text-muted mb-4">Ingresa el código de 6 dígitos enviado a tu correo.</p>

        @if ($errors->any())
            <div class="alert alert-danger p-2 mb-3">{{ $errors->first() }}</div>
        @endif

        <form id="two-fa-form" method="POST" action="{{ route('2fa.check') }}">
            @csrf
            <input type="text" name="code" class="form-control form-control-lg text-center mb-4"
                   maxlength="6" placeholder="000000" style="font-size: 2rem; letter-spacing: 10px;" required autofocus>

            <button type="submit" id="btn-submit" class="btn-ejidal">
                Verificar y Entrar
            </button>
        </form>

        <a href="{{ route('login') }}" class="d-block mt-4 text-decoration-none text-secondary small">
            <i class="fas fa-arrow-left"></i> Volver al inicio
        </a>
    </div>

    <script>
        const input = document.querySelector('input[name="code"]');
        input.addEventListener('input', function() {
            if (this.value.length === 6) {
                document.getElementById('btn-submit').innerHTML = 'Verificando...';
                document.getElementById('two-fa-form').submit();
            }
        });
    </script>
@endsection