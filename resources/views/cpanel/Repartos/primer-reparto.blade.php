@extends('cpanel/plantilla')
@section('title', 'Primer Reparto')

@section('content')
    <style>
        .select2-container--open { z-index: 9999 !important; }
        .select2-container .select2-selection--single { height: 38px !important; display: flex; align-items: center; border: 1px solid #ced4da; }
        .card-header-ejidal { background-color: #1b4b36; color: white; }
        .btn-ejidal { background-color: #1b4b36; color: white; border: none; }
        .btn-ejidal:hover { background-color: #143828; color: white; }

        .info-card { background-color: #f8f9fa; border-left: 5px solid #1b4b36; }
        .info-label { font-weight: bold; color: #6c757d; font-size: 0.9rem; text-transform: uppercase; }
        .info-value { font-weight: 700; color: #1b4b36; font-size: 1.1rem; }
        .text-neto { color: #0d6efd !important; }
    </style>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-hand-holding-usd me-2"></i> Primer Reparto
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-sm btn-ejidal" data-bs-toggle="modal" data-bs-target="#modalPrestamo"
                    @if(isset($deadlinePasada) && $deadlinePasada) disabled title="Periodo Cerrado" @endif>
                <i class="fas fa-plus me-1"></i> Agregar Préstamo
            </button>
        </div>
    </div>

    <div class="card info-card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="info-label">Ejidatario</div>
                    <div id="info-ejidatario" class="info-value">-</div>
                </div>
                <div class="col-md-4">
                    <div class="info-label">Descripción</div>
                    <div id="info-descripcion" class="info-value">-</div>
                </div>
                <div class="col-md-4">
                    <div class="info-label">Disponible (Total)</div>
                    <div id="info-disponible" class="info-value">$0.00</div>
                </div>
                <div class="col-md-6">
                    <div class="info-label">Préstamo Actual</div>
                    <div id="info-prestamo" class="info-value text-danger">$0.00</div>
                </div>
                <div class="col-md-6">
                    <div class="info-label">Saldo Neto Restante</div>
                    <div id="info-neto" class="info-value text-neto">$0.00</div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-4 shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-2"></i> Listado de Préstamos Registrados</span>
            <div style="width: 300px;">
                <input type="text" id="tabla-search" class="form-control form-control-sm" placeholder="Buscar ejidatario...">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabla-prestamos">
                    <thead class="table-light">
                    <tr>
                        <th class="ps-3">NO.</th>
                        <th>EJIDATARIO</th>
                        <th>PRÉSTAMO</th>
                        <th>DESCRIPCIÓN</th>
                        <th>FECHA</th>
                        <th>SALDO RESTANTE (REPARTO 1)</th>
                        <th class="text-center">ACCIONES</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($prestamos as $prestamo)
                        <tr>
                            <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                            <td class="fw-bold">
                                {{ $prestamo->ejidatario->usuario->Nombres ?? '' }} {{ $prestamo->ejidatario->usuario->Apellido_Paterno ?? '' }}
                            </td>
                            <td class="text-danger fw-bold">${{ number_format($prestamo->Cantidad, 2) }}</td>
                            <td class="text-muted small">{{ $prestamo->Motivo }}</td>
                            <td>{{ \Carbon\Carbon::parse($prestamo->Fecha)->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge {{ $prestamo->Saldo_Continuo > 0 ? 'bg-warning text-dark' : 'bg-success' }}">
                                    ${{ number_format($prestamo->Saldo_Continuo, 2) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if(!(isset($deadlinePasada) && $deadlinePasada))
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary btn-editar"
                                                data-id="{{ $prestamo->Id_Prestamo }}"
                                                data-nombre="{{ $prestamo->ejidatario->usuario->Nombres }} {{ $prestamo->ejidatario->usuario->Apellido_Paterno }}"
                                                data-motivo="{{ $prestamo->Motivo }}"
                                                data-cantidad="{{ $prestamo->Cantidad }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-success btn-abonar"
                                                data-url="{{ route('prestamo.abonar', $prestamo->Id_Prestamo) }}"
                                                data-nombre="{{ $prestamo->ejidatario->usuario->Nombres }} {{ $prestamo->ejidatario->usuario->Apellido_Paterno }}"
                                                data-saldo="{{ $prestamo->Saldo_Continuo }}">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </button>
                                        <form action="{{ route('prestamo.eliminar', $prestamo->Id_Prestamo) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('¿Eliminar registro?')">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-muted small"><i class="fas fa-lock"></i> Cerrado</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4">No hay registros.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPrestamo" tabindex="-1" aria-hidden="true"
         data-search-url="{{ route('ejidatarios.buscar') }}"
         data-saldo-url="{{ url('primer-reparto/ejidatario') }}/__ID__/saldo">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-ejidal text-white">
                    <h5 class="modal-title">Nueva Solicitud</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('prestamo.agregar') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">Seleccionar Ejidatario</label>
                                <select id="ejidatario-select" name="id_ejidatario" class="form-control" style="width:100%" required></select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Saldo Disponible</label>
                                <div id="saldo-info" class="alert alert-secondary py-2 text-center">Seleccione ejidatario</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Descripción / Motivo</label>
                                <input type="text" id="input-motivo" name="motivo" class="form-control" required placeholder="Ej. Gastos médicos">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Cantidad ($)</label>
                                <input type="number" id="input-cantidad" name="cantidad" step="0.01" class="form-control" required min="1">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-ejidal">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="form-editar" method="POST">
                @csrf @method('PATCH')
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Editar Préstamo</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Ejidatario</label>
                            <input type="text" id="editar-nombre" class="form-control bg-light" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <input type="text" id="editar-motivo" name="motivo" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Monto</label>
                            <input type="number" id="editar-cantidad" name="cantidad" class="form-control" step="0.01" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalAbono" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="form-abono" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Registrar Abono</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Ejidatario</label>
                            <input type="text" id="abono-nombre" class="form-control bg-light" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Saldo Pendiente</label>
                            <input type="text" id="abono-saldo" class="form-control bg-light text-danger fw-bold" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Monto a Abonar</label>
                            <input type="number" name="monto_abono" class="form-control" step="0.01" required min="0.01">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Confirmar Abono</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            const modalPrestamo = document.getElementById('modalPrestamo');
            let saldoDisponibleGlobal = 0;

            $('#ejidatario-select').select2({
                placeholder: 'Buscar ejidatario...',
                dropdownParent: $('#modalPrestamo'),
                minimumInputLength: 2,
                ajax: {
                    url: modalPrestamo.dataset.searchUrl,
                    dataType: 'json',
                    delay: 250,
                    data: params => ({ q: params.term }),
                    processResults: data => ({ results: data }),
                    cache: true
                }
            });

            $('#ejidatario-select').on('select2:select', function(e) {
                const dataEjidatario = e.params.data;
                const url = modalPrestamo.dataset.saldoUrl.replace('__ID__', dataEjidatario.id);

                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        saldoDisponibleGlobal = parseFloat(data.saldo_disponible) || 0;

                        $('#saldo-info').removeClass('alert-secondary alert-danger').addClass('alert-success fw-bold')
                            .text("Saldo Disponible: $" + saldoDisponibleGlobal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                        $('#info-ejidatario').text(dataEjidatario.text || "-");
                        $('#info-disponible').text("$" + saldoDisponibleGlobal.toLocaleString('en-US', {minimumFractionDigits: 2}));

                        actualizarCalculosPanel();
                    })
                    .catch(err => {
                        console.error("Error:", err);
                        $('#saldo-info').text("Error al obtener saldo").addClass('alert-danger');
                    });
            });

            $('#input-motivo').on('input', function() {
                $('#info-descripcion').text($(this).val() || "-");
            });

            $('#input-cantidad').on('input', function() {
                actualizarCalculosPanel();
            });

            function actualizarCalculosPanel() {
                const cantidadInput = parseFloat($('#input-cantidad').val()) || 0;

                $('#info-prestamo').text("$" + cantidadInput.toLocaleString('en-US', {minimumFractionDigits: 2}));

                const neto = saldoDisponibleGlobal - cantidadInput;

                if(neto < 0) {
                    $('#info-neto').removeClass('text-neto').addClass('text-danger');
                } else {
                    $('#info-neto').removeClass('text-danger').addClass('text-neto');
                }

                $('#info-neto').text("$" + neto.toLocaleString('en-US', {minimumFractionDigits: 2}));
            }

            $(document).on('click', '.btn-editar', function() {
                const id = $(this).data('id');
                const nombre = $(this).data('nombre');
                const motivo = $(this).data('motivo');
                const cantidad = parseFloat($(this).data('cantidad')) || 0;

                const actionUrl = "{{ route('prestamo.actualizar', ':id') }}".replace(':id', id);

                $('#form-editar').attr('action', actionUrl);
                $('#editar-nombre').val(nombre);
                $('#editar-motivo').val(motivo);
                $('#editar-cantidad').val(cantidad.toFixed(2));

                $('#modalEditar').modal('show');
            });

            $(document).on('click', '.btn-abonar', function() {
                const url = $(this).data('url');
                const nombre = $(this).data('nombre');
                const saldo = parseFloat($(this).data('saldo')) || 0;

                $('#form-abono').attr('action', url);
                $('#abono-nombre').val(nombre);
                $('#abono-saldo').val("$" + saldo.toLocaleString('en-US', {minimumFractionDigits: 2}));

                $('#modalAbono').modal('show');
            });

            $('#tabla-search').on('keyup', function() {
                const val = $(this).val().toLowerCase();
                $('#tabla-prestamos tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
                });
            });
        });
    </script>
@endsection