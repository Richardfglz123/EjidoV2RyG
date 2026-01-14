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

    <!-- FILTROS DE REPORTE -->
    <div class="card card-ejidal mb-3">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-filter me-2"></i> Reportes
        </div>
        <div class="card-body">
            <form id="filtrosForm" class="row g-2">
                <div class="col-md-3">
                    <label class="form-label">Desde</label>
                    <input type="date" name="fecha_inicio" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="fecha_fin" class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Mes</label>
                    <input type="number" name="mes" class="form-control" placeholder="1-12">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Año</label>
                    <input type="number" name="anio" class="form-control" placeholder="2026">
                </div>

                <div class="col-md-2 d-grid align-items-end">
                    <button type="button" id="pdfBtn" class="btn btn-danger mb-1">
                        <i class="fas fa-file-pdf me-1"></i> PDF
                    </button>
                    <button type="button" id="excelBtn" class="btn btn-success">
                        <i class="fas fa-file-excel me-1"></i> Excel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- BÚSQUEDA -->
    <div class="card card-ejidal mb-4">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-search me-2"></i> Búsqueda</span>
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

    <!-- TABLA -->
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
                                <a href="{{ asset('storage/'.$r->Evidencia) }}" target="_blank"
                                   class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-file-alt"></i> Ver
                                </a>
                            @else
                                <span class="text-muted small">Sin archivo</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group shadow-sm">
                                <a href="{{ route('datos_historicos.edit', $r->Id_DatosH) }}"
                                   class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('datos_historicos.destroy', $r->Id_DatosH) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('¿Eliminar registro?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            No se encontraron registros.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

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
