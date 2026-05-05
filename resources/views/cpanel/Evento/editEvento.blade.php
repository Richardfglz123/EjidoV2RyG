@extends('cpanel/plantilla')
@section('title','Editar evento')

@section('content')

    <div class="card card-ejidal mt-3">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-edit me-2"></i> Editar evento
        </div>

        <form action="{{ route('eventos.update', $evento->Id_Evento) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card-body">

                {{-- Nombre y categoría --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre del Evento</label>
                        <input type="text" name="Nombre_Evento"
                               class="form-control @error('Nombre_Evento') is-invalid @enderror"
                               value="{{ old('Nombre_Evento', $evento->Nombre_Evento) }}" required>

                        @error('Nombre_Evento')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Categoría del evento</label>
                        <select name="Id_Categoria_Evento"
                                class="form-select @error('Id_Categoria_Evento') is-invalid @enderror" required>

                            <option value="">Seleccionar...</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->Id_Categoria_Evento }}"
                                        {{ old('Id_Categoria_Evento', $evento->Id_Categoria_Evento) == $cat->Id_Categoria_Evento ? 'selected' : '' }}>
                                    {{ $cat->Nombre_Categoria }}
                                </option>
                            @endforeach
                        </select>

                        @error('Id_Categoria_Evento')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Observaciones</label>
                    <textarea name="Observaciones"
                              class="form-control @error('Observaciones') is-invalid @enderror"
                              rows="3">{{ old('Observaciones', $evento->Observaciones) }}</textarea>

                    @error('Observaciones')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="text-end">
                    <a href="{{ route('eventos.index') }}" class="btn btn-secondary">
                        Cancelar
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Guardar cambios
                    </button>
                </div>

            </div>
        </form>
    </div>

@endsection