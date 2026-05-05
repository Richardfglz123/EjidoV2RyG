@extends('cpanel/plantilla')
@section('title','Gestión de Multas')
@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-money-bill-wave me-2"></i> Gestión de Multas
        </h1>

        <a href="{{ route('multas.create') }}" class="btn btn-ejidal shadow-sm">
            <i class="fas fa-plus me-1"></i> Nueva Configuración
        </a>
    </div>

    <div class="card card-ejidal mb-4 shadow-sm">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-search me-2"></i> Búsqueda por Año
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('multas.index') }}" class="row g-3">
                <div class="col-md-9">
                    <input type="text" name="anio" class="form-control"
                           placeholder="Escribe el año (Ej. 2026)..."
                           value="{{ request('anio') }}">
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-ejidal w-100">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card card-ejidal shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-2"></i> Listado de Multas por Año</span>
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
                @forelse($data as $anio => $registros)
                    <tr>
                        <td class="ps-3 fw-bold text-dark">{{ $anio }}</td>

                        <td>
                            @php $asamblea = $registros->where('Tipo', 'Asamblea')->first(); @endphp
                            @if($asamblea)
                                <span class="badge bg-success">
                                    ${{ number_format($asamblea->Costo, 2) }}
                                </span>
                            @else
                                <span class="badge bg-light text-muted border">No asignado</span>
                            @endif
                        </td>

                        <td>
                            @php $faena = $registros->where('Tipo', 'Faena')->first(); @endphp
                            @if($faena)
                                <span class="badge bg-warning text-dark">
                                    ${{ number_format($faena->Costo, 2) }}
                                </span>
                            @else
                                <span class="badge bg-light text-muted border">No asignado</span>
                            @endif
                        </td>

                        <td class="text-center">
                            <div class="btn-group shadow-sm">
                                @php $idEdit = $registros->first()->Id_MultaC; @endphp

                                <a href="{{ route('multas.edit', $idEdit) }}"
                                   class="btn btn-warning btn-sm" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <form action="{{ route('multas.destroy', $idEdit) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('¿Seguro de eliminar la configuración del año {{ $anio }}?')"
                                            title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-5">
                            <i class="fas fa-folder-open fa-3x mb-3 d-block text-secondary"></i>
                            No se encontraron registros de multas.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection