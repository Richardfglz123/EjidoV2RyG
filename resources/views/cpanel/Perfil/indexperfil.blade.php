@extends('cpanel.plantilla')
@section('title','Mi Perfil')
@section('content')

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card perfil-card">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-user"></i> Mi perfil
        </div>

        <div class="card-body">
            {{-- Información NO editable --}}
            <div class="mb-4 p-3 bg-light rounded">
                <p class="mb-1">
                    <strong>Nombre Completo:</strong>
                    {{ $usuario->Nombres }}
                    {{ $usuario->Apellido_Paterno }}
                    {{ $usuario->Apellido_Materno }}
                </p>
            </div>

            <form method="POST" action="{{ route('perfil.update') }}">
                @csrf
                @method('PUT')

                {{-- Campos editables --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Usuario</label>
                        <input type="text" name="Usuario" class="form-control"
                               value="{{ $usuario->Usuario }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Correo</label>
                        <input type="email" name="Correo" class="form-control"
                               value="{{ $usuario->Correo }}" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="Telefono" class="form-control"
                               value="{{ $usuario->Telefono }}" required>
                    </div>
                </div>

                {{-- Contraseñas --}}
                <div class="row border-top pt-3 mt-3">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nueva contraseña</label>
                        <input type="password" name="Contraseña" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Confirmar contraseña</label>
                        <input type="password" name="Contraseña_confirmation" class="form-control">
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-ejidal">
                        <i class="fas fa-save me-1"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
