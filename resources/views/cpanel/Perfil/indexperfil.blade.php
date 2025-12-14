@extends('cpanel.plantilla')
@section('title','Mi Perfil')
@section('content')

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Usamos la clase 'perfil-card' para el estilo general del CSS que proporcionaste --}}
    <div class="card perfil-card">
        {{-- Usamos la clase de encabezado del ejemplo para asegurar el color si está definida --}}
        <div class="card-header card-header-ejidal">
            <i class="fas fa-user"></i> Mi perfil
        </div>

        <div class="card-body">
            {{-- Sección de información NO editable (Nombre completo y Rol) --}}
            <div class="mb-4 p-3 bg-light rounded">
                <p class="mb-1">
                    <strong>Nombre Completo:</strong>
                    {{ $usuario->Nombres }}
                    {{ $usuario->Apellido_Paterno }}
                    {{ $usuario->Apellido_Materno }}
                </p>
                {{-- RESTABLECIDO EL ROL --}}
                <p class="mb-0">
                    <strong>Rol:</strong> {{ $usuario->Tipo_Rol }}
                </p>
            </div>

            <form method="POST" action="{{ route('perfil.update') }}">
                @csrf
                @method('PUT')
                {{-- Información de Ejidatario --}}
                @if($usuario->Id_Ejidatario)
                    <div class="mb-4 p-3 bg-success bg-opacity-10 rounded border border-success">
                        <h6 class="mb-2 text-success">
                            <i class="fas fa-leaf me-1"></i> Datos de Ejidatario
                        </h6>

                        <p class="mb-1">
                            <strong>Número de Ejidatario:</strong>
                            {{ $usuario->Num_Ejidatario }}
                        </p>

                        <p class="mb-0">
                            <strong>No. Parcela:</strong>
                            {{ $usuario->No_Parcela }}
                        </p>
                    </div>
                @else
                    <div class="mb-4 p-3 bg-warning bg-opacity-10 rounded border border-warning">
                        <p class="mb-0 text-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Este usuario no tiene información de Ejidatario asignada.
                        </p>
                    </div>
                @endif

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

                {{-- Botón de guardar con la clase 'btn-ejidal' para asegurar el color --}}
                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-ejidal">
                        <i class="fas fa-save me-1"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
