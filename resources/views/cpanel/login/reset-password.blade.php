@extends('cpanel/login/sesion')
@section('title', 'Restablecer contraseña')

@section('content')
    <h2>Restablecer contraseña</h2>

    @if ($errors->any())
        <ul class="messages">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('password.reset') }}">
        @csrf
        <div class="form-group">
            <label>Código de verificación</label>
            <input type="text" name="code" maxlength="6" required>
        </div>
        <div class="form-group">
            <label>Nueva contraseña</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-group">
            <label>Confirmar contraseña</label>
            <input type="password" name="password_confirmation" required>
        </div>
        <button type="submit">Actualizar contraseña</button>
    </form>
@endsection