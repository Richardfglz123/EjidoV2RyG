@extends('cpanel/plantilla')
@section('title','Ejido San Rafael Ixtapalucan - Ejidatarios')

@section('content')

    @php
        $sesionActual = session('usuario', session('2fa_user', []));
        $misPermisos = $sesionActual['permisos'] ?? [];
        $miRol = strtolower(trim($sesionActual['rol'] ?? ''));
        $esAdmin = ($miRol === 'administrador' || ($sesionActual['id_rol'] ?? null) == 2);

        $puedeCrear = $esAdmin || in_array('ejidatarios_crear', $misPermisos);
        $puedeEditar = $esAdmin || in_array('ejidatarios_editar', $misPermisos);
        $puedeEliminar = $esAdmin || in_array('ejidatarios_eliminar', $misPermisos);
    @endphp

    <style>
        .text-header-main { color: #000000 !important; font-weight: normal !important; }

        /* Paginador */
        .pagination .page-item.active .page-link {
            background-color: #198754 !important;
            border-color: #198754 !important;
            color: #ffffff !important;
        }
        .pagination .page-link { color: #198754 !important; }

        /* Evitar saltos de página */
        .table tr, .table td { transition: none !important; transform: none !important; }
        body.modal-open { overflow: hidden !important; padding-right: 0 !important; }

        /* Estilo del contenedor del nombre en el modal */
        .qr-nombre-ejidatario {
            font-size: 0.95rem;
            color: #198754;
            letter-spacing: 0.5px;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }

        .qr-raw-text {
            display: block;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 8px;
            font-family: monospace;
            font-size: 10px;
            color: #6c757d;
            word-break: break-all;
            border-radius: 4px;
        }
    </style>

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-header-main">
            <i class="fas fa-users me-2"></i> Listado de Ejidatarios
        </h1>
        @if($puedeCrear)
            <a href="{{ route('Ejidatarios.create') }}" class="btn btn-ejidal shadow-sm">
                <i class="fas fa-plus-circle me-1"></i> Nuevo Ejidatario
            </a>
        @endif
    </div>

    {{-- Buscador --}}
    <div class="card card-ejidal mb-4">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-search me-2"></i> Búsqueda de Ejidatarios
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('Ejidatarios.index') }}" class="row g-3">
                <div class="col-md-10">
                    <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre, CURP o RFC..." value="{{ request('buscar') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-ejidal w-100">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-ejidal shadow-sm">
        <div class="card-header card-header-ejidal">
            <span><i class="fas fa-list me-2"></i> Ejidatarios Registrados</span>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th class="ps-3 text-center" style="width: 70px;">#</th>
                    <th>Datos del Ejidatario</th>
                    <th class="d-none d-md-table-cell">Identificación</th>
                    <th class="text-center d-none d-md-table-cell">Asistencia QR</th>
                    <th class="text-center d-none d-md-table-cell">Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
                </thead>
                <tbody>
                @forelse($data as $fila)
                    @php
                        // Limpieza y construcción del nombre completo
                        $nombrePartes = [$fila->Nombres, $fila->Apellido_Paterno, $fila->Apellido_Materno];
                        $nombreCompleto = implode(' ', array_filter($nombrePartes));
                        $nombreLimpio = preg_replace('/\s+/', ' ', trim(str_ireplace(['\n', "\n", "\r"], ' ', $nombreCompleto)));
                    @endphp
                    <tr>
                        <td class="ps-3 text-center fw-bold text-muted">{{ $fila->Num_Ejidatario }}</td>
                        <td>
                            <div class="text-dark fw-bold text-uppercase">{{ $nombreLimpio }}</div>

                            {{-- Bloque visible ÚNICAMENTE en móviles --}}
                            <div class="d-md-none mt-2">
                                <div class="small text-muted mb-1"><strong>CURP:</strong> {{ $fila->CURP ?? 'N/A' }}</div>
                                <div class="small text-muted mb-2"><strong>RFC:</strong> {{ $fila->RFC ?? 'N/A' }}</div>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <span class="badge {{ $fila->NombreEstatus == 'Activo' ? 'bg-success' : 'bg-info' }}">
                                        {{ strtoupper($fila->NombreEstatus) }}
                                    </span>
                                    @if(!empty($fila->qr_payload))
                                        <button type="button" class="btn btn-xs btn-outline-dark py-0 px-2 small" style="font-size: 0.75rem;" data-bs-toggle="modal" data-bs-target="#modalQR{{ $fila->Id_Ejidatario }}">
                                            <i class="fas fa-qrcode"></i> VER QR
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Celdas ocultas en móvil, visibles desde tablets en adelante --}}
                        <td class="d-none d-md-table-cell">
                            <div class="small"><strong>CURP:</strong> {{ $fila->CURP ?? 'N/A' }}</div>
                            <div class="small"><strong>RFC:</strong> {{ $fila->RFC ?? 'N/A' }}</div>
                        </td>
                        <td class="text-center d-none d-md-table-cell">
                            @if(!empty($fila->qr_payload))
                                <button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#modalQR{{ $fila->Id_Ejidatario }}">
                                    <i class="fas fa-qrcode"></i> VER QR
                                </button>
                            @else
                                <span class="text-muted small">Sin QR</span>
                            @endif
                        </td>
                        <td class="text-center d-none d-md-table-cell">
                            <span class="badge {{ $fila->NombreEstatus == 'Activo' ? 'bg-success' : 'bg-info' }}">
                                {{ strtoupper($fila->NombreEstatus) }}
                            </span>
                        </td>

                        <td class="text-center">
                            <div class="btn-group">
                                @if($puedeEditar)
                                    <a href="{{ route('Ejidatarios.edit', $fila->Id_Ejidatario) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                @endif
                                @if($puedeEliminar)
                                    <form action="{{ route('Ejidatarios.destroy', $fila->Id_Ejidatario) }}" method="post" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar?')"><i class="fas fa-trash"></i></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-5">No hay registros.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-light border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
            <div>
                <a href="{{ route('reportes.ejidatarios.pdf') }}" class="btn btn-outline-danger btn-sm me-2" target="_blank"><i class="fas fa-file-pdf"></i> PDF</a>
                <a href="{{ route('reportes.ejidatarios.excel') }}" class="btn btn-outline-success btn-sm"><i class="fas fa-file-excel"></i> Excel</a>
            </div>
            <div class="pagination-sm">
                {{ $data->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    {{-- JS--}}
    @foreach($data as $fila)
        @if(!empty($fila->qr_payload))
            @php
                $nombrePartes = [$fila->Nombres, $fila->Apellido_Paterno, $fila->Apellido_Materno];
            @endphp
            <div class="modal fade" id="modalQR{{ $fila->Id_Ejidatario }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-sm p-3">
                    <div class="modal-content shadow-lg border-0">
                        <div class="modal-header border-0 pb-0">
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center pt-0">
                            <h6 class="fw-bold text-muted mb-3">CREDENCIAL DIGITAL QR</h6>

                            {{-- Generación del QR --}}
                            <div class="p-3 border bg-white shadow-sm d-inline-block mb-3 rounded">
                                {!! QrCode::size(180)->generate($fila->qr_payload) !!}
                            </div>

                            {{-- Nombre Completo debajo del QR --}}
                            <div class="qr-nombre-ejidatario fw-bold text-uppercase mb-2">
                                {{ $nombreCompletoModal }}
                            </div>

                            <p class="text-muted mb-1" style="font-size: 10px; font-weight: bold;">CÓDIGO DE VALIDACIÓN:</p>
                            <div class="qr-raw-text">{{ $fila->qr_payload }}</div>
                        </div>

                        {{-- Footer corregido: botones limpios y alineados --}}
                        <div class="modal-footer border-0 justify-content-center pb-4">
                            <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i> Cerrar
                            </button>

                            <a href="{{ route('Ejidatarios.reimprimirGafete', $fila->Id_Ejidatario) }}"
                               class="btn btn-primary btn-sm px-3"
                               target="_blank">
                                <i class="fas fa-print me-1"></i> Reimprimir Gafete
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

@endsection