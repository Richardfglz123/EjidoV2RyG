@extends('cpanel/plantilla')
@section('title','Editar multas')
@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-edit me-2"></i> Editar Configuración Anual
        </h1>
    </div>

    <div class="card card-ejidal shadow-sm">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-calendar-alt me-2"></i> Gestión del Año: {{ $multaReferencia->Año }}
        </div>

        <form action="{{ route('multas.update', $multaReferencia->Id_MultaC) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card-body">
                <div class="alert alert-light border mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="mb-0 fw-bold text-dark">Año de Gestión</h6>
                            <small class="text-muted">El año no puede ser modificado desde aquí</small>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="nuevo_anio" class="form-control bg-white text-center fw-bold fs-5"
                                   value="{{ $multaReferencia->Año }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-light">
                            <h5 class="text-success fw-bold mb-3">
                                <i class="fas fa-users me-2"></i> Multa Asamblea
                            </h5>
                            <label class="form-label">Costo Actualizado ($)</label>
                            <input type="number" name="costo_asamblea"
                                   class="form-control @error('costo_asamblea') is-invalid @enderror"
                                   value="{{ old('costo_asamblea', $asamblea->Costo ?? '') }}"
                                   onkeydown="return event.keyCode !== 69 && event.keyCode !== 187 && event.keyCode !== 189"
                                   required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-light">
                            <h5 class="text-success fw-bold mb-3">
                                <i class="fas fa-tools me-2"></i> Multa Faena
                            </h5>
                            <label class="form-label">Costo Actualizado ($)</label>
                            <input type="number" name="costo_falta"
                                   class="form-control @error('costo_falta') is-invalid @enderror"
                                   value="{{ old('costo_falta', $faena->Costo ?? '') }}"
                                   onkeydown="return event.keyCode !== 69 && event.keyCode !== 187 && event.keyCode !== 189"
                                   required>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <a href="{{ route('multas.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Regresar
                    </a>
                    <button type="submit" class="btn btn-ejidal px-4">
                        <i class="fas fa-sync-alt me-1"></i> Actualizar Costos
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection