@extends('cpanel/plantilla')
@section('title','Editar evento')

@section('content')

    <div class="card card-ejidal">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-calendar-edit me-2"></i> Editar evento
        </div>

        <form action="{{ url('/admon/Eventos/'.$fila->Id_Evento) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card-body">

                {{-- Nombre y categoría --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Nombre del Evento</label>
                        <input type="text" name="nombreEvento"
                               class="form-control @error('nombreEvento') is-invalid @enderror"
                               value="{{ old('nombreEvento', $fila->nombreEvento) }}" required>

                        @error('nombreEvento')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label>Categoría del evento</label>
                        <select name="categoria"
                                class="form-select @error('categoria') is-invalid @enderror" required>

                            <option value="">Seleccionar...</option>

                            <option value="asambleaEl" {{ old('categoria', $fila->categoria) == 'asambleaEl' ? 'selected' : '' }}>1ra Asamblea elección</option>
                            <option value="asambleaex" {{ old('categoria', $fila->categoria) == 'asambleaex' ? 'selected' : '' }}>Asamblea extraordinaria</option>
                            <option value="asambleadic" {{ old('categoria', $fila->categoria) == 'asambleadic' ? 'selected' : '' }}>Asamblea Diciembre</option>
                            <option value="asambleaen" {{ old('categoria', $fila->categoria) == 'asambleaen' ? 'selected' : '' }}>Asamblea Enero</option>
                            <option value="asambleamar" {{ old('categoria', $fila->categoria) == 'asambleamar' ? 'selected' : '' }}>Asamblea marzo</option>
                            <option value="asambleajun" {{ old('categoria', $fila->categoria) == 'asambleajun' ? 'selected' : '' }}>Asamblea Junio</option>
                            <option value="asambleasep" {{ old('categoria', $fila->categoria) == 'asambleasep' ? 'selected' : '' }}>Asamblea Septiembre</option>
                            <option value="asambleaor" {{ old('categoria', $fila->categoria) == 'asambleaor' ? 'selected' : '' }}>Asamblea ordinaria</option>
                            <option value="faenasan" {{ old('categoria', $fila->categoria) == 'faenasan' ? 'selected' : '' }}>Faena saneamiento</option>
                            <option value="faenaap" {{ old('categoria', $fila->categoria) == 'faenaap' ? 'selected' : '' }}>Faena aprovechamiento</option>
                            <option value="otro" {{ old('categoria', $fila->categoria) == 'otro' ? 'selected' : '' }}>Otro</option>

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
                              rows="3">{{ old('observaciones', $fila->observaciones) }}</textarea>

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
                        <i class="fas fa-save me-1"></i> Guardar cambios
                    </button>
                </div>

            </div>
        </form>
    </div>

@endsection