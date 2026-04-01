@extends('cpanel.plantilla')
@section('title', 'Listado de Parcelas')
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

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card perfil-card shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center">
                <i class="fas fa-map-marked-alt me-2"></i>
                <span>Listado de Parcelas Registradas</span>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="inputBusqueda" class="form-control border-start-0" placeholder="Buscar..." onkeyup="filtrarTabla()">
                </div>

                @if($puedeCrear)
                    <a href="{{ route('parcelas.create') }}" class="btn btn-light btn-sm text-dark fw-bold shadow-sm">
                        <i class="fas fa-plus-circle me-1 text-success"></i> Nueva Parcela
                    </a>
                @endif
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaParcelas">
                    <thead class="bg-light text-ejidal">
                    <tr>
                        <th class="ps-3" style="width: 150px;">No. Parcela</th>
                        <th>Ubicación / Paraje</th>
                        <th>Ejidatario Asignado</th>
                        <th class="text-center" style="width: 150px;">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($parcelas as $p)
                        <tr class="fila-parcela">
                            <td class="ps-3">
                                <span class="badge bg-light text-dark border fw-bold">#{{ $p->noParcela }}</span>
                            </td>
                            <td><i class="fas fa-location-dot me-1 text-muted"></i> {{ $p->ubicacion ?? 'No especificada' }}</td>
                            <td>
                                @if($p->ejidatario)
                                    <i class="fas fa-user-circle me-1 text-muted"></i> {{ $p->ejidatario }}
                                @else
                                    <span class="text-danger small fst-italic">Sin asignar</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group shadow-sm">
                                    <a href="{{ route('parcelas.ver', ['noParcela' => $p->noParcela]) }}"
                                       class="btn btn-outline-info btn-sm" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    @if($puedeEditar)
                                        <a href="{{ route('parcelas.editar', $p->Id_Parcela) }}"
                                           class="btn btn-outline-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif

                                    @if($puedeEliminar)
                                        <form action="{{ route('parcelas.eliminar', $p->Id_Parcela) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm"
                                                    onclick="return confirm('¿Eliminar parcela?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-5">No hay parcelas.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- PIE DE TARJETA: Reportes Verdes y Rojos --}}
        <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
            <div>
                <a href="#" class="btn btn-outline-danger btn-sm me-2">
                    <i class="fas fa-file-pdf me-1"></i> Reporte PDF
                </a>
                <a href="#" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-file-excel me-1"></i> Reporte Excel
                </a>
            </div>
            <small class="text-muted">Total: {{ count($parcelas) }} parcelas</small>
        </div>
    </div>

    <script>
        function filtrarTabla() {
            const filter = document.getElementById("inputBusqueda").value.toUpperCase();
            const filas = document.getElementsByClassName("fila-parcela");
            for (let i = 0; i < filas.length; i++) {
                const texto = filas[i].textContent || filas[i].innerText;
                filas[i].style.display = texto.toUpperCase().includes(filter) ? "" : "none";
            }
        }
    </script>
@endsection