@extends('cpanel.plantilla')
@section('title', 'Nueva Entrada de Inventario')
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
            <span><i class="fas fa-file-import me-2"></i> Registrar Entrada de Artículo</span>
            <a href="{{ route('entradas.index') }}" class="btn btn-light btn-sm text-dark">
                <i class="fas fa-list me-1"></i> Ver Listado
            </a>
        </div>

        <div class="card-body">
            {{-- Banner informativo --}}
            <div class="mb-4 p-3 bg-light rounded border-start border-4 border-ejidal">
                <p class="mb-0 text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Utilice este formulario para incrementar el stock de un artículo existente en el inventario del ejido.
                </p>
            </div>

            <form method="POST" action="{{ route('entradas.store') }}">
                @csrf

                <div class="row">
                    {{-- Selección de Artículo --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Artículo</label>
                        <select name="id_equipo" class="form-select @error('id_equipo') is-invalid @enderror" required>
                            <option value="">Seleccione el artículo...</option>
                            @foreach($articulos as $a)
                                <option value="{{ $a->id_equipo }}" {{ old('id_equipo') == $a->id_equipo ? 'selected' : '' }}>
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
                        <label class="form-label fw-bold">Cantidad a Ingresar</label>
                        <input type="number" name="cantidad" class="form-control @error('cantidad') is-invalid @enderror"
                               value="{{ old('cantidad') }}" required min="1">
                        @error('cantidad')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    {{-- Fecha de Entrada --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Fecha de Entrada</label>
                        <input type="date" name="fecha_entrada" class="form-control @error('fecha_entrada') is-invalid @enderror"
                               value="{{ old('fecha_entrada', date('Y-m-d')) }}" required>
                        @error('fecha_entrada')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Observaciones --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="1"
                                  placeholder="Ej. Compra nueva, devolución, donación...">{{ old('observaciones') }}</textarea>
                    </div>
                </div>

                <div class="text-end mt-4 border-top pt-3">
                    <a href="{{ route('entradas.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-ejidal px-4">
                        <i class="fas fa-save me-1"></i> Guardar Entrada
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
