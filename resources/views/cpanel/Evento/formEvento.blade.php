@extends('cpanel/plantilla')
@section('title','Registro Evento')
@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-list-check me-2"></i> Eventos
        </h1>

        <!-- Botones exportar -->
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-file-excel me-1"></i> Excel
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-file-pdf me-1"></i> PDF
                </button>
            </div>
        </div>
    </div>

    <div class="card card-ejidal">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-calendar-check me-2"></i> Nuevo Evento
        </div>

        <form action="{{ route('eventos.store') }}" method="POST">
            @csrf

            <div class="card-body">

                {{-- Nombre y Categoría --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Nombre del Evento</label>
                        <input type="text" name="nombreEvento"
                               class="form-control @error('nombreEvento') is-invalid @enderror"
                               value="{{ old('nombreEvento') }}" required>

                        @error('nombreEvento')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label>Categoría del evento</label>
                        <select name="categoria"
                                class="form-select @error('categoria') is-invalid @enderror" required>

                            <option value="">Seleccionar...</option>
                            <option value="asambleaEl">1ra Asamblea elección</option>
                            <option value="asambleaex">Asamblea extraordinaria</option>
                            <option value="asambleadic">Asamblea Diciembre</option>
                            <option value="asambleaen">Asamblea Enero</option>
                            <option value="asambleamar">Asamblea marzo</option>
                            <option value="asambleajun">Asamblea Junio</option>
                            <option value="asambleasep">Asamblea Septiembre</option>
                            <option value="asambleaor">Asamblea ordinaria</option>
                            <option value="faenasan">Faena saneamiento</option>
                            <option value="faenaap">Faena aprovechamiento</option>
                            <option value="otro">Otro</option>

                        </select>

                        @error('categoria')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Observaciones --}}
                <div class="mb-3">
                    <label>Observaciones</label>
                    <textarea name="observaciones"
                              class="form-control @error('observaciones') is-invalid @enderror"
                              rows="3">{{ old('observaciones') }}</textarea>

                    @error('observaciones')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Botones --}}
                <div class="text-end">
                    <a href="{{ route('eventos.index') }}" class="btn btn-secondary">
                        Cancelar
                    </a>

                    <button type="submit" class="btn btn-ejidal">
                        <i class="fas fa-calendar-plus me-1"></i> Registrar Evento
                    </button>
                </div>

            </div>
        </form>
    </div>

@endsection