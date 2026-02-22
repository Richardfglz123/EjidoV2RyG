@extends('cpanel/plantilla')

@section('title', 'Descuento por Faenas')

@section('content')

    {{-- Encabezado Institucional --}}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-tools me-2"></i> Descuento por Faenas
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0 d-flex gap-3 align-items-center">

            {{-- Filtro Switch Estilo Bootstrap --}}
            <div class="form-check form-switch bg-white border rounded-pill px-4 py-2 shadow-sm">
                <input class="form-check-input" type="checkbox" id="toggleDeudores" style="cursor: pointer;">
                <label class="form-check-label small fw-bold text-secondary" for="toggleDeudores">
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
            <span><i class="fas fa-list-ol me-2"></i> Registro de Faenas y Adeudos</span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-secondary small text-uppercase">
                    <tr>
                        <th class="ps-4 py-3">Ejidatario</th>
                        <th class="py-3">Saneamiento</th>
                        <th class="py-3">Aprovechamiento</th>
                        <th class="py-3 text-center">Total de Adeudo</th>
                    </tr>
                    </thead>
                    <tbody id="tabla-ejidatarios">
                    @forelse($ejidatarios as $ejidatario)
                        @php
                            $descuentosDelEjidatario = $ejidatario->descuentos->keyBy('tipo');
                            $total_deuda = $ejidatario->descuentos->whereIn('tipo', $faenas)->sum('descuento');
                        @endphp
                        <tr class="ejidatario-row" data-total="{{ $total_deuda }}">
                            <td class="ps-4 fw-bold">
                                <span class="text-dark">{{ optional($ejidatario->usuario)->nombre_completo }}</span>
                            </td>

                            @foreach($faenas as $nombre_faena)
                                <td>
                                    @php
                                        $monto_actual = optional($descuentosDelEjidatario->get($nombre_faena))->descuento ?? 0;
                                    @endphp
                                    @if($monto_actual > 0)
                                        <span class="text-danger fw-bold">
                                                <i class="fas fa-exclamation-circle me-1 small"></i>
                                                ${{ number_format($monto_actual, 2) }}
                                            </span>
                                    @else
                                        <span class="text-muted small">Sin cargo</span>
                                    @endif
                                </td>
                            @endforeach

                            <td class="text-center">
                                <div class="badge {{ $total_deuda > 0 ? 'bg-danger' : 'bg-success' }} px-3 py-2" style="min-width: 80px;">
                                    ${{ number_format($total_deuda, 2) }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
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

    {{-- Modal: Registro de Faena (Diseño unificado) --}}
    <div id="modal-faena" class="d-none" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 1060; display: flex; align-items: center; justify-content: center;">
        <div class="card card-ejidal shadow-lg border-0" style="width: 95%; max-width: 500px;">
            <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-white"><i class="fas fa-pen-square me-2"></i> Asignar Descuento</h5>
                <button type="button" id="btn-cerrar-modal" class="btn-close btn-close-white" aria-label="Close"></button>
            </div>

            <form id="form-aplicar-faena">
                @csrf
                <div class="card-body">

                    {{-- Buscador en vivo --}}
                    <div class="mb-3 position-relative">
                        <label class="form-label fw-bold small text-secondary">1. Buscar Ejidatario</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-search"></i></span>
                            <input type="text" id="input-busqueda" class="form-control border-start-0" placeholder="Escriba nombre o apellido..." autocomplete="off">
                        </div>
                        <div id="resultados-busqueda" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 1100; max-height: 200px; overflow-y: auto;">
                            {{-- Los resultados de AJAX se inyectan aquí --}}
                        </div>
                    </div>

                    {{-- Datos Seleccionados --}}
                    <div class="mb-3 bg-light p-3 rounded border">
                        <label class="form-label fw-bold small text-success"><i class="fas fa-user-check me-1"></i> Seleccionado:</label>
                        <input type="text" id="nombre-seleccionado" class="form-control-plaintext fw-bold py-0" readonly value="Ningún ejidatario seleccionado">
                        <input type="hidden" id="id-ejidatario-hidden" name="id_ejidatario">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-secondary">2. Tipo de Faena</label>
                            <select name="nombre_faena" class="form-select" id="select-faena" required>
                                <option value="">Seleccione...</option>
                                @foreach($faenas as $nombre_faena)
                                    <option value="{{ $nombre_faena }}">{{ $nombre_faena }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-secondary">3. Monto de Multa</label>
                            <select name="id_multa_c" class="form-select" id="select-multa">
                                <option value="">Sin multa (Limpiar)</option>
                                @foreach($catalogoFaenas as $multa)
                                    <option value="{{ $multa->id_multa_c }}">
                                        {{ $multa->tipo }} (${{ number_format($multa->monto, 2) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light d-flex justify-content-end gap-2 py-3">
                    <button type="button" id="btn-cancelar" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-ejidal px-4 shadow-sm">
                        <i class="fas fa-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // 1. Lógica de Filtrado "Solo Deudores"
            const toggleDeudores = document.getElementById('toggleDeudores');
            toggleDeudores.addEventListener('change', function() {
                const rows = document.querySelectorAll('.ejidatario-row');
                rows.forEach(row => {
                    const total = parseFloat(row.dataset.total);
                    if (this.checked) {
                        row.style.display = (total > 0) ? '' : 'none';
                    } else {
                        row.style.display = '';
                    }
                });
            });

            // 2. Control del Modal
            const modal = document.getElementById('modal-faena');
            const btnAbrir = document.getElementById('btn-abrir-modal');
            const btnCerrar = document.getElementById('btn-cerrar-modal');
            const btnCancelar = document.getElementById('btn-cancelar');

            const toggleModal = () => {
                modal.classList.toggle('d-none');
                if (modal.classList.contains('d-none')) {
                    document.getElementById('form-aplicar-faena').reset();
                    document.getElementById('nombre-seleccionado').value = 'Ningún ejidatario seleccionado';
                    document.getElementById('resultados-busqueda').classList.add('d-none');
                }
            };

            btnAbrir.addEventListener('click', toggleModal);
            btnCerrar.addEventListener('click', toggleModal);
            btnCancelar.addEventListener('click', toggleModal);

            // 3. Buscador AJAX de Ejidatarios
            const inputBusqueda = document.getElementById('input-busqueda');
            const resultados = document.getElementById('resultados-busqueda');

            inputBusqueda.addEventListener('input', function() {
                const query = this.value;
                if (query.length < 3) {
                    resultados.classList.add('d-none');
                    return;
                }

                fetch(`{{ route('faenas.buscar') }}?query=${query}`)
                    .then(res => res.json())
                    .then(data => {
                        resultados.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(item => {
                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'list-group-item list-group-item-action small py-2';
                                btn.innerHTML = `<i class="fas fa-user me-2 text-muted"></i>${item.nombre_completo}`;
                                btn.onclick = () => {
                                    document.getElementById('id-ejidatario-hidden').value = item.ejidatario.id_ejidatario;
                                    document.getElementById('nombre-seleccionado').value = item.nombre_completo;
                                    inputBusqueda.value = '';
                                    resultados.classList.add('d-none');
                                };
                                resultados.appendChild(btn);
                            });
                            resultados.classList.remove('d-none');
                        } else {
                            resultados.innerHTML = '<div class="list-group-item disabled small">No se encontraron resultados</div>';
                            resultados.classList.remove('d-none');
                        }
                    });
            });

            // 4. Envío del Formulario
            document.getElementById('form-aplicar-faena').addEventListener('submit', function(e) {
                e.preventDefault();
                const idEjidatario = document.getElementById('id-ejidatario-hidden').value;

                if (!idEjidatario) {
                    alert('Debe seleccionar un ejidatario de la lista.');
                    return;
                }

                const formData = new FormData(this);
                const data = Object.fromEntries(formData.entries());

                fetch('{{ route("faenas.aplicar") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            window.location.reload();
                        } else {
                            alert('Error al procesar la solicitud.');
                        }
                    })
                    .catch(() => alert('Error de conexión con el servidor.'));
            });
        });
    </script>
@endpush