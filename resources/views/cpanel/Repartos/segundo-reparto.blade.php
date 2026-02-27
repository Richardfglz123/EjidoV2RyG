@extends('cpanel/plantilla')
@section('title', 'Segundo Reparto')

@section('content')
    <style>
        .select2-container--open { z-index: 9999 !important; }
        .select2-container .select2-selection--single { height: 38px !important; display: flex; align-items: center; border: 1px solid #ced4da; }
        .card-header-ejidal { background-color: #1b4b36; color: white; }
        .btn-ejidal { background-color: #1b4b36; color: white; border: none; }
        .btn-ejidal:hover { background-color: #143828; color: white; }
        .select2-results__option--highlighted { background-color: #1b4b36 !important; }

        .page-layout { display: flex; gap: 20px; align-items: flex-start; }
        .main-section { flex: 1; min-width: 0; }
        .sidebar-info { width: 300px; position: sticky; top: 20px; }

        .info-panel { background: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; padding: 15px; shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .info-title { font-size: 0.9rem; font-weight: bold; color: #1b4b36; border-bottom: 2px solid #1b4b36; margin-bottom: 12px; padding-bottom: 5px; text-transform: uppercase; }
        .info-item { margin-bottom: 10px; display: flex; flex-direction: column; }
        .info-label { font-size: 0.75rem; color: #666; font-weight: 600; }
        .info-value { font-size: 0.85rem; color: #333; word-break: break-word; }

        .btn-gestionar { padding: 2px 8px; font-size: 0.75rem; font-weight: 600; border-radius: 4px; }
        .table-row-active { background-color: #e9ecef !important; }
    </style>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-file-invoice-dollar me-2"></i> Segundo Reparto
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('reparto.segundo.pdf') }}" target="_blank" class="btn btn-sm btn-outline-danger me-2">
                <i class="fas fa-file-pdf me-1"></i> PDF General
            </a>
            <button type="button" class="btn btn-sm btn-ejidal" data-bs-toggle="modal" data-bs-target="#modalPrestamo"
                    @if($deadlinePasada) disabled title="Periodo Cerrado" @endif>
                <i class="fas fa-plus me-1"></i> Nuevo Préstamo
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="page-layout">
        <div class="main-section">
            <div class="card mb-4 shadow-sm">
                <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-users me-2"></i> Gestión de Reparto y Descuentos</span>
                    <div style="width: 250px;">
                        <input type="text" id="tabla-search" class="form-control form-control-sm" placeholder="Buscar ejidatario...">
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tabla-prestamos">
                            <thead class="table-light">
                            <tr>
                                <th class="ps-3">No.</th>
                                <th>Ejidatario</th>
                                <th>Desc. Asambleas</th>
                                <th>Desc. Faenas</th>
                                <th>Préstamos (2do)</th>
                                <th>Monto Reparto</th>
                                <th class="fw-bold">Total a Pagar</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($ejidatarios as $ejidatario)
                                @php $primerPrestamo = $ejidatario->prestamos->first(); @endphp
                                <tr class="fila-ejidatario"
                                    data-nombre="{{ $ejidatario->usuario?->Nombres }} {{ $ejidatario->usuario?->Apellido_Paterno }}"
                                    data-asambleas="${{ number_format($ejidatario->total_descuento_asambleas, 2) }}"
                                    data-faenas="${{ number_format($ejidatario->total_descuento_faenas, 2) }}"
                                    data-prestamos="${{ number_format($ejidatario->total_prestamos_reparto2, 2) }}"
                                    data-reparto="${{ number_format($montoReparto2, 2) }}"
                                    data-total="${{ number_format($ejidatario->total_a_pagar, 2) }}">

                                    <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                                    <td class="fw-bold">
                                        {{ $ejidatario->usuario?->Nombres }} {{ $ejidatario->usuario?->Apellido_Paterno }}
                                    </td>
                                    <td class="text-danger">-${{ number_format($ejidatario->total_descuento_asambleas, 2) }}</td>
                                    <td class="text-danger">-${{ number_format($ejidatario->total_descuento_faenas, 2) }}</td>
                                    <td class="text-warning fw-bold">${{ number_format($ejidatario->total_prestamos_reparto2, 2) }}</td>
                                    <td class="text-success">${{ number_format($montoReparto2, 2) }}</td>
                                    <td class="bg-light fw-bold text-dark">${{ number_format($ejidatario->total_a_pagar, 2) }}</td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            @if(!$deadlinePasada)
                                                @if($ejidatario->total_prestamos_reparto2 > 0)
                                                    <button type="button" class="btn btn-warning btn-gestionar btn-editar"
                                                            data-id="{{ $primerPrestamo->id_prestamo ?? '' }}"
                                                            data-nombre="{{ $ejidatario->usuario?->Nombres }} {{ $ejidatario->usuario?->Apellido_Paterno }}"
                                                            data-motivo="{{ $primerPrestamo->motivo ?? '' }}"
                                                            data-cantidad="{{ $primerPrestamo->monto_original ?? 0 }}">Préstamos</button>
                                                @endif
                                                @if($ejidatario->total_descuento_faenas > 0)
                                                    <button type="button" class="btn btn-primary btn-gestionar btn-detalle-faenas" data-id="{{ $ejidatario->id_ejidatario }}">Faenas</button>
                                                @endif
                                                @if($ejidatario->total_descuento_asambleas > 0)
                                                    <button type="button" class="btn btn-danger btn-gestionar btn-detalle-asambleas" data-id="{{ $ejidatario->id_ejidatario }}">Asambleas</button>
                                                @endif
                                                @if($primerPrestamo)
                                                    <button type="button" class="btn btn-success btn-gestionar btn-abonar"
                                                            data-id="{{ $primerPrestamo->id_prestamo }}"
                                                            data-nombre="{{ $ejidatario->usuario?->Nombres }} {{ $ejidatario->usuario?->Apellido_Paterno }}"
                                                            data-saldo="{{ $ejidatario->total_a_pagar }}">Abonar</button>
                                                @endif
                                            @else
                                                <span class="text-muted small"><i class="fas fa-lock"></i></span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center py-4">No hay ejidatarios registrados.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-center">
                {{ $ejidatarios->links() }}
            </div>
        </div>

        <div class="sidebar-info">
            <div class="info-panel">
                <h3 class="info-title"><i class="fas fa-info-circle me-1"></i> Información</h3>
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Ejidatario</span>
                        <span class="info-value" id="info-ejidatario">-</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Descripción (Descuentos)</span>
                        <span class="info-value" id="info-descripcion">-</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Disponible (Reparto 2)</span>
                        <span class="info-value" id="info-disponible">-</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Préstamos (2do Rep)</span>
                        <span class="info-value" id="info-prestamo">-</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Total Neto a Pagar</span>
                        <span class="info-value fw-bold text-success" id="info-saldo">-</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPrestamo" aria-hidden="true" data-search-url="{{ route('ejidatarios.buscar') }}">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-ejidal text-white">
                    <h5 class="modal-title">Solicitud de Préstamo (2do Reparto)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('prestamo2.agregar') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">Buscar Ejidatario</label>
                                <select id="ejidatario-select" name="id_ejidatario" class="form-control" style="width:100%" required></select>
                            </div>
                            <div class="col-md-12">
                                <div id="saldo-info" class="alert alert-secondary py-2 text-center">Selecciona un ejidatario para ver su saldo.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Descripción</label>
                                <input type="text" name="motivo" class="form-control" required placeholder="Ej. Préstamo personal">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cantidad</label>
                                <input type="number" name="cantidad" step="0.01" class="form-control" required min="0.01">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-ejidal">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditar" aria-hidden="true">
        <div class="modal-dialog">
            <form id="form-editar" method="POST">
                @csrf @method('PATCH')
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Editar Préstamo (Reparto 2)</h5>
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
                            <label class="form-label">Monto (Préstamo Original)</label>
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

    <div class="modal fade" id="modalAbono" aria-hidden="true">
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
                            <label class="form-label">Deuda Actual</label>
                            <input type="text" id="abono-saldo" class="form-control bg-light fw-bold text-danger" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Monto a Abonar</label>
                            <input type="number" name="monto_abono" class="form-control" step="0.01" required min="0.01">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Guardar Abono</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalDetalles" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" id="modal-detalles-header">
                    <h5 class="modal-title" id="modal-detalles-title">Detalle</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="detalle-mensaje" class="alert alert-info py-2 small mb-3"></div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                            <tr id="detalle-tabla-head"></tr>
                            </thead>
                            <tbody id="detalle-tabla-body"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            $('.fila-ejidatario').on('mouseenter click', function() {
                $('.fila-ejidatario').removeClass('table-row-active');
                $(this).addClass('table-row-active');

                const d = $(this).data();
                $('#info-ejidatario').text(d.nombre);
                $('#info-descripcion').text(`Asambleas: ${d.asambleas} | Faenas: ${d.faenas}`);
                $('#info-disponible').text(d.reparto);
                $('#info-prestamo').text(d.prestamos);
                $('#info-saldo').text(d.total);
            });

            $('#ejidatario-select').select2({
                placeholder: 'Escribe nombre del ejidatario...',
                dropdownParent: $('#modalPrestamo'),
                minimumInputLength: 2,
                ajax: {
                    url: '{{ route("ejidatarios.buscar") }}',
                    dataType: 'json',
                    delay: 300,
                    data: params => ({ q: params.term }),
                    processResults: data => ({ results: data }),
                    cache: true
                }
            });

            $('#ejidatario-select').on('select2:select', function(e) {
                const ejidatarioId = e.params.data.id;
                $('#saldo-info').html('<i class="fas fa-spinner fa-spin"></i> Consultando disponible...');
                $.get('/segundo-reparto/ejidatario/' + ejidatarioId + '/saldo')
                    .done(function(res) {
                        if (res.success) {
                            const monto = parseFloat(res.saldo_disponible).toLocaleString('en-US', { minimumFractionDigits: 2 });
                            $('#saldo-info').removeClass('alert-secondary alert-danger').addClass('alert-success fw-bold')
                                .html('💵 Disponible para este reparto: <strong>$' + monto + '</strong>');
                        }
                    });
            });

            $(document).on('click', '.btn-editar', function() {
                const id = $(this).data('id');
                $('#form-editar').attr('action', '/prestamo2/actualizar/' + id);
                $('#editar-nombre').val($(this).data('nombre'));
                $('#editar-motivo').val($(this).data('motivo'));
                $('#editar-cantidad').val($(this).data('cantidad'));
                $('#modalEditar').modal('show');
            });

            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        $('.fila-ejidatario').on('mouseenter click', function() {
            $('.fila-ejidatario').removeClass('table-row-active');
            $(this).addClass('table-row-active');

            const d = $(this).data();
            $('#info-ejidatario').text(d.nombre);
            $('#info-descripcion').text(`Asambleas: ${d.asambleas} | Faenas: ${d.faenas}`);
            $('#info-disponible').text(d.reparto);
            $('#info-prestamo').text(d.prestamos);
            $('#info-saldo').text(d.total);
        });

        $('#ejidatario-select').select2({
            placeholder: 'Escribe nombre del ejidatario...',
            dropdownParent: $('#modalPrestamo'),
            minimumInputLength: 2,
            ajax: {
                url: '{{ route("ejidatarios.buscar") }}',
                dataType: 'json',
                delay: 300,
                data: params => ({ q: params.term }),
                processResults: data => ({ results: data }),
                cache: true
            }
        });

        $('#ejidatario-select').on('select2:select', function(e) {
            const ejidatarioId = e.params.data.id;
            $('#saldo-info').html('<i class="fas fa-spinner fa-spin"></i> Consultando disponible...');
            $.get('/segundo-reparto/ejidatario/' + ejidatarioId + '/saldo')
                .done(function(res) {
                    if (res.success) {
                        const monto = parseFloat(res.saldo_disponible).toLocaleString('en-US', { minimumFractionDigits: 2 });
                        $('#saldo-info').removeClass('alert-secondary alert-danger').addClass('alert-success fw-bold')
                            .html('💵 Disponible para este reparto: <strong>$' + monto + '</strong>');
                    }
                });
        });

        // Botón Editar/Gestionar Préstamo
        $(document).on('click', '.btn-editar', function() {
            const id = $(this).data('id');
            $('#form-editar').attr('action', '/prestamo2/actualizar/' + id);
            $('#editar-nombre').val($(this).data('nombre'));
            $('#editar-motivo').val($(this).data('motivo'));
            $('#editar-cantidad').val($(this).data('cantidad'));
            $('#modalEditar').modal('show');
        });

        // Botón Abonar
        $(document).on('click', '.btn-abonar', function() {
            const id = $(this).data('id');
            $('#form-abono').attr('action', '/prestamo2/abonar/' + id);
            $('#abono-nombre').val($(this).data('nombre'));
            $('#abono-saldo').val('$' + parseFloat($(this).data('saldo')).toLocaleString('en-US', { minimumFractionDigits: 2 }));
            $('#modalAbono').modal('show');
        });

        $('#tabla-search').on('keyup', function() {
            const val = $(this).val().toLowerCase();
            $('#tabla-prestamos tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
            });
        });

        $('.btn-detalle-faenas, .btn-detalle-asambleas').on('click', function() {
            const tipo = $(this).hasClass('btn-detalle-faenas') ? 'Faenas' : 'Asambleas';
            const color = tipo === 'Faenas' ? 'bg-primary' : 'bg-danger';
            const msg = tipo === 'Faenas'
                ? 'Aquí se listan los descuentos por faenas no realizadas. Puedes "Reprogramar/Perdonar" el descuento para eliminarlo.'
                : 'Aquí se listan los descuentos por inasistencia a asambleas. Puedes "Reprogramar/Perdonar" el descuento para eliminarlo.';

            $('#modal-detalles-header').removeClass('bg-primary bg-danger').addClass(color + ' text-white');
            $('#modal-detalles-title').text('Detalle de ' + tipo + ' Pendientes');
            $('#detalle-mensaje').text(msg);
            $('#detalle-tabla-head').html(`<th>DESCRIPCIÓN DE ${tipo.toUpperCase()}</th><th>MONTO DESCUENTO</th><th>ACCIONES</th>`);

            $('#modalDetalles').modal('show');
        });
        tón Abonar
            $(document).on('click', '.btn-abonar', function() {
                const id = $(this).data('id');
                $('#form-abono').attr('action', '/prestamo2/abonar/' + id);
                $('#abono-nombre').val($(this).data('nombre'));
                $('#abono-saldo').val('$' + parseFloat($(this).data('saldo')).toLocaleString('en-US', { minimumFractionDigits: 2 }));
                $('#modalAbono').modal('show');
            });

            $('#tabla-search').on('keyup', function() {
                const val = $(this).val().toLowerCase();
                $('#tabla-prestamos tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
                });
            });

            $('.btn-detalle-faenas, .btn-detalle-asambleas').on('click', function() {
                const tipo = $(this).hasClass('btn-detalle-faenas') ? 'Faenas' : 'Asambleas';
                const color = tipo === 'Faenas' ? 'bg-primary' : 'bg-danger';
                const msg = tipo === 'Faenas'
                    ? 'Aquí se listan los descuentos por faenas no realizadas. Puedes "Reprogramar/Perdonar" el descuento para eliminarlo.'
                    : 'Aquí se listan los descuentos por inasistencia a asambleas. Puedes "Reprogramar/Perdonar" el descuento para eliminarlo.';

                $('#modal-detalles-header').removeClass('bg-primary bg-danger').addClass(color + ' text-white');
                $('#modal-detalles-title').text('Detalle de ' + tipo + ' Pendientes');
                $('#detalle-mensaje').text(msg);
                $('#detalle-tabla-head').html(`<th>DESCRIPCIÓN DE ${tipo.toUpperCase()}</th><th>MONTO DESCUENTO</th><th>ACCIONES</th>`);

                $('#modalDetalles').modal('show');
            });
        });
    </script>
@endsection