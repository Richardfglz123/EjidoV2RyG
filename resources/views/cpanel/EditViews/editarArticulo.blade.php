@extends('cpanel.plantilla')
@section('title', 'Editar Artículo')
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
            <span><i class="fas fa-box-open me-2"></i> Actualizar Datos del Artículo</span>
            <a href="{{ route('articulos.index') }}" class="btn btn-light btn-sm text-dark fw-bold">
                <i class="fas fa-list me-1"></i> Volver al Listado
            </a>
        </div>

        <div class="card-body">
            {{-- Banner de Edición --}}
            <div class="mb-4 p-3 bg-light rounded border-start border-4 border-warning">
                <p class="mb-0 text-muted small">
                    <i class="fas fa-info-circle me-1 text-warning"></i>
                    Está editando un recurso del inventario general. Asegúrese de que la <strong>cantidad</strong> y el <strong>estado</strong> reflejen la realidad física del equipo.
                </p>
            </div>

            <form method="POST" action="{{ route('articulos.update', $articulo) }}">
                @csrf
                @method('PUT')


                <div class="row">
                    {{-- Descripción --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Descripción del Artículo</label>
                        <input type="text" name="descripcion"
                               class="form-control @error('descripcion') is-invalid @enderror"
                               value="{{ old('descripcion', $articulo->descripcion) }}" required>
                        @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Estado --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Estado Físico</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-tools"></i></span>
                            <input type="text" name="estado"
                                   class="form-control @error('estado') is-invalid @enderror"
                                   placeholder="Ej: Nuevo, Desgastado, Dañado"
                                   value="{{ old('estado', $articulo->estado) }}" required>
                        </div>
                        @error('estado')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    {{-- Cantidad --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Cantidad / Stock</label>
                        <input type="number" name="cantidad"
                               class="form-control @error('cantidad') is-invalid @enderror"
                               value="{{ old('cantidad', $articulo->cantidad) }}" required>
                        @error('cantidad')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Medida --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Unidad de Medida</label>
                        <input type="text" name="medida"
                               class="form-control @error('medida') is-invalid @enderror"
                               placeholder="Ej: Pza, Kg, Lts"
                               value="{{ old('medida', $articulo->medida) }}" required>
                        @error('medida')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Fecha Registro --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Fecha de Registro</label>
                        <input type="date" name="fecha_registro"
                               class="form-control @error('fecha_registro') is-invalid @enderror"
                               value="{{ old('fecha_registro', $articulo->fecha_registro) }}">
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
                        <i class="fas fa-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
