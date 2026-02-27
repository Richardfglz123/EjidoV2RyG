@extends('cpanel/plantilla')

@section('title', 'Descuento por Asambleas')

@section('content')
    <style>
        .select2-container--open { z-index: 9999 !important; }
        .select2-container .select2-selection--single { height: 38px !important; display: flex; align-items: center; border: 1px solid #ced4da; }
        .card-header-ejidal { background-color: #1b4b36; color: white; }
        .btn-ejidal { background-color: #1b4b36; color: white; border: none; }
        .btn-ejidal:hover { background-color: #143828; color: white; }
        .select2-results__option--highlighted { background-color: #1b4b36 !important; }
        .text-ejidal { color: #1b4b36; }
    </style>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-clipboard-list me-2"></i> Descuento por Asambleas
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0 d-flex gap-3 align-items-center">
            {{-- Switch para filtrar deudores (Recarga la página para aplicar filtro de servidor) --}}
            <div class="form-check form-switch bg-white border rounded-pill px-4 py-2 shadow-sm">
                <input class="form-check-input" type="checkbox" id="toggleDescuento"
                       style="cursor: pointer;" {{ request('filtrar_descuentos') == 'on' ? 'checked' : '' }}>
                <label class="form-check-label small fw-bold text-secondary" for="toggleDescuento">
                    <i class="fas fa-filter me-1 text-success"></i> Solo Deudores
                </label>
            </div>
            <button type="button" class="btn btn-ejidal shadow-sm" data-bs-toggle="modal" data-bs-target="#modalDescuento">
                <i class="fas fa-plus-circle me-1"></i> Agregar Descuento
            </button>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-table me-2"></i> Listado de Asistencias y Descuentos</span>
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
                        <th class="ps-4 py-3">Ejidatario</th>
                        @foreach($asambleas as $asamblea)
                            <th class="py-3 text-center">{{ $asamblea }}</th>
                        @endforeach
                        <th class="py-3 text-center bg-light fw-bold border-start">Total Deuda</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($ejidatarios as $ejidatario)
                        @php
                            $descuentosDelEjidatario = $ejidatario->descuentos->keyBy('tipo');
                            $total_deuda = $ejidatario->descuentos->sum('descuento');
                        @endphp
                        <tr class="ejidatario-row" data-total="{{ $total_deuda }}">
                            <td class="ps-4 fw-bold">
                                {{ $ejidatario->usuario?->Nombres }} {{ $ejidatario->usuario?->Apellido_Paterno }}
                            </td>
                            @foreach($asambleas as $nombre_asamblea)
                                <td class="text-center">
                                    @php $monto = $descuentosDelEjidatario->get($nombre_asamblea)->descuento ?? 0; @endphp
                                    @if($monto > 0)
                                        <span class="text-danger fw-bold">${{ number_format($monto, 2) }}</span>
                                    @else
                                        <span class="text-muted small">Sin cargo</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="text-center border-start">
                                <div class="badge {{ $total_deuda > 0 ? 'bg-danger' : 'bg-success' }} px-3 py-2">
                                    ${{ number_format($total_deuda, 2) }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($asambleas) + 2 }}" class="text-center py-5 text-muted">
                                <i class="fas fa-search fa-2x mb-3 d-block"></i>
                                @if(!request()->filled('query') && request('filtrar_descuentos') !== 'on')
                                    Use el buscador o el filtro de deudores para mostrar resultados.
                                @else
                                    No se encontraron registros para esta búsqueda.
                                @endif
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

    <div class="modal fade" id="modalDescuento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header card-header-ejidal">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Gestionar Descuento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="form-descuento">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">1. Buscar Ejidatario</label>
                            <select id="ejidatario-select" name="id_ejidatario" class="form-control" style="width:100%" required></select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">2. Asamblea</label>
                            <select name="nombre_asamblea" class="form-select" required>
                                <option value="">-- Selecciona asamblea --</option>
                                @foreach($asambleas as $as)
                                    <option value="{{ $as }}">{{ $as }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">3. Multa a Aplicar</label>
                            <select name="id_multa_c" class="form-select">
                                <option value="">Sin descuento (Quitar multa)</option>
                                @foreach($catalogoMultas as $multa)
                                    <option value="{{ $multa->id_multa_c }}">${{ number_format($multa->monto, 2) }} - {{ $multa->tipo }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-ejidal">Guardar Descuento</button>
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
                dropdownParent: $('#modalDescuento'),
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

            $('#form-descuento').on('submit', function(e) {
                e.preventDefault();
                $.post('{{ route("descuentos.store") }}', $(this).serialize())
                    .done(res => { if(res.success) window.location.reload(); })
                    .fail(() => alert("Error de servidor"));
            });

            $('#toggleDescuento').on('change', function() {
                const url = new URL(window.location.href);
                if (this.checked) {
                    url.searchParams.set('filtrar_descuentos', 'on');
                } else {
                    url.searchParams.delete('filtrar_descuentos');
                }
                window.location.href = url.toString();
            });
        });
    </script>
@endsection