@extends('cpanel/plantilla')

@section('title', 'Configuración de Descuentos')

@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-hand-holding-usd me-2"></i> Descuentos de faenas y asambleas
        </h1>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-start border-danger border-4">
                <div class="card-body py-3">
                    <p class="text-muted mb-0 small fw-bold text-uppercase">Monto Actual del Descuento Seleccionado</p>
                    <h2 class="text-danger mb-0 fw-bold">
                        ${{ number_format($descuentoSeleccionado->Costo ?? 0, 2) }}
                        <small class="text-muted fs-6">MXN</small>
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-ejidal mb-4">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-search me-2"></i> 1. Seleccione un Tipo de Descuento para editar
        </div>
        <div class="card-body">
            <form action="{{ route('descuento.descuento') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label class="fw-bold">Tipo de descuento (Catálogo)</label>
                        <select name="id_multa_c" class="form-control" onchange="this.form.submit()">
                            <option value="">-- Seleccionar opción --</option>
                            @foreach ($descuentos as $item)
                                @php
                                    // Lógica para mostrar nombres limpios en el select
                                    $nombreLimpio = $item->Tipo;
                                    if(stripos($item->Tipo, 'asamble') !== false) $nombreLimpio = 'ASAMBLEAS';
                                    if(stripos($item->Tipo, 'saneamient') !== false) $nombreLimpio = 'SANEAMIENTO';
                                    if(stripos($item->Tipo, 'aprovecham') !== false) $nombreLimpio = 'APROVECHAMIENTO';
                                @endphp
                                <option value="{{ $item->Id_MultaC }}"
                                        {{ (isset($descuentoSeleccionado) && $descuentoSeleccionado->Id_MultaC == $item->Id_MultaC) ? 'selected' : '' }}>
                                    {{ $nombreLimpio }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if ($descuentoSeleccionado)
        @php
            $tituloForm = $descuentoSeleccionado->Tipo;
            if(stripos($tituloForm, 'asamble') !== false) $tituloForm = 'ASAMBLEAS (GENERAL)';
            if(stripos($tituloForm, 'saneamient') !== false) $tituloForm = 'SANEAMIENTO';
            if(stripos($tituloForm, 'aprovecham') !== false) $tituloForm = 'APROVECHAMIENTO';
        @endphp

        <div class="card card-ejidal shadow">
            <div class="card-header card-header-ejidal">
                <i class="fas fa-edit me-2"></i> 2. Modificar valores para: <strong>{{ $tituloForm }}</strong>
            </div>

            <form action="{{ route('descuento.update', $descuentoSeleccionado->Id_MultaC) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Monto del Descuento ($)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="Costo" class="form-control" step="0.01"
                                       value="{{ $descuentoSeleccionado->Costo }}" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Año Fiscal</label>
                            <input type="text" name="Año" class="form-control bg-light"
                                   value="{{ $descuentoSeleccionado->Año }}" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Nombre en Base de Datos</label>
                            <input type="text" class="form-control bg-light"
                                   value="{{ $descuentoSeleccionado->Tipo }}" readonly disabled>
                            <div class="form-text small">Nombre técnico del registro original.</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Creado Por</label>
                            <input type="text" class="form-control bg-light" value="{{ $descuentoSeleccionado->Id_Creo ?? 'Sistema' }}" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Fecha de Registro</label>
                            <input type="text" class="form-control bg-light"
                                   value="{{ $descuentoSeleccionado->Fecha_Creo }}" readonly>
                        </div>
                    </div>

                    <div class="text-end border-top pt-3 mt-4">
                        <a href="{{ route('menu') }}" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @endif

@endsection