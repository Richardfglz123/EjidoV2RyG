@extends('cpanel/plantilla')
@section('title','Descuento de Asambleas')

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
        .table td { vertical-align: middle; white-space: nowrap; padding: 15px 10px !important; }

        .user-name { font-size: 1.05rem; letter-spacing: 0.3px; }

        .avatar-placeholder {
            width: 40px; height: 40px; border-radius: 50%;
            background-color: #f8f9fa; display: flex;
            align-items: center; justify-content: center; border: 1px solid #dee2e6;
        }

        .badge-asistencia {
            font-size: 0.75rem; padding: 0.5em 0.8em;
            border-radius: 50rem; text-transform: uppercase; font-weight: bold;
        }
    </style>

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-header-main">
            <i class="fas fa-users text-success me-2"></i> Descuento Asambleas
        </h1>
    </div>

    {{-- Buscador estilo Usuarios/Faenas --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-search me-2"></i> Búsqueda de Ejidatarios
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('descuentos.asambleas') }}" class="row g-3">
                <div class="col-md-10">
                    <input type="text" name="query" class="form-control" placeholder="Buscar ejidatario por nombre..." value="{{ request('query') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-ejidal w-100">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Listado Principal --}}
    <div class="card shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-2"></i> Estatus de Asistencia</span>
            <span class="badge bg-light text-dark">Módulo Asambleas</span>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                <tr class="small text-uppercase">
                    <th class="ps-3 text-center" style="width: 70px;">Ref</th>
                    <th style="min-width: 250px;">Ejidatario</th>
                    {{-- Usamos $eventosAsambleas que viene del controlador --}}
                    @forelse($eventosAsambleas as $ev)
                        <th class="text-center">{{ $ev->Nombre_Evento }}</th>
                    @empty
                        <th class="text-center text-muted">No hay asambleas registradas</th>
                    @endforelse
                </tr>
                </thead>
                <tbody>
                @forelse($ejidatarios as $ejidatario)
                    @php
                        $idsAsistidos = $ejidatario->asistencias_asambleas ?? [];
                    @endphp
                    <tr>
                        <td class="ps-3 text-center">
                            <div class="avatar-placeholder mx-auto">
                                <i class="fas fa-user text-muted"></i>
                            </div>
                        </td>

                        <td>
                            <div class="text-dark fw-bold">
                                {{ $ejidatario->usuario?->Nombres }}
                            </div>
                            <div class="small text-muted text-uppercase">
                                {{ $ejidatario->usuario?->Apellido_Paterno }} {{ $ejidatario->usuario?->Apellido_Materno }}
                            </div>
                        </td>

                        @foreach($eventosAsambleas as $ev)
                            @php
                                $asistio = in_array($ev->Id_Evento, $idsAsistidos);
                            @endphp
                            <td class="text-center">
                                @if($asistio)
                                    <span class="badge badge-asistencia bg-success">
                                        <i class="fas fa-check-circle me-1"></i> Asistió
                                    </span>
                                @else
                                    <span class="badge badge-asistencia bg-danger">
                                        <i class="fas fa-times-circle me-1"></i> No Asistió
                                    </span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($eventosAsambleas) + 2 }}" class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fa-3x mb-3 d-block"></i>
                            No se encontraron registros de ejidatarios.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-light border-top d-flex justify-content-between align-items-center py-3">
            <div class="small text-muted">
                Mostrando <b>{{ $ejidatarios->count() }}</b> registros.
            </div>
            <div class="pagination-sm">
                {{ $ejidatarios->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

@endsection