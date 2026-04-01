@extends('cpanel/plantilla')
@section('title', 'Segundo Reparto')

@section('content')
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal fw-normal">
            <i class="fas fa-file-invoice-dollar me-2"></i> Segundo Reparto
        </h1>

        <form action="{{ route('reparto.segundo') }}" method="GET">
            <div class="input-group input-group-sm" style="width: 300px;">
                <span class="input-group-text bg-white border-end-0">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input type="text" name="query" class="form-control border-start-0 ps-0" placeholder="Buscar ejidatario..." value="{{ request('query') }}">
                <button class="btn btn-ejidal px-3 fw-normal" type="submit">Buscar</button>
            </div>
        </form>
    </div>

    <div class="card card-ejidal shadow-sm border-0">
        <div class="card-header card-header-ejidal py-3 d-flex justify-content-between align-items-center">
            <span class="fw-normal text-white"><i class="fas fa-list-ol me-2"></i> RESUMEN DE LIQUIDACIÓN</span>
            <span class="badge bg-white text-dark fw-normal p-2">MONTO BASE R2: ${{ number_format($montoFijoR2, 2) }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center text-dark" id="tabla-reparto">
                    <thead>
                    <tr class="bg-light text-muted small text-uppercase">
                        <th class="py-3">No.</th>
                        <th class="text-start">EJIDATARIO</th>
                        <th>DESC. ASAMBLEA</th>
                        <th>DESC. FAENAS</th>
                        <th>PRÉSTAMOS (R1)</th>
                        <th>MONTO BASE</th>
                        <th class="bg-light">TOTAL A PAGAR</th>
                        <th>ACCIONES</th>
                    </tr>
                    </thead>
                    <tbody class="fw-normal">
                    @forelse($ejidatarios as $ejidatario)
                        <tr>
                            <td class="text-muted small">{{ ($ejidatarios->currentPage() - 1) * $ejidatarios->perPage() + $loop->iteration }}</td>
                            <td class="text-start text-uppercase small">
                                <strong>{{ $ejidatario->usuario?->Nombres }} {{ $ejidatario->usuario?->Apellido_Paterno }}</strong>
                            </td>

                            <td>
                                @if($ejidatario->total_asambleas > 0)
                                    <button type="button" class="btn btn-sm btn-outline-danger py-0 fw-bold" onclick="verDetalle({{ $ejidatario->Id_Ejidatario }}, 'asambleas')">
                                        -${{ number_format($ejidatario->total_asambleas, 2) }}
                                    </button>
                                @else
                                    <span class="text-muted small">--</span>
                                @endif
                            </td>

                            <td>
                                @if($ejidatario->total_faenas > 0)
                                    <button type="button" class="btn btn-sm btn-outline-danger py-0 fw-bold" onclick="verDetalle({{ $ejidatario->Id_Ejidatario }}, 'faenas')">
                                        -${{ number_format($ejidatario->total_faenas, 2) }}
                                    </button>
                                @else
                                    <span class="text-muted small">--</span>
                                @endif
                            </td>

                            <td class="text-danger fw-bold">
                                ${{ number_format($ejidatario->deuda_arrastrada_r1, 2) }}
                            </td>

                            <td class="text-muted">${{ number_format($montoFijoR2, 2) }}</td>

                            <td class="bg-light fw-bold text-dark h6 mb-0">
                                ${{ number_format($ejidatario->total_a_pagar, 2) }}
                            </td>

                            <td>
                                @php $prestamo = $ejidatario->prestamos->first(); @endphp
                                @if($ejidatario->deuda_arrastrada_r1 > 0 && $prestamo)
                                    <button type="button" class="btn btn-warning btn-sm text-dark fw-bold shadow-sm"
                                            onclick="abrirModalAbono('{{ $ejidatario->usuario?->Nombres }} {{ $ejidatario->usuario?->Apellido_Paterno }}', {{ $ejidatario->deuda_arrastrada_r1 }}, '{{ route('prestamo.abonar', $prestamo->Id_Prestamo) }}')">
                                        <i class="fas fa-hand-holding-usd me-1"></i> Abono
                                    </button>
                                @else
                                    <span class="badge bg-light text-success border small"><i class="fas fa-check"></i> Pagado</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-4">No se encontraron resultados.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0">
            {{ $ejidatarios->links() }}
        </div>
    </div>

    {{-- MODAL DETALLE (Asambleas/Faenas) --}}
    <div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="tituloDetalle">Detalles</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <table class="table mb-0">
                        <thead class="small text-uppercase bg-light">
                        <tr><th>Concepto</th><th>Monto</th><th class="text-center">Acción</th></tr>
                        </thead>
                        <tbody id="cuerpoDetalle"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL ABONO --}}
    <div class="modal fade" id="modalAbono" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content card-ejidal border-0 shadow-lg">
                <div class="modal-header card-header-ejidal border-0">
                    <h5 class="modal-title fw-normal text-white"><i class="fas fa-coins me-2"></i> Registrar Abono</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formAbono" method="POST">
                    @csrf
                    <div class="modal-body p-4 bg-white text-dark text-center">
                        <h6 id="nombreEjidatario" class="fw-bold mb-1"></h6>
                        <div class="p-2 border rounded bg-light mb-3">
                            <small class="text-danger d-block fw-bold small">SALDO PENDIENTE</small>
                            <span class="h4 mb-0 fw-bold text-danger" id="saldoPendiente"></span>
                        </div>
                        <div class="form-group text-start">
                            <label class="small fw-bold mb-2">MONTO A ABONAR:</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="monto_abono" id="inputMonto" class="form-control fw-bold text-center" step="0.01" min="0.01" required>
                                <button type="button" class="btn btn-dark" onclick="liquidarTodo()">TODO</button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-ejidal px-3">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- SCRIPTS FINALES --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let saldoGlobal = 0;

        // --- LÓGICA DE DETALLES Y ELIMINACIÓN ---
        function verDetalle(id, tipo) {
            const url = tipo === 'asambleas' ? `/detalle-asambleas/${id}` : `/detalle-faenas/${id}`;
            $('#tituloDetalle').text(tipo === 'asambleas' ? 'DESCUENTOS POR ASAMBLEAS' : 'DESCUENTOS POR FAENAS');
            $('#cuerpoDetalle').html('<tr><td colspan="3" class="text-center py-3">Cargando...</td></tr>');
            $('#modalDetalle').modal('show');

            $.get(url, function(data) {
                let html = '';
                if(data.length > 0) {
                    data.forEach(d => {
                        // Verificamos si el ID viene como 'id' o 'Id_Descuento' según tu base de datos
                        let idDescuento = d.id || d.Id_Descuento;
                        html += `<tr>
                            <td class="small text-uppercase">${d.tipo}</td>
                            <td class="text-danger fw-bold">-$${parseFloat(d.Descuento).toFixed(2)}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger py-0" onclick="eliminarDescuento(${idDescuento})">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>`;
                    });
                } else {
                    html = '<tr><td colspan="3" class="text-center py-3 text-muted">Sin registros.</td></tr>';
                }
                $('#cuerpoDetalle').html(html);
            });
        }

        function eliminarDescuento(id) {
            if(!confirm('¿Deseas perdonar/eliminar este descuento? El total a pagar se actualizará.')) return;

            $.ajax({
                url: `/descuento/eliminar/${id}`,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    if(res.success) {
                        location.reload();
                    } else {
                        alert('Error al eliminar');
                    }
                }
            });
        }

        // --- LÓGICA DE ABONOS (RESTAURADA Y PROBADA) ---
        function abrirModalAbono(nombre, saldo, url) {
            saldoGlobal = parseFloat(saldo);
            $('#nombreEjidatario').text(nombre);
            $('#saldoPendiente').text('$' + saldoGlobal.toLocaleString('en-US', {minimumFractionDigits: 2}));
            $('#inputMonto').val('').attr('max', saldoGlobal);
            $('#formAbono').attr('action', url);
            $('#modalAbono').modal('show');
        }

        function liquidarTodo() {
            $('#inputMonto').val(saldoGlobal.toFixed(2));
        }

        $('#formAbono').on('submit', function(e) {
            let monto = parseFloat($('#inputMonto').val());
            if (monto > saldoGlobal) {
                e.preventDefault();
                alert('No puedes abonar más del saldo pendiente ($' + saldoGlobal.toFixed(2) + ')');
            }
        });
    </script>
@endsection