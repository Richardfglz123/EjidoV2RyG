@extends('cpanel.plantilla')
@section('title', 'Expedientes')

@section('content')

    <div class="card card-ejidal mb-4">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-folder-open me-2"></i> Gestión de Expedientes</span>
            <a href="{{ route('expedientes.create') }}" class="btn btn-ejidal text-white">
                <i class="fas fa-plus me-1"></i> Nuevo Expediente
            </a>
        </div>

        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                        <input
                                type="text"
                                id="searchExpediente"
                                class="form-control"
                                placeholder="Buscar por nombre, documento o dato...">
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle">
                    <thead>
                    <tr>
                        <th>Ejidatario</th>
                        <th>Documento</th>
                        <th>Archivo</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    {{-- Usamos $expedientes, asegúrate de enviar esta variable desde el controlador --}}
                    @forelse($expedientes as $expediente)
                        <tr class="fila-expediente" data-search="{{ strtolower($expediente->usuario->Nombres ?? '') }}">
                            <td>{{ $expediente->usuario->Nombres ?? 'Sin nombre' }} {{ $expediente->usuario->Apellido_Paterno ?? '' }}</td>
                            <td>{{ $expediente->nombre_documento }}</td>
                            <td>
                                <a href="{{ route('ver.expediente', ['path' => $expediente->ruta_archivo]) }}" target="_blank" class="btn btn-sm btn-info">
                                    <i class="fas fa-file-pdf"></i> Ver PDF
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('expedientes.edit', $expediente->Id_Documento) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <form action="{{ route('expedientes.destroy', $expediente->Id_Documento) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Está seguro de eliminar este expediente?');">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No se encontraron expedientes.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('searchExpediente').addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            const filas = document.querySelectorAll('.fila-expediente');
            filas.forEach(fila => {
                const contenido = fila.getAttribute('data-search');
                fila.classList.toggle('d-none', !contenido.includes(term));
            });
        });
    </script>

@endsection