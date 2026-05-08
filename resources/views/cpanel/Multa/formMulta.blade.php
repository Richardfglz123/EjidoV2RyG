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

            <div class="card-body px-4">
                @if($errors->any())
                    <div class="alert alert-danger shadow-sm">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li><i class="fas fa-exclamation-circle me-2"></i> {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- SECCIÓN: MULTA POR ASAMBLEA --}}
                <h5 class="text-success fw-bold mb-3">
                    <i class="fas fa-users me-2"></i> Multa por Asamblea
                </h5>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Año de Aplicación</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-success"><i class="fas fa-calendar-alt"></i></span>
                            <input type="number"
                                   name="anio_asamblea"
                                   class="form-control @error('anio_asamblea') is-invalid @enderror"
                                   value="{{ old('anio_asamblea', date('Y')) }}"
                                   min="2000"
                                   max="2099"
                                   placeholder="Ej. 2026"
                                   required>
                        </div>
                        <div class="form-text">Ingresa un año</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Costo ($)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-success"><i class="fas fa-dollar-sign"></i></span>
                            <input type="number"
                                   name="costo_asamblea"
                                   class="form-control @error('costo_asamblea') is-invalid @enderror"
                                   value="{{ old('costo_asamblea') }}"
                                   placeholder="0.00"
                                   step="0.01"
                                   onkeydown="return event.keyCode !== 69 && event.keyCode !== 187 && event.keyCode !== 189"
                                   required>
                        </div>
                        <div class="form-text">Monto de la multa por inasistencia</div>
                    </div>
                </div>

                <hr class="my-4">

                {{-- SECCIÓN: MULTA POR FAENA --}}
                <h5 class="text-success fw-bold mb-3">
                    <i class="fas fa-tools me-2"></i> Multa por Faena
                </h5>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Año de Aplicación</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-success"><i class="fas fa-calendar-alt"></i></span>
                            <input type="number"
                                   name="anio_falta"
                                   class="form-control @error('anio_falta') is-invalid @enderror"
                                   value="{{ old('anio_falta', date('Y')) }}"
                                   min="2000"
                                   max="2099"
                                   placeholder="Ej. 2026"
                                   required>
                        </div>
                        <div class="form-text">Ingesa un año</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Costo ($)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-success"><i class="fas fa-dollar-sign"></i></span>
                            <input type="number"
                                   name="costo_falta"
                                   class="form-control @error('costo_falta') is-invalid @enderror"
                                   value="{{ old('costo_falta') }}"
                                   placeholder="0.00"
                                   step="0.01"
                                   onkeydown="return event.keyCode !== 69 && event.keyCode !== 187 && event.keyCode !== 189"
                                   required>
                        </div>
                        <div class="form-text">Monto por no asistir a la faena</div>
                    </div>
                </div>

                <div class="text-end mt-4 pt-3 border-top">
                    <a href="{{ route('multas.index') }}" class="btn btn-secondary px-4 me-2">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-ejidal px-5 shadow-sm">
                        <i class="fas fa-save me-1"></i> Guardar
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection