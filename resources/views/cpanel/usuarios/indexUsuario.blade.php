@extends('cpanel/plantilla')
@section('title','Ejido San Rafael Ixtapalucan')

@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-users me-2"></i> Listado de Usuarios
        </h1>

        <a href="{{ route('Usuarios.create') }}" class="btn btn-ejidal">
            <i class="fas fa-user-plus me-1"></i> Nuevo Usuario
        </a>
    </div>

    <div class="card card-ejidal">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-2"></i> Usuarios Registrados</span>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                <tr>
                    <th>Nombres</th>
                    <th>Apellido Paterno</th>
                    <th>Apellido Materno</th>
                    <th>Usuario</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th class="text-center">Acciones</th>
                </tr>
                </thead>
                <tbody>
                @forelse($data as $fila)
                    <tr>
                        <td>{{ $fila->Nombres }}</td>
                        <td>{{ $fila->Apellido_Paterno }}</td>
                        <td>{{ $fila->Apellido_Materno }}</td>
                        <td>{{ $fila->Usuario }}</td>
                        <td>{{ $fila->Correo }}</td>
                        <td>{{ $fila->Telefono }}</td>
                        <td class="text-center">
                            <div class="btn-group shadow-sm">
                                <a href="{{ url('/admon/Usuarios/'.$fila->Id_Usuario.'/edit') }}"
                                   class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <form action="{{ url('/admon/Usuarios/'.$fila->Id_Usuario) }}" method="post" class="d-inline">
                                    @csrf
                                    {{ method_field('DELETE') }}
                                    <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('¿Estás seguro que lo quieres eliminar?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            No hay usuarios registrados.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            <div class="d-flex justify-content-center mt-2 small">
                {{ $data->links('pagination::bootstrap-4') }}
            </div>
        </div>

        <div class="card-footer bg-transparent border-0">
            <a href="{{ route('reportes.usuarios.pdf') }}" class="btn btn-primary btn-sm" target="_blank">
                <i class="fas fa-file-pdf me-1"></i> Genrar PDF
            </a>
            <a href="{{ route('reportes.usuarios.excel') }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> Generar Excel
            </a>
        </div>
    </div>

@endsection
