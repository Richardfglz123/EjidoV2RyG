@extends('cpanel.plantilla')
@section('title', 'Reparto Utilidad')

@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-hand-holding-usd me-2"></i> Reparto Utilidad
        </h1>
    </div>

    {{-- Visualización del Monto Actual --}}
    <div class="card card-ejidal mb-4 text-center">
        <div class="card-body py-4">
            <h6 class="text-uppercase text-muted fw-bold mb-1">Monto Actual Seleccionado</h6>
            <h2 class="display-5 text-ejidal fw-bold">
                ${{ number_format($utilidadSeleccionada->Monto ?? 0, 2) }}
            </h2>
            <p class="text-muted mb-0">Pesos Mexicanos (MXN)</p>
        </div>
    </div>

    {{-- Formulario 1: Selección --}}
    <div class="card card-ejidal mb-4">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-search me-2"></i> 1. Seleccione un Reparto para Ver y Editar
        </div>
        <div class="card-body">
            <form action="{{ route('monto.index') }}" method="GET">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Tipo de reparto</label>
                        <select name="id_utilidad" class="form-select" onchange="this.form.submit()">
                            <option value="">Seleccionar reparto...</option>
                            @foreach ($utilidades as $utilidad)
                                <option value="{{ $utilidad->Id_Utilidad }}"
                                        @if(isset($utilidadSeleccionada) && $utilidadSeleccionada->Id_Utilidad == $utilidad->Id_Utilidad) selected @endif>
                                    {{-- CAMBIO AQUÍ: Tipo_Reparto --}}
                                    {{ ucwords(str_replace('_', ' ', $utilidad->Tipo_Reparto)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Formulario 2: Edición --}}
    @if (isset($utilidadSeleccionada) && $utilidadSeleccionada)
        <div class="card card-ejidal">
            <div class="card-header card-header-ejidal">
                <i class="fas fa-edit me-2"></i> 2. Edite la Información del Reparto
            </div>

            <form action="{{ route('monto.update', $utilidadSeleccionada->Id_Utilidad) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="fw-bold">Monto</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="monto" class="form-control" step="0.01"
                                       value="{{ $utilidadSeleccionada->Monto }}" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="fw-bold">Año</label>
                            <input type="number" name="anio" class="form-control"
                                   value="{{ $utilidadSeleccionada->Año }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="fw-bold">Tipo de reparto</label>
                            <input type="text" name="nombre_reparto" class="form-control bg-light"
                                   value="{{ $utilidadSeleccionada->Tipo_Reparto }}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-bold">Responsable</label>
                            <select name="responsable" class="form-select" required>
                                <option value="">Seleccione un usuario...</option>
                                @foreach($usuarios as $usuario)
                                    @php
                                        $nombreCompleto = $usuario->Nombres . ' ' . $usuario->Apellido_Paterno . ' ' . $usuario->Apellido_Materno;
                                        // Comprobamos contra Id_Modificado o Id_Creo
                                        $responsableActual = $utilidadSeleccionada->Id_Modificado ?? $utilidadSeleccionada->Id_Creo;
                                    @endphp
                                    <option value="{{ $nombreCompleto }}"
                                            @if($responsableActual == $nombreCompleto) selected @endif>
                                        {{ $nombreCompleto }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-bold">Fecha de Registro (Calendario)</label>
                            <input type="date" name="fecha_registro" class="form-control"
                                   value="{{ $utilidadSeleccionada->Fecha_Registro ? \Carbon\Carbon::parse($utilidadSeleccionada->Fecha_Registro)->format('Y-m-d') : '' }}">
                        </div>
                    </div>

                    <div class="text-end border-top pt-3">
                        <button type="submit" class="btn btn-secondary me-2">
                            Cancelar
                        </button>

                        <button type="submit" class="btn btn-ejidal">
                            <i class="fas fa-save me-1"></i> Guardar cambios
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @endif
@endsection