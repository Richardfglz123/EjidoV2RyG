@extends('cpanel/plantilla')
@section('title','Descuento de Asambleas')

@section('content')

    <style>
        .text-header-main {
            color: #000000 !important;
            font-weight: normal !important;
        }

        .pagination .page-item.active .page-link {
            background-color: #198754 !important;
            border-color: #198754 !important;
            color: #ffffff !important;
        }

        .pagination .page-link {
            color: #198754 !important;
        }

        .table tr, .table td {
            transition: none !important;
            transform: none !important;
        }

        .table td {
            vertical-align: middle;
            white-space: nowrap;
            padding: 15px 10px !important;
        }

        .avatar-placeholder {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #dee2e6;
        }

        .badge-asistencia {
            font-size: 0.75rem;
            padding: 0.5em 0.8em;
            border-radius: 50rem;
            text-transform: uppercase;
            font-weight: bold;
        }

        .card-ejidal {
            border: 1px solid #198754 !important;
        }

        .card-header-ejidal {
            background-color: #198754 !important;
            color: white !important;
            font-weight: 600;
        }

        .btn-ejidal {
            background-color: #198754;
            color: white;
            border: none;
        }

        .btn-ejidal:hover {
            background-color: #146c43;
            color: white;
        }

        #busquedaEjidatario:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
        }
    </style>

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-header-main">
            <i class="fas fa-users text-success me-2"></i> Descuento Asambleas
        </h1>
    </div>

    <div class="card card-ejidal mb-4">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-search me-2"></i> Búsqueda de Ejidatarios
        </div>

        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">Nombre o Apellido</label>

                    <input
                            type="text"
                            id="busquedaEjidatario"
                            class="form-control"
                            placeholder="Buscar ejidatario..."
                    >
                </div>
            </div>
        </div>
    </div>

    <div class="card card-ejidal shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span>
                <i class="fas fa-list me-2"></i> Estatus de Asistencia
            </span>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped align-middle mb-0" id="tablaEjidatarios">
                <thead class="table-light">
                <tr class="small text-uppercase">
                    <th class="ps-3 text-center" style="width: 70px;">Ref</th>
                    <th style="min-width: 250px;">Ejidatario</th>

                    @foreach($eventosAsambleas as $ev)
                        <th class="text-center">
                            {{ $ev->Nombre_Evento }}
                        </th>
                    @endforeach
                </tr>
                </thead>

                <tbody>
                @forelse($ejidatarios as $ejidatario)

                    @php
                        $idsAsistidos = $ejidatario->asistencias_asambleas ?? [];
                    @endphp

                    <tr class="fila-ejidatario">
                        <td class="ps-3 text-center">
                            <div class="avatar-placeholder mx-auto">
                                <i class="fas fa-user text-muted"></i>
                            </div>
                        </td>

                        <td class="texto-busqueda">
                            <div class="text-dark fw-bold">
                                {{ $ejidatario->Nombres }}
                            </div>

                            <div class="small text-muted text-uppercase">
                                {{ $ejidatario->Apellido_Paterno }}
                                {{ $ejidatario->Apellido_Materno }}
                            </div>
                        </td>

                        @foreach($eventosAsambleas as $ev)
                            <td class="text-center">
                                @if(in_array($ev->Id_Evento, $idsAsistidos))
                                    <span class="badge badge-asistencia bg-success">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Asistió
                                    </span>
                                @else
                                    <span class="badge badge-asistencia bg-danger">
                                        <i class="fas fa-times-circle me-1"></i>
                                        No Asistió
                                    </span>
                                @endif
                            </td>
                        @endforeach
                    </tr>

                @empty
                    <tr id="sinResultados">
                        <td colspan="{{ count($eventosAsambleas) + 2 }}"
                            class="text-center py-5 text-muted">

                            <i class="fas fa-folder-open fa-3x mb-3 d-block"></i>

                            No se encontraron registros.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-light border-top d-flex justify-content-end align-items-center">
            <div class="pagination-sm">
                {{ $ejidatarios->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const buscador = document.getElementById('busquedaEjidatario');
            const filas = document.querySelectorAll('.fila-ejidatario');

            buscador.addEventListener('keyup', function () {

                const texto = this.value.toLowerCase().trim();

                let visibles = 0;

                filas.forEach(fila => {

                    const contenido = fila
                        .querySelector('.texto-busqueda')
                        .innerText
                        .toLowerCase();

                    if (contenido.includes(texto)) {

                        fila.style.display = '';
                        visibles++;

                    } else {

                        fila.style.display = 'none';
                    }
                });

                let filaNoResultados = document.getElementById('sinResultadosBusqueda');

                if (visibles === 0) {

                    if (!filaNoResultados) {

                        const tbody = document.querySelector('#tablaEjidatarios tbody');

                        filaNoResultados = document.createElement('tr');

                        filaNoResultados.id = 'sinResultadosBusqueda';

                        filaNoResultados.innerHTML = `
                            <td colspan="{{ count($eventosAsambleas) + 2 }}"
                                class="text-center py-5 text-muted">

                                <i class="fas fa-search fa-2x mb-3 d-block"></i>

                                No se encontraron coincidencias.
                            </td>
                        `;

                        tbody.appendChild(filaNoResultados);
                    }

                } else {

                    if (filaNoResultados) {
                        filaNoResultados.remove();
                    }
                }
            });
        });
    </script>

@endsection