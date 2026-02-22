@extends('cpanel/plantilla')
@section('title', 'Segundo Reparto')

@section('content')
    <style>
        /* Estilos para corregir visibilidad y estética de Select2 */
        .select2-container--open { z-index: 9999 !important; }
        .select2-container .select2-selection--single { height: 38px !important; display: flex; align-items: center; border: 1px solid #ced4da; }
        .card-header-ejidal { background-color: #1b4b36; color: white; }
        .btn-ejidal { background-color: #1b4b36; color: white; border: none; }
        .btn-ejidal:hover { background-color: #143828; color: white; }
        .select2-results__option--highlighted { background-color: #1b4b36 !important; }
    </style>

    {{-- Select2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Encabezado --}}
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

    {{-- Tabla de Gestión General --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-users me-2"></i> Gestión de Reparto y Descuentos</span>
            <div style="width: 300px;">
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
                        <tr>
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
                                @if(!$deadlinePasada)
                                    <div class="btn-group btn-group-sm">
                                        @if($primerPrestamo)
                                            {{-- Botón Editar Préstamo --}}
                                            <button type="button" class="btn btn-outline-primary btn-editar"
                                                    data-id="{{ $primerPrestamo->id_prestamo }}"
                                                    data-nombre="{{ $ejidatario->usuario?->Nombres }} {{ $ejidatario->usuario?->Apellido_Paterno }}"
                                                    data-motivo="{{ $primerPrestamo->motivo }}"
                                                    data-cantidad="{{ $primerPrestamo->monto_original }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            {{-- Botón Abonar --}}
                                            <button type="button" class="btn btn-outline-success btn-abonar"
                                                    data-id="{{ $primerPrestamo->id_prestamo }}"
                                                    data-nombre="{{ $ejidatario->usuario?->Nombres }} {{ $ejidatario->usuario?->Apellido_Paterno }}"
                                                    data-saldo="{{ $ejidatario->total_a_pagar }}">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </button>
                                        @else
                                            <span class="text-muted small">Sin préstamo</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted small"><i class="fas fa-lock"></i> Cerrado</span>
                                @endif
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

    {{-- MODAL NUEVO PRÉSTAMO (Sin tabindex para Select2) --}}
    <div class="modal fade" id="modalPrestamo" aria-hidden="true"
         data-search-url="{{ route('ejidatarios.buscar') }}">
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
                                <label class="form-label fw-bold">Seleccionar Ejidatario</label>
                                <select id="ejidatario-select" name="id_ejidatario" class="form-control" style="width:100%" required></select>
                            </div>
                            <div class="col-md-12">
                                <div id="saldo-info" class="alert alert-secondary py-2 text-center">Seleccione un ejidatario para ver su disponible</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Descripción</label>
                                <input type="text" name="motivo" class="form-control" required placeholder="Ej. Préstamo personal">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cantidad a Prestar ($)</label>
                                <input type="number" name="cantidad" step="0.01" class="form-control" required min="0.01">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-ejidal">Guardar Préstamo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL EDITAR --}}
    <div class="modal fade" id="modalEditar" aria-hidden="true">
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
                            <label class="form-label">Monto Original ($)</label>
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

    {{-- MODAL ABONO --}}
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
                            <label class="form-label">Saldo Actual a Pagar</label>
                            <input type="text" id="abono-saldo" class="form-control bg-light fw-bold text-danger" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Monto a Abonar ($)</label>
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

    {{-- Paginación --}}
    <div class="d-flex justify-content-center mt-3">
        {{ $ejidatarios->links() }}
    </div>



        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

        <script>
            $(document).ready(function() {
                // Configuración de seguridad CSRF
                $.ajaxSetup({
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
                });

                // 1. SELECT2: Búsqueda de ejidatarios
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

                // 2. GESTIÓN DE SALDO: Al seleccionar un ejidatario
                $('#ejidatario-select').on('select2:select', function(e) {
                    const data = e.params.data;
                    const ejidatarioId = data.id; // El controlador mapea Id_Ejidatario a id

                    $('#saldo-info').html('<i class="fas fa-spinner fa-spin"></i> Calculando disponible...');

                    // Llamada a la ruta del Segundo Reparto (NO al primero)
                    $.get('/segundo-reparto/ejidatario/' + ejidatarioId + '/saldo')
                        .done(function(res) {
                            if (res.success) {
                                const monto = parseFloat(res.saldo_disponible).toLocaleString('en-US', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                                $('#saldo-info')
                                    .removeClass('alert-secondary alert-danger')
                                    .addClass('alert-success fw-bold')
                                    .html('💵 Disponible para este reparto: <strong>$' + monto + '</strong>');
                            }
                        })
                        .fail(function(xhr) {
                            console.error("Error saldo:", xhr.responseText);
                            $('#saldo-info')
                                .removeClass('alert-success alert-secondary')
                                .addClass('alert-danger')
                                .text('Error al obtener el saldo del servidor.');
                        });
                });

                // 3. ACCIÓN: Editar Préstamo
                $(document).on('click', '.btn-editar', function() {
                    const id = $(this).data('id');
                    $('#form-editar').attr('action', '/prestamo2/actualizar/' + id);
                    $('#editar-nombre').val($(this).data('nombre'));
                    $('#editar-motivo').val($(this).data('motivo'));
                    $('#editar-cantidad').val($(this).data('cantidad'));
                    $('#modalEditar').modal('show');
                });

                // 4. ACCIÓN: Registrar Abono
                $(document).on('click', '.btn-abonar', function() {
                    const id = $(this).data('id');
                    $('#form-abono').attr('action', '/prestamo2/abonar/' + id);
                    $('#abono-nombre').val($(this).data('nombre'));

                    const saldo = parseFloat($(this).data('saldo')).toLocaleString('en-US', {
                        minimumFractionDigits: 2
                    });
                    $('#abono-saldo').val('$' + saldo);
                    $('#modalAbono').modal('show');
                });

                // 5. BUSCADOR DE TABLA
                $('#tabla-search').on('keyup', function() {
                    const val = $(this).val().toLowerCase();
                    $('#tabla-prestamos tbody tr').filter(function() {
                        $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
                    });
                });

                // RESETEAR MODAL AL CERRAR
                $('#modalPrestamo').on('hidden.bs.modal', function () {
                    $('#ejidatario-select').val(null).trigger('change');
                    $('#saldo-info').removeClass('alert-success alert-danger').addClass('alert-secondary')
                        .text('Seleccione un ejidatario para ver su disponible');
                });
            });
        </script>
    @endsection