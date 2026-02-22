@extends('cpanel/plantilla')

@section('title', 'Descuento por Asambleas')

@section('content')

    {{-- Encabezado Institucional --}}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-clipboard-list me-2"></i> Descuento por Asambleas
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0 d-flex gap-3 align-items-center">

            {{-- Filtro Switch Estilo Bootstrap --}}
            <div class="form-check form-switch bg-white border rounded-pill px-4 py-2 shadow-sm">
                <input class="form-check-input" type="checkbox" id="toggleDescuento" style="cursor: pointer;">
                <label class="form-check-label small fw-bold text-secondary" for="toggleDescuento">
                    <i class="fas fa-filter me-1 text-success"></i> Solo Deudores
                </label>
            </div>

            <button type="button" id="btn-abrir-modal" class="btn btn-ejidal shadow-sm">
                <i class="fas fa-plus-circle me-1"></i> Agregar Descuento
            </button>
        </div>
    </div>

    {{-- Contenedor Principal: Tabla en Card Ejidal --}}
    <div class="card card-ejidal shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-table me-2"></i> Listado de Asistencias y Descuentos</span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                    <thead class="table-light text-secondary text-uppercase" style="font-size: 0.75rem;">
                    <tr>
                        <th class="ps-4 py-3">Ejidatario</th>
                        @foreach($asambleas as $asamblea)
                            <th class="py-3 text-center">{{ $asamblea }}</th>
                        @endforeach
                        <th class="py-3 text-center bg-light fw-bold border-start">Total Descuento</th>
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
                                <span class="text-dark">{{ optional($ejidatario->usuario)->nombre_completo }}</span>
                            </td>

                            @foreach($asambleas as $nombre_asamblea)
                                <td class="text-center">
                                    @php
                                        $monto_actual = optional($descuentosDelEjidatario->get($nombre_asamblea))->descuento ?? 0;
                                    @endphp
                                    @if($monto_actual > 0)
                                        <span class="text-danger fw-bold">
                                                ${{ number_format($monto_actual, 2) }}
                                            </span>
                                    @else
                                        <span class="text-muted small">Sin cargo</span>
                                    @endif
                                </td>
                            @endforeach

                            <td class="text-center border-start">
                                <div class="badge {{ $total_deuda > 0 ? 'bg-danger' : 'bg-success' }} px-3 py-2" style="min-width: 80px;">
                                    ${{ number_format($total_deuda, 2) }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($asambleas) + 2 }}" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                No se encontraron registros de ejidatarios.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Footer con Paginación --}}
        <div class="card-footer bg-white py-3">
            <div class="d-flex justify-content-center">
                {{ $ejidatarios->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    {{-- Modal: Registro de Descuento (Estilo Card Ejidal) --}}
    <div id="modal-agregar" class="d-none" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 1060; display: flex; align-items: center; justify-content: center;">
        <div class="card card-ejidal shadow-lg border-0" style="width: 95%; max-width: 500px;">
            <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-white"><i class="fas fa-pen-square me-2"></i> Aplicar Multa de Asamblea</h5>
                <button type="button" id="btn-cerrar-modal" class="btn-close btn-close-white"></button>
            </div>

            <form id="form-agregar-descuento">
                @csrf
                <div class="card-body">

                    {{-- Buscador en vivo --}}
                    <div class="mb-3 position-relative">
                        <label class="form-label fw-bold small text-secondary">1. Buscar Ejidatario</label>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-search"></i></span>
                            <input type="text" id="buscador-ejidatario" class="form-control border-start-0" placeholder="Escriba nombre del ejidatario..." autocomplete="off">
                        </div>
                        <div id="lista-resultados" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 1100; max-height: 200px; overflow-y: auto;">
                        </div>
                    </div>

                    {{-- Datos Seleccionados --}}
                    <div class="mb-3 bg-light p-3 rounded border">
                        <label class="form-label fw-bold small text-success"><i class="fas fa-user-check me-1"></i> Seleccionado:</label>
                        <input type="text" id="ejidatario-nombre" class="form-control-plaintext fw-bold py-0" readonly value="Ningún ejidatario seleccionado">
                        <input type="hidden" id="ejidatario-id" name="id_ejidatario">
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold small text-secondary">2. Seleccionar Asamblea</label>
                            <select id="asamblea-nombre-modal" name="nombre_asamblea" class="form-select shadow-sm" required>
                                <option value="">-- Elige una asamblea --</option>
                                @foreach($asambleas as $nombre_asamblea)
                                    <option value="{{ $nombre_asamblea }}">{{ $nombre_asamblea }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold small text-secondary">3. Multa a Aplicar</label>
                            <select id="multa-id-modal" name="id_multa_c" class="form-select shadow-sm">
                                <option value="">Sin multa (Quitar registro)</option>
                                @foreach($catalogoMultas as $multa)
                                    <option value="{{ $multa->id_multa_c }}">${{ number_format($multa->monto, 2) }} ({{ $multa->tipo }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light d-flex justify-content-end gap-2 py-3">
                    <button type="button" id="btn-cancelar" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-ejidal px-4">
                        <i class="fas fa-save me-1"></i> Guardar Descuento
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // 1. Filtrado de Deudores
            const toggle = document.getElementById('toggleDescuento');
            if (toggle) {
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('filtrar_descuentos') === 'on') toggle.checked = true;

                toggle.addEventListener('change', function() {
                    const url = new URL(window.location.href);
                    if (this.checked) {
                        url.searchParams.set('filtrar_descuentos', 'on');
                    } else {
                        url.searchParams.delete('filtrar_descuentos');
                    }
                    url.searchParams.set('page', '1');
                    window.location.href = url.toString();
                });
            }

            // 2. Control del Modal
            const modal = document.getElementById('modal-agregar');
            const btnAbrir = document.getElementById('btn-abrir-modal');
            const btnCerrar = document.getElementById('btn-cerrar-modal');
            const btnCancelar = document.getElementById('btn-cancelar');

            const toggleModal = () => {
                modal.classList.toggle('d-none');
                if (modal.classList.contains('d-none')) {
                    document.getElementById('form-agregar-descuento').reset();
                    document.getElementById('ejidatario-nombre').value = 'Ningún ejidatario seleccionado';
                    document.getElementById('lista-resultados').classList.add('d-none');
                }
            };

            btnAbrir.onclick = toggleModal;
            btnCerrar.onclick = toggleModal;
            btnCancelar.onclick = toggleModal;

            // 3. Buscador en Vivo
            const buscadorInput = document.getElementById('buscador-ejidatario');
            const resultadosDiv = document.getElementById('lista-resultados');

            buscadorInput.addEventListener('input', function() {
                const query = this.value;
                if (query.length < 3) {
                    resultadosDiv.classList.add('d-none');
                    return;
                }

                fetch(`{{ route('asambleas.buscar') }}?query=${query}`)
                    .then(res => res.json())
                    .then(data => {
                        resultadosDiv.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(usuario => {
                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'list-group-item list-group-item-action py-2 small';
                                btn.innerHTML = `<i class="fas fa-user me-2 text-muted"></i>${usuario.nombre_completo}`;
                                btn.onclick = () => {
                                    document.getElementById('ejidatario-id').value = usuario.ejidatario.id_ejidatario;
                                    document.getElementById('ejidatario-nombre').value = usuario.nombre_completo;
                                    buscadorInput.value = '';
                                    resultadosDiv.classList.add('d-none');
                                };
                                resultadosDiv.appendChild(btn);
                            });
                            resultadosDiv.classList.remove('d-none');
                        } else {
                            resultadosDiv.innerHTML = '<div class="list-group-item disabled small">No hay resultados</div>';
                            resultadosDiv.classList.remove('d-none');
                        }
                    });
            });

            // 4. Guardar vía Fetch
            document.getElementById('form-agregar-descuento').addEventListener('submit', function(e) {
                e.preventDefault();
                const data = {
                    id_ejidatario: document.getElementById('ejidatario-id').value,
                    nombre_asamblea: document.getElementById('asamblea-nombre-modal').value,
                    id_multa_c: document.getElementById('multa-id-modal').value,
                    _token: '{{ csrf_token() }}'
                };

                fetch('{{ route("descuentos.store") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                })
                    .then(res => res.json())
                    .then(result => {
                        if (result.success) window.location.reload();
                        else alert('Error al guardar datos.');
                    });
            });
        });
    </script>
@endpush