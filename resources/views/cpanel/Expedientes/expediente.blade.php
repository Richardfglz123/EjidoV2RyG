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
                    @forelse($expedientes as $expediente)
                        <tr>
                            <td>{{ $expediente->usuario->Nombres ?? 'N/A' }} {{ $expediente->usuario->Apellido_Paterno ?? '' }}</td>
                            <td>{{ $expediente->nombre_documento }}</td>
                            <td>
                                <a href="{{ route('ver.expediente', ['path' => $expediente->ruta_archivo]) }}" target="_blank" class="btn btn-sm btn-info">Ver PDF</a>
                            </td>
                            <td>
                                <a href="{{ route('expedientes.edit', $expediente->Id_Documento) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                <form action="{{ route('expedientes.destroy', $expediente->Id_Documento) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Seguro?');">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">No hay expedientes.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection