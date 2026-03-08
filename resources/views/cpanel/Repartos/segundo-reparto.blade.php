@extends('cpanel/plantilla')
@section('title', 'Segundo Reparto')

@section('content')
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal fw-normal">
            <i class="fas fa-file-invoice-dollar me-2"></i> Segundo Reparto
        </h1>
        <form action="{{ route('reparto.segundo') }}" method="GET" class="input-group input-group-sm" style="width: 300px;">
            <input type="text" name="query" class="form-control border-end-0" placeholder="Buscar ejidatario..." value="{{ request('query') }}">
            <button class="btn btn-outline-secondary border-start-0 bg-white" type="submit"><i class="fas fa-search"></i></button>
        </form>
    </div>

    <div class="card card-ejidal shadow-sm border-0">
        <div class="card-header card-header-ejidal py-3 d-flex justify-content-between align-items-center">
            <span class="fw-normal text-white"><i class="fas fa-list-ol me-2"></i> RESUMEN DE LIQUIDACIÓN</span>
            <span class="badge bg-white text-dark fw-normal p-2">MONTO FIJO R2: ${{ number_format($montoFijoR2, 2) }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center text-dark">
                    <thead>
                    <tr class="bg-light text-muted small text-uppercase">
                        <th class="py-3">No.</th>
                        <th class="text-start">EJIDATARIO</th>
                        <th>DESC. ASAMBLEA</th>
                        <th>DESC. FAENAS</th>
                        <th>PRESTAMOS (R1)</th>
                        <th>2DO REPARTO</th>
                        <th class="bg-light">TOTAL A PAGAR</th>
                        <th>ACCIONES</th>
                    </tr>
                    </thead>
                    <tbody class="fw-normal">
                    @foreach($ejidatarios as $ejidatario)
                        <tr>
                            <td class="text-muted small">{{ $loop->iteration }}</td>
                            <td class="text-start text-uppercase small text-dark">
                                <strong>{{ $ejidatario->usuario?->Nombres }} {{ $ejidatario->usuario?->Apellido_Paterno }}</strong>
                            </td>

                            <td>
                                @if($ejidatario->total_asambleas > 0)
                                    <button class="btn btn-sm btn-outline-danger py-0 fw-bold" onclick="verDetalle({{ $ejidatario->Id_Ejidatario }}, 'asambleas')">
                                        -${{ number_format($ejidatario->total_asambleas, 2) }}
                                    </button>
                                @else
                                    <span class="text-muted small">--</span>
                                @endif
                            </td>

                            <td>
                                @if($ejidatario->total_faenas > 0)
                                    <button class="btn btn-sm btn-outline-danger py-0 fw-bold" onclick="verDetalle({{ $ejidatario->Id_Ejidatario }}, 'faenas')">
                                        -${{ number_format($ejidatario->total_faenas, 2) }}
                                    </button>
                                @else
                                    <span class="text-muted small">--</span>
                                @endif
                            </td>

                            <td class="text-dark fw-bold">${{ number_format($ejidatario->deuda_arrastrada_r1, 2) }}</td>
                            <td class="text-dark fw-bold">${{ number_format($montoFijoR2, 2) }}</td>

                            <td class="bg-light fw-bold text-dark h6 mb-0">
                                ${{ number_format($ejidatario->total_a_pagar, 2) }}
                            </td>

                            <td>
                                @php $prestamo = $ejidatario->prestamos->where('Id_Utilidad', 1)->first(); @endphp
                                @if($ejidatario->deuda_arrastrada_r1 > 0 && $prestamo)
                                    <button type="button" class="btn btn-warning btn-sm text-dark fw-bold shadow-sm"
                                            onclick="abrirModalAbono('{{ $ejidatario->usuario?->Nombres }} {{ $ejidatario->usuario?->Apellido_Paterno }}', {{ $ejidatario->deuda_arrastrada_r1 }}, '{{ route('prestamo.abonar', $prestamo->Id_Prestamo) }}')">
                                        <i class="fas fa-hand-holding-usd me-1"></i> Abono
                                    </button>
                                @else
                                    <span class="badge bg-light text-muted border small"><i class="fas fa-check"></i> Pagado</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
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

                                <input type="number"
                                       name="monto_abono"
                                       id="inputMonto"
                                       class="form-control fw-bold text-dark text-center"
                                       step="0.01"
                                       min="0.01"
                                       required
                                >

                                <button type="button" class="btn btn-dark" onclick="liquidarTodo()">TODO</button>

                            </div>
                        </div>

                    </div>

                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-secondary px-3 fw-normal" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-ejidal px-3 fw-normal">Guardar</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>

        let saldoGlobal = 0;

        function abrirModalAbono(nombre, saldo, url){

            saldoGlobal = saldo;

            $('#nombreEjidatario').text(nombre);

            $('#saldoPendiente').text('$' + parseFloat(saldo).toLocaleString('en-US',{minimumFractionDigits:2}));

            $('#inputMonto').val('').attr('max', saldo);

            $('#formAbono').attr('action', url);

            $('#modalAbono').modal('show');

        }

        function liquidarTodo(){

            $('#inputMonto').val(saldoGlobal);

        }

        $('#inputMonto').on('input', function(){

            let valor = parseFloat(this.value);

            if(valor <= 0){
                this.setCustomValidity("El monto debe ser mayor a 0");
            }else if(valor > saldoGlobal){
                this.setCustomValidity("No puede ser mayor al saldo pendiente");
            }else{
                this.setCustomValidity("");
            }

        });

        $('#inputMonto').on('keypress', function(e){

            let char = String.fromCharCode(e.which);

            if(!/[0-9.]/.test(char)){
                e.preventDefault();
            }

        });

    </script>

@endsection