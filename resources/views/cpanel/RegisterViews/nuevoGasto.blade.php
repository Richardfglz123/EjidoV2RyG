@extends('cpanel.plantilla')
@section('title', 'Nueva Entrada - Gasto')
@section('content')

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card perfil-card">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-wallet me-2"></i> Registro de Gasto</span>
            <a href="{{ route('gastos.index') }}" class="btn btn-light btn-sm text-dark">
                <i class="fas fa-list me-1"></i> Ver Listado
            </a>
        </div>

        <div class="card-body">
            {{-- Información de ayuda --}}
            <div class="mb-4 p-3 bg-light rounded">
                <p class="mb-0 text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Ingrese los detalles del egreso. Asegúrese de que el monto y el concepto sean correctos.
                </p>
            </div>

            <form method="POST" action="{{ route('gastos.store') }}">
                @csrf

                <div class="row">
                    {{-- Responsable --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Responsable</label>
                        <input type="text" name="responsable"
                               class="form-control @error('responsable') is-invalid @enderror"
                               value="{{ old('responsable') }}" required>
                        @error('responsable')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Fecha --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Fecha</label>
                        <input type="date" name="fecha"
                               class="form-control @error('fecha') is-invalid @enderror"
                               value="{{ old('fecha', date('Y-m-d')) }}" required>
                        @error('fecha')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    {{-- Monto --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Monto ($)</label>
                        <input type="number" step="0.01" name="monto"
                               class="form-control @error('monto') is-invalid @enderror"
                               value="{{ old('monto') }}" required>
                        @error('monto')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Medida --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Medida</label>
                        <input type="text" name="medida"
                               class="form-control @error('medida') is-invalid @enderror"
                               placeholder="Ej. Pago, Compra, Unidad"
                               value="{{ old('medida') }}" required>
                        @error('medida')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Concepto --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Concepto</label>
                        <input type="text" name="concepto"
                               class="form-control @error('concepto') is-invalid @enderror"
                               value="{{ old('concepto') }}" required>
                        @error('concepto')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="text-end mt-4 border-top pt-3">
                    <a href="{{ route('gastos.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-ejidal px-4">
                        <i class="fas fa-save me-1"></i> Guardar Gasto
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
