@extends('cpanel/plantilla')
@section('title','Ejido San Rafael Ixtapalucan')

@section('content')

    @php
        $sesionActual = session('usuario', session('2fa_user', []));

        if (is_object($sesionActual)) {
            $sesionArray = (array) $sesionActual;
        } else {
            $sesionArray = $sesionActual ?? [];
        }

        $misPermisos = $sesionArray['permisos'] ?? [];
        $miId = $sesionArray['Id_Usuario'] ?? $sesionArray['id'] ?? null;
        $miRol = strtolower(trim($sesionArray['rol'] ?? ''));

        $esAdmin = ($miRol === 'administrador' || ($sesionArray['id_rol'] ?? null) == 1 || ($sesionArray['id_rol'] ?? null) == 2);

        $puedeCrear = $esAdmin || in_array('usuarios_crear', $misPermisos);
        $puedeEditar = $esAdmin || in_array('usuarios_editar', $misPermisos);
        $puedeEliminar = $esAdmin || in_array('usuarios_eliminar', $misPermisos);
    @endphp

    <style>
        .text-header-main { color: #000000 !important; font-weight: normal !important; }

        .avatar-sm {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
            background-color: #f8f9fa;
        }

        /* Paginador: Forzar visibilidad del número */
        .pagination .page-item.active .page-link {
            background-color: #198754 !important;
            border-color: #198754 !important;
            color: #ffffff !important;
        }
        .pagination .page-link {
            color: #198754 !important;
        }

        /* Bloqueo de movimientos fantasma */
        .table tr, .table td {
            transition: none !important;
            transform: none !important;
        }
    </style>

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-header-main">
            <i class="fas fa-users me-2"></i> Listado de Usuarios
        </h1>

        @if($puedeCrear)
            {{-- Corregido a minúsculas --}}
            <a href="{{ route('usuarios.create') }}" class="btn btn-ejidal shadow-sm">
                <i class="fas fa-user-plus me-1"></i> Nuevo Usuario
            </a>
        @endif
    </div>

    <div class="card card-ejidal mb-4">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-search me-2"></i> Búsqueda de Usuarios
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('usuarios.index') }}" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" placeholder="Nombre..." value="{{ request('nombre') }}">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Apellido</label>
                    <input type="text" name="apellido" class="form-control" placeholder="Apellido..." value="{{ request('apellido') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-ejidal w-100">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-ejidal shadow-sm">
        <div class="card-header card-header-ejidal">
            <span><i class="fas fa-list me-2"></i> Usuarios Registrados</span>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th class="ps-3 text-center" style="width: 70px;">Foto</th>
                    <th>Datos Personales</th>
                    <th>Usuario @if($esAdmin) / Rol @endif</th>
                    <th>Contacto</th>
                    <th class="text-center">Acciones</th>
                </tr>
                </thead>
                <tbody>

                @forelse($data as $fila)
                    @php
                        $nombresLimpio = str_ireplace(['\n', "\n", "\r"], ' ', $fila->Nombres);
                        $apellidoPLimpio = str_ireplace(['\n', "\n", "\r"], ' ', $fila->Apellido_Paterno);
                        $apellidoMLimpio = str_ireplace(['\n', "\n", "\r"], ' ', $fila->Apellido_Materno);

                        $nombresLimpio = preg_replace('/\s+/', ' ', trim($nombresLimpio));
                        $apellidoPLimpio = preg_replace('/\s+/', ' ', trim($apellidoPLimpio));
                        $apellidoMLimpio = preg_replace('/\s+/', ' ', trim($apellidoMLimpio));
                    @endphp
                    <tr>
                        <td class="ps-3 text-center">
                            @php $fotoUsuario = $fila->foto ?? null; @endphp
                            @if($fotoUsuario)
                                <img src="{{ asset('storage/' . $fotoUsuario) }}" class="avatar-sm" alt="Foto">
                            @else
                                <div class="avatar-sm d-flex align-items-center justify-content-center bg-light mx-auto border">
                                    <i class="fas fa-user text-muted"></i>
                                </div>
                            @endif
                        </td>

                        <td>
                            <div class="text-dark fw-bold">{{ $nombresLimpio }}</div>
                            <div class="small text-muted text-uppercase">{{ $apellidoPLimpio }} {{ $apellidoMLimpio }}</div>
                        </td>

                        <td>
                            <div class="text-dark mb-1">{{ $fila->Usuario }}</div>
                            @if($esAdmin)
                                @php
                                    $rolFila = strtolower(trim($fila->rol ?? ''));
                                    $badgeClass = match(true) {
                                        str_contains($rolFila, 'admin') => 'bg-danger',
                                        str_contains($rolFila, 'secret') => 'bg-warning text-dark',
                                        default => 'bg-success',
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">
                                    {{ strtoupper($fila->rol ?? 'USUARIO') }}
                                </span>
                            @endif
                        </td>

                        <td>
                            <div class="small"><i class="fas fa-envelope me-1 text-muted"></i> {{ $fila->Correo }}</div>
                            <div class="small"><i class="fas fa-phone me-1 text-muted"></i> {{ $fila->Telefono }}</div>
                        </td>

                        <td class="text-center">
                            <div class="btn-group">
                                @if($puedeEditar)
                                    <a href="{{ route('usuarios.edit', $fila->Id_Usuario) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif

                                @if($puedeEliminar && $miId != $fila->Id_Usuario && !str_contains(strtolower($fila->rol ?? ''), 'admin'))
                                    <form action="{{ route('usuarios.destroy', $fila->Id_Usuario) }}" method="post" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar usuario?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-5">No hay registros.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-light border-top d-flex justify-content-between align-items-center">
            <div>
                <a href="{{ route('reportes.usuarios.pdf') }}" class="btn btn-outline-danger btn-sm me-2" target="_blank">
                    <i class="fas fa-file-pdf me-1"></i> PDF
                </a>
                <a href="{{ route('reportes.usuarios.excel') }}" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-file-excel me-1"></i> Excel
                </a>
            </div>

            <div class="pagination-sm">
                {{ $data->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@endsection