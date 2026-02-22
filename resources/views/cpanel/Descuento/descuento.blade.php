@extends('cpanel/plantilla')

@section('title', 'Descuento de faenas y asambleas')

@section('content')

    {{-- Encabezado con estilo Ejidal --}}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-hand-holding-usd me-2"></i> Descuento de faenas y asambleas
        </h1>
    </div>

    {{-- Card de Monto Actual (Estilo similar a los stats de usuarios) --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-start border-danger border-4">
                <div class="card-body py-3">
                    <p class="text-muted mb-0 small fw-bold text-uppercase">Monto Actual del Descuento Seleccionado</p>
                    <h2 class="text-danger mb-0 fw-bold">
                        ${{ number_format($descuentoSeleccionado->monto ?? 0, 2) }}
                        <small class="text-muted fs-6">MXN</small>
                    </h2>
                </div>
            </div>
        </div>
    </div>

    {{-- Card de Selección --}}
    <div class="card card-ejidal mb-4">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-search me-2"></i> 1. Seleccione un Descuento para Editar
        </div>
        <div class="card-body">
            <form action="{{ route('descuento.index') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label class="fw-bold">Tipo de descuento</label>
                        <select name="id_multa_c" class="form-control" onchange="this.form.submit()">
                            <option value="">Seleccionar descuento...</option>
                            @foreach ($descuentos as $descuento)
                                <option
                                        value="{{ $descuento->id_multa_c }}"
                                        @if($descuentoSeleccionado && $descuentoSeleccionado->id_multa_c == $descuento->id_multa_c) selected @endif
                                >
                                    {{ $descuento->tipo }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Elija una opción para cargar la configuración.</small>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Card de Configuración (Solo si hay uno seleccionado) --}}
    @if ($descuentoSeleccionado)
        <div class="card card-ejidal">
            <div class="card-header card-header-ejidal">
                <i class="fas fa-cog me-2"></i> Configuración y Detalles
            </div>

            <form action="{{ route('descuento.update', $descuentoSeleccionado->id_multa_c) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="card-body">
                    <div class="row mb-3">
                        {{-- Configuración Principal --}}
                        <div class="col-md-4">
                            <label>Tarifa por ausencias (Monto)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="monto" class="form-control" step="0.01"
                                       value="{{ $descuentoSeleccionado->monto }}" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label>Año</label>
                            <input type="number" name="anio" class="form-control bg-light"
                                   value="{{ $descuentoSeleccionado->anio }}" readonly>
                        </div>

                        <div class="col-md-4">
                            <label>Tipo (No editable)</label>
                            <input type="text" class="form-control bg-light"
                                   value="{{ $descuentoSeleccionado->tipo }}" readonly disabled>
                        </div>
                    </div>

                    <div class="row mb-3">
                        {{-- Detalles Adicionales --}}
                        <div class="col-md-6">
                            <label>Responsable</label>
                            <input type="text" name="responsable" class="form-control bg-light"
                                   value="Jose" readonly>
                        </div>

                        <div class="col-md-6">
                            <label>Fecha de Registro</label>
                            <input type="date" name="fecha_registro" class="form-control bg-light"
                                   value="{{ $descuentoSeleccionado->fecha_registro }}" readonly>
                        </div>
                    </div>
                    {{-- Botones al final de la card --}}
                    <div class="text-end border-top pt-3 mt-4">
                        <a href="{{ route('descuento.index') }}" class="btn btn-secondary">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-ejidal">
                            <i class="fas fa-save me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @endif

@endsection