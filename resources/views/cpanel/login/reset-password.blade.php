@extends('cpanel/login/sesion')
@section('title', 'Restablecer contraseña - Sistema Ejidal')

@section('content')
    <h2>Restablecer Contraseña</h2>
    <p class="text-center text-muted mb-4" style="font-size: 0.9rem;">
        Crea una nueva contraseña segura para recuperar el acceso a tu cuenta.
    </p>

    @if ($errors->any())
        <ul class="error-list">
            @foreach ($errors->all() as $error)
                <li><i class="fas fa-times-circle me-2"></i>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('password.reset') }}">
        @csrf

        {{-- Código de Verificación --}}
        <div class="mb-3">
            <label class="form-label">Código de Verificación</label>
            <div class="input-group-password">
                <input
                        type="text"
                        name="code"
                        placeholder="123456"
                        maxlength="6"
                        class="form-control"
                        style="letter-spacing: 4px; font-weight: 700; text-align: center; border-radius: 8px;"
                        required
                        autofocus
                >
            </div>
        </div>

        {{-- Nueva Contraseña --}}
        <div class="mb-3">
            <label class="form-label">Nueva Contraseña</label>
            <div class="input-group-password">
                <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="••••••••"
                        class="form-control"
                        style="padding: 0.75rem 1rem; border-radius: 8px;"
                        required
                >
                <i class="fas fa-eye toggle-password" id="btnToggleNew" style="right: 15px; cursor: pointer; color: #6c757d; position: absolute; top: 50%; transform: translateY(-50%);"></i>
            </div>
        </div>

        {{-- Confirmar Contraseña --}}
        <div class="mb-4">
            <label class="form-label">Confirmar Contraseña</label>
            <div class="input-group-password">
                <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder="••••••••"
                        class="form-control"
                        style="padding: 0.75rem 1rem; border-radius: 8px;"
                        required
                >
            </div>
        </div>

        <button type="submit" class="btn-ejidal">
            <i class="fas fa-key me-2"></i> Actualizar Contraseña
        </button>
    </form>

    <div class="text-center mt-4">
        <a href="{{ route('login') }}" class="text-decoration-none small text-muted">
            <i class="fas fa-times me-1"></i> Cancelar y volver
        </a>
    </div>

    <script>
        const btnToggle = document.querySelector('#btnToggleNew');
        const passwordInput = document.querySelector('#password');
        const confirmInput = document.querySelector('#password_confirmation');

        btnToggle.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            confirmInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
@endsection