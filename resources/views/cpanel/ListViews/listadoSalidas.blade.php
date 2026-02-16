@extends('cpanel.plantilla')
@section('title', 'Listado de Salidas')

@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-arrow-up me-2"></i> Listado de Salidas de Inventario
        </h1>

        <a href="{{ route('salidas.create') }}" class="btn btn-ejidal shadow-sm">
            <i class="fas fa-plus-circle me-1"></i> Nueva Salida
        </a>
    </div>

    {{-- CARD DE FILTROS --}}
    <div class="card card-ejidal mb-4 shadow-sm">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-search me-2"></i> Búsqueda y Filtros
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('salidas.index') }}" class="row g-3">
                <div class="col-md-8">
                    <label class="form-label small fw-bold">Buscar Artículo o Responsable</label>
                    <input type="text" name="buscar" class="form-control" placeholder="Escriba aquí..." value="{{ request('buscar') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-ejidal w-100">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLA DE RESULTADOS --}}
    <div class="card card-ejidal shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list-ul me-2"></i> Salidas Registradas</span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light text-ejidal">
                    <tr>
                        <th class="ps-3">Artículo</th>
                        <th>Cantidad</th>
                        <th>Tipo de Salida</th>
                        <th>Fecha</th>
                        <th>Responsable</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($salidas as $s)
                        <tr>
                            <td class="ps-3 fw-bold">{{ $s->articulo->descripcion }}</td>
                            <td>
                                <span class="badge bg-secondary px-3">{{ $s->cantidad }}</span>
                            </td>
                            <td>{{ $s->tipo_salida }}</td>
                            <td>{{ \Carbon\Carbon::parse($s->fecha_salida)->format('d/m/Y') }}</td>
                            <td>
                                <i class="fas fa-user-circle me-1 text-muted"></i> {{ $s->responsable }}
                            </td>
                            <td class="text-center">
                                <div class="btn-group shadow-sm" role="group">
                                    <a href="{{ route('salidas.edit', $s->id_salida) }}" class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('salidas.destroy', $s->id_salida) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar esta salida?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3 d-block"></i> No hay salidas registradas.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- BOTONES DE REPORTE Y PAGINACIÓN --}}
            <div class="card-footer bg-light border-top d-flex justify-content-between align-items-center">
                <div>
                    {{-- Aquí corregimos los nombres de las rutas --}}
                    <a href="{{ route('salidas.pdf') }}" class="btn btn-outline-danger btn-sm me-2" target="_blank">
                        <i class="fas fa-file-pdf me-1"></i> Generar PDF
                    </a>
                    <a href="{{ route('salidas.excel') }}" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-file-excel me-1"></i> Generar Excel
                    </a>
                </div>




            <div class="pagination-sm">
                @if(method_exists($salidas, 'links'))
                    {{ $salidas->links('pagination::bootstrap-4') }}
                @endif
            </div>
        </div>
@endsection
