@extends('cpanel/plantilla')
@section('title','Ejidatarios')
@section('content')

    @php
        $sesionActual = session('usuario', session('2fa_user', []));
        $miRol = strtolower(trim($sesionActual['rol'] ?? ''));
        $esAdmin = ($miRol === 'administrador' || ($sesionActual['id_rol'] ?? null) == 2);
    @endphp

    <style>
        .text-ejidal { color: #198754 !important; font-weight: 700; }
        .card-ejidal { border-color: #198754 !important; border-radius: 8px; }
        .card-header-ejidal { background-color: #198754 !important; color: white !important; font-weight: 600; }

        .fila-ejidatario, .card, .table, tr, td {
            transition: none !important;
            transform: none !important;
        }

        .fila-ejidatario:hover {
            background-color: rgba(25, 135, 84, 0.05) !important;
            transform: none !important;
        }

        .qr-raw-text {
            display: block;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 8px;
            font-family: monospace;
            font-size: 11px;
            color: #d63384;
            white-space: normal !important;
            text-align: center;
            word-break: break-all;
        }

        .btn-ver-qr {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .pagination { margin: 0; font-size: 13px; }
        .page-link { color: #198754; }
        .page-item.active .page-link { background-color: #198754; border-color: #198754; }
    </style>

    <div class="card card-ejidal shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-users me-2"></i> Lista de Ejidatarios</span>
            <a href="{{ route('Ejidatarios.create') }}" class="btn btn-sm btn-light fw-bold text-success shadow-sm">
                <i class="fas fa-plus-circle me-1"></i> Nuevo Ejidatario
            </a>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-striped align-middle mb-0">
                <thead class="bg-light">
                <tr>
                    <th class="ps-3" style="width: 60px;">#</th>
                    <th>Nombre Completo</th>
                    <th>CURP / RFC</th>
                    <th class="text-center">Código QR</th>
                    <th class="text-center">Estatus</th>
                    <th class="text-center">Acciones</th>
                </tr>
                </thead>
                <tbody>
                @foreach($data as $fila)
                    @php
                        // Limpieza de caracteres \N y saltos de línea para que se vea limpio
                        $nombreCompleto = $fila->Nombres . ' ' . $fila->Apellido_Paterno . ' ' . $fila->Apellido_Materno;
                        $nombreLimpio = str_ireplace(['\n', "\n", "\r"], ' ', $nombreCompleto);
                        $nombreLimpio = preg_replace('/\s+/', ' ', trim($nombreLimpio));

                        $payloadLimpio = str_ireplace(['\n', "\n", "\r"], ' ', $fila->qr_payload ?? '');
                    @endphp
                    <tr class="fila-ejidatario">
                        <td class="ps-3 fw-bold text-muted">{{ $fila->Num_Ejidatario }}</td>
                        <td>
                            <div class="fw-bold text-uppercase">{{ $nombreLimpio }}</div>
                            <small class="text-muted">ID: {{ $fila->Id_Usuario }}</small>
                        </td>
                        <td>
                            <small class="d-block"><strong>Curp:</strong> {{ $fila->CURP ?? 'N/A' }}</small>
                            <small class="d-block"><strong>RFC:</strong> {{ $fila->RFC ?? 'N/A' }}</small>
                        </td>

                        <td class="text-center">
                            @if(!empty($fila->qr_payload))
                                <button type="button" class="btn btn-sm btn-outline-dark btn-ver-qr" data-bs-toggle="modal" data-bs-target="#modalQR{{ $fila->Id_Ejidatario }}">
                                    <i class="fas fa-qrcode"></i> VER QR
                                </button>

                                <div class="modal fade" id="modalQR{{ $fila->Id_Ejidatario }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-sm">
                                        <div class="modal-content">
                                            <div class="modal-header border-0 pb-0">
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-center pt-0">
                                                <h6 class="fw-bold mb-3">QR DE ASISTENCIA</h6>
                                                <div class="p-2 border bg-white shadow-sm d-inline-block mb-3">
                                                    {!! QrCode::size(180)->generate($fila->qr_payload) !!}
                                                </div>
                                                <p class="small fw-bold text-uppercase mb-1">{{ $nombreLimpio }}</p>
                                                <hr>
                                                <p class="text-start mb-1" style="font-size: 10px; font-weight: bold;">Nombre:</p>
                                                <div class="qr-raw-text">{{ $payloadLimpio }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted small italic">Sin QR</span>
                            @endif
                        </td>

                        <td class="text-center">
                            <span class="badge {{ $fila->NombreEstatus == 'Activo' ? 'bg-success' : 'bg-info' }}">
                                {{ $fila->NombreEstatus }}
                            </span>
                        </td>

                        <td class="text-center">
                            <a href="{{ route('Ejidatarios.edit', $fila->Id_Ejidatario) }}" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-edit"></i>
                            </a>

                            @if($esAdmin)
                                <form action="{{ route('Ejidatarios.destroy', $fila->Id_Ejidatario) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar ejidatario?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white border-top-0 p-3 d-flex justify-content-between align-items-center">
            <div>
                <a href="{{ route('reportes.ejidatarios.pdf') }}" class="btn btn-sm btn-ejidal shadow-sm" target="_blank">
                    <i class="fas fa-file-pdf me-1"></i> Generar PDF
                </a>
            </div>
            <div>
                {{ $data->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection