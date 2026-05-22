@extends('cpanel/plantilla')

@section('title', 'Configuración de Descuentos')

@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-hand-holding-usd me-2"></i> Descuentos de faenas y asambleas
        </h1>
    </div>

    <div class="card card-ejidal mb-4">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-search me-2"></i> 1. Seleccione un Tipo de Descuento para editar
        </div>
        <div class="card-body">
            <form action="{{ route('descuento.descuento') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label class="fw-bold">Tipo de descuento</label>
                        <select name="id_multa_c" class="form-control" onchange="this.form.submit()">
                            <option value="">-- Seleccionar opción --</option>
                            @php $vistos = []; @endphp
                            @foreach ($descuentos as $item)
                                @php
                                    // Normalizamos el texto para comparar
                                    $tipoUpper = strtoupper($item->Tipo);

                                    if(strpos($tipoUpper, 'ASAMBLE') !== false) $tipoLimpio = 'ASAMBLEAS';
                                    elseif(strpos($tipoUpper, 'SANEAMIENT') !== false) $tipoLimpio = 'SANEAMIENTO';
                                    elseif(strpos($tipoUpper, 'APROVECHAM') !== false) $tipoLimpio = 'APROVECHAMIENTO';
                                    elseif(strpos($tipoUpper, 'FAENA') !== false) $tipoLimpio = 'FAENA';
                                    else $tipoLimpio = $item->Tipo;
                                @endphp

                                @if(!in_array($tipoLimpio, $vistos))
                                    <option value="{{ $item->Id_MultaC }}" {{ (isset($descuentoSeleccionado) && $descuentoSeleccionado->Id_MultaC == $item->Id_MultaC) ? 'selected' : '' }}>
                                        {{ $tipoLimpio }}
                                    </option>
                                    @php $vistos[] = $tipoLimpio; @endphp
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if ($descuentoSeleccionado)
        <div class="card card-ejidal shadow">
            <div class="card-header card-header-ejidal">
                <i class="fas fa-edit me-2"></i> 2. Modificar valores
            </div>

            <form action="{{ route('descuento.update', $descuentoSeleccionado->Id_MultaC) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Monto del Descuento ($)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="Costo" class="form-control" step="0.01"
                                       value="{{ $descuentoSeleccionado->Costo }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Año Fiscal</label>
                            <input type="text" class="form-control bg-light" value="{{ date('Y') }}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Modificado por</label>
                            <input type="text" name="responsable" class="form-control bg-light"
                                   value="{{ session('usuario.nombre') ?? 'Usuario Actual' }}" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Fecha de Modificación</label>
                            <input type="text" class="form-control bg-light" value="{{ date('d/m/Y') }}" readonly>
                        </div>
                    </div>

                    <div class="text-end border-top pt-3 mt-4">
                        <a href="{{ route('menu') }}" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @endif

@endsection