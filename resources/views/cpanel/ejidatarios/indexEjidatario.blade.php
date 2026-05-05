@extends('cpanel/plantilla')
@section('title','Ejidatarios')
@section('content')

    @php
        $sesionActual = session('usuario', session('2fa_user', []));
        $misPermisos = $sesionActual['permisos'] ?? [];
        $miId = $sesionActual['id'] ?? null;
        $miRol = strtolower(trim($sesionActual['rol'] ?? ''));

        // Lógica de Superusuario
        $esAdmin = ($miRol === 'administrador' || ($sesionActual['id_rol'] ?? null) == 2);

        $puedeCrear = $esAdmin || in_array('usuarios_crear', $misPermisos);
        $puedeEditar = $esAdmin || in_array('usuarios_editar', $misPermisos);
        $puedeEliminar = $esAdmin || in_array('usuarios_eliminar', $misPermisos);
    @endphp

    <style>
        /* Estilos Verdes Uniformes para coincidir con el resto del sistema */
        .text-ejidal { color: #198754 !important; font-weight: 700; }
        .card-ejidal { border-color: #198754 !important; }
        .card-header-ejidal { background-color: #198754 !important; color: white !important; font-weight: 600; }

        /* Botón Verde Personalizado */
        .btn-ejidal {
            background-color: #198754 !important;
            border-color: #198754 !important;
            color: white !important;
        }
        .btn-ejidal:hover {
            background-color: #157347 !important;
            border-color: #157347 !important;
            color: white !important;
        }

        /* Ajuste para el buscador al hacer foco */
        #inputBusquedaEjidatario:focus {
            border-color: #198754 !important;
            box-shadow: 0 0 0 .25rem rgba(25, 135, 84, 0.25) !important;
        }
    </style>

    <div class="card card-ejidal shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span>
                <i class="fas fa-list me-2"></i> Ejidatarios Registrados
            </span>

            <div class="d-flex align-items-center gap-2">
                {{-- BUSCADOR DINÁMICO --}}
                <div class="input-group input-group-sm" style="width: 300px;">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" id="inputBusquedaEjidatario" class="form-control border-start-0"
                           placeholder="Buscar..." onkeyup="filtrarEjidatarios()">
                </div>

                @if($puedeCrear)
                    <a href="{{ route('Ejidatarios.create') }}" class="btn btn-sm btn-light text-dark fw-bold shadow-sm">
                        <i class="fas fa-plus me-1 text-success"></i> Nuevo Ejidatario
                    </a>
                @endif
            </div>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-striped align-middle mb-0" id="tablaEjidatarios">
                <thead class="bg-light">
                <tr>
                    <th class="ps-3">#</th>
                    <th>Datos</th>
                    <th>Dirección</th>
                    <th>Ejidatario</th>
                    <th>Estatus</th>
                    <th class="text-center">Acciones</th>
                </tr>
                </thead>
                <tbody>
                @foreach($data as $fila)
                    <tr class="fila-ejidatario">
                        <td class="ps-3 fw-bold">{{ $fila->Num_Ejidatario }}</td>
                        <td>
                            <span class="d-block small"><strong>CURP:</strong> {{ $fila->CURP }}</span>
                            <span class="d-block small"><strong>RFC:</strong> {{ $fila->RFC }}</span>
                        </td>
                        <td class="small">
                            {{ $fila->Calle }} #{{ $fila->Num_Exterior }}<br>
                            <span class="text-muted">{{ $fila->Colonia }}, {{ $fila->Municipio }}</span>
                        </td>
                        <td>{{ $fila->Nombres }} {{ $fila->Apellido_Paterno }}</td>
                        {{-- CAMBIO: Badge de estatus ahora es verde (success) en lugar de azul (primary) --}}
                        <td>
                            <span class="badge {{ $fila->NombreEstatus == 'Activo' ? 'bg-success' : 'bg-info' }}">
                                {{ $fila->NombreEstatus }}
                            </span>
                        </td>

                        <td class="text-center">
                            @if($puedeEditar)
                                <a href="{{ route('Ejidatarios.edit', $fila->Id_Ejidatario) }}"
                                   class="btn btn-sm btn-outline-success me-1" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif

                            @if($puedeEliminar && ($esAdmin || $miId != $fila->Id_Usuario))
                                <form action="{{ route('Ejidatarios.destroy', $fila->Id_Ejidatario) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('¿Eliminar registro de ejidatario?')" title="Eliminar">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white border-top-0 p-3">
            {{-- CAMBIO: Botón PDF ahora usa btn-ejidal (Verde) en lugar de btn-primary (Azul) --}}
            <a href="{{ route('reportes.ejidatarios.pdf') }}" class="btn btn-ejidal shadow-sm" target="_blank">
                <i class="fas fa-file-pdf me-1"></i> Generar PDF
            </a>
            <a href="{{ route('reportes.ejidatarios.excel') }}" class="btn btn-success shadow-sm ms-2" style="background-color: #157347;">
                <i class="fas fa-file-excel me-1"></i> Descargar Excel
            </a>
        </div>
    </div>

    <script>
        function filtrarEjidatarios() {
            const filter = document.getElementById("inputBusquedaEjidatario").value.toUpperCase();
            const filas = document.getElementsByClassName("fila-ejidatario");
            for (let i = 0; i < filas.length; i++) {
                const texto = filas[i].textContent || filas[i].innerText;
                filas[i].style.display = texto.toUpperCase().includes(filter) ? "" : "none";
            }
        }
    </script>
@endsection