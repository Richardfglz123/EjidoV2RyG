@extends('cpanel/plantilla')

@section('title', 'Expedientes Digitales')

@section('content')

    {{-- Encabezado Institucional --}}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <div>
            <h1 class="h2 text-ejidal">
                <i class="fas fa-folder-open me-2"></i> Expedientes Digitales
            </h1>
            <p class="text-muted small mb-0">Administración y consulta de documentación digital por beneficiario.</p>
        </div>
    </div>

    {{-- Stats Cards (Estilo unificado) --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-ejidal border-4">
                <div class="card-body py-3">
                    <p class="text-muted mb-0 small fw-bold text-uppercase">Beneficiarios</p>
                    <h3 class="fw-bold mb-0 text-ejidal">{{ $total_usuarios }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-primary border-4">
                <div class="card-body py-3">
                    <p class="text-muted mb-0 small fw-bold text-uppercase">Expedientes Activos</p>
                    <h3 class="fw-bold mb-0 text-primary">{{ $total_documentos }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-secondary border-4">
                <div class="card-body py-3">
                    <p class="text-muted mb-0 small fw-bold text-uppercase">Periodo Fiscal</p>
                    <h3 class="fw-bold mb-0 text-secondary">{{ date('Y') }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Buscador Estilizado --}}
    <div class="card card-ejidal mb-4">
        <div class="card-body py-2">
            <div class="input-group">
                <span class="input-group-text bg-white border-0 fs-5"><i class="fas fa-search text-muted"></i></span>
                <input type="text" id="searchUsuario" class="form-control border-0 fs-5" placeholder="Buscar beneficiario por nombre o apellido...">
            </div>
        </div>
    </div>

    <h4 class="text-secondary mb-4 border-bottom pb-2">
        <i class="fas fa-users me-2"></i> Carpetas de Usuarios
    </h4>

    {{-- Grid de Carpetas --}}
    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4" id="folderContainer">
        @foreach ($usuarios as $usuario)
            <div class="col folder-item" data-name="{{ strtolower($usuario->nombre . ' ' . $usuario->apellido_paterno . ' ' . $usuario->apellido_materno) }}">
                <div class="card h-100 border-0 shadow-sm text-center p-3"
                     onclick="abrirExpediente({{ $usuario->id_usuario }}, '{{ $usuario->nombre }} {{ $usuario->apellido_paterno }}')"
                     style="cursor: pointer; transition: transform 0.2s; background: #fff;"
                     onmouseover="this.style.transform='translateY(-5px)'"
                     onmouseout="this.style.transform='translateY(0)'">

                    <div class="py-2">
                        <i class="fas fa-folder fa-5x text-warning"></i>
                    </div>
                    <div class="card-body px-1">
                        <h6 class="fw-bold text-ejidal mb-1">
                            {{ $usuario->nombre }}<br>
                            <small class="text-dark text-uppercase">{{ $usuario->apellido_paterno }} {{ $usuario->apellido_materno }}</small>
                        </h6>
                        <span class="badge bg-light text-muted border">ID: {{ $usuario->id_usuario }}</span>
                    </div>
                    <div class="card-footer bg-transparent border-0 px-0">
                        <button class="btn btn-outline-ejidal btn-sm w-100">
                            <i class="fas fa-folder-open me-1"></i> Abrir Expediente
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div id="noResults" class="text-center py-5 d-none">
        <i class="fas fa-search fa-3x text-muted mb-3"></i>
        <p class="text-muted">No se encontraron beneficiarios con ese nombre.</p>
    </div>

    {{-- Modal de Expediente (Estilo Card Ejidal) --}}
    <div id="modal-expediente" class="d-none" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 1060; display: flex; align-items: center; justify-content: center;">
        <div class="card card-ejidal shadow-lg border-0" style="width: 95%; max-width: 700px;">

            <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 text-white"><i class="fas fa-file-export me-2"></i> Expediente Digital</h5>
                    <small class="text-white-50" id="modal_nombre_usuario"></small>
                </div>
                <button type="button" onclick="cerrarExpediente()" class="btn-close btn-close-white"></button>
            </div>

            <form action="{{ route('expedientes.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id_usuario" id="modal_id_usuario">

                <div class="card-body">
                    <div class="mb-4">
                        <label class="fw-bold small text-secondary mb-2">1. Seleccionar Programa para archivar</label>
                        <select name="id_programa" class="form-select border-ejidal" required>
                            @foreach($programas as $prog)
                                <option value="{{ $prog->id_programa }}">{{ $prog->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="border-top pt-3">
                        <label class="fw-bold small text-secondary mb-3">2. Carga de Documentación</label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">INE (Frente y Reverso)</label>
                                <div class="border rounded p-3 text-center bg-light" onclick="document.getElementById('file_ine').click()" style="cursor:pointer; border: 2px dashed #ccc !important;">
                                    <input type="file" name="doc_ine" id="file_ine" hidden onchange="updateFileLabel(this, 'lbl_ine')">
                                    <i class="fas fa-id-card fa-2x text-muted mb-2"></i>
                                    <div class="small text-truncate" id="lbl_ine">Seleccionar archivo...</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold">CURP (Actualizada)</label>
                                <div class="border rounded p-3 text-center bg-light" onclick="document.getElementById('file_curp').click()" style="cursor:pointer; border: 2px dashed #ccc !important;">
                                    <input type="file" name="doc_curp" id="file_curp" hidden onchange="updateFileLabel(this, 'lbl_curp')">
                                    <i class="fas fa-fingerprint fa-2x text-muted mb-2"></i>
                                    <div class="small text-truncate" id="lbl_curp">Seleccionar archivo...</div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Comprobante de Domicilio</label>
                                <div class="border rounded p-2 d-flex align-items-center bg-light" onclick="document.getElementById('file_comp').click()" style="cursor:pointer; border: 2px dashed #ccc !important;">
                                    <input type="file" name="doc_comprobante" id="file_comp" hidden onchange="updateFileLabel(this, 'lbl_comp')">
                                    <i class="fas fa-home me-3 text-muted ps-2"></i>
                                    <div class="small text-truncate" id="lbl_comp">Seleccionar comprobante...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light d-flex justify-content-end gap-2 py-3">
                    <button type="button" onclick="cerrarExpediente()" class="btn btn-secondary px-4">Cancelar</button>
                    <button type="submit" class="btn btn-ejidal px-4">
                        <i class="fas fa-save me-1"></i> Guardar en Expediente
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('js')
    <script>
        function abrirExpediente(idUsuario, nombreUsuario) {
            document.getElementById('modal_id_usuario').value = idUsuario;
            document.getElementById('modal_nombre_usuario').textContent = "Beneficiario: " + nombreUsuario;

            // Reset labels
            document.getElementById('lbl_ine').innerHTML = "Seleccionar archivo...";
            document.getElementById('lbl_curp').innerHTML = "Seleccionar archivo...";
            document.getElementById('lbl_comp').innerHTML = "Seleccionar comprobante...";

            document.getElementById('modal-expediente').classList.remove('d-none');
        }

        function cerrarExpediente() {
            document.getElementById('modal-expediente').classList.add('d-none');
        }

        function updateFileLabel(input, labelId) {
            if (input.files && input.files.length > 0) {
                const label = document.getElementById(labelId);
                label.innerHTML = `<span class="text-success fw-bold"><i class="fas fa-check-circle"></i> ${input.files[0].name}</span>`;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchUsuario');
            const items = document.querySelectorAll('.folder-item');
            const noResults = document.getElementById('noResults');

            searchInput.addEventListener('input', function() {
                const term = this.value.toLowerCase();
                let hasResults = false;

                items.forEach(item => {
                    const name = item.dataset.name;
                    if (name.includes(term)) {
                        item.classList.remove('d-none');
                        hasResults = true;
                    } else {
                        item.classList.add('d-none');
                    }
                });

                noResults.classList.toggle('d-none', hasResults);
            });

            // Cerrar modal al clic fuera
            document.getElementById('modal-expediente').onclick = function(e) {
                if (e.target === this) cerrarExpediente();
            }
        });
    </script>
@endpush