@extends('cpanel.plantilla')
@section('title', 'Reparto Utilidad')

@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-hand-holding-usd me-2"></i> Reparto Utilidad
        </h1>
    </div>

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
    <div class="card card-ejidal mb-4 border-0 shadow-sm">
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
                            @foreach ($utilidades as $util)
                                <option value="{{ $util->Id_Utilidad }}"
                                        @if(isset($utilidadSeleccionada) && $utilidadSeleccionada->Id_Utilidad == $util->Id_Utilidad) selected @endif>
                                    {{ $util->Tipo_Reparto }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if (isset($utilidadSeleccionada) && $utilidadSeleccionada)
        <div class="card card-ejidal border-0 shadow-sm">
            <div class="card-header card-header-ejidal">
                <i class="fas fa-edit me-2"></i> 2. Edite la Información del Reparto
            </div>

            <form action="{{ route('monto.update', $utilidadSeleccionada->Id_Utilidad) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="fw-bold">Monto del Reparto: {{ $utilidadSeleccionada->Tipo_Reparto }} (Año: {{ date('Y') }})</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="monto" class="form-control" step="0.01"
                                       value="{{ $utilidadSeleccionada->Monto }}" required>
                            </div>
                        </div>

                        <!-- Campos ocultos para mantener la integridad del envío -->
                        <input type="hidden" name="anio" value="{{ date('Y') }}">
                        <input type="hidden" name="nombre_reparto" value="{{ $utilidadSeleccionada->Tipo_Reparto }}">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-bold">Responsable del cambio</label>
                            <input type="text" class="form-control bg-light"
                                   value="{{ Auth::check() ? Auth::user()->Nombres . ' ' . Auth::user()->Apellido_Paterno : 'Invitado' }}"
                                   readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-bold">Última Modificación</label>
                            <input type="text" class="form-control bg-light"
                                   value="{{ $utilidadSeleccionada->Fecha_Modificado ?? 'Sin modificaciones' }}" readonly>
                        </div>
                    </div>

                    <div class="text-end border-top pt-3">
                        <a href="{{ route('menu') }}" class="btn btn-secondary me-2">Cancelar</a>
                        <button type="submit" class="btn btn-ejidal">
                            <i class="fas fa-save me-1"></i> Guardar cambios
                        </button>
                    </div>
                </div>
            </form>
    @endif
@endsection