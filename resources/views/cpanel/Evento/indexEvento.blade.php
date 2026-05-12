@extends('cpanel/plantilla')
@section('title','Eventos')

@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-calendar-check me-2"></i> Listado de Eventos
        </h1>

        <a href="{{ route('eventos.create') }}" class="btn btn-ejidal shadow-sm">
            <i class="fas fa-calendar-plus me-1"></i> Nuevo Evento
        </a>
    </div>

    {{-- FILTROS --}}
    <div class="card card-ejidal mb-4 shadow-sm">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-search me-2"></i> Búsqueda y Filtros
        </div>
        <div class="card-body p-4">
            <form method="GET" action="{{ route('eventos.index') }}" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-ejidal">Nombre del evento</label>
                    <input type="text" name="nombreEvento"
                           class="form-control"
                           placeholder="Buscar evento..."
                           value="{{ request('nombreEvento') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-ejidal">Categoría</label>
                    <select name="categoria" class="form-select">
                        <option value="">Todas las categorías</option>
                        <option value="1" {{ request('categoria') == '1' ? 'selected' : '' }}>1ra Asamblea elección</option>
                        <option value="2" {{ request('categoria') == '2' ? 'selected' : '' }}>Asamblea extraordinaria</option>
                        <option value="3" {{ request('categoria') == '3' ? 'selected' : '' }}>Asamblea Diciembre</option>
                        <option value="4" {{ request('categoria') == '4' ? 'selected' : '' }}>Asamblea Enero</option>
                        <option value="5" {{ request('categoria') == '5' ? 'selected' : '' }}>Asamblea Marzo</option>
                        <option value="6" {{ request('categoria') == '6' ? 'selected' : '' }}>Asamblea Junio</option>
                        <option value="7" {{ request('categoria') == '7' ? 'selected' : '' }}>Asamblea Seprimbre (corte de caja)</option>
                        <option value="8" {{ request('categoria') == '8' ? 'selected' : '' }}>Asamblea Ordinaria</option>
                        <option value="9" {{ request('categoria') == '9' ? 'selected' : '' }}>Faena Saneamiento </option>
                        <option value="10" {{ request('categoria') == '10' ? 'selected' : '' }}>Faena Aprovechamiento</option>
                        <option value="11" {{ request('categoria') == '11' ? 'selected' : '' }}>Otro</option>

                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-ejidal w-100 shadow-sm">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- RESULTADOS --}}
    @if(request()->filled('nombreEvento') || request()->filled('categoria'))
        <div class="alert alert-light border-start border-4 border-success py-2 shadow-sm d-flex justify-content-between align-items-center">
            <span class="text-ejidal">
                <i class="fas fa-info-circle me-2"></i>
                Resultados encontrados: <strong>{{ $data->total() }}</strong>
            </span>
            <a href="{{ route('eventos.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar filtros</a>
        </div>
    @endif

    {{-- TABLA --}}
    <div class="card card-ejidal shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-2"></i> Eventos Registrados</span>
            <span class="badge bg-white text-ejidal">{{ $data->total() }} total</span>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                <tr>
                    <th class="ps-4 border-0">Nombre del Evento</th>
                    <th class="border-0">Categoría</th>
                    <th class="border-0">Observaciones</th>
                    <th class="text-center border-0">Acciones</th>
                </tr>
                </thead>

                <tbody>
                @forelse($data as $fila)
                    <tr>
                        <td class="ps-4 fw-bold text-dark">{{ $fila->Nombre_Evento }}</td>
                        <td>
                            <span class="badge rounded-pill bg-light text-success border border-success">
                                {{ $fila->categoria->Nombre_Categoria ?? 'Sin Categoría' }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $fila->Observaciones }}</td>

                        <td class="text-center">
                            <div class="btn-group shadow-sm">
                                <a href="{{ route('eventos.edit', $fila->Id_Evento) }}"
                                   class="btn btn-outline-warning btn-sm" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <form action="{{ route('eventos.destroy', $fila->Id_Evento) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm"
                                            onclick="return confirm('¿Eliminar evento?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-5">
                            <i class="fas fa-calendar-times fa-3x mb-3 d-block text-ejidal" style="opacity: 0.3;"></i>
                            No hay eventos registrados que coincidan con la búsqueda.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white border-top">
            <div class="d-flex justify-content-center py-2">
                {{ $data->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

@endsection