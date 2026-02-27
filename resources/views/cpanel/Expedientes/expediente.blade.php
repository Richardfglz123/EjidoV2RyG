@extends('cpanel/plantilla')
@section('title', 'Expedientes Digitales')

@section('content')

    <div class="card card-ejidal mb-4">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-folder-open me-2"></i> Expedientes Digitales</span>
            <div class="d-flex gap-3" style="font-size: 0.85rem;">
                <div class="text-end">
                    <span class="d-block opacity-75">Beneficiarios</span>
                    <span class="badge bg-white text-dark">{{ $total_usuarios }}</span>
                </div>
                <div class="text-end border-start ps-3">
                    <span class="d-block opacity-75">Con Expediente</span>
                    <span class="badge bg-success">{{ $total_con_expediente }}</span>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                        <input type="text" id="searchUsuario" class="form-control" placeholder="Buscar beneficiario por nombre...">
                    </div>
                </div>
            </div>

            <div class="row g-4" id="folderContainer">
                @foreach ($usuarios as $usuario)
                    @php
                        $docs = $usuario->documentos->pluck('ruta_archivo', 'nombre_documento');
                    @endphp
                    <div class="col-sm-6 col-md-4 col-lg-3 user-folder"
                         data-name="{{ strtolower($usuario->Nombres . ' ' . $usuario->Apellido_Paterno) }}">

                        <div class="card h-100 text-center shadow-sm border-light"
                             style="cursor: pointer; transition: 0.3s;"
                             onclick="abrirExpediente(
                                '{{ $usuario->Id_Usuario }}',
                                '{{ $usuario->Nombres }} {{ $usuario->Apellido_Paterno }}',
                                '{{ $docs['INE'] ?? '' }}',
                                '{{ $docs['CURP'] ?? '' }}',
                                '{{ $docs['DOMICILIO'] ?? '' }}'
                             )"
                             onmouseover="this.style.transform='translateY(-5px)';"
                             onmouseout="this.style.transform='translateY(0)';"
                        >
                            <div class="card-body p-4">
                                <i class="fas fa-folder fa-4x mb-3" style="color: #f1c40f;"></i>
                                <h6 class="fw-bold text-dark mb-2">{{ $usuario->Nombres }} {{ $usuario->Apellido_Paterno }}</h6>
                                <div class="mb-3">
                                    <span class="badge rounded-pill {{ $docs->count() > 0 ? 'bg-success' : 'bg-light text-muted border' }}" style="font-size: 0.7rem;">
                                        {{ $docs->count() }} ARCHIVO(S)
                                    </span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary w-100">
                                    <i class="fas fa-upload me-1"></i> Gestionar
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalExpediente" tabindex="-1" aria-labelledby="modalExpedienteLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0">
                <form action="{{ route('expedientes.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id_usuario" id="modal_id_usuario">

                    <div class="modal-header card-header-ejidal text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-file-pdf me-2"></i>
                            Expediente: <span id="modal_nombre_usuario" class="fw-bold"></span>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4 bg-light">
                        <div class="row g-3">
                            <div class="col-md-4 text-center">
                                <div id="card-ine" class="card h-100 border-0 shadow-sm p-3">
                                    <i class="fas fa-address-card fa-2x mb-2 text-secondary"></i>
                                    <h6 class="fw-bold small">INE</h6>
                                    <span id="status-ine" class="badge bg-secondary mb-2">Pendiente</span>
                                    <input type="file" name="doc_ine" class="form-control form-control-sm mb-2" accept=".pdf">
                                    <a id="btn-ine" href="#" target="_blank" class="btn btn-sm btn-outline-primary d-none w-100">Ver PDF</a>
                                </div>
                            </div>

                            <div class="col-md-4 text-center">
                                <div id="card-curp" class="card h-100 border-0 shadow-sm p-3">
                                    <i class="fas fa-file-invoice fa-2x mb-2 text-secondary"></i>
                                    <h6 class="fw-bold small">CURP</h6>
                                    <span id="status-curp" class="badge bg-secondary mb-2">Pendiente</span>
                                    <input type="file" name="doc_curp" class="form-control form-control-sm mb-2" accept=".pdf">
                                    <a id="btn-curp" href="#" target="_blank" class="btn btn-sm btn-outline-primary d-none w-100">Ver PDF</a>
                                </div>
                            </div>

                            <div class="col-md-4 text-center">
                                <div id="card-comp" class="card h-100 border-0 shadow-sm p-3">
                                    <i class="fas fa-home fa-2x mb-2 text-secondary"></i>
                                    <h6 class="fw-bold small">Comprobante</h6>
                                    <span id="status-comp" class="badge bg-secondary mb-2">Pendiente</span>
                                    <input type="file" name="doc_comprobante" class="form-control form-control-sm mb-2" accept=".pdf">
                                    <a id="btn-comp" href="#" target="_blank" class="btn btn-sm btn-outline-primary d-none w-100">Ver PDF</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-white">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-ejidal text-white">
                            <i class="fas fa-save me-1"></i> Guardar Expediente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function abrirExpediente(id, nombre, rutaIne, rutaCurp, rutaComp) {
            document.getElementById('modal_id_usuario').value = id;
            document.getElementById('modal_nombre_usuario').innerText = nombre;

            const fileInputs = document.querySelectorAll('input[type="file"]');
            fileInputs.forEach(input => input.value = '');

            configurarFichaModal('ine', rutaIne);
            configurarFichaModal('curp', rutaCurp);
            configurarFichaModal('comp', rutaComp);

            var myModal = new bootstrap.Modal(document.getElementById('modalExpediente'));
            myModal.show();
        }

        function configurarFichaModal(tipo, ruta) {
            const btn = document.getElementById('btn-' + tipo);
            const status = document.getElementById('status-' + tipo);

            if (ruta && ruta !== '') {
                btn.href = "{{ asset('') }}" + ruta;
                btn.classList.remove('d-none');
                status.className = "badge bg-success mb-2";
                status.textContent = "Cargado";
            } else {
                btn.classList.add('d-none');
                status.className = "badge bg-secondary mb-2";
                status.textContent = "Pendiente";
            }
        }

        document.getElementById('searchUsuario').addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            const folders = document.querySelectorAll('.user-folder');
            folders.forEach(f => {
                const name = f.getAttribute('data-name');
                f.classList.toggle('d-none', !name.includes(term));
            });
        });
    </script>

@endsection