@extends('cpanel.plantilla')
@section('title', 'Editar Gasto')
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
            <span><i class="fas fa-edit me-2"></i> Modificar Registro de Gasto</span>
            <a href="{{ route('gastos.index') }}" class="btn btn-light btn-sm text-dark fw-bold">
                <i class="fas fa-list me-1"></i> Ver Listado
            </a>
        </div>

        <div class="card-body">
            {{-- Banner de Identificación --}}
            <div class="mb-4 p-3 bg-light rounded border-start border-4 border-warning">
                <p class="mb-0 text-muted">
                    <i class="fas fa-fingerprint me-1 text-warning"></i>
                    Editando Gasto <strong>ID: {{ $gasto->idGasto }}</strong>. Asegúrese de que los montos coincidan con sus comprobantes físicos.
                </p>
            </div>

            <form action="{{ route('gastos.update', ['id' => $gasto->Id_Gasto]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    {{-- Responsable --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Responsable del Gasto</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
                            <input type="text" name="responsable"
                                   class="form-control @error('responsable') is-invalid @enderror"
                                   value="{{ old('responsable', $gasto->Responsable) }}" required>
                        </div>
                        @error('responsable')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Fecha --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Fecha del Gasto</label>
                        <input type="date" name="fecha"
                               class="form-control @error('fecha') is-invalid @enderror"
                               value="{{ old('fecha', $gasto->Fecha) }}" required>
                        @error('fecha')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    {{-- Monto --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Monto ($)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" name="monto"
                                   class="form-control @error('monto') is-invalid @enderror"
                                   value="{{ old('monto', $gasto-> Monto) }}" required>
                        </div>
                        @error('monto')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Medida --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Medida / Unidad</label>
                        <input type="text" name="medida"
                               class="form-control @error('medida') is-invalid @enderror"
                               placeholder="Ej. Pieza, Litro, Servicio"
                               value="{{ old('medida', $gasto->Medida) }}" required>
                        @error('medida')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Concepto --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Concepto de Gasto</label>
                        <input type="text" name="concepto"
                               class="form-control @error('concepto') is-invalid @enderror"
                               value="{{ old('concepto', $gasto->Concepto) }}" required>
                        @error('concepto')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="text-end mt-4 border-top pt-3">
                    <a href="{{ route('gastos.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-ejidal px-5">
                        <i class="fas fa-save me-1"></i> Actualizar Gasto

                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
