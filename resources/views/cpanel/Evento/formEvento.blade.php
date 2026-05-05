@extends('cpanel/plantilla')
@section('title','Registro Evento')
@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-calendar-plus me-2"></i> Registro de Evento
        </h1>
    </div>

    <div class="card card-ejidal shadow-sm">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-calendar-check me-2"></i> Datos del Nuevo Evento
        </div>

        <form action="{{ route('eventos.store') }}" method="POST">
            @csrf
            <div class="card-body p-4">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-ejidal">Nombre del Evento</label>
                        <input type="text" name="Nombre_Evento" class="form-control @error('Nombre_Evento') is-invalid @enderror"
                               value="{{ old('Nombre_Evento') }}" required>
                        @error('Nombre_Evento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-ejidal">Categoría del evento</label>
                        <div class="input-group">
                            <select name="Id_Categoria_Evento" class="form-select @error('Id_Categoria_Evento') is-invalid @enderror" required>
                                <option value="">Seleccionar...</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->Id_Categoria_Evento }}" {{ old('Id_Categoria_Evento') == $cat->Id_Categoria_Evento ? 'selected' : '' }}>
                                        {{ $cat->Nombre_Categoria }}
                                    </option>
                                @endforeach
                            </select>
                            <a href="{{ route('categorias.index') }}" class="btn btn-outline-success" title="Gestionar Categorías">
                                <i class="fas fa-cog"></i>
                            </a>
                        </div>
                        @error('Id_Categoria_Evento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold text-ejidal">Observaciones</label>
                    <textarea name="Observaciones" class="form-control @error('Observaciones') is-invalid @enderror" rows="3">{{ old('Observaciones') }}</textarea>
                    @error('Observaciones') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="text-end border-top pt-3">
                    <a href="{{ route('eventos.index') }}" class="btn btn-secondary px-4 me-2">Cancelar</a>
                    <button type="submit" class="btn btn-ejidal px-5 shadow-sm">
                        <i class="fas fa-save me-2"></i> Registrar Evento
                    </button>
                </div>
            </div>
        </form>
    </div>

@endsection