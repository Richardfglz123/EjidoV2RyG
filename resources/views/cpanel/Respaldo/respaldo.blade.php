@extends('cpanel/plantilla')
@section('title', 'Respaldos de Base de Datos')

@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-database me-2"></i> Respaldo
        </h1>
    </div>

    <div class="card card-ejidal mb-4">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-file-export me-2"></i> Crear Nuevo Respaldo
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <p class="mb-0">Se generará un archivo <code>.sql</code> con toda la estructura y datos actuales del sistema (Tablas, Ejidatarios, Usuarios, etc.).</p>
                    <small class="text-muted">El proceso puede tardar unos segundos dependiendo del tamaño de la información.</small>
                </div>
                <div class="col-md-4 text-end">
                    <form action="{{ route('Respaldos.store') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-ejidal">
                            <i class="fas fa-plus-circle me-1"></i> Generar Ahora
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-ejidal">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-history me-2"></i> Historial de Respaldos
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                    <tr>
                        <th class="ps-3">Nombre del Archivo</th>
                        <th>Tamaño</th>
                        <th>Fecha de Creación</th>
                        <th class="text-end pe-3">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @isset($respaldos)
                        @foreach($respaldos as $r)
                            <tr>
                                <td class="ps-3">
                                    <i class="far fa-file-code text-success me-2"></i>
                                    {{ $r['nombre'] }}
                                </td>
                                <td>{{ $r['tamaño'] }}</td>
                                <td>{{ $r['fecha'] }}</td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('Respaldos.download', $r['nombre']) }}" class="btn btn-sm btn-outline-primary" title="Descargar">
                                        <i class="fas fa-download"></i>
                                    </a>

                                    <form action="{{ route('Respaldos.destroy', $r['nombre']) }}" method="POST" class="d-inline form-eliminar">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                No se encontraron archivos de respaldo disponibles
                            </td>
                        </tr>
                    @endisset
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.form-eliminar').forEach(form => {
            form.addEventListener('submit', function(e) {
                if(!confirm('¿Estás seguro de eliminar este respaldo? Esta acción no se puede deshacer.')) {
                    e.preventDefault();
                }
            });
        });
    </script>

@endsection