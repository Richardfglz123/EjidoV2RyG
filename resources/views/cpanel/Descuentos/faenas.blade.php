@extends('cpanel/plantilla')
@section('title','Control de Asistencia - Faenas')

@section('content')

    <style>
        .text-header-main { color: #000000 !important; font-weight: normal !important; }
        .pagination .page-item.active .page-link {
            background-color: #198754 !important;
            border-color: #198754 !important;
            color: #ffffff !important;
        }
        .pagination .page-link {
            color: #198754 !important;
        }

        .table tr, .table td {
            transition: none !important;
            transform: none !important;
        }

        .avatar-placeholder {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #dee2e6;
        }

        /* Estilos de los Badges de Asistencia */
        .badge-asistencia {
            font-size: 0.75rem;
            padding: 0.5em 0.8em;
            border-radius: 50rem;
            text-transform: uppercase;
            font-weight: bold;
        }
    </style>

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-header-main">
            <i class="fas fa-clipboard-check me-2"></i> Asistencia de Faenas
        </h1>
    </div>

    <div class="card card-ejidal mb-4">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-search me-2"></i> Búsqueda de Ejidatarios
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('descuentos.faenas') }}" class="row g-3">
                <div class="col-md-10">
                    <label class="form-label">Nombre o Apellido</label>
                    <input type="text" name="query" class="form-control" placeholder="Buscar ejidatario..." value="{{ request('query') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-ejidal w-100">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Listado Estilo Ejidal --}}
    <div class="card card-ejidal shadow-sm">
        <div class="card-header card-header-ejidal">
            <span><i class="fas fa-list me-2"></i> Estatus de Asistencia</span>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                <tr class="small text-uppercase">
                    <th class="ps-3 text-center" style="width: 70px;">Ref</th>
                    <th>Ejidatario</th>
                    @foreach($eventosFaenas as $evento)
                        <th class="text-center">{{ $evento->Nombre_Evento }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @forelse($ejidatarios as $ejidatario)
                    @php
                        $idsAsistidos = $ejidatario->asistencias_confirmadas ?? [];
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

                        @foreach($eventosFaenas as $evento)
                            @php
                                $asistio = in_array($evento->Id_Evento, $idsAsistidos);
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
                        <td colspan="{{ count($eventosFaenas) + 2 }}" class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fa-3x mb-3 d-block"></i>
                            No se encontraron registros.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-light border-top d-flex justify-content-end align-items-center">
            <div class="pagination-sm">
                {{ $ejidatarios->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

@endsection