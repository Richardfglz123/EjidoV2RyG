@extends('cpanel.plantilla')
@section('title', 'Listado de Gastos')
@section('content')

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- CARD DE FILTROS --}}
    <div class="card perfil-card mb-4 shadow-sm">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-search me-2"></i> Filtros de Búsqueda
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('gastos.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Responsable</label>
                        <input type="text" name="responsable" class="form-control"
                               placeholder="Nombre del responsable" value="{{ request('responsable') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Concepto</label>
                        <input type="text" name="concepto" class="form-control"
                               placeholder="Ej. Mantenimiento" value="{{ request('concepto') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Fecha Exacta</label>
                        <input type="date" name="fecha" class="form-control" value="{{ request('fecha') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-ejidal w-100">
                            <i class="fas fa-filter me-1"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLA DE RESULTADOS --}}
    <div class="card perfil-card shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-hand-holding-usd me-2"></i> Registro de Egresos y Gastos</span>
            <a href="{{ route('gastos.create') }}" class="btn btn-light btn-sm text-dark fw-bold">
                <i class="fas fa-plus-circle me-1"></i> Nuevo Gasto
            </a>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-ejidal">
                    <tr>
                        <th class="ps-3">Responsable</th>
                        <th>Fecha</th>
                        <th>Monto</th>
                        <th>Concepto</th>
                        <th>Medida</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($gastos as $g)
                        <tr>
                            <td class="ps-3">
                                <i class="fas fa-user-circle text-muted me-1"></i>
                                {{ $g->Responsable }}
                            </td>
                            <td>{{ \Carbon\Carbon::parse($g->Fecha)->format('d/m/Y') }}</td>
                            <td>
                                <span class="text-danger fw-bold">
                                    $ {{ number_format($g->Monto, 2) }}
                                </span>
                            </td>
                            <td>
                                <span class="small">{{ $g->Concepto }}</span>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $g->Medida }}</span></td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="{{ route('gastos.edit', ['id' => $g->Id_Gasto]) }}"
                                       class="btn btn-outline-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form action="{{ route('gastos.destroy', ['id' => $g->Id_Gasto]) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm"
                                                onclick="return confirm('¿Eliminar este registro de gasto?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-file-invoice-dollar fa-3x mb-3 d-block"></i>
                                No se encontraron gastos con los criterios seleccionados.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- FOOTER DE ACCIONES --}}
        <div class="card-footer bg-light border-top d-flex justify-content-between align-items-center">
            <div>
                <a href="{{ route('gastos.pdf') }}" class="btn btn-outline-danger btn-sm me-2" target="_blank">
                    <i class="fas fa-file-pdf me-1"></i> Reporte PDF
                </a>
                <a href="{{ route('gastos.excel') }}" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-file-excel me-1"></i> Reporte Excel
                </a>
            </div>
        </div>

        {{-- PAGINACIÓN --}}
        @if(method_exists($gastos, 'links'))
            <div class="card-footer bg-white border-top">
                {{ $gastos->links() }}
            </div>
        @endif
    </div>

@endsection
