@extends('cpanel/plantilla')
@section('title', 'Primer Reparto')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal fw-normal">
            <i class="fas fa-hand-holding-usd me-2"></i> Primer Reparto
        </h1>
        <div class="d-flex gap-2">
            <div class="input-group input-group-sm" style="width: 250px;">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" id="buscador-tabla" class="form-control border-start-0" placeholder="Buscar en préstamos...">
            </div>

            <button type="button" class="btn btn-ejidal shadow-sm px-3 fw-normal" data-bs-toggle="modal" data-bs-target="#modalPrestamo"
                    @if(isset($deadlinePasada) && $deadlinePasada) disabled @endif>
                <i class="fas fa-plus me-1"></i> Agregar Préstamo
            </button>
        </div>
    </div>

    @if(isset($deadlinePasada) && $deadlinePasada)
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-3 fa-lg"></i>
            <div class="fw-normal">
                Periodo de préstamos cerrado. La fecha límite ({{ \Carbon\Carbon::parse($reparto1->Fecha_Eliminado)->format('d/m/Y') }}) ha vencido. Ya no es posible registrar o modificar movimientos en el primer reparto.
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm fw-normal" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card card-ejidal shadow-sm border-0">
        <div class="card-header card-header-ejidal py-3 fw-normal">
            <i class="fas fa-list me-2"></i> Préstamos Registrados (Fondo: ${{ number_format($montoReparto1, 2) }})
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabla-prestamos">
                    <thead>
                    <tr class="bg-light fw-normal" style="font-size: 0.9rem;">
                        <th class="ps-3 text-muted fw-normal" width="50">#</th>
                        <th class="fw-normal">EJIDATARIO</th>
                        <th class="fw-normal">DEUDA ACTUAL</th>
                        <th class="fw-normal">DESCRIPCIÓN</th>
                        <th class="fw-normal">FECHA</th>
                        <th class="fw-normal">SALDO DISPONIBLE</th>
                        <th class="text-center fw-normal">ACCIONES</th>
                    </tr>
                    </thead>
                    <tbody class="fw-normal">
                    @forelse ($prestamos as $prestamo)
                        <tr>
                            <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                            <td class="text-dark">
                                {{ $prestamo->ejidatario->usuario->Nombres ?? '' }} {{ $prestamo->ejidatario->usuario->Apellido_Paterno ?? '' }}
                            </td>
                            <td class="text-danger fw-normal">${{ number_format($prestamo->Cantidad, 2) }}</td>
                            <td class="text-muted small">{{ $prestamo->Motivo }}</td>
                            <td>{{ \Carbon\Carbon::parse($prestamo->Fecha)->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge border border-success text-dark fw-normal" style="background-color: #f0fdf4;">
                                    ${{ number_format($montoReparto1 - $prestamo->Cantidad, 2) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if(!(isset($deadlinePasada) && $deadlinePasada))
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-secondary btn-editar"
                                                data-id="{{ $prestamo->Id_Prestamo }}"
                                                data-idejidatario="{{ $prestamo->Id_Ejidatario }}"
                                                data-nombre="{{ $prestamo->ejidatario->usuario->Nombres }} {{ $prestamo->ejidatario->usuario->Apellido_Paterno }}"
                                                data-motivo="{{ $prestamo->Motivo }}"
                                                data-cantidad="{{ $prestamo->Cantidad }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-abonar"
                                                style="border: 1px solid #1b4b36 !important;"
                                                data-url="{{ route('prestamo.abonar', $prestamo->Id_Prestamo) }}"
                                                data-nombre="{{ $prestamo->ejidatario->usuario->Nombres }} {{ $prestamo->ejidatario->usuario->Apellido_Paterno }}"
                                                data-saldo="{{ $prestamo->Cantidad }}">
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
                                    <span class="badge bg-light text-muted fw-normal border px-3"><i class="fas fa-lock me-1"></i> Cerrado</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-5 text-muted">No hay registros de préstamos.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL AGREGAR --}}
    <div class="modal fade" id="modalPrestamo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header card-header-ejidal">
                    <h5 class="modal-title text-white fw-normal"><i class="fas fa-plus-circle me-2"></i>Nueva Solicitud</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="form-agregar-prestamo" action="{{ route('prestamo.agregar') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4 fw-normal">
                        <div class="mb-3 text-start">
                            <label class="text-muted small text-uppercase">Ejidatario</label>
                            <select id="ejidatario-select" name="id_ejidatario" class="form-control" style="width:100%" required></select>
                        </div>
                        <div class="row mb-3 text-center bg-light py-3 rounded mx-0">
                            <div class="col-6 border-end">
                                <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem;">Saldo Disponible</small>
                                <span id="saldo-info" class="text-ejidal h5 mb-0 fw-normal">$0.00</span>
                            </div>
                            <div class="col-6">
                                <label class="small text-muted text-uppercase mb-1">Monto a Prestar</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-end-0">$</span>
                                    <input type="number" id="input-cantidad" name="cantidad" step="0.01" class="form-control text-center fw-normal border-start-0" required min="0.01">
                                </div>
                            </div>
                        </div>
                        <div class="mb-0 text-start">
                            <label class="text-muted small text-uppercase">Motivo del Préstamo</label>
                            <input type="text" name="motivo" class="form-control fw-normal" required placeholder="Ej. Gastos médicos">
                        </div>
                    </div>
                    <div class="modal-footer bg-light justify-content-center">
                        <button type="button" class="btn btn-secondary px-4 fw-normal" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-ejidal px-4 fw-normal" id="btn-guardar-prestamo">
                            <i class="fas fa-save me-1"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL EDITAR --}}
    <div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header card-header-ejidal">
                    <h5 class="modal-title text-white fw-normal"><i class="fas fa-edit me-2"></i>Editar Préstamo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="form-editar" method="POST">
                    @csrf @method('PATCH')
                    <div class="modal-body p-4 fw-normal">
                        <div class="mb-3 text-start">
                            <label class="text-muted small text-uppercase">Ejidatario</label>
                            <input type="text" id="editar-nombre" class="form-control bg-light border-0 fw-normal" readonly>
                        </div>
                        <div class="mb-3 text-start">
                            <label class="text-muted small text-uppercase">Monto ($)</label>
                            <input type="number" id="editar-cantidad" name="cantidad" class="form-control fw-normal" step="0.01" required min="0.01">
                            <small id="error-editar-cantidad" class="text-danger d-none"></small>
                        </div>
                        <div class="mb-0 text-start">
                            <label class="text-muted small text-uppercase">Descripción</label>
                            <input type="text" id="editar-motivo" name="motivo" class="form-control fw-normal" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light justify-content-center">
                        <button type="button" class="btn btn-secondary fw-normal" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-ejidal px-4 fw-normal">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL ABONO --}}
    <div class="modal fade" id="modalAbono" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow text-center">
                <div class="modal-header card-header-ejidal">
                    <h5 class="modal-title mx-auto text-white fw-normal"><i class="fas fa-cash-register me-2"></i>Registrar Abono</h5>
                    <button type="button" class="btn-close btn-close-white ms-0" data-bs-dismiss="modal"></button>
                </div>
                <form id="form-abono" method="POST">
                    @csrf
                    <div class="modal-body p-4 fw-normal">
                        <h6 id="abono-nombre" class="mb-3 text-dark fw-normal">Cargando...</h6>
                        <div class="alert alert-warning py-2 border-0 shadow-sm fw-normal">
                            Deuda actual: <span id="abono-saldo" class="text-danger fw-normal">$0.00</span>
                        </div>
                        <div class="mb-0">
                            <label class="text-muted small text-uppercase mb-2">Monto a Abonar ($)</label>
                            <input type="number" id="input-abono" name="monto_abono" class="form-control form-control-lg text-center text-ejidal fw-normal" step="0.01" required min="0.01">
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

    {{-- SCRIPTS --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            let saldoMaximoPrestamo = 0;
            let deudaActualAbono = 0;
            let saldoDisponibleParaEditar = 0;

            $("#buscador-tabla").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#tabla-prestamos tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            $('#ejidatario-select').select2({
                placeholder: 'Seleccionar ejidatario...',
                dropdownParent: $('#modalPrestamo'),
                ajax: {
                    url: "{{ route('ejidatarios.buscar') }}",
                    dataType: 'json',
                    delay: 250,
                    data: params => ({ q: params.term }),
                    processResults: data => ({ results: data }),
                    cache: true
                }
            });

            $('#ejidatario-select').on('select2:select', function(e) {
                const id = e.params.data.id;
                let url = "{{ route('prestamo.saldo', ':id') }}".replace(':id', id);
                $('#saldo-info').html('<i class="fas fa-spinner fa-spin"></i>');
                fetch(url).then(res => res.json()).then(data => {
                    saldoMaximoPrestamo = parseFloat(data.saldo_disponible) || 0;
                    $('#saldo-info').text("$" + saldoMaximoPrestamo.toLocaleString('en-US', { minimumFractionDigits: 2 }));
                    $('#input-cantidad').attr('max', saldoMaximoPrestamo);
                });
            });

            // Validación Agregar Préstamo
            $('#form-agregar-prestamo').on('submit', function(e) {
                const cantidad = parseFloat($('#input-cantidad').val());
                if (cantidad <= 0) {
                    e.preventDefault();
                    alert('El monto debe ser mayor a 0.');
                    return false;
                }
                if (cantidad > saldoMaximoPrestamo) {
                    e.preventDefault();
                    alert('Error: El monto excede el saldo disponible ($' + saldoMaximoPrestamo.toLocaleString('en-US', { minimumFractionDigits: 2 }) + ')');
                    return false;
                }
            });

            // Validación Abono
            $('#form-abono').on('submit', function(e) {
                const abono = parseFloat($('#input-abono').val());
                if (abono <= 0) {
                    e.preventDefault();
                    alert('El monto del abono debe ser mayor a 0.');
                    return false;
                }
                if (abono > deudaActualAbono) {
                    e.preventDefault();
                    alert('Error: No puedes abonar más de la deuda actual ($' + deudaActualAbono.toLocaleString('en-US', { minimumFractionDigits: 2 }) + ')');
                    return false;
                }
            });

            // Modal Editar - Cargar datos
            $(document).on('click', '.btn-editar', function() {
                const idPrestamo = $(this).data('id');
                const idEjidatario = $(this).data('idejidatario');
                const cantidadActual = parseFloat($(this).data('cantidad'));
                const nombre = $(this).data('nombre');
                const motivo = $(this).data('motivo');

                $('#form-editar').attr('action', "{{ route('prestamo.actualizar', ':id') }}".replace(':id', idPrestamo));
                $('#editar-nombre').val(nombre);
                $('#editar-motivo').val(motivo);
                $('#editar-cantidad').val(cantidadActual);
                $('#error-editar-cantidad').addClass('d-none');

                let url = "{{ route('prestamo.saldo', ':id') }}".replace(':id', idEjidatario);
                fetch(url).then(res => res.json()).then(data => {
                    // El saldo disponible real para editar es (SaldoActualEnFondo + LoQueYaTeníaPrestado)
                    saldoDisponibleParaEditar = (parseFloat(data.saldo_disponible) || 0) + cantidadActual;
                    $('#editar-cantidad').attr('max', saldoDisponibleParaEditar);
                });

                $('#modalEditar').modal('show');
            });

            // Validación Editar
            $('#form-editar').on('submit', function(e) {
                const cantidadNueva = parseFloat($('#editar-cantidad').val());
                if (cantidadNueva <= 0) {
                    e.preventDefault();
                    alert('El monto debe ser mayor a 0.');
                    return false;
                }
                if (cantidadNueva > saldoDisponibleParaEditar) {
                    e.preventDefault();
                    $('#error-editar-cantidad').removeClass('d-none').text('Error: El límite es $' + saldoDisponibleParaEditar.toLocaleString('en-US', { minimumFractionDigits: 2 }));
                    alert('No puedes superar el saldo disponible de $' + saldoDisponibleParaEditar.toLocaleString('en-US', { minimumFractionDigits: 2 }));
                    return false;
                }
            });

            // Modal Abonar - Cargar datos
            $(document).on('click', '.btn-abonar', function() {
                deudaActualAbono = parseFloat($(this).data('saldo'));
                $('#form-abono').attr('action', $(this).data('url'));
                $('#abono-nombre').text($(this).data('nombre'));
                $('#abono-saldo').text("$" + deudaActualAbono.toLocaleString('en-US', { minimumFractionDigits: 2 }));
                $('#input-abono').attr('max', deudaActualAbono).val('');
                $('#modalAbono').modal('show');
            });
        });
    </script>
@endsection