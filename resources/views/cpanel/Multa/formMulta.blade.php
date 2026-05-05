@extends('cpanel/plantilla')
@section('title','Registro de multas')
@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-money-bill-wave me-2"></i> Registro de Multas Anuales
        </h1>
    </div>

    <div class="card card-ejidal shadow-sm">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-plus-circle me-2"></i> Configurar Nuevos Costos
        </div>

        <form action="{{ route('multas.store') }}" method="POST">
            @csrf

            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <h5 class="text-success fw-bold mb-3">
                    <i class="fas fa-users me-2"></i> Multa por Asamblea
                </h5>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Año</label>
                        <select name="anio_asamblea" class="form-select @error('anio_asamblea') is-invalid @enderror" required>
                            <option value="">Seleccionar año...</option>
                            @for($i = 2026; $i <= 2031; $i++)
                                <option value="{{ $i }}" {{ old('anio_asamblea') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Costo ($)</label>
                        <input type="number" name="costo_asamblea"
                               class="form-control @error('costo_asamblea') is-invalid @enderror"
                               value="{{ old('costo_asamblea') }}"
                               placeholder="0.00"
                               onkeydown="return event.keyCode !== 69 && event.keyCode !== 187 && event.keyCode !== 189"
                               required>
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="text-success fw-bold mb-3">
                    <i class="fas fa-tools me-2"></i> Multa por Faena (Falta)
                </h5>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Año</label>
                        <select name="anio_falta" class="form-select @error('anio_falta') is-invalid @enderror" required>
                            <option value="">Seleccionar año...</option>
                            @for($i = 2026; $i <= 2031; $i++)
                                <option value="{{ $i }}" {{ old('anio_falta') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Costo ($)</label>
                        <input type="number" name="costo_falta"
                               class="form-control @error('costo_falta') is-invalid @enderror"
                               value="{{ old('costo_falta') }}"
                               placeholder="0.00"
                               onkeydown="return event.keyCode !== 69 && event.keyCode !== 187 && event.keyCode !== 189"
                               required>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <a href="{{ route('multas.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-ejidal px-4">
                        <i class="fas fa-save me-1"></i> Guardar Configuración
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection