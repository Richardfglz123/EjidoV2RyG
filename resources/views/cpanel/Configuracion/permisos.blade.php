@extends('cpanel.plantilla')

@section('title', 'Gestión de Accesos')

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        .loading-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255,255,255,0.7); display: none; align-items: center;
            justify-content: center; z-index: 10; border-radius: 12px;
        }
        .form-switch .form-check-input { width: 2.5em; height: 1.25em; cursor: pointer; }
        .icon-container { width: 35px; color: #1a4d2e; font-size: 1.1rem; }

        .select2-container--bootstrap-5 .select2-selection {
            border-color: #1a4d2e !important;
            min-height: 38px;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
        }
    </style>

    <div class="container-fluid animate__animated animate__fadeIn">

        <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2 text-ejidal mb-0"><i class="fas fa-user-shield me-2"></i> Gestión de Accesos</h1>
            <button type="submit" form="formPermisos" class="btn btn-ejidal px-4 shadow-sm">
                <i class="fas fa-save me-2"></i> Guardar Cambios
            </button>
        </div>

        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center" style="background-color: #e9f5ed; color: #1a4d2e;">
            <i class="fas fa-info-circle me-3 fa-lg"></i>
            <span><strong>Nota:</strong> Al activar <b>Escritura</b>, se habilitará automáticamente <b>Lectura para funcionamiento valido</b></span>
        </div>

        <form id="formPermisos" action="{{ route('configuracion.permisos.guardar') }}" method="POST">
            @csrf

            <div class="card card-ejidal mb-4 shadow-sm">
                <div class="card-header card-header-ejidal">
                    <i class="fas fa-user-tag me-2"></i> Asignacion de Usuario
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="fw-bold small mb-1">BUSCAR USUARIO (Escribe para buscar)</label>
                            <select name="Id_Usuario" id="selectUsuario" class="form-select" required></select>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold small mb-1">ROL DEL SISTEMA</label>
                            <select name="Id_Rol" id="selectRol" class="form-select border-ejidal" required>
                                <option value="">-- Seleccionar Rol --</option>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->Id_Rol }}">{{ $rol->Tipo_Rol }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-ejidal shadow-sm position-relative mb-4">
                <div id="loader" class="loading-overlay">
                    <div class="spinner-border text-ejidal" role="status"></div>
                </div>

                <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-list-check me-2"></i> Tabla de Permisos</span>
                    <div class="form-check form-switch d-flex align-items-center gap-3 m-0 p-0">
                        <label class="form-check-label text-white fw-bold small mb-0" for="checkTodos" style="cursor:pointer;">MARCAR TODO</label>
                        <input class="form-check-input m-0" type="checkbox" id="checkTodos" style="float: none;">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3 border-0">Módulo</th>
                            <th class="text-center py-3 border-0" style="width: 150px;">Lectura</th>
                            <th class="text-center py-3 border-0" style="width: 150px;">Escritura</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php
                            $modulos = [
                                ['key'=>'usuarios','label'=>'Usuarios','icon'=>'fas fa-users'],
                                ['key'=>'ejidatarios','label'=>'Ejidatarios','icon'=>'fas fa-person-digging'],
                                ['key'=>'actividades','label'=>'Actividades','icon'=>'fas fa-clipboard-check'],
                                ['key'=>'gestion','label'=>'Gestión (Actividades/Progr.)','icon'=>'fas fa-tasks'],
                                ['key'=>'asambleas','label'=>'Asambleas','icon'=>'fas fa-gavel'],
                                ['key'=>'asistencia','label'=>'Pase de Lista','icon'=>'fas fa-list-check'],
                                ['key'=>'expedientes','label'=>'Expedientes Digitales','icon'=>'fas fa-folder-open'],
                                ['key'=>'parcelas','label'=>'Parcelas','icon'=>'fas fa-map-marked-alt'],
                                ['key'=>'utilidades','label'=>'Finanzas / Repartos','icon'=>'fas fa-hand-holding-usd'],
                                ['key'=>'gastos','label'=>'Gastos','icon'=>'fas fa-wallet'],
                                ['key'=>'inventario','label'=>'Inventario','icon'=>'fas fa-warehouse'],
                                ['key'=>'apoyos','label'=>'Apoyos Sociales','icon'=>'fas fa-hands-helping'],
                                ['key'=>'historicos','label'=>'Datos Históricos','icon'=>'fas fa-scroll'],
                                ['key'=>'respaldo','label'=>'Respaldo','icon'=>'fas fa-database'],
                                ['key'=>'configuracion','label'=>'Configuración','icon'=>'fas fa-cogs'],
                            ];
                        @endphp
                        @foreach($modulos as $m)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-container"><i class="{{ $m['icon'] }}"></i></div>
                                        <span class="fw-bold text-dark">{{ $m['label'] }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input permiso permiso-ver" type="checkbox" name="permisos[]" value="{{ $m['key'] }}_ver">
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input permiso permiso-crear" type="checkbox" name="permisos[]" value="{{ $m['key'] }}_crear">
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-5">
                <button type="submit" class="btn btn-ejidal px-5 shadow-sm">
                    <i class="fas fa-save me-2"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            const selectRol = document.getElementById('selectRol');
            const permisos = document.querySelectorAll('.permiso');
            const checkTodos = document.getElementById('checkTodos');
            const loader = document.getElementById('loader');

            $('#selectUsuario').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Escribe el nombre del usuario...',
                minimumInputLength: 2,
                language: {
                    inputTooShort: () => "Ingresa 2 o más caracteres",
                    searching: () => "Buscando...",
                    noResults: () => "No se encontraron usuarios"
                },
                ajax: {
                    url: "{{ route('configuracion.usuarios.buscar_ajax') }}",
                    dataType: 'json',
                    delay: 250,
                    data: (params) => ({ q: params.term }),
                    processResults: (data) => ({ results: data }),
                    cache: true
                }
            });

            function cargarDatos(userId) {
                if (!userId) {
                    permisos.forEach(p => p.checked = false);
                    checkTodos.checked = false;
                    return;
                }
                loader.style.display = 'flex';
                fetch(`/configuracion/permisos/buscar/${userId}`)
                    .then(r => r.json())
                    .then(data => {
                        selectRol.value = data.Id_Rol || '';
                        const asignados = data.permisos || [];
                        permisos.forEach(p => p.checked = asignados.includes(p.value));
                        actualizarCheckTodos();
                    })
                    .catch(err => console.error("Error cargando permisos:", err))
                    .finally(() => { loader.style.display = 'none'; });
            }

            $('#selectUsuario').on('change', function() {
                cargarDatos(this.value);
            });

            function actualizarCheckTodos() {
                const total = permisos.length;
                const marcados = document.querySelectorAll('.permiso:checked').length;
                checkTodos.checked = (total === marcados && total > 0);
            }

            checkTodos.addEventListener('change', () => {
                permisos.forEach(p => p.checked = checkTodos.checked);
            });

            document.querySelectorAll('.permiso-crear').forEach((crear, i) => {
                const ver = document.querySelectorAll('.permiso-ver')[i];
                crear.addEventListener('change', () => {
                    if (crear.checked) ver.checked = true;
                    actualizarCheckTodos();
                });
            });

            document.querySelectorAll('.permiso-ver').forEach(ver => {
                ver.addEventListener('change', actualizarCheckTodos);
            });
        });
    </script>
@endsection