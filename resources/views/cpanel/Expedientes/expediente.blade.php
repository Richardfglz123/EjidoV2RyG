@extends('cpanel/plantilla')
@section('title', 'Expedientes Digitales')

@push('css')
    <style>
        /* Estilos personalizados adaptados al tema Ejidal */
        .folder-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 25px; }
        .folder-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 12px;
            padding: 25px 20px;
            transition: 0.3s;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .folder-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-color: #2c3e50; /* Color oscuro similar al tema ejidal */
        }
        .folder-icon { font-size: 55px; color: #f1c40f; margin-bottom: 15px; }
        .folder-name { font-weight: 700; color: #2c3e50; font-size: 1.05em; }

        .btn-open-folder {
            background: #f8f9fa;
            color: #2c3e50;
            border: 1px solid #ddd;
            padding: 8px 0;
            width: 100%;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 15px;
        }

        /* Estilos del Modal y Cards de documentos */
        .doc-card {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            background: #fcfcfc;
            transition: 0.3s;
        }
        .doc-card.available { border-color: #198754; background: #fff; }
        .doc-icon { font-size: 40px; display: block; margin-bottom: 10px; }
        .doc-status { font-size: 0.75rem; font-weight: bold; text-transform: uppercase; padding: 3px 10px; border-radius: 15px; }

        .status-no { background: #6c757d; color: white; }
        .status-yes { background: #198754; color: white; }

        .btn-ver-doc { background: #2c3e50; color: white; border-radius: 4px; padding: 6px 12px; text-decoration: none; font-size: 0.85rem; }
        .btn-ver-doc:hover { background: #1a252f; color: white; }
    </style>
@endpush

@section('content')

    {{-- Título de la página --}}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-folder-open me-2"></i> Expedientes Digitales
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="me-3 text-end">
                <small class="text-muted d-block">Beneficiarios</small>
                <strong>{{ $total_usuarios }}</strong>
            </div>
            <div class="text-end">
                <small class="text-muted d-block">Con Expediente</small>
                <strong>{{ $total_con_expediente }}</strong>
            </div>
        </div>
    </div>

    {{-- Buscador --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                <input type="text" id="searchUsuario" class="form-control" placeholder="Buscar beneficiario por nombre...">
            </div>
        </div>
    </div>

    {{-- Grid de Carpetas --}}
    <div class="folder-grid" id="folderContainer">
        @foreach ($usuarios as $usuario)
            <div class="folder-card"
                 data-name="{{ strtolower($usuario->nombre . ' ' . $usuario->apellido_paterno) }}"
                 onclick="abrirExpediente(
                    '{{ $usuario->nombre }} {{ $usuario->apellido_paterno }}',
                    '{{ $usuario->documentos->ruta_ine ?? '' }}',
                    '{{ $usuario->documentos->ruta_curp ?? '' }}',
                    '{{ $usuario->documentos->ruta_comprobante ?? '' }}'
                 )">

                <div class="folder-icon"><i class="fas fa-folder"></i></div>
                <div class="folder-name">{{ $usuario->nombre }} {{ $usuario->apellido_paterno }}</div>

                <div class="mt-2">
                    @if($usuario->documentos)
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Completo</span>
                    @else
                        <span class="badge bg-light text-muted border">Vacío</span>
                    @endif
                </div>

                <button class="btn-open-folder">Abrir Expediente</button>
            </div>
        @endforeach
    </div>

    {{-- Mensaje Sin Resultados --}}
    <div id="noResults" class="text-center py-5" style="display: none;">
        <i class="fas fa-search fa-3x text-muted mb-3"></i>
        <p class="text-muted">No se encontraron carpetas con ese nombre.</p>
    </div>

    {{-- Modal de Expediente (Bootstrap Nativo) --}}
    <div class="modal fade" id="modalExpediente" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-ejidal text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-id-card me-2"></i>
                        Expediente: <span id="modal_nombre_usuario" class="fw-bold"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        {{-- Documento: INE --}}
                        <div class="col-md-4">
                            <div class="doc-card" id="card-ine">
                                <span class="doc-icon text-secondary"><i class="fas fa-address-card"></i></span>
                                <h6 class="fw-bold">INE</h6>
                                <p><span id="status-ine" class="doc-status status-no">Pendiente</span></p>
                                <a id="btn-ine" href="#" target="_blank" class="btn btn-sm btn-secondary disabled w-100">No disponible</a>
                            </div>
                        </div>

                        {{-- Documento: CURP --}}
                        <div class="col-md-4">
                            <div class="doc-card" id="card-curp">
                                <span class="doc-icon text-secondary"><i class="fas fa-file-invoice"></i></span>
                                <h6 class="fw-bold">CURP</h6>
                                <p><span id="status-curp" class="doc-status status-no">Pendiente</span></p>
                                <a id="btn-curp" href="#" target="_blank" class="btn btn-sm btn-secondary disabled w-100">No disponible</a>
                            </div>
                        </div>

                        {{-- Documento: Comprobante --}}
                        <div class="col-md-4">
                            <div class="doc-card" id="card-comp">
                                <span class="doc-icon text-secondary"><i class="fas fa-home"></i></span>
                                <h6 class="fw-bold">Domicilio</h6>
                                <p><span id="status-comp" class="doc-status status-no">Pendiente</span></p>
                                <a id="btn-comp" href="#" target="_blank" class="btn btn-sm btn-secondary disabled w-100">No disponible</a>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-4 mb-0 border-0 shadow-sm" style="font-size: 0.85rem;">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Nota:</strong> Estos documentos son cargados por el beneficiario desde su propio portal de acceso.
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar Carpeta</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        const myModal = new bootstrap.Modal(document.getElementById('modalExpediente'));

        function abrirExpediente(nombre, rutaIne, rutaCurp, rutaComp) {
            document.getElementById('modal_nombre_usuario').textContent = nombre;

            configurarDocumento('ine', rutaIne);
            configurarDocumento('curp', rutaCurp);
            configurarDocumento('comp', rutaComp);

            myModal.show();
        }

        function configurarDocumento(tipo, ruta) {
            const btn = document.getElementById('btn-' + tipo);
            const status = document.getElementById('status-' + tipo);
            const card = document.getElementById('card-' + tipo);

            if (ruta && ruta.trim() !== '') {
                btn.href = "{{ url('/') }}/" + ruta;
                btn.classList.remove('btn-secondary', 'disabled');
                btn.classList.add('btn-ver-doc');
                btn.innerHTML = '<i class="fas fa-eye me-1"></i> Ver Documento';

                status.className = "doc-status status-yes";
                status.textContent = "Cargado";
                card.classList.add('available');
            } else {
                btn.href = "#";
                btn.classList.add('btn-secondary', 'disabled');
                btn.classList.remove('btn-ver-doc');
                btn.textContent = "No disponible";

                status.className = "doc-status status-no";
                status.textContent = "Pendiente";
                card.classList.remove('available');
            }
        }

        // Buscador en tiempo real
        document.getElementById('searchUsuario').addEventListener('keyup', function(e) {
            const term = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.folder-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const name = card.getAttribute('data-name');
                if(name.includes(term)) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            document.getElementById('noResults').style.display = visibleCount === 0 ? 'block' : 'none';
        });
    </script>
@endpush