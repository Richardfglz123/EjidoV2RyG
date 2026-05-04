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
        <div class="card-body">
            <form method="GET" action="{{ route('eventos.index') }}" class="row g-3">

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Nombre del evento</label>
                    <input type="text" name="nombreEvento"
                           class="form-control"
                           placeholder="Buscar evento..."
                           value="{{ request('nombreEvento') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold">Categoría</label>
                    <select name="categoria" class="form-select">
                        <option value="">Todas</option>
                        <option value="asambleaEl">1ra Asamblea elección</option>
                        <option value="asambleaex">Asamblea extraordinaria</option>
                        <option value="asambleaor">Asamblea ordinaria</option>
                        <option value="faenasan">Faena saneamiento</option>
                        <option value="faenaap">Faena aprovechamiento</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-ejidal w-100">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- RESULTADOS --}}
    @if(request()->filled('nombreEvento') || request()->filled('categoria'))
        <div class="alert alert-info py-2 shadow-sm d-flex justify-content-between align-items-center">
        <span>
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
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th class="ps-3">Nombre</th>
                    <th>Categoría</th>
                    <th>Observaciones</th>
                    <th class="text-center">Acciones</th>
                </tr>
                </thead>

                <tbody>
                @forelse($data as $fila)
                    <tr>
                        <td class="ps-3">{{ $fila->nombreEvento }}</td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                {{ $fila->categoria }}
                            </span>
                        </td>
                        <td>{{ $fila->observaciones }}</td>

                        <td class="text-center">
                            <div class="btn-group shadow-sm">

                                <a href="{{ url('/admon/Eventos/'.$fila->Id_Evento.'/edit') }}"
                                   class="btn btn-warning btn-sm"
                                   title="Editar Evento">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <form action="{{ url('/admon/Eventos/'.$fila->Id_Evento) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-danger btn-sm"
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
                            <i class="fas fa-calendar-times fa-3x mb-3 d-block"></i>
                            No hay eventos registrados.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- FOOTER --}}
        <div class="card-footer bg-light border-top d-flex justify-content-between align-items-center">
            {{-- <div>
                 <a href="{{ route('reportes.eventos.pdf') }}"
                    class="btn btn-outline-danger btn-sm me-2" target="_blank">
                     <i class="fas fa-file-pdf me-1"></i> PDF
                 </a>

                 <a href="{{ route('reportes.eventos.excel') }}"
                    class="btn btn-outline-success btn-sm">
                     <i class="fas fa-file-excel me-1"></i> Excel
                 </a>
             </div> --}}

            <div class="pagination-sm">
                {{ $data->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

@endsection