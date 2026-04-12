@extends('cpanel.plantilla')

@section('title', 'Configuracion')

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        .loading-overlay {
            position:absolute; top:0; left:0; width:100%; height:100%;
            background:rgba(255,255,255,0.7); display:none;
            align-items:center; justify-content:center;
            z-index:10; border-radius:12px;
        }
        .form-switch .form-check-input { width:2.5em; height:1.25em; cursor:pointer; }
        .icon-container { width:35px; color:#1a4d2e; font-size:1.1rem; }

        .select2-container--bootstrap-5 .select2-selection {
            border-color:#1a4d2e !important;
            min-height:38px;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            line-height:38px;
        }
    </style>

    <div class="container-fluid animate__animated animate__fadeIn">

        <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2 text-ejidal mb-0">
                <i class="fas fa-user-shield me-2"></i> Permisos del sistema
            </h1>
            <button type="submit" form="formPermisos" class="btn btn-ejidal px-4 shadow-sm">
                <i class="fas fa-save me-2"></i> Guardar Cambios
            </button>
        </div>

        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center"
             style="background-color:#e9f5ed; color:#1a4d2e;">
            <i class="fas fa-info-circle me-3 fa-lg"></i>
            <div>
                <div><strong>Nota:</strong> Escritura activa automáticamente Lectura</div>
                <div><strong>Nota:</strong> Eliminar activa Escritura y Lectura</div>
                <div><strong>Nota:</strong> Los cambios afectan a todos los usuarios con el mismo rol</div>
            </div>
        </div>

        <form id="formPermisos" action="{{ route('configuracion.permisos.guardar') }}" method="POST">
            @csrf

            <div class="card card-ejidal mb-4 shadow-sm">
                <div class="card-header card-header-ejidal">
                    <i class="fas fa-user-tag me-2"></i> Asignación de Usuario
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="fw-bold small mb-1">Buscar Usuario</label>
                            <select name="Id_Usuario" id="selectUsuario" class="form-select" required></select>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold small mb-1">Rol</label>
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

            {{-- Ttabla de permisos--}}
            <div class="card card-ejidal shadow-sm position-relative mb-4">
                <div id="loader" class="loading-overlay">
                    <div class="spinner-border text-ejidal"></div>
                </div>

                <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-list-check me-2"></i> Tabla de Permisos</span>
                    <div class="form-check form-switch d-flex align-items-center gap-3 m-0 p-0">
                        <label class="form-check-label text-white fw-bold small mb-0"
                               for="checkTodos" style="cursor:pointer;">
                            MARCAR TODO
                        </label>
                        <input class="form-check-input m-0" type="checkbox" id="checkTodos">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3 border-0">Módulo</th>
                            <th class="text-center py-3 border-0" style="width:150px;">Lectura</th>
                            <th class="text-center py-3 border-0" style="width:150px;">Escritura</th>
                            <th class="text-center py-3 border-0 text-danger" style="width:150px;">Eliminar</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php
                            $modulos = [
                                ['key'=>'usuarios','label'=>'Usuarios','icon'=>'fas fa-users'],
                                ['key'=>'ejidatarios','label'=>'Ejidatarios','icon'=>'fas fa-person-digging'],
                                ['key'=>'actividades','label'=>'Actividades','icon'=>'fas fa-clipboard-check'],
                                ['key'=>'gestion','label'=>'Gestión','icon'=>'fas fa-tasks'],

                                ['key'=>'asistencia','label'=>'Pase de Lista','icon'=>'fas fa-list-check'],
                                ['key'=>'expedientes','label'=>'Expedientes','icon'=>'fas fa-folder-open'],
                                ['key'=>'parcelas','label'=>'Parcelas','icon'=>'fas fa-map-marked-alt'],
                                ['key'=>'utilidades','label'=>'Finanzas','icon'=>'fas fa-hand-holding-usd'],
                                ['key'=>'gastos','label'=>'Gastos','icon'=>'fas fa-wallet'],
                                ['key'=>'inventario','label'=>'Inventario','icon'=>'fas fa-warehouse'],
                                ['key'=>'apoyos','label'=>'Apoyos','icon'=>'fas fa-hands-helping'],
                                ['key'=>'historicos','label'=>'Históricos','icon'=>'fas fa-scroll'],
                                ['key'=>'respaldo','label'=>'Respaldo','icon'=>'fas fa-database'],
                                ['key'=>'configuracion','label'=>'Configuración','icon'=>'fas fa-cogs'],
                            ];
                        @endphp

                        @foreach($modulos as $m)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-container">
                                            <i class="{{ $m['icon'] }}"></i>
                                        </div>
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
                                <td class="text-center">
                                    @php
                                        $excluidos = ['configuracion', 'respaldo', 'expedientes'];
                                    @endphp

                                    @if(!in_array($m['key'], $excluidos))
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input permiso permiso-eliminar"
                                                   type="checkbox"
                                                   name="permisos[]"
                                                   value="{{ $m['key'] }}_eliminar">
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" name="confirmacion_global" required>
                <label class="form-check-label fw-bold text-danger">
                    Entiendo que estos cambios afectarán a TODOS los usuarios con este rol asignad
                </label>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-5">
                <button type="submit" class="btn btn-ejidal px-5 shadow-sm">
                    <i class="fas fa-save me-2"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>

    {{-- js --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {

            @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: '¡Atención!',
                text: '{{ $errors->first() }}',
                confirmButtonColor: '#1a4d2e'
            });
            @endif

            @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: '{{ session('success') }}',
                confirmButtonColor: '#1a4d2e',
                timer: 3000
            });
            @endif

            const $selectRol   = $('#selectRol');
            const $permisos    = $('.permiso');
            const $checkTodos  = $('#checkTodos');
            const $loader      = $('#loader');

            $('#selectUsuario').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Escribe el nombre del usuario...',
                allowClear: true,
                minimumInputLength: 1,
                ajax: {
                    url: "{{ route('configuracion.usuarios.buscar_ajax') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term || ''
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    }
                }
            });


            function pintarInterfaz(listaPermisos) {
                $permisos.prop('checked', false);
                $checkTodos.prop('checked', false);
                let asignados = [];

                if (Array.isArray(listaPermisos)) {
                    asignados = listaPermisos;
                } else if (typeof listaPermisos === 'object' && listaPermisos !== null) {
                    asignados = Object.values(listaPermisos);
                } else if (typeof listaPermisos === 'string') {
                    try {
                        asignados = JSON.parse(listaPermisos);
                    } catch (e) {
                        asignados = [];
                    }
                }

                asignados = asignados.map(p => String(p).trim());

                $permisos.each(function () {
                    const valorCheck = $(this).val();
                    if (asignados.includes(valorCheck)) {
                        $(this).prop('checked', true);
                    }
                });

                $('.permiso-crear:checked').each(function () {
                    $(this).closest('tr').find('.permiso-ver').prop('checked', true);
                });

                actualizarEstadoCheckTodos();
            }

            function actualizarEstadoCheckTodos() {
                const total = $permisos.length;
                const marcados = $('.permiso:checked').length;
                $checkTodos.prop('checked', total === marcados && total > 0);
            }

            // 🔥 AQUÍ ESTÁ EL FIX IMPORTANTE
            $('#selectUsuario').on('change', function () {
                const userId = $(this).val();

                if (!userId) {
                    pintarInterfaz([]);
                    $selectRol.val("");
                    return;
                }

                $loader.css('display', 'flex');

                $.ajax({
                    url: `/configuracion/permisos/buscar/${userId}`,
                    type: 'GET',
                    cache: false,
                    success: function (data) {

                        $selectRol.val(data.Id_Rol);

                        // 🔥 SOLUCIÓN: mostrar nombre en Select2
                        if (data.nombre) {
                            let option = new Option(data.nombre, userId, true, true);
                            $('#selectUsuario').append(option).trigger('change');
                        }

                        pintarInterfaz(data.permisos);
                    },
                    error: err => console.error(err),
                    complete: () => $loader.hide()
                });
            });

            $selectRol.on('change', function () {
                const rolId = $(this).val();

                if (!rolId) {
                    pintarInterfaz([]);
                    return;
                }

                $loader.css('display', 'flex');

                $.ajax({
                    url: `/configuracion/permisos/rol/${rolId}`,
                    type: 'GET',
                    cache: false,
                    success: function (data) {
                        pintarInterfaz(data.permisos);
                    },
                    error: err => {
                        console.error(err);
                        pintarInterfaz([]);
                    },
                    complete: () => $loader.hide()
                });
            });

            $checkTodos.on('change', function () {
                $permisos.prop('checked', $(this).is(':checked'));
            });

            $(document).on('change', '.permiso-crear', function () {
                if ($(this).is(':checked')) {
                    $(this).closest('tr').find('.permiso-ver').prop('checked', true);
                }
                actualizarEstadoCheckTodos();
            });

            $(document).on('change', '.permiso-eliminar', function () {
                if ($(this).is(':checked')) {
                    const $fila = $(this).closest('tr');
                    $fila.find('.permiso-ver').prop('checked', true);
                    $fila.find('.permiso-crear').prop('checked', true);
                }
                actualizarEstadoCheckTodos();
            });

            $(document).on('change', '.permiso-ver', function () {
                actualizarEstadoCheckTodos();
            });

        });
    </script>
@endsection