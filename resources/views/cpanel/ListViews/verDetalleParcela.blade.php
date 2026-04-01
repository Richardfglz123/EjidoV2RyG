@extends('cpanel.plantilla')
@section('title', 'Detalle de Parcela')
@section('content')

    @php
        $sesion = session('usuario') ?? session('2fa_user') ?? [];
        $rolNombre = strtolower(trim($sesion['rol'] ?? ''));
        $rolId = $sesion['id_rol'] ?? $sesion['Id_Rol'] ?? null;
        $permisos = $sesion['permisos'] ?? [];

        $esAdmin = ($rolNombre === 'administrador' || $rolId == 2);
        $puedeEditar = in_array('usuarios_editar', $permisos);
    @endphp

    <div class="container-fluid">
        {{-- Cabecera de Página --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="text-ejidal mb-0">
                <i class="fas fa-search-location me-2"></i>Consulta Detallada de Parcela
            </h3>
            <a href="{{ route('parcelas.index') }}" class="btn btn-ejidal shadow-sm">
                <i class="fas fa-list-ul me-1"></i> Volver al Listado
            </a>
        </div>

        <div class="row g-4">
            {{-- Columna: Información Técnica y Propietario --}}
            <div class="col-lg-7">
                <div class="card card-ejidal h-100 shadow-sm">
                    <div class="card-header card-header-ejidal">
                        <i class="fas fa-id-card me-2"></i>Ficha Técnica del Inmueble
                    </div>
                    <div class="card-body">
                        <table class="table table-hover border">
                            <tbody>
                            <tr>
                                <th class="bg-light w-40 text-secondary">No. de Parcela:</th>
                                <td class="fw-bold fs-5 text-primary">#{{ $parcela->No_Parcela }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light text-secondary">Ejidatario Titular:</th>
                                <td>
                                    <i class="fas fa-user-circle me-1 text-muted"></i>
                                    {{ $parcela->ejidatario->usuario->Nombres ?? 'N/A' }}
                                    {{ $parcela->ejidatario->usuario->Apellido_Paterno ?? '' }}
                                    {{ $parcela->ejidatario->usuario->Apellido_Materno ?? '' }}
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light text-secondary">Superficie Registrada:</th>
                                <td class="fw-bold">{{ number_format($parcela->Superficie, 2) }} m²</td>
                            </tr>
                            <tr>
                                <th class="bg-light text-secondary">Uso de Suelo:</th>
                                <td><span class="badge bg-success">{{ $parcela->usoSuelo->Nombre_Uso ?? 'General' }}</span></td>
                            </tr>
                            <tr>
                                <th class="bg-light text-secondary">Ubicación / Paraje:</th>
                                <td>{{ $parcela->Ubicacion ?? 'Dato no disponible' }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Columna: Información Administrativa RAN --}}
            <div class="col-lg-5">
                <div class="card card-ejidal h-100 shadow-sm">
                    <div class="card-header card-header-ejidal bg-danger">
                        <i class="fas fa-university me-2"></i>Datos Administrativos (RAN)
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="text-muted fw-bold small mb-1">Número de Inscripción RAN</label>
                            <div class="h5 p-3 border rounded bg-white text-dark shadow-xs">
                                {{ $parcela->infAdmin->Num_InscripcionRAN ?? '---' }}
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="text-muted fw-bold small mb-1">Clave Núcleo Agrario</label>
                            <div class="h5 p-3 border rounded bg-white text-dark shadow-xs">
                                {{ $parcela->infAdmin->ClaveNucleoAgrario ?? '---' }}
                            </div>
                        </div>
                        <div>
                            <label class="text-muted fw-bold small mb-1">Comunidad / Asentamiento</label>
                            <div class="h5 p-3 border rounded bg-white text-dark shadow-xs">
                                {{ $parcela->infAdmin->Comunidad ?? '---' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Columna: Colindancias --}}
            <div class="col-md-6">
                <div class="card card-ejidal shadow-sm">
                    <div class="card-header card-header-ejidal">
                        <i class="fas fa-border-style me-2"></i>Límites y Colindancias
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Norte:</span> <span class="fw-bold">{{ $parcela->colindancia->norte ?? 'Sin datos' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Sur:</span> <span class="fw-bold">{{ $parcela->colindancia->sur ?? 'Sin datos' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Este:</span> <span class="fw-bold">{{ $parcela->colindancia->este ?? 'Sin datos' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Oeste:</span> <span class="fw-bold">{{ $parcela->colindancia->oeste ?? 'Sin datos' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Columna: Puntos de Control --}}
            <div class="col-md-6">
                <div class="card card-ejidal shadow-sm">
                    <div class="card-header card-header-ejidal">
                        <i class="fas fa-crosshairs me-2"></i>Vértices Geodésicos
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 230px;">
                            <table class="table table-sm mb-0">
                                <thead class="table-dark">
                                <tr>
                                    <th class="ps-3">Punto</th>
                                    <th>Coordenada X</th>
                                    <th>Coordenada Y</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($parcela->coordenadas as $coord)
                                    <tr>
                                        <td class="ps-3 fw-bold">{{ $coord->Punto }}</td>
                                        <td>{{ $coord->CoordenadaX }}</td>
                                        <td>{{ $coord->CoordenadaY }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center py-4 text-muted">No hay coordenadas disponibles</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Barra de Acciones Final --}}
        <div class="mt-4 mb-5 p-3 bg-white border rounded d-flex justify-content-end gap-3 shadow-sm">
            @if($esAdmin || $puedeEditar)
                <a href="{{ route('parcelas.editar', $parcela->Id_Parcela) }}" class="btn btn-warning px-5">
                    <i class="fas fa-edit me-2"></i> Editar Registro
                </a>
            @endif
            <a href="{{ route('parcelas.index') }}" class="btn btn-secondary px-4">
                <i class="fas fa-times me-2"></i> Cerrar
            </a>
        </div>
    </div>

    <style>
        .text-ejidal { color: #2e7d32; }
        .card-ejidal { border: 1px solid #2e7d32; border-radius: 8px; overflow: hidden; }
        .card-header-ejidal { background-color: #2e7d32; color: white; font-weight: bold; }
        .btn-ejidal { background-color: #2e7d32; color: white; }
        .btn-ejidal:hover { background-color: #1b5e20; color: white; }
        .w-40 { width: 40%; }
        .shadow-xs { box-shadow: inset 0 1px 2px rgba(0,0,0,0.075); }
    </style>

@endsection