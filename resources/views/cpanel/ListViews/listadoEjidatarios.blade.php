@extends('cpanel.plantilla')
@section('title', 'Listado de Ejidatarios')
@section('content')

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card perfil-card shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-users me-2"></i> Padrón de Ejidatarios</span>
            <div class="btn-group">
                <a href="{{ route('ejidatarios.create') }}" class="btn btn-light btn-sm text-dark fw-bold">
                    <i class="fas fa-plus-circle me-1"></i> Nuevo Ejidatario
                </a>
                <button type="button" class="btn btn-light btn-sm ms-1" title="Exportar Lista">
                    <i class="fas fa-file-download"></i>
                </button>
            </div>
        </div>

        <div class="card-body p-0">
            {{-- Contenedor con scroll para tablas anchas --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="min-width: 1200px;">
                    <thead class="bg-light text-ejidal">
                    <tr>
                        <th class="ps-3">No.</th>
                        <th>Nombre Completo</th>
                        <th>CURP / RFC</th>
                        <th>Contacto</th>
                        <th>Fechas (Nac./Ing.)</th>
                        <th>Estatus</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($ejidatarios as $e)
                        <tr>
                            <td class="ps-3">
                                <span class="badge bg-secondary">#{{ $e->numeroEjidatario }}</span>
                            </td>
                            <td>
                                <div class="fw-bold">{{ $e->nombre }}</div>
                                <small class="text-muted text-uppercase">{{ $e->apellidoPaterno }} {{ $e->apellidoMaterno }}</small>
                            </td>
                            <td>
                                <div class="small"><strong>CURP:</strong> {{ $e->curp }}</div>
                                <div class="small"><strong>RFC:</strong> {{ $e->rfc }}</div>
                            </td>
                            <td>
                                <div class="small"><i class="fas fa-phone me-1 text-muted"></i>{{ $e->telefono }}</div>
                                <div class="small"><i class="fas fa-envelope me-1 text-muted"></i>{{ $e->email }}</div>
                            </td>
                            <td>
                                <div class="small"><strong>Nac:</strong> {{ $e->fechaNacimiento }}</div>
                                <div class="small"><strong>Ing:</strong> {{ $e->fechaIngreso }}</div>
                            </td>
                            <td>
                                @if($e->idEstatus == 1)
                                    <span class="badge rounded-pill bg-success">Activo</span>
                                @elseif($e->idEstatus == 2)
                                    <span class="badge rounded-pill bg-danger">Baja</span>
                                @else
                                    <span class="badge rounded-pill bg-warning text-dark">Suspendido</span>
                                @endif
                            </td>
                            <td class="text-center px-3">
                                <div class="btn-group">
                                    <a href="{{ route('ejidatarios.edit', $e->id) }}" class="btn btn-outline-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-users-slash fa-3x mb-3 d-block"></i>
                                No hay ejidatarios registrados en el padrón.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
