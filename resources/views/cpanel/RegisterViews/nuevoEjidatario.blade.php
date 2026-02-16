@extends('cpanel.plantilla')
@section('title', 'Nuevo Ejidatario')
@section('content')

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card perfil-card shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-users me-2"></i> Registrar Nuevo Ejidatario</span>
            <div class="btn-group">
                <button type="button" class="btn btn-light btn-sm"><i class="fas fa-file-export me-1"></i> Exportar</button>
                <button type="button" class="btn btn-light btn-sm"><i class="fas fa-print me-1"></i> Imprimir</button>
            </div>
        </div>

        <div class="card-body">
            {{-- Instrucción / Información --}}
            <div class="mb-4 p-3 bg-light rounded border-start border-4 border-ejidal">
                <p class="mb-0 text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Ingrese los datos personales y de registro del nuevo miembro del ejido. Los campos con asterisco son obligatorios.
                </p>
            </div>

            <form method="POST" action="{{ route('ejidatarios.store') }}">
                @csrf

                {{-- Datos Personales --}}
                <h6 class="text-ejidal fw-bold mb-3"><i class="fas fa-user-tag me-1"></i> Datos Personales</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nombre(s)</label>
                        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}" required>
                        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Apellido Paterno</label>
                        <input type="text" name="apellidoPaterno" class="form-control @error('apellidoPaterno') is-invalid @enderror" value="{{ old('apellidoPaterno') }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Apellido Materno</label>
                        <input type="text" name="apellidoMaterno" class="form-control" value="{{ old('apellidoMaterno') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Fecha de Nacimiento</label>
                        <input type="date" name="fechaNacimiento" class="form-control" value="{{ old('fechaNacimiento') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">CURP</label>
                        <input type="text" name="curp" class="form-control" value="{{ old('curp') }}" placeholder="18 caracteres">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">RFC</label>
                        <input type="text" name="rfc" class="form-control" value="{{ old('rfc') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Clave de Elector</label>
                        <input type="text" name="claveElector" class="form-control" value="{{ old('claveElector') }}">
                    </div>
                </div>

                <hr class="my-4">

                {{-- Contacto y Domicilio --}}
                <h6 class="text-ejidal fw-bold mb-3"><i class="fas fa-map-marker-alt me-1"></i> Contacto y Domicilio</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Dirección</label>
                        <textarea name="direccion" class="form-control" rows="2">{{ old('direccion') }}</textarea>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="tel" name="telefono" class="form-control" value="{{ old('telefono') }}" maxlength="10">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Correo Electrónico</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                    </div>
                </div>

                <hr class="my-4">

                {{-- Información Ejidal --}}
                <h6 class="text-ejidal fw-bold mb-3"><i class="fas fa-seedling me-1"></i> Información del Ejido</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Fecha de Ingreso</label>
                        <input type="date" name="fechaIngreso" class="form-control" value="{{ old('fechaIngreso', date('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Número de Ejidatario</label>
                        <input type="text" name="numeroEjidatario" class="form-control" value="{{ old('numeroEjidatario') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Estatus</label>
                        <select class="form-select" name="idEstatus">
                            <option value="1" selected>Activo</option>
                            <option value="2">Baja</option>
                            <option value="3">Suspendido</option>
                        </select>
                    </div>
                </div>

                <div class="text-end mt-4 border-top pt-3">
                    <a href="{{ route('ejidatarios.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-ejidal px-4">
                        <i class="fas fa-save me-1"></i> Guardar Ejidatario
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
