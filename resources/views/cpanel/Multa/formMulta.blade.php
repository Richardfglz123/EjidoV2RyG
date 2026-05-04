@extends('cpanel/plantilla')
@section('title','Registro de multas')
@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-money-bill-wave me-2"></i> Multas
        </h1>
    </div>

    <div class="card card-ejidal">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-plus-circle me-2"></i> Registrar Multas
        </div>

        <form action="{{ route('multas.store') }}" method="POST">
            @csrf

            <div class="card-body">

                {{-- MULTA POR ASAMBLEA --}}
                <h5 class="text-success fw-bold mb-3">
                    <i class="fas fa-users me-2"></i> Multa por Asamblea
                </h5>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Año</label>
                        <select name="anio_asamblea"
                                class="form-select @error('anio_asamblea') is-invalid @enderror" required>

                            <option value="">Seleccionar...</option>
                            @for($i = 2026; $i <= 2031; $i++)
                                <option value="{{ $i }}" {{ old('anio_asamblea') == $i ? 'selected' : '' }}>
                                    {{ $i }}
                                </option>
                            @endfor
                        </select>

                        @error('anio_asamblea')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label>Costo por Asamblea</label>
                        <input type="number" name="costo_asamblea"
                               class="form-control @error('costo_asamblea') is-invalid @enderror"
                               value="{{ old('costo_asamblea') }}"
                               placeholder="Ej. 100" required>

                        @error('costo_asamblea')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr class="my-4">

                {{-- MULTA POR FAENA --}}
                <h5 class="text-success fw-bold mb-3">
                    <i class="fas fa-tools me-2"></i> Multa por Faena
                </h5>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Año</label>
                        <select name="anio_falta"
                                class="form-select @error('anio_falta') is-invalid @enderror" required>

                            <option value="">Seleccionar...</option>
                            @for($i = 2026; $i <= 2031; $i++)
                                <option value="{{ $i }}" {{ old('anio_falta') == $i ? 'selected' : '' }}>
                                    {{ $i }}
                                </option>
                            @endfor
                        </select>

                        @error('anio_falta')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label>Costo por Faena</label>
                        <input type="number" name="costo_falta"
                               class="form-control @error('costo_falta') is-invalid @enderror"
                               value="{{ old('costo_falta') }}"
                               placeholder="Ej. 150" required>

                        @error('costo_falta')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- BOTONES --}}
                <div class="text-end">
                    <a href="{{ route('multas.index') }}" class="btn btn-secondary">
                        Cancelar
                    </a>

                    <button type="submit" class="btn btn-ejidal">
                        <i class="fas fa-save me-1"></i> Guardar Multas
                    </button>
                </div>

            </div>
        </form>
    </div>

@endsection