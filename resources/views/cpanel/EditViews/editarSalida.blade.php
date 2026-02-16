@extends('cpanel.plantilla')
@section('title', 'Editar Salida')
@section('content')

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card perfil-card shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-edit me-2"></i> Actualizar Registro de Salida</span>
            <a href="{{ route('salidas.index') }}" class="btn btn-light btn-sm text-dark fw-bold">
                <i class="fas fa-arrow-left me-1"></i> Volver al Listado
            </a>
        </div>

        <div class="card-body">
            {{-- Aviso de Edición --}}
            <div class="mb-4 p-3 bg-light rounded border-start border-4 border-warning">
                <p class="mb-0 text-muted">
                    <i class="fas fa-exclamation-triangle me-1 text-warning"></i>
                    Está editando un registro existente. Los cambios afectarán directamente el historial de inventario.
                </p>
            </div>

            <form method="POST" action="{{ route('salidas.update', $salida->id_salida) }}">
                @csrf
                @method('PUT')

                <div class="row">
                    {{-- Artículo --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Artículo</label>
                        <select name="id_equipo" class="form-select @error('id_equipo') is-invalid @enderror" required>
                            @foreach($articulos as $a)
                                <option value="{{ $a->id_equipo }}"
                                    {{ $a->id_equipo == $salida->id_equipo ? 'selected' : '' }}>
                                    {{ $a->descripcion }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_equipo')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Cantidad --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Cantidad</label>
                        <input type="number" name="cantidad"
                               class="form-control @error('cantidad') is-invalid @enderror"
                               value="{{ old('cantidad', $salida->cantidad) }}" required min="1">
                        @error('cantidad')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    {{-- Fecha de Salida --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Fecha de Salida</label>
                        <input type="date" name="fecha_salida"
                               class="form-control @error('fecha_salida') is-invalid @enderror"
                               value="{{ old('fecha_salida', $salida->fecha_salida) }}" required>
                        @error('fecha_salida')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tipo de Salida --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tipo de Salida</label>
                        <input type="text" name="tipo_salida"
                               class="form-control @error('tipo_salida') is-invalid @enderror"
                               value="{{ old('tipo_salida', $salida->tipo_salida) }}" required>
                        @error('tipo_salida')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    {{-- Responsable --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Responsable</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user-edit"></i></span>
                            <input type="text" name="responsable"
                                   class="form-control @error('responsable') is-invalid @enderror"
                                   value="{{ old('responsable', $salida->responsable) }}" required>
                        </div>
                        @error('responsable')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Observaciones --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="1">{{ old('observaciones', $salida->observaciones) }}</textarea>
                    </div>
                </div>

                <div class="text-end mt-4 border-top pt-3">
                    <a href="{{ route('salidas.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-times me-1"></i> Descartar
                    </a>
                    <button type="submit" class="btn btn-ejidal px-4">
                        <i class="fas fa-sync-alt me-1"></i> Actualizar Salida
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
