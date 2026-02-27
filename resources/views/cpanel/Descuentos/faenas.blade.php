@extends('cpanel/plantilla')

@section('title', 'Descuento por Faenas')

@section('content')
    <style>
        .select2-container--open { z-index: 9999 !important; }
        .select2-container .select2-selection--single { height: 38px !important; display: flex; align-items: center; border: 1px solid #ced4da; }
        .card-header-ejidal { background-color: #1b4b36; color: white; }
        .btn-ejidal { background-color: #1b4b36; color: white; border: none; }
        .btn-ejidal:hover { background-color: #143828; color: white; }
        .select2-results__option--highlighted { background-color: #1b4b36 !important; }
        .text-ejidal { color: #1b4b36; }
        /* Ajuste para que la tabla no se mueva */
        #tabla-principal { table-layout: fixed; width: 100%; }
        #tabla-principal th, #tabla-principal td { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    </style>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-tools me-2"></i> Descuento por Faenas
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0 d-flex gap-3 align-items-center">
            <div class="form-check form-switch bg-white border rounded-pill px-4 py-2 shadow-sm">
                <input class="form-check-input" type="checkbox" id="toggleDeudores"
                       style="cursor: pointer;" {{ request('filtrar_deudores') == 'on' ? 'checked' : '' }}>
                <label class="form-check-label small fw-bold text-secondary" for="toggleDeudores">
                    <i class="fas fa-filter me-1 text-success"></i> Solo Deudores
                </label>
            </div>
            <button type="button" class="btn btn-ejidal shadow-sm" data-bs-toggle="modal" data-bs-target="#modalFaena">
                <i class="fas fa-plus-circle me-1"></i> Agregar Descuento
            </button>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-table me-2"></i> Registro de Faenas y Adeudos</span>
            <form action="{{ url()->current() }}" method="GET" style="width: 300px;">
                <div class="input-group input-group-sm">
                    <input type="text" name="query" class="form-control" placeholder="Buscar ejidatario..." value="{{ request('query') }}">
                    <button class="btn btn-light border" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabla-principal" style="font-size: 0.85rem;">
                    <thead class="table-light text-secondary text-uppercase" style="font-size: 0.75rem;">
                    <tr>
                        <th style="width: 35%;" class="ps-4 py-3">Ejidatario</th>
                        <th style="width: 20%;" class="py-3 text-center">Saneamiento</th>
                        <th style="width: 20%;" class="py-3 text-center">Aprovechamiento</th>
                        <th style="width: 25%;" class="py-3 text-center bg-light fw-bold border-start">Total Adeudo</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($ejidatarios as $ejidatario)
                        @php
                            $descuentosDelEjidatario = $ejidatario->descuentos->keyBy('tipo');
                            $total_deuda = $ejidatario->descuentos->whereIn('tipo', $faenas)->sum('descuento');
                        @endphp
                        <tr class="ejidatario-row">
                            <td class="ps-4 fw-bold">
                                {{ $ejidatario->usuario?->Nombres }} {{ $ejidatario->usuario?->Apellido_Paterno }}
                            </td>
                            <td class="text-center">
                                @php $mSaneamiento = $descuentosDelEjidatario->get('Descuento faenas de saneamient')->descuento ?? 0; @endphp
                                @if($mSaneamiento > 0)
                                    <span class="text-danger fw-bold">${{ number_format($mSaneamiento, 2) }}</span>
                                @else
                                    <span class="text-muted small">Sin cargo</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @php $mAprovecha = $descuentosDelEjidatario->get('Descuento faenas de aprovecham')->descuento ?? 0; @endphp
                                @if($mAprovecha > 0)
                                    <span class="text-danger fw-bold">${{ number_format($mAprovecha, 2) }}</span>
                                @else
                                    <span class="text-muted small">Sin cargo</span>
                                @endif
                            </td>
                            <td class="text-center border-start bg-light">
                                <div class="badge {{ $total_deuda > 0 ? 'bg-danger' : 'bg-success' }} px-3 py-2">
                                    ${{ number_format($total_deuda, 2) }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fas fa-user-slash fa-2x mb-3 d-block"></i>
                                <strong>No se encontraron ejidatarios registrados.</strong><br>
                                <small>Use el buscador superior o el filtro de deudores para visualizar datos.</small>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3">
            {{ $ejidatarios->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <div class="modal fade" id="modalFaena" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header card-header-ejidal">
                    <h5 class="modal-title"><i class="fas fa-pen-square me-2"></i> Agregar o Modificar Descuento de Faena</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="form-aplicar-faena">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Buscar Ejidatario</label>
                            <select id="ejidatario-select" name="id_ejidatario" class="form-control" style="width:100%" required></select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Tipo de Faena</label>
                            <select name="nombre_faena" class="form-select" required>
                                <option value="">-- Selecciona la faena --</option>
                                <option value="Descuento faenas de saneamient">Descuento faenas de saneamient</option>
                                <option value="Descuento faenas de aprovecham">Descuento faenas de aprovecham</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Multa a Aplicar</label>
                            <select name="id_multa_c" class="form-select">
                                <option value="">Sin descuento (Quitar multa)</option>
                                @foreach($catalogoFaenas as $multa)
                                    <option value="{{ $multa->id_multa_c }}">
                                        {{ $multa->tipo }} (${{ number_format($multa->monto, 2) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-ejidal px-4">Guardar Descuento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#ejidatario-select').select2({
                placeholder: 'Escribe nombre...',
                dropdownParent: $('#modalFaena'),
                minimumInputLength: 2,
                ajax: {
                    url: '{{ route("ejidatarios.buscar") }}',
                    dataType: 'json',
                    delay: 250,
                    data: params => ({ q: params.term }),
                    processResults: data => ({ results: data }),
                    cache: true
                }
            });

            $('#form-aplicar-faena').on('submit', function(e) {
                e.preventDefault();
                $.post('{{ route("faenas.aplicar") }}', $(this).serialize())
                    .done(res => { if(res.success) window.location.reload(); })
                    .fail(() => alert("Error de servidor"));
            });

            $('#toggleDeudores').on('change', function() {
                const url = new URL(window.location.href);
                this.checked ? url.searchParams.set('filtrar_deudores', 'on') : url.searchParams.delete('filtrar_deudores');
                window.location.href = url.toString();
            });
        });
    </script>
@endsection