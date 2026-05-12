@extends('cpanel/plantilla')
@section('title','Ejido San Rafael Ixtapalucan')

@section('content')

    <style>
        .text-header-main { color: #000000 !important; font-weight: normal !important; }
        .card-header-ejidal { background-color: #198754; color: white; }
        .btn-ejidal { background-color: #198754; color: white; border: none; }
        .btn-ejidal:hover { background-color: #146c43; color: white; }

        .pagination .page-item.active .page-link {
            background-color: #198754 !important;
            border-color: #198754 !important;
            color: #ffffff !important;
        }
        .pagination .page-link { color: #198754 !important; }

        .table tr, .table td { transition: none !important; transform: none !important; }
        .table td { vertical-align: middle; }

        /* Ajuste de visualización */
        .table-responsive { min-height: 400px; }
        .table td { white-space: nowrap; padding: 15px 10px !important; }
        .user-name { font-size: 1.05rem; letter-spacing: 0.3px; }
    </style>

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-header-main">
            <i class="fas fa-clipboard-check me-2"></i> Descuentos por Faenas
        </h1>
    </div>

    {{-- Buscador --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-search me-2"></i> Filtro de Ejidatarios
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('descuentos.faenas') }}" class="row g-3">
                <div class="col-md-10">
                    <input type="text" name="query" class="form-control" placeholder="Buscar por nombre o apellido..." value="{{ request('query') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-ejidal w-100">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Listado --}}
    <div class="card shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-2"></i> Relación de Faltas y Descuentos Aplicados</span>
            <span class="badge bg-light text-dark">Sincronizado con Pase de Lista</span>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                <tr class="small text-uppercase">
                    <th class="ps-4" style="width: 30%;">Ejidatario / Estado de Asistencia</th>
                    @foreach($catalogoFaenas as $cat)
                        <th class="text-center">{{ $cat->Tipo }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @forelse($ejidatarios as $ejidatario)
                    @php
                        $misDescuentos = $ejidatario->descuentos->keyBy('Id_MultaC');
                        $faltasCount = $ejidatario->pasesLista->where('Estatus', 'Falta')->count();
                    @endphp
                    <tr>
                        <td class="ps-4">
                            <div class="user-name text-dark fw-bold">
                                {{ $ejidatario->usuario?->Nombres }} {{ $ejidatario->usuario?->Apellido_Paterno }} {{ $ejidatario->usuario?->Apellido_Materno }}
                            </div>
                            <div class="mt-1">
                                @if($faltasCount > 0)
                                    <span class="badge rounded-pill bg-danger">
                                        <i class="fas fa-exclamation-triangle me-1"></i> {{ $faltasCount }} Faltas Detectadas
                                    </span>
                                @else
                                    <span class="badge rounded-pill bg-success opacity-75">
                                        <i class="fas fa-check me-1"></i> Sin Faltas
                                    </span>
                                @endif
                            </div>
                        </td>

                        @foreach($catalogoFaenas as $cat)
                            @php
                                $monto = $misDescuentos->get($cat->Id_MultaC)->Descuento ?? 0;
                            @endphp
                            <td class="text-center">
                                @if($monto > 0)
                                    <div class="fw-bold text-danger">
                                        ${{ number_format($monto, 2) }}
                                    </div>
                                    <small class="text-muted" style="font-size: 0.7rem;">AUTOMÁTICO</small>
                                @else
                                    <span class="text-muted opacity-25">--</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($catalogoFaenas) + 1 }}" class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fa-3x mb-3 d-block"></i>
                            No se encontraron registros de ejidatarios.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
            <div class="small text-muted">
                Mostrando <strong>{{ $ejidatarios->count() }}</strong> registros de la página actual.
            </div>
            <div class="pagination-sm">
                {{ $ejidatarios->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

@endsection