@extends('cpanel/plantilla')

@section('title', 'Descuento por Asambleas')

@section('content')
    <style>
        .select2-container--open { z-index: 9999 !important; }
        .card-header-ejidal { background-color: #1b4b36; color: white; }
        .btn-ejidal { background-color: #1b4b36; color: white; }
        .text-ejidal { color: #1b4b36; }
    </style>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal"><i class="fas fa-clipboard-list me-2"></i> Descuento por Asambleas</h1>
        <button type="button" class="btn btn-ejidal shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAsamblea">
            <i class="fas fa-plus-circle me-1"></i> Agregar o Modificar
        </button>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header card-header-ejidal">
            <span class="text-uppercase small fw-bold">Registro</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 text-center align-middle">
                    <thead>
                    <tr class="bg-light text-muted small">
                        <th class="ps-4 text-start">Ejidatario</th>
                        @foreach($catalogoMultas as $m)
                            <th>{{ $m->Tipo }}</th>
                        @endforeach
                        <th class="border-start bg-light">Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($ejidatarios as $ejidatario)
                        @php
                            $descPorId = $ejidatario->descuentos->keyBy('Id_MultaC');
                            $total_ejidatario = 0;
                        @endphp
                        <tr>
                            <td class="text-start ps-4 fw-bold">
                                {{ $ejidatario->usuario?->Nombres }} {{ $ejidatario->usuario?->Apellido_Paterno }}
                            </td>
                            @foreach($catalogoMultas as $m)
                                @php
                                    $monto = $descPorId->get($m->Id_MultaC)->Descuento ?? 0;
                                    $total_ejidatario += $monto;
                                @endphp
                                <td>
                                    @if($monto > 0)
                                        <span class="text-danger fw-bold">${{ number_format($monto, 2) }}</span>
                                    @else
                                        <span class="text-muted small">--</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="fw-bold text-danger border-start bg-light">
                                ${{ number_format($total_ejidatario, 2) }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $ejidatarios->links() }}
        </div>
    </div>

    {{-- MODAL ASAMBLEA --}}
    <div class="modal fade" id="modalAsamblea" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header card-header-ejidal">
                    <h5 class="modal-title">Gestionar Descuento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="form-asamblea">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">1. Ejidatario</label>
                            <select id="ejidatario-select-asamblea" name="id_ejidatario" class="form-control" required style="width: 100%"></select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">2. Seleccionar Asamblea</label>
                            <select name="id_multa_c" class="form-select" required>
                                <option value="" selected disabled>Elija la asamblea...</option>
                                @foreach($catalogoMultas as $m)
                                    <option value="{{ $m->Id_MultaC }}">
                                        {{ $m->Tipo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-success">3. Multa a aplicar</label>
                            <select name="concepto_monto" class="form-select border-success" required>
                                <option value="" selected disabled>Seleccione el monto...</option>
                                <option value="SANEAMIENTO">Saneamiento (${{ number_format($costoSaneamiento ?? 100, 2) }})</option>
                                <option value="APROVECHAMIENTO">Aprovechamiento (${{ number_format($costoAprovechamiento ?? 200, 2) }})</option>
                                <option value="ASAMBLEAS">Asambleas (${{ number_format($costoAsambleas ?? 200, 2) }})</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-primary">4. Acción a realizar</label>
                            <select name="accion" class="form-select border-primary">
                                <option value="guardar">Aplicar / Actualizar</option>
                                <option value="eliminar">Sin descuento (Quitar multa)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-ejidal px-4">Ejecutar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#ejidatario-select-asamblea').select2({
                dropdownParent: $('#modalAsamblea'),
                placeholder: 'Buscar ejidatario...',
                ajax: {
                    url: '{{ route("descuentos.buscar_ejidatario") }}',
                    dataType: 'json',
                    delay: 250,
                    data: params => ({ q: params.term }),
                    processResults: data => ({ results: data })
                }
            });

            $('#form-asamblea').on('submit', function(e) {
                e.preventDefault();
                const $btn = $(this).find('button[type="submit"]');
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

                $.ajax({
                    url: '{{ route("descuentos.store") }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        location.reload();
                    },
                    error: function(xhr) {
                        alert("Error al procesar la solicitud.");
                        $btn.prop('disabled', false).text('Ejecutar Cambios');
                    }
                });
            });
        });
    </script>
@endsection