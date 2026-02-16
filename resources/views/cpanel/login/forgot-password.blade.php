@extends('cpanel/login/sesion')
@section('title', 'Recuperar contraseña')

@section('content')
    <h2>Recuperar contraseña</h2>
    <p class="text-center mb-4">
        Ingresa tu <strong>correo o usuario</strong> y te enviaremos un código de verificación
    </p>

    @if ($errors->any())
        <ul class="messages">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('password.send') }}">
        @csrf
        <div class="form-group">
            <label for="username">Correo o Usuario</label>
            <input type="text" id="username" name="username" value="{{ old('username') }}" required>
        </div>
        <button type="submit">Enviar código</button>
    </form>

    <div class="extra" style="text-align: center; margin-top: 15px;">
        <a href="{{ route('login') }}" style="color: #2c5e1a; font-weight: 600; text-decoration: none;">
            ← Volver al inicio de sesión
        </a>
    </div>
@endsection
