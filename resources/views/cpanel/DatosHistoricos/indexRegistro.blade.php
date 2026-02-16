@extends('cpanel/plantilla')
@section('title', 'Datos Históricos')

@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-history me-2"></i> Datos Históricos
        </h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Formulario de Reportes --}}
    <div class="card card-ejidal mb-3">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-filter me-2"></i> Reportes por Rango de Fechas
        </div>
        <div class="card-body">
            <form id="filtrosForm" class="row g-2">
                <div class="col-md-4">
                    <label class="form-label">Desde</label>
                    <input type="date" name="fecha_inicio" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="fecha_fin" class="form-control">
                </div>
                <div class="col-md-4 d-grid align-items-end">
                    <div class="btn-group">
                        <button type="button" id="pdfBtn" class="btn btn-danger">
                            <i class="fas fa-file-pdf me-1"></i> PDF
                        </button>
                        <button type="button" id="excelBtn" class="btn btn-success">
                            <i class="fas fa-file-excel me-1"></i> Excel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Buscador --}}
    <div class="card card-ejidal mb-4">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-search me-2"></i> Búsqueda General</span>
            <a href="{{ route('datos_historicos.create') }}" class="btn btn-light btn-sm">
                <i class="fas fa-plus-circle me-1"></i> Nuevo registro
            </a>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-10">
                    <input type="text" name="buscar" class="form-control"
                           placeholder="Buscar por título..." value="{{ request('buscar') }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-ejidal w-100">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla de Registros --}}
    <div class="card card-ejidal">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-list me-2"></i> Listado de Registros
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                <tr>
                    <th>Título</th>
                    <th>Descripción</th>
                    <th>Fecha</th>
                    <th>Evidencia</th>
                    <th class="text-center">Acciones</th>
                </tr>
                </thead>
                <tbody>
                @forelse($registros as $r)
                    <tr>
                        <td class="fw-bold">{{ $r->Titulo }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($r->Descripcion, 60, '...') }}</td>
                        <td>{{ \Carbon\Carbon::parse($r->Fecha)->format('d/m/Y') }}</td>

                        <td>
                            @if($r->Evidencia)
                                @php
                                    $datosEvidencia = json_decode($r->Evidencia, true);
                                    $fotos = (json_last_error() !== JSON_ERROR_NONE) ? [$r->Evidencia] : $datosEvidencia;
                                    $totalFotos = is_array($fotos) ? count($fotos) : 0;
                                @endphp

                                @if($totalFotos > 0)
                                    <button type="button" class="btn btn-sm btn-ejidal"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalGaleria{{ $r->Id_DatosH }}">
                                        <i class="fas fa-folder-open me-1"></i> Ver archivos ({{ $totalFotos }})
                                    </button>
                                @else
                                    <span class="text-muted small">Sin archivo</span>
                                @endif
                            @else
                                <span class="text-muted small">Sin archivo</span>
                            @endif
                        </td>

                        <td class="text-center">
                            <div class="btn-group shadow-sm">
                                <a href="{{ route('datos_historicos.edit', $r->Id_DatosH) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('datos_historicos.destroy', $r->Id_DatosH) }}" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">No hay registros.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modales de Galería --}}
    @foreach($registros as $r)
        @if($r->Evidencia)
            <div class="modal fade" id="modalGaleria{{ $r->Id_DatosH }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header card-header-ejidal text-white">
                            <h5 class="modal-title"><i class="fas fa-folder-open me-2"></i> Evidencia: {{ $r->Titulo }}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body bg-light">
                            <div class="row g-3">
                                @php
                                    $datosM = json_decode($r->Evidencia, true);
                                    $fotosM = (json_last_error() !== JSON_ERROR_NONE) ? [$r->Evidencia] : $datosM;
                                @endphp
                                @foreach($fotosM as $foto)
                                    <div class="col-md-4 col-6">
                                        <div class="card h-100 shadow-sm border-ejidal text-center p-2">
                                            @if(\Illuminate\Support\Str::endsWith(strtolower($foto), '.pdf'))
                                                <div class="d-flex flex-column align-items-center justify-content-center" style="height: 150px;">
                                                    <i class="fas fa-file-pdf fa-4x text-danger mb-2"></i>
                                                    <a href="{{ asset('storage/' . $foto) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                                                        Abrir PDF
                                                    </a>
                                                </div>
                                            @else
                                                <a href="{{ asset('storage/' . $foto) }}" target="_blank">
                                                    <img src="{{ asset('storage/' . $foto) }}" class="card-img-top img-fluid" style="height: 150px; object-fit: cover;">
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    <script>
        const form = document.getElementById('filtrosForm');

        document.getElementById('pdfBtn').onclick = () => {
            const params = new URLSearchParams(new FormData(form)).toString();
            window.open("{{ route('datos_historicos.reporte.pdf') }}?" + params, "_blank");
        };

        document.getElementById('excelBtn').onclick = () => {
            const params = new URLSearchParams(new FormData(form)).toString();
            window.location = "{{ route('datos_historicos.reporte.excel') }}?" + params;
        };
    </script>

@endsection