@extends('cpanel/plantilla')
@section('title', 'Segundo Reparto')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        .pagination .page-item.active .page-link { background-color: #1b4b36 !important; border-color: #1b4b36 !important; color: #ffffff !important; }
        .pagination .page-link { color: #1b4b36 !important; }
        .pagination svg { width: 20px; height: 20px; }
        .repro-input-row { background-color: #f8f9fa; border-left: 4px solid #1b4b36; }
    </style>

    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal fw-normal">
            <i class="fas fa-file-invoice-dollar me-2"></i> Segundo Reparto — Año {{ now()->year }}
        </h1>
        <div class="d-flex gap-2">
            <form action="{{ route('reparto.segundo') }}" method="GET" id="formBuscador" class="d-flex gap-2 mb-0">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="query" id="inputBuscadorGlobal" class="form-control border-start-0"
                           placeholder="Buscar por nombre o apellido..." value="{{ request('query') }}" autocomplete="off">
                </div>
                @if(request('query'))
                    <a href="{{ route('reparto.segundo') }}" class="btn btn-sm btn-outline-danger d-flex align-items-center">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm fw-normal" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm fw-normal" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card card-ejidal shadow-sm border-0">
        <div class="card-header card-header-ejidal py-3 fw-normal">
            <i class="fas fa-list-ol me-2"></i> Resumen de Liquidación de Ejidatarios
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabla-segundo-reparto">
                    <thead>
                    <tr class="bg-light fw-normal" style="font-size: 0.9rem;">
                        <th class="ps-3 text-muted fw-normal" width="50">#</th>
                        <th class="fw-normal">EJIDATARIO</th>
                        <th class="text-center fw-normal">DESC. ASAMBLEA</th>
                        <th class="text-center fw-normal">DESC. FAENAS</th>
                        <th class="text-center fw-normal">PRÉSTAMOS (Pri.Rep)</th>
                        <th class="text-center fw-normal">MONTO R2</th>
                        <th class="text-center fw-normal">TOTAL</th>
                        <th class="text-center fw-normal" width="160">ESTADO</th>
                        <th class="text-center fw-normal" width="140">ACCIONES</th>
                    </tr>
                    </thead>
                    <tbody class="fw-normal">
                    @forelse($ejidatarios as $ejidatario)
                        <tr>
                            <td class="ps-3 text-muted small">
                                {{ ($ejidatarios->currentPage() - 1) * $ejidatarios->perPage() + $loop->iteration }}
                            </td>
                            <td class="text-dark">
                                <span class="fw-bold">{{ $ejidatario->Nombres }}</span>
                                <div class="small text-muted text-uppercase" style="font-size: 0.75rem;">
                                    {{ $ejidatario->Apellido_Paterno }} {{ $ejidatario->Apellido_Materno }}
                                </div>
                            </td>

                            <td class="text-center">
                                @if($ejidatario->total_asambleas > 0)
                                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 fw-normal" style="font-size: 0.85rem;"
                                            onclick="verDetalle({{ $ejidatario->Id_Ejidatario }}, 'asambleas')">
                                        ${{ number_format($ejidatario->total_asambleas, 2) }}
                                    </button>
                                @else
                                    <span class="badge border border-success text-dark fw-normal" style="background-color: #f0fdf4; font-size: 0.8rem;">
                                        <i class="fas fa-check text-success me-1"></i> Sin asistencia
                                    </span>
                                @endif
                            </td>

                            <td class="text-center">
                                @if($ejidatario->total_faenas > 0)
                                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 fw-normal" style="font-size: 0.85rem;"
                                            onclick="verDetalle({{ $ejidatario->Id_Ejidatario }}, 'faenas')">
                                        ${{ number_format($ejidatario->total_faenas, 2) }}
                                    </button>
                                @else
                                    <span class="badge border border-success text-dark fw-normal" style="background-color: #f0fdf4; font-size: 0.8rem;">
                                        <i class="fas fa-check text-success me-1"></i>  Sin asistencia
                                    </span>
                                @endif
                            </td>

                            <td class="text-center text-danger fw-normal">
                                ${{ number_format($ejidatario->deuda_arrastrada_r1, 2) }}
                            </td>

                            <td class="text-center text-muted small">${{ number_format($montoFijoR2, 2) }}</td>

                            <td class="text-center fw-normal">
                                @if($ejidatario->total_a_pagar >= 0)
                                    <span class="text-ejidal">${{ number_format($ejidatario->total_a_pagar, 2) }}</span>
                                @else
                                    <span class="text-danger">${{ number_format(abs($ejidatario->total_a_pagar), 2) }}</span>
                                @endif
                            </td>

                            <td class="text-center">
                                @if($ejidatario->total_a_pagar > 0)
                                    <span class="badge border border-success text-dark fw-normal d-block py-1" style="background-color: #f0fdf4;">
                                        <i class="fas fa-hand-holding-usd text-success me-1"></i> Sin deuda
                                    </span>
                                @elseif($ejidatario->total_a_pagar == 0)
                                    <span class="badge bg-light text-dark border fw-normal d-block py-1">
                                        <i class="fas fa-check-circle text-success me-1"></i> Liquidado
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-normal d-block py-1">
                                        <i class="fas fa-exclamation-triangle me-1"></i> Adeudo
                                    </span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('reparto.segundo.ticket', $ejidatario->Id_Ejidatario) }}"
                                       class="btn btn-outline-primary"
                                       target="_blank"
                                       title="Imprimir Ticket">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>

                                    @if($ejidatario->total_a_pagar < 0)
                                        <button type="button" class="btn btn-outline-success"
                                                style="border: 1px solid #1b4b36 !important;"
                                                onclick="abrirModalAbono({{ $ejidatario->Id_Ejidatario }}, '{{ $ejidatario->Nombres }} {{ $ejidatario->Apellido_Paterno }}', {{ abs($ejidatario->total_a_pagar) }})"
                                                title="Registrar Abono">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </button>

                                        <button type="button" class="btn btn-outline-secondary"
                                                onclick="if(confirm('¿Seguro que desea pasar esta deuda al siguiente año para liberar su Segundo Reparto actual?')) { document.getElementById('form-posponer-{{ $ejidatario->Id_Ejidatario }}').submit(); }"
                                                title="Aplazar Deuda al Siguiente Año">
                                            <i class="fas fa-clock"></i>
                                        </button>

                                        <form action="{{ route('reparto.segundo.posponer', $ejidatario->Id_Ejidatario) }}"
                                              method="POST"
                                              id="form-posponer-{{ $ejidatario->Id_Ejidatario }}"
                                              class="d-none">
                                            @csrf
                                        </form>
                                    @else
                                        <span class="badge bg-light text-muted fw-normal border ms-1 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center py-5 text-muted">No se encontraron ejidatarios.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light border-top d-flex justify-content-between align-items-center">
            <div class="small text-muted">Mostrando <b>{{ $ejidatarios->firstItem() }}</b> al <b>{{ $ejidatarios->lastItem() }}</b></div>
            <div class="pagination-sm">{{ $ejidatarios->appends(request()->query())->links('pagination::bootstrap-4') }}</div>
        </div>
    </div>

    {{-- MODAL ABONOS --}}
    <div class="modal fade" id="modalAbonarDeuda" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow text-center">
                <div class="modal-header card-header-ejidal">
                    <h5 class="modal-title mx-auto text-white fw-normal"><i class="fas fa-cash-register me-2"></i>Registrar Abono</h5>
                    <button type="button" class="btn-close btn-close-white ms-0" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST" id="formAbonoDinamico">
                    @csrf
                    <div class="modal-body p-4 fw-normal">
                        <h6 id="modalAbonoNombre" class="mb-3 text-dark fw-normal">Cargando...</h6>
                        <div class="alert alert-warning py-2 border-0 shadow-sm fw-normal">
                            Deuda actual en caja: <span id="labelDeudaMax" class="text-danger fw-normal">$0.00</span>
                        </div>
                        <div class="mb-0">
                            <label class="text-muted small text-uppercase mb-2">Monto a Recibir ($)</label>
                            <input type="number" name="monto" id="monto_abono_input" class="form-control form-control-lg text-center text-ejidal fw-normal" step="0.01" min="0.01" required placeholder="0.00">
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center bg-light">
                        <button type="button" class="btn btn-secondary px-4 fw-normal" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-ejidal px-4 shadow-sm fw-normal">Confirmar Pago</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL DETALLES --}}
    <div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header card-header-ejidal">
                    <h5 class="modal-title text-white fw-normal" id="tituloDetalle">Detalle de Faltas</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                        <tr class="small fw-normal">
                            <th class="ps-3 fw-normal">Concepto / Fecha</th>
                            <th class="fw-normal">Monto</th>
                            <th class="text-center fw-normal">Opciones</th>
                        </tr>
                        </thead>
                        <tbody id="cuerpoDetalle" class="fw-normal"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

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

        function abrirModalAbono(id, nombreCompleto, deudaMaxima) {
            let urlAction = "{{ route('prestamo2.abonar', ':id') }}".replace(':id', id);
            $('#formAbonoDinamico').attr('action', urlAction);

            $('#modalAbonoNombre').text(nombreCompleto);
            $('#labelDeudaMax').text("$" + parseFloat(deudaMaxima).toLocaleString('en-US', { minimumFractionDigits: 2 }));
            $('#monto_abono_input').val(parseFloat(deudaMaxima).toFixed(2)).attr('max', parseFloat(deudaMaxima).toFixed(2));
            $('#modalAbonarDeuda').modal('show');
        }

        $('#formAbonoDinamico').on('submit', function(e) {
            const abono = parseFloat($('#monto_abono_input').val());
            const maximoString = $('#labelDeudaMax').text().replace('$', '').replace(/,/g, '');
            const deudaMaxima = parseFloat(maximoString);

            if (abono <= 0) {
                e.preventDefault();
                alert('El monto del abono debe ser mayor a 0.');
                return false;
            }
        });

        function verDetalle(id, tipo) {
            const baseUrl = tipo === 'asambleas' ? "{{ url('admon/finanzas/segundo-reparto/detalle-asambleas') }}" : "{{ url('admon/finanzas/segundo-reparto/detalle-faenas') }}";
            const url = `${baseUrl}/${id}`;

            $('#tituloDetalle').text(tipo === 'asambleas' ? 'Faltas en Asambleas' : 'Faltas en Faenas');
            $('#cuerpoDetalle').html('<tr><td colspan="3" class="text-center py-3">Buscando registros...</td></tr>');
            $('#modalDetalle').modal('show');

            $.get(url, function(data) {
                let html = '';
                if (Array.isArray(data) && data.length > 0) {
                    data.forEach((d, index) => {
                        html += `
                        <tr class="align-middle">
                            <td class="ps-3 small text-uppercase fw-normal">${d.tipo}</td>
                            <td class="text-danger fw-normal">$${parseFloat(d.Descuento).toFixed(2)}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-success fw-normal px-3" style="border: 1px solid #1b4b36 !important;" onclick="mostrarRepro(${index})">
                                    <i class="fas fa-calendar-alt"></i> Reprogramar
                                </button>
                            </td>
                        </tr>
                        <tr id="repro_form_${index}" style="display:none;" class="repro-input-row">
                            <td colspan="3" class="p-3 border-bottom">
                                <div class="d-flex align-items-center justify-content-center gap-3">
                                    <label class="small fw-normal mb-0 text-ejidal">NUEVA FECHA:</label>
                                    <input type="date" id="date_${index}" class="form-control form-control-sm w-auto shadow-sm">
                                    <button class="btn btn-sm btn-ejidal px-3 shadow-sm fw-normal" onclick="confirmarReprogramacion(${id}, '${d.tipo}', ${index})">
                                        Confirmar
                                    </button>
                                    <button class="btn btn-sm btn-secondary" onclick="mostrarRepro(${index})">
                                        Cancelar
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                    });
                } else {
                    html = '<tr><td colspan="3" class="text-center py-4 text-muted small">Sin faltas pendientes en este ciclo.</td></tr>';
                }
                $('#cuerpoDetalle').html(html);
            }).fail(function() {
                $('#cuerpoDetalle').html('<tr><td colspan="3" class="text-center py-3 text-danger">Error de comunicación.</td></tr>');
            });
        }

        function mostrarRepro(index) {
            $(`#repro_form_${index}`).toggle();
        }

        function confirmarReprogramacion(idEjidatario, tipoEvento, index) {
            const inputFecha = $(`#date_${index}`);
            const fecha = inputFecha.val();
            const hoy = new Date().toISOString().split('T')[0];

            if(!fecha) { alert("Por favor seleccione una fecha."); return; }
            if(fecha < hoy) { alert("No se puede programar una fecha anterior al día de hoy."); return; }

            $.ajax({
                url: "{{ route('reprogramar.falta') }}",
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
                        alert("Error: " + res.message);
                    }
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    alert("Error crítico al procesar la solicitud.");
                }
            });
        }
    </script>
@endsection