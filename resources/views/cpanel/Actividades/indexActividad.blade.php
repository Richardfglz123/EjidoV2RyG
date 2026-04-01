@extends('cpanel/plantilla')
@section('title','Actividades')
@section('content')

    @php
        $sesionActual = session('usuario', session('2fa_user', []));
        $misPermisos = $sesionActual['permisos'] ?? [];
        $miRol = strtolower(trim($sesionActual['rol'] ?? ''));

        // Lógica de Superusuario
        $esAdmin = ($miRol === 'administrador' || ($sesionActual['id_rol'] ?? null) == 2);

        $puedeCrear = $esAdmin || in_array('usuarios_crear', $misPermisos);
        $puedeEditar = $esAdmin || in_array('usuarios_editar', $misPermisos);
        $puedeEliminar = $esAdmin || in_array('usuarios_eliminar', $misPermisos);
    @endphp

    <div class="card card-ejidal mb-3">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-filter me-2"></i> Filtros de Reporte
        </div>
        <div class="card-body">
            <form id="filtrosForm" class="row g-2">
                <div class="col-md-5">
                    <label class="form-label">Desde</label>
                    <input type="date" name="fecha_inicio" class="form-control">
                </div>

                <div class="col-md-5">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="fecha_fin" class="form-control">
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

    @if($puedeCrear)
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('actividades.create') }}" class="btn btn-ejidal">
                <i class="fas fa-plus me-1"></i> Nueva Actividad
            </a>
        </div>
    @endif

    <div class="card card-ejidal">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-list me-2"></i> Actividades Registradas
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Descripción</th>
                    <th>Fecha de inicio</th>
                    <th>Fecha de fin</th>
                    <th>Estado</th>
                    <th>Registrado por</th>
                    <th class="text-center">Acciones</th>
                </tr>
                </thead>
                <tbody>
                @foreach($data as $fila)
                    <tr>
                        <td>{{ $fila->Tipo }}</td>
                        <td>{{ $fila->Descripcion }}</td>
                        <td>{{ $fila->FechaInicio }}</td>
                        <td>{{ $fila->FechaFin }}</td>
                        <td>
                            <span class="badge {{ $fila->Estado_Actividad == 'Completada' ? 'bg-success' : 'bg-warning' }}">
                                {{ $fila->Estado_Actividad }}
                            </span>
                        </td>
                        <td>{{ $fila->Id_Creo }}</td>
                        <td class="text-center">
                            <div class="btn-group">
                                @if($puedeEditar)
                                    <a href="{{ route('actividades.edit', $fila->Id_Actividad) }}"
                                       class="btn btn-sm btn-outline-success" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif

                                @if($puedeEliminar)
                                    <form action="{{ route('actividades.destroy', $fila->Id_Actividad) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('¿Seguro?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Script de Reportes (Sin cambios) --}}
    <script>
        const form = document.getElementById('filtrosForm');
        document.getElementById('pdfBtn').onclick = () => {
            const params = new URLSearchParams(new FormData(form)).toString();
            window.open("{{ route('actividades.reporte.pdf') }}?" + params, "_blank");
        };
        document.getElementById('excelBtn').onclick = () => {
            const params = new URLSearchParams(new FormData(form)).toString();
            window.location = "{{ route('actividades.reporte.excel') }}?" + params;
        };
    </script>
@endsection