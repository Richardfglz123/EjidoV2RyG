@extends('cpanel/plantilla')
@section('title','Ejido San Rafael Ixtapalucan')

@section('content')

    <style>
        :root {
            --ejidal-dark: #1b4b36;
            --ejidal-light: #2d6a4f;
            --ejidal-accent: #e8f5e9;
        }

        .text-header-main { color: #2c3e50; font-weight: 700 !important; letter-spacing: -0.5px; }

        /* Estilos de Tarjetas */
        .card { border: none; border-radius: 12px; overflow: hidden; }
        .card-header-ejidal {
            background: linear-gradient(45deg, var(--ejidal-dark), var(--ejidal-light));
            color: white;
            font-weight: 600;
            border: none;
        }

        /* Botones */
        .btn-ejidal { background-color: var(--ejidal-dark); color: white; border-radius: 8px; transition: all 0.3s; }
        .btn-ejidal:hover { background-color: #0d2e1f; color: white; transform: translateY(-1px); shadow: 0 4px 8px rgba(0,0,0,0.1); }

        /* Tabla Estilizada */
        .table thead th {
            background-color: #f8f9fa;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            color: #6c757d;
            border-bottom: 2px solid #edf2f7;
        }
        .table tbody tr { transition: background 0.2s; }
        .table tbody tr:hover { background-color: var(--ejidal-accent) !important; }

        .user-name { font-size: 0.95rem; color: #2d3436; }
        .amount-badge {
            background: #fff5f5;
            color: #e03131;
            padding: 5px 10px;
            border-radius: 6px;
            font-weight: 700;
            border: 1px solid #ffc9c9;
            display: inline-block;
        }

        /* Pagination fix */
        .pagination .page-item.active .page-link { background-color: var(--ejidal-dark) !important; border-color: var(--ejidal-dark) !important; }
        .pagination .page-link { color: var(--ejidal-dark); border-radius: 5px; margin: 0 2px; }

        /* Scrollbar suave para tablas */
        .table-responsive::-webkit-scrollbar { height: 8px; }
        .table-responsive::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 10px; }
    </style>

    <div class="d-flex justify-content-between align-items-center pt-4 pb-3 mb-4">
        <h1 class="h3 text-header-main m-0">
            <i class="fas fa-hand-holding-usd text-success me-2"></i> Descuentos por Asambleas
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item small">Administración</li>
                <li class="breadcrumb-item small active">Descuentos</li>
            </ol>
        </nav>
    </div>

    {{-- Buscador Optimizado --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('descuentos.asambleas') }}" class="row g-2">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="query" class="form-control border-start-0 ps-0" placeholder="Buscar ejidatario por nombre o apellido..." value="{{ request('query') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-ejidal w-100 fw-bold">
                        Filtrar Registros
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Listado Principal --}}
    <div class="card shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center">
                <i class="fas fa-users-cog me-2"></i>
                <span>Control de Asistencias y Multas</span>
            </div>
            <span class="badge bg-white text-dark fw-normal" style="font-size: 0.75rem;">
                <i class="fas fa-sync-alt me-1 text-success"></i> Sincronizado
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                    <tr>
                        <th class="ps-4" style="min-width: 250px;">Ejidatario / Estado</th>
                        @foreach($catalogoMultas as $m)
                            <th class="text-center">{{ $m->Tipo }}</th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($ejidatarios as $ejidatario)
                        @php
                            $descPorId = $ejidatario->descuentos->keyBy('Id_MultaC');
                            $faltasCount = $ejidatario->pasesLista->where('Estatus', 'Falta')->count();
                        @endphp
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="user-name fw-bold mb-1">
                                    {{ $ejidatario->usuario?->Nombres }} {{ $ejidatario->usuario?->Apellido_Paterno }}
                                </div>
                                @if($faltasCount > 0)
                                    <span class="badge bg-soft-danger text-danger border border-danger-subtle px-2 py-1" style="font-size: 0.7rem; background-color: #fff5f5;">
                                            <i class="fas fa-times-circle me-1"></i> {{ $faltasCount }} Faltas Registradas
                                        </span>
                                @else
                                    <span class="badge bg-soft-success text-success border border-success-subtle px-2 py-1" style="font-size: 0.7rem; background-color: #f0fff4;">
                                            <i class="fas fa-check-circle me-1"></i> Sin Faltas
                                        </span>
                                @endif
                            </td>

                            @foreach($catalogoMultas as $m)
                                @php
                                    $monto = $descPorId->get($m->Id_MultaC)->Descuento ?? 0;
                                @endphp
                                <td class="text-center">
                                    @if($monto > 0)
                                        <div class="amount-badge">
                                            ${{ number_format($monto, 2) }}
                                        </div>
                                    @else
                                        <span class="text-muted opacity-50 small">--</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($catalogoMultas) + 1 }}" class="text-center py-5">
                                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="80" class="opacity-25 mb-3">
                                <p class="text-muted">No se encontraron resultados para la búsqueda.</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-light border-top d-flex justify-content-between align-items-center py-3">
            <div class="small text-secondary">
                Mostrando página <b>{{ $ejidatarios->currentPage() }}</b> de <b>{{ $ejidatarios->lastPage() }}</b>
            </div>
            <div>
                {{ $ejidatarios->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

@endsection