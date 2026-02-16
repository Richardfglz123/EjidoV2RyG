@extends('cpanel.plantilla')
@section('title', 'Nueva Salida')
@section('content')

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card perfil-card shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-sign-out-alt me-2"></i> Registrar Salida de Inventario</span>
            <a href="{{ route('salidas.index') }}" class="btn btn-light btn-sm text-dark">
                <i class="fas fa-list me-1"></i> Ver Listado
            </a>
        </div>

        <div class="card-body">
            {{-- Banner informativo --}}
            <div class="mb-4 p-3 bg-light rounded border-start border-4 border-ejidal">
                <p class="mb-0 text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Utilice este formulario para registrar el retiro de artículos o herramientas. Los campos marcados con asterisco son obligatorios.
                </p>
            </div>

            <form method="POST" action="{{ route('salidas.store') }}">
                @csrf

                <div class="row">
                    {{-- Artículo --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Artículo</label>
                        <select name="id_equipo" class="form-select @error('id_equipo') is-invalid @enderror" required>
                            <option value="">Seleccione un artículo...</option>
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
                        <label class="form-label fw-bold">Cantidad</label>
                        <input type="number" name="cantidad" class="form-control @error('cantidad') is-invalid @enderror"
                               value="{{ old('cantidad') }}" required min="1">
                        @error('cantidad')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    {{-- Fecha de Salida --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Fecha de Salida</label>
                        <input type="date" name="fecha_salida" class="form-control @error('fecha_salida') is-invalid @enderror"
                               value="{{ old('fecha_salida', date('Y-m-d')) }}" required>
                        @error('fecha_salida')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tipo de Salida --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tipo de Salida</label>
                        <input type="text" name="tipo_salida" class="form-control @error('tipo_salida') is-invalid @enderror"
                               placeholder="Ej. Préstamo, Donación, Desecho"
                               value="{{ old('tipo_salida') }}" required>
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
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" name="responsable" class="form-control @error('responsable') is-invalid @enderror"
                                   value="{{ old('responsable') }}" required>
                        </div>
                        @error('responsable')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Observaciones --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="1"
                                  placeholder="Detalles adicionales...">{{ old('observaciones') }}</textarea>
                    </div>
                </div>

                <div class="text-end mt-4 border-top pt-3">
                    <a href="{{ route('salidas.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-ejidal px-4">
                        <i class="fas fa-save me-1"></i> Guardar Salida
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
