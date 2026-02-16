@extends('cpanel/plantilla')
@section('title','Ejido San Rafael Ixtapalucan')

@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-users me-2"></i> Listado de Usuarios
        </h1>

        <a href="{{ route('Usuarios.create') }}" class="btn btn-ejidal shadow-sm">
            <i class="fas fa-user-plus me-1"></i> Nuevo Usuario
        </a>
    </div>

    <div class="card card-ejidal mb-4 shadow-sm">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-search me-2"></i> Búsqueda y Filtros
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('Usuarios.index') }}" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label small fw-bold">Nombre</label>
                    <input
                        type="text"
                        name="nombre"
                        class="form-control"
                        placeholder="Buscar por nombre..."
                        value="{{ request('nombre') }}"
                    >
                </div>

                <div class="col-md-5">
                    <label class="form-label small fw-bold">Apellido</label>
                    <input
                        type="text"
                        name="apellido"
                        class="form-control"
                        placeholder="Buscar por apellido..."
                        value="{{ request('apellido') }}"
                    >
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-ejidal w-100">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if(request()->filled('nombre') || request()->filled('apellido'))
        <div class="alert alert-info py-2 shadow-sm d-flex justify-content-between align-items-center">
            <span>
                <i class="fas fa-info-circle me-2"></i>
                Resultados encontrados: <strong>{{ $data->total() }}</strong>
            </span>
            <a href="{{ route('Usuarios.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar filtros</a>
        </div>
    @endif

    <div class="card card-ejidal shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-2"></i> Usuarios Registrados</span>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th class="ps-3">Nombres</th>
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
                        <td class="ps-3">{{ $fila->Nombres }}</td>
                        <td>{{ $fila->Apellido_Paterno }}</td>
                        <td>{{ $fila->Apellido_Materno }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $fila->Usuario }}</span></td>
                        <td>{{ $fila->Correo }}</td>
                        <td>{{ $fila->Telefono }}</td>
                        <td class="text-center">
                            <div class="btn-group shadow-sm" role="group">
                                <a href="{{ url('/admon/Usuarios/'.$fila->Id_Usuario.'/edit') }}"
                                   class="btn btn-warning btn-sm" title="Editar Usuario">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <form action="{{ url('/admon/Usuarios/'.$fila->Id_Usuario) }}" method="post" class="d-inline">
                                    @csrf
                                    {{ method_field('DELETE') }}
                                    <button class="btn btn-danger btn-sm"
                                            title="Eliminar Usuario"
                                            onclick="return confirm('¿Estás seguro que lo quieres eliminar?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="fas fa-folder-open fa-3x mb-3 d-block"></i>
                            No hay usuarios registrados con esos criterios.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-light border-top d-flex justify-content-between align-items-center">
            <div>
                <a href="{{ route('reportes.usuarios.pdf') }}" class="btn btn-outline-danger btn-sm me-2" target="_blank">
                    <i class="fas fa-file-pdf me-1"></i> Generar PDF
                </a>
                <a href="{{ route('reportes.usuarios.excel') }}" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-file-excel me-1"></i> Generar Excel
                </a>
            </div>

            <div class="pagination-sm">
                {{ $data->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

@endsection
