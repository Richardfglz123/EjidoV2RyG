@extends('cpanel/plantilla')
@section('title','Multas')
@section('content')

    {{-- ENCABEZADO --}}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-money-bill-wave me-2"></i> Gestión de Multas
        </h1>

        <a href="{{ route('multas.create') }}" class="btn btn-ejidal shadow-sm">
            <i class="fas fa-plus me-1"></i> Nueva Configuración
        </a>
    </div>

    {{-- FILTROS --}}
    <div class="card card-ejidal mb-4 shadow-sm">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-search me-2"></i> Búsqueda y Filtros
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('multas.index') }}" class="row g-3">

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Año</label>
                    <input type="text" name="anio" class="form-control"
                           placeholder="Buscar por año..."
                           value="{{ request('anio') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold">Tipo</label>
                    <select name="tipo" class="form-select">
                        <option value="">Todos</option>
                        <option value="asamblea" {{ request('tipo')=='asamblea' ? 'selected' : '' }}>Asamblea</option>
                        <option value="faena" {{ request('tipo')=='faena' ? 'selected' : '' }}>Faena</option>
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
    @if(request()->filled('anio') || request()->filled('tipo'))
        <div class="alert alert-info py-2 shadow-sm d-flex justify-content-between align-items-center">
            <span>
                <i class="fas fa-info-circle me-2"></i>
                Resultados encontrados: <strong>{{ $data->total() }}</strong>
            </span>
            <a href="{{ route('multas.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar filtros</a>
        </div>
    @endif

    {{-- TABLA --}}
    <div class="card card-ejidal shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-2"></i> Multas Registradas</span>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th class="ps-3">Año</th>
                    <th>Multa Asamblea</th>
                    <th>Multa Faena</th>
                    <th class="text-center">Acciones</th>
                </tr>
                </thead>

                <tbody>
                @forelse($data as $fila)
                    <tr>
                        <td class="ps-3">{{ $fila->anio }}</td>
                        <td>
                            <span class="badge bg-success">
                                ${{ number_format($fila->costo_asamblea,2) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-warning text-dark">
                                ${{ number_format($fila->costo_falta,2) }}
                            </span>
                        </td>

                        <td class="text-center">
                            <div class="btn-group shadow-sm">

                                <a href="{{ url('/admon/Multas/'.$fila->id.'/edit') }}"
                                   class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <form action="{{ url('/admon/Multas/'.$fila->id) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('¿Eliminar registro?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-5">
                            <i class="fas fa-folder-open fa-3x mb-3 d-block"></i>
                            No hay registros de multas.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- FOOTER --}}
        <div class="card-footer bg-light border-top d-flex justify-content-between align-items-center">

    {{--  <div>
          <a href="{{ route('reportes.multas.pdf') }}" class="btn btn-outline-danger btn-sm me-2" target="_blank">
              <i class="fas fa-file-pdf me-1"></i> PDF
          </a>

          <a href="{{ route('reportes.multas.excel') }}" class="btn btn-outline-success btn-sm">
              <i class="fas fa-file-excel me-1"></i> Excel
          </a>
      </div> --}}

      <div class="pagination-sm">
          {{ $data->links('pagination::bootstrap-4') }}
      </div>

  </div>
</div>

@endsection