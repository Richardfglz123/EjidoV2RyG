@extends('cpanel/plantilla')
@section('title','Ejido San Rafael Ixtapalucan')

@section('content')

    @php
        // Detectamos la sesión activa (normal o tras 2FA)
        $sesionActual = session('usuario', session('2fa_user', []));
        $misPermisos = $sesionActual['permisos'] ?? [];
        $miId = $sesionActual['id'] ?? null;
        $miRol = strtolower(trim($sesionActual['rol'] ?? ''));

        // Lógica de Superusuario: El administrador siempre tiene acceso
        $esAdmin = ($miRol === 'administrador' || ($sesionActual['id_rol'] ?? null) == 2);

        $puedeCrear = $esAdmin || in_array('usuarios_crear', $misPermisos);
        $puedeEditar = $esAdmin || in_array('usuarios_editar', $misPermisos);
        $puedeEliminar = $esAdmin || in_array('usuarios_eliminar', $misPermisos);
    @endphp

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-users me-2"></i> Listado de Usuarios
        </h1>

        @if($puedeCrear)
            <a href="{{ route('Usuarios.create') }}" class="btn btn-ejidal shadow-sm">
                <i class="fas fa-user-plus me-1"></i> Nuevo Usuario
            </a>
        @endif
    </div>

    {{-- Buscador --}}
    <div class="card card-ejidal mb-4 shadow-sm">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-search me-2"></i> Búsqueda de Usuarios
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('Usuarios.index') }}" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label small fw-bold">Nombre</label>
                    <input type="text" name="nombre" class="form-control" placeholder="Buscar por nombre..." value="{{ request('nombre') }}">
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-bold">Apellido</label>
                    <input type="text" name="apellido" class="form-control" placeholder="Buscar por apellido..." value="{{ request('apellido') }}">
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
                    {{-- Solo el admin ve la cabecera del Rol --}}
                    <th>Usuario @if($esAdmin) / Rol @endif</th>
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
                        <td>
                            <div class="fw-bold text-dark mb-1">{{ $fila->Usuario }}</div>
                            @if($esAdmin)
                                @php
                                    $rolFila = strtolower(trim($fila->rol ?? ''));
                                    $badgeClass = str_contains($rolFila, 'admin') ? 'bg-danger' : (str_contains($rolFila, 'ejidatario') ? 'bg-success' : 'bg-secondary');
                                @endphp
                                <span class="badge {{ $badgeClass }} shadow-sm" style="font-size: 0.65rem;">
                                    <i class="fas fa-shield-alt me-1"></i> {{ strtoupper($fila->rol ?? 'SIN ROL') }}
                                </span>
                            @endif
                        </td>
                        <td>{{ $fila->Correo }}</td>
                        <td>{{ $fila->Telefono }}</td>
                        <td class="text-center">
                            <div class="btn-group shadow-sm">
                                @if($puedeEditar)
                                    <a href="{{ url('/admon/Usuarios/'.$fila->Id_Usuario.'/edit') }}" class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif

                                @if($puedeEliminar && $miId != $fila->Id_Usuario && !str_contains(strtolower($fila->rol ?? ''), 'admin'))
                                    <form action="{{ url('/admon/Usuarios/'.$fila->Id_Usuario) }}" method="post" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar este usuario?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fa-3x mb-3 d-block"></i>
                            No hay registros disponibles.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- PIE DE CARD: Reportes y Paginación (Aquí estaban los botones verdes) --}}
        <div class="card-footer bg-light border-top d-flex justify-content-between align-items-center">
            <div>
                <a href="{{ route('reportes.usuarios.pdf') }}" class="btn btn-outline-danger btn-sm me-2" target="_blank">
                    <i class="fas fa-file-pdf me-1"></i> Generar PDF
                </a>
                <a href="{{ route('reportes.usuarios.excel') }}" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-file-excel me-1"></i> Generar Excel
                </a>
            </div>

            {{-- PAGINACIÓN: Esto hace que salgan el resto de los usuarios --}}
            <div class="pagination-sm">
                {{ $data->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@endsection