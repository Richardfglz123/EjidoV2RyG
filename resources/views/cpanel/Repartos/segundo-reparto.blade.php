@extends('cpanel/plantilla')
@section('title', 'Segundo Reparto - San Rafael Ixtapalucan')

@section('content')

    <style>
        .text-header-main { color: #000000 !important; font-weight: normal !important; }
        .pagination .page-item.active .page-link { background-color: #198754 !important; border-color: #198754 !important; color: #ffffff !important; }
        .pagination .page-link { color: #198754 !important; }
        .search-container { position: relative; }
        .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6c757d; }
        .search-input { padding-left: 35px !important; }
        .pagination svg { width: 20px; height: 20px; }

        .repro-input-row { background-color: #f8f9fa; border-left: 4px solid #198754; }
    </style>

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-header-main">
            <i class="fas fa-file-invoice-dollar me-2"></i> Segundo Reparto
        </h1>
    </div>

    {{-- Buscador Global --}}
    <div class="card card-ejidal mb-4 shadow-sm">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-search me-2"></i> Búsqueda Global de Ejidatarios
        </div>
        <div class="card-body">
            <form action="{{ route('reparto.segundo') }}" method="GET" id="formBuscador">
                <div class="row">
                    <div class="col-md-12 search-container">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="query" id="inputBuscadorGlobal" class="form-control search-input"
                               placeholder="Escriba nombre o apellido..."
                               value="{{ request('query') }}" autocomplete="off">
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla Principal --}}
    <div class="card card-ejidal shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between">
            <span><i class="fas fa-list-ol me-2"></i> Resumen de Liquidación</span>
            @if(request('query'))
                <a href="{{ route('reparto.segundo') }}" class="badge bg-danger text-decoration-none">
                    <i class="fas fa-times"></i> Limpiar búsqueda
                </a>
            @endif
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                <tr class="small text-uppercase">
                    <th class="ps-3 text-center" style="width: 60px;">No.</th>
                    <th>Datos del Ejidatario</th>
                    <th class="text-center">Desc. Asamblea</th>
                    <th class="text-center">Desc. Faenas</th>
                    <th class="text-center">Préstamos (R1)</th>
                    <th class="text-center">2Do Reparto</th>
                    <th class="text-center bg-light fw-bold text-dark">Total a Pagar</th>
                    <th class="text-center">Acciones</th>
                </tr>
                </thead>
                <tbody>
                @forelse($ejidatarios as $ejidatario)
                    <tr>
                        <td class="ps-3 text-center text-muted small">
                            {{ ($ejidatarios->currentPage() - 1) * $ejidatarios->perPage() + $loop->iteration }}
                        </td>
                        {{-- Busca este bloque en tu tabla --}}
                        <td>
                            <div class="text-dark fw-bold">
                                {{-- CAMBIO AQUÍ: Sin el "usuario?->" --}}
                                {{ $ejidatario->Nombres }}
                            </div>
                            <div class="small text-muted text-uppercase">
                                {{ $ejidatario->Apellido_Paterno }} {{ $ejidatario->Apellido_Materno }}
                            </div>
                        </td>

                        <td class="text-center">
                            @if($ejidatario->total_asambleas > 0)
                                <button type="button" class="btn btn-sm btn-danger py-0 px-2 fw-bold shadow-sm"
                                        onclick="verDetalle({{ $ejidatario->Id_Ejidatario }}, 'asambleas')">
                                    -${{ number_format($ejidatario->total_asambleas, 2) }}
                                </button>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle fw-normal">
                                    <i class="fas fa-check-circle"></i> Al corriente
                                </span>
                            @endif
                        </td>

                        <td class="text-center">
                            @if($ejidatario->total_faenas > 0)
                                <button type="button" class="btn btn-sm btn-danger py-0 px-2 fw-bold shadow-sm"
                                        onclick="verDetalle({{ $ejidatario->Id_Ejidatario }}, 'faenas')">
                                    -${{ number_format($ejidatario->total_faenas, 2) }}
                                </button>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle fw-normal">
                                    <i class="fas fa-check-circle"></i> Al corriente
                                </span>
                            @endif
                        </td>

                        <td class="text-center text-danger fw-bold">${{ number_format($ejidatario->deuda_arrastrada_r1, 2) }}</td>
                        <td class="text-center text-muted small">${{ number_format($montoFijoR2, 2) }}</td>
                        <td class="text-center bg-light fw-bold text-dark fs-6">${{ number_format($ejidatario->total_a_pagar, 2) }}</td>
                        <td class="text-center">
                            <span class="badge bg-light text-success border fw-normal shadow-sm">
                                <i class="fas fa-check"></i> Pagado
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-5">No se encontraron resultados.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-light border-top d-flex justify-content-between align-items-center">
            <div class="small text-muted">Mostrando <b>{{ $ejidatarios->firstItem() }}</b> al <b>{{ $ejidatarios->lastItem() }}</b></div>
            <div class="pagination-sm">{{ $ejidatarios->appends(request()->query())->links('pagination::bootstrap-4') }}</div>
        </div>
    </div>

    {{-- MODAL DE DETALLES CON REPROGRAMACIÓN --}}
    <div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header card-header-ejidal text-white">
                    <h5 class="modal-title fs-6" id="tituloDetalle">Detalle de Faltas</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                        <tr class="small">
                            <th class="ps-3">Concepto / Fecha</th>
                            <th>Monto</th>
                            <th class="text-center">Opciones</th>
                        </tr>
                        </thead>
                        <tbody id="cuerpoDetalle"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPTS --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let timer;
        document.getElementById('inputBuscadorGlobal').addEventListener('input', function() {
            clearTimeout(timer);
            timer = setTimeout(() => {
                if(this.value.length >= 3 || this.value.length == 0) {
                    document.getElementById('formBuscador').submit();
                }
            }, 800);
        });

        function verDetalle(id, tipo) {
            const baseUrl = tipo === 'asambleas' ? "{{ url('detalle-asambleas') }}" : "{{ url('detalle-faenas') }}";
            const url = `${baseUrl}/${id}`;

            $('#tituloDetalle').text(tipo === 'asambleas' ? 'FALTAS EN ASAMBLEAS' : 'FALTAS EN FAENAS');
            $('#cuerpoDetalle').html('<tr><td colspan="3" class="text-center py-3">Buscando registros...</td></tr>');
            $('#modalDetalle').modal('show');

            $.get(url, function(data) {
                let html = '';
                if (Array.isArray(data) && data.length > 0) {
                    data.forEach((d, index) => {
                        html += `
                        <tr class="align-middle">
                            <td class="ps-3 small text-uppercase fw-bold">${d.tipo}</td>
                            <td class="text-danger fw-bold">-$${parseFloat(d.Descuento).toFixed(2)}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-success shadow-sm fw-bold px-3" onclick="mostrarRepro(${index})">
                                    <i class="fas fa-calendar-alt"></i> Reprogramar
                                </button>
                            </td>
                        </tr>
                        <tr id="repro_form_${index}" style="display:none;" class="repro-input-row">
                            <td colspan="3" class="p-3 border-bottom">
                                <div class="d-flex align-items-center justify-content-center gap-3">
                                    <label class="small fw-bold mb-0">NUEVA FECHA:</label>
                                    <input type="date" id="date_${index}" class="form-control form-control-sm w-auto shadow-sm">
                                    <button class="btn btn-sm btn-primary px-3 shadow-sm" onclick="confirmarReprogramacion(${id}, '${d.tipo}', ${index})">
                                        CONFIRMAR
                                    </button>
                                    <button class="btn btn-sm btn-light border" onclick="mostrarRepro(${index})">
                                        CANCELAR
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                    });
                } else {
                    html = '<tr><td colspan="3" class="text-center py-4 text-muted small">Sin faltas pendientes en este ciclo.</td></tr>';
                }
                $('#cuerpoDetalle').html(html);
            }).fail(function(jqXHR) {
                console.error("Error detallado:", jqXHR.responseText);
                $('#cuerpoDetalle').html('<tr><td colspan="3" class="text-center py-3 text-danger">Error de comunicación con el servidor.</td></tr>');
            });
        }

        function mostrarRepro(index) {
            $(`#repro_form_${index}`).toggle();
        }

        function confirmarReprogramacion(idEjidatario, tipoEvento, index) {
            const inputFecha = $(`#date_${index}`);
            const fecha = inputFecha.val();

            const hoy = new Date().toISOString().split('T')[0];

            if(!fecha) {
                alert("Por favor seleccione una fecha.");
                return;
            }

            if(fecha < hoy) {
                alert("No se puede programar una fecha anterior al día de hoy.");
                inputFecha.addClass('is-invalid');
                return;
            }

            $.ajax({
                url: "{{ url('reprográmr-falta') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id_ejidatario: idEjidatario,
                    tipo_evento: tipoEvento,
                    fecha_nueva: fecha
                },
                success: function(res) {
                    if(res.success) {
                        alert("¡Reprogramación exitosa!");
                        location.reload();
                    } else {
                        alert("Atención: " + res.message);
                    }
                },
                error: function(xhr) {
                    let errorMsg = "Error desconocido";
                    if(xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    alert("Error crítico: " + errorMsg);
                    console.error(xhr.responseText);
                }
            });
        }
    </script>

@endsection