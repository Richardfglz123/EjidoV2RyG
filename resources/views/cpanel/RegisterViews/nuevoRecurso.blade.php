@extends('cpanel.plantilla')
@section('title', 'Nuevo Recurso')
@section('content')

    {{-- Alertas de éxito o error --}}
    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card perfil-card">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-plus-circle me-2"></i> Registrar Recurso
        </div>

        <div class="card-body">
            {{-- Encabezado informativo --}}
            <div class="mb-4 p-3 bg-light rounded">
                <p class="mb-0 text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Complete los campos para dar de alta un nuevo recurso en el sistema del Ejido.
                </p>
            </div>

            <form method="POST" action="{{ route('recursos.store') }}">
                @csrf

                <div class="row">
                    {{-- Tipo de Recurso --}}
                    <div class="col-md-6 mb-3">
                        <label for="tipo" class="form-label">Tipo de Recurso</label>
                        <input type="text" name="tipo" id="tipo"
                               class="form-control @error('tipo') is-invalid @enderror"
                               value="{{ old('tipo') }}" required>
                        @error('tipo')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Cantidad --}}
                    <div class="col-md-6 mb-3">
                        <label for="cantidad" class="form-label">Cantidad</label>
                        <input type="number" name="cantidad" id="cantidad"
                               class="form-control @error('cantidad') is-invalid @enderror"
                               value="{{ old('cantidad') }}" required>
                        @error('cantidad')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    {{-- Fecha de Recibo --}}
                    <div class="col-md-6 mb-3">
                        <label for="fecha_recibo" class="form-label">Fecha de Recibo</label>
                        <input type="date" name="fecha_recibo" id="fecha_recibo"
                               class="form-control @error('fecha_recibo') is-invalid @enderror"
                               value="{{ old('fecha_recibo') }}" required>
                        @error('fecha_recibo')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Número de Beneficiarios --}}
                    <div class="col-md-6 mb-3">
                        <label for="num_beneficiarios" class="form-label">Número de Beneficiarios</label>
                        <input type="number" name="num_beneficiarios" id="num_beneficiarios"
                               class="form-control @error('num_beneficiarios') is-invalid @enderror"
                               value="{{ old('num_beneficiarios') }}" required>
                        @error('num_beneficiarios')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Nombre del Representante --}}
                <div class="col-md-12 mb-3">
                    <label for="nombre_representante" class="form-label">Nombre del Representante</label>
                    <input type="text" name="nombre_representante" id="nombre_representante"
                           class="form-control @error('nombre_representante') is-invalid @enderror"
                           value="{{ old('nombre_representante') }}" required>
                    @error('nombre_representante')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="text-end mt-4 border-top pt-3">
                    <a href="{{ route('recursos.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-ejidal">
                        <i class="fas fa-save me-1"></i> Guardar Recurso
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
