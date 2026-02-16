@extends('cpanel.plantilla')
@section('title', 'Nuevo Artículo')
@section('content')

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card perfil-card shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-boxes me-2"></i> Registrar Nuevo Artículo</span>
            <a href="{{ route('articulos.index') }}" class="btn btn-light btn-sm text-dark">
                <i class="fas fa-list me-1"></i> Ver Listado
            </a>
        </div>

        <div class="card-body">
            {{-- Banner informativo --}}
            <div class="mb-4 p-3 bg-light rounded border-start border-4 border-ejidal">
                <p class="mb-0 text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Registre las herramientas, insumos o bienes del ejido para mantener el control del inventario.
                </p>
            </div>

            <form method="POST" action="{{ route('articulos.store') }}">
                @csrf

                <div class="row">
                    {{-- Descripción --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Descripción del Artículo</label>
                        <input type="text" name="descripcion"
                               class="form-control @error('descripcion') is-invalid @enderror"
                               placeholder="Ej. Pala, Carretilla, Bulto de Fertilizante"
                               value="{{ old('descripcion') }}" required>
                        @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Cantidad --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Cantidad</label>
                        <input type="number" name="cantidad"
                               class="form-control @error('cantidad') is-invalid @enderror"
                               value="{{ old('cantidad') }}" required>
                        @error('cantidad')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    {{-- Estado --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Estado / Condición</label>
                        <input type="text" name="estado"
                               class="form-control @error('estado') is-invalid @enderror"
                               placeholder="Ej. Nuevo, Usado, Excelente"
                               value="{{ old('estado') }}" required>
                        @error('estado')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Medida --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Unidad de Medida</label>
                        <input type="text" name="medida"
                               class="form-control @error('medida') is-invalid @enderror"
                               placeholder="Ej. Pieza, Kg, Litro"
                               value="{{ old('medida') }}" required>
                        @error('medida')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    {{-- Fecha de Registro --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Fecha de Registro</label>
                        <input type="date" name="fecha_registro"
                               class="form-control @error('fecha_registro') is-invalid @enderror"
                               value="{{ old('fecha_registro', date('Y-m-d')) }}" required>
                        @error('fecha_registro')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="text-end mt-4 border-top pt-3">
                    <a href="{{ route('articulos.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-ejidal px-4">
                        <i class="fas fa-save me-1"></i> Guardar Artículo
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
