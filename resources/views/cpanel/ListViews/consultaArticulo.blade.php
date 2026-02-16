@extends('cpanel.plantilla')
@section('title', 'Inventario de Artículos')

@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-boxes me-2"></i> Inventario General
        </h1>
        <a href="{{ route('articulos.create') }}" class="btn btn-ejidal shadow-sm">
            <i class="fas fa-plus-circle me-1"></i> Nuevo Artículo
        </a>
    </div>

    {{-- Filtros --}}
    <div class="card card-ejidal mb-4 shadow-sm">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-search me-2"></i> Filtros de Búsqueda
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('articulos.buscar') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Descripción</label>
                    <input type="text" name="descripcion" class="form-control" value="{{ request('descripcion') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Estado</label>
                    <input type="text" name="estado" class="form-control" value="{{ request('estado') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Fecha Registro</label>
                    <input type="date" name="fecha_registro" class="form-control" value="{{ request('fecha_registro') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-ejidal w-100"><i class="fas fa-search"></i> Buscar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="card card-ejidal shadow-sm">
        <div class="card-header card-header-ejidal">
            <span><i class="fas fa-list me-2"></i> Existencias en Almacén</span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light text-ejidal">
                    <tr>
                        <th class="ps-3">Descripción</th>
                        <th class="text-center">Stock</th>
                        <th>Estado</th>
                        <th>Unidad</th>
                        <th>Registro</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($articulos as $a)
                        <tr>
                            <td class="ps-3 fw-bold">{{ $a->Descripcion }}</td>
                            <td class="text-center">
                                    <span class="badge {{ $a->Cantidad > 5 ? 'bg-ejidal-light text-ejidal' : 'bg-danger-light text-danger' }} px-3">
                                        {{ $a->Cantidad }}
                                    </span>
                            </td>
                            <td>{{ $a->Estado }}</td>
                            <td>{{ $a->Medida }}</td>
                            <td>{{ \Carbon\Carbon::parse($a->fecha_registro)->format('d/m/Y') }}</td>
                            <td class="text-center">
                                <div class="btn-group shadow-sm">
                                    <a href="{{ route('articulos.edit', $a->Id_Articulo) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('articulos.destroy', $a->Id_Articulo) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar artículo?')"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">No se encontraron artículos.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Footer con Reportes --}}
        <div class="card-footer bg-light border-top d-flex justify-content-between align-items-center">
            <div>
                <a href="{{ route('articulos.pdf') }}" class="btn btn-outline-danger btn-sm me-2" target="_blank">
                    <i class="fas fa-file-pdf me-1"></i> Exportar PDF
                </a>
                <a href="{{ route('articulos.excel') }}" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-file-excel me-1"></i> Exportar Excel
                </a>
            </div>
            <div class="pagination-sm">
                @if(method_exists($articulos, 'links'))
                    {{ $articulos->appends(request()->query())->links() }}
                @endif
            </div>
        </div>
    </div>
@endsection
