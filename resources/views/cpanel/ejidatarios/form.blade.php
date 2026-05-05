@extends('cpanel/plantilla')
@section('title', 'Gestión de Ejidatarios')

@section('content')
    {{-- Librerías --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        /* Título en Negro (Igual que Usuarios) */
        .text-negro-titulo { color: #000000 !important; font-weight: normal !important; }

        /* EL VERDE EXACTO (#198754) APLICADO A LAS CLASES */
        .card-header-ejidal {
            background-color: #198754 !important;
            color: white !important;
        }

        .btn-ejidal {
            background-color: #198754 !important;
            border-color: #198754 !important;
            color: white !important;
        }

        .btn-ejidal:hover {
            background-color: #157347 !important;
            border-color: #157347 !important;
            color: white !important;
        }

        /* Ajustes Select2 para que combine con el verde */
        .select2-container--bootstrap-5.select2-container--focus .select2-selection {
            border-color: #198754 !important;
            box-shadow: 0 0 0 .25rem rgba(25, 135, 84, 0.25) !important;
        }

        .input-readonly { background-color: #f8f9fa !important; cursor: not-allowed; border-color: #dee2e6 !important; }
    </style>

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-negro-titulo">
            <i class="fas fa-person-digging me-2"></i> Ejidatarios
        </h1>
    </div>

    <div class="card card-ejidal shadow-sm">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-user-edit me-2"></i>
            {{ isset($fila) ? 'Editar Ejidatario' : 'Registrar Nuevo Ejidatario' }}
        </div>

        <form action="{{ isset($fila) ? route('Ejidatarios.update', $fila->Id_Ejidatario) : route('Ejidatarios.store') }}" method="POST" id="formEjidatario">
            @csrf
            @if(isset($fila)) @method('PUT') @endif

            <div class="card-body">

                <h6 class="text-muted border-bottom pb-2 mb-3">Datos Generales</h6>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Número de Ejidatario</label>
                        <input type="text" name="Num_Ejidatario" class="form-control"
                               value="{{ old('Num_Ejidatario', $fila->Num_Ejidatario ?? '') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label>Fecha de Ingreso</label>
                        <input type="date" name="Fecha_Ingreso" class="form-control"
                               value="{{ old('Fecha_Ingreso', $fila->Fecha_Ingreso ?? '') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label>Fecha de Nacimiento</label>
                        <input type="date" name="Fecha_Nacimiento" id="fecha_nacimiento" class="form-control"
                               max="{{ date('Y-m-d') }}" value="{{ old('Fecha_Nacimiento', $fila->Fecha_Nacimiento ?? '') }}" required>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label>CURP</label>
                        <input type="text" name="CURP" class="form-control contador" placeholder="GARC900101HDFLNS09" maxlength="18"
                               oninput="this.value=this.value.toUpperCase()" value="{{ old('CURP', $fila->CURP ?? '') }}" required>
                        <small class="text-muted">18 caracteres · <span class="chars">0</span>/18</small>
                    </div>
                    <div class="col-md-4">
                        <label>RFC</label>
                        <input type="text" name="RFC" class="form-control contador" placeholder="GARC9001019A1" maxlength="13"
                               oninput="this.value=this.value.toUpperCase()" value="{{ old('RFC', $fila->RFC ?? '') }}" required>
                        <small class="text-muted">12–13 caracteres · <span class="chars">0</span>/13</small>
                    </div>
                    <div class="col-md-4">
                        <label>Clave de Elector</label>
                        <input type="text" name="Clave_Elector" class="form-control contador" placeholder="GARC900101HDFLNS09" maxlength="18"
                               oninput="this.value=this.value.toUpperCase()" value="{{ old('Clave_Elector', $fila->Clave_Elector ?? '') }}" required>
                        <small class="text-muted">18 caracteres · <span class="chars">0</span>/18</small>
                    </div>
                </div>

                <h6 class="text-muted border-bottom pb-2 mb-3">Domicilio</h6>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="fw-bold">Código Postal</label>
                        <input type="text" name="Codigo_Postal" id="codigo_postal" class="form-control"
                               maxlength="5" placeholder="72000" oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                               value="{{ old('Codigo_Postal', $fila->Codigo_Postal ?? '') }}" required>
                        <small id="cp_status" class="form-text"></small>
                    </div>
                    <div class="col-md-4">
                        <label>Estado</label>
                        <input type="text" name="Estado" id="estado" class="form-control input-readonly"
                               value="{{ old('Estado', $fila->Estado ?? '') }}" readonly required>
                    </div>
                    <div class="col-md-5">
                        <label>Municipio</label>
                        <input type="text" name="Municipio" id="municipio" class="form-control input-readonly"
                               value="{{ old('Municipio', $fila->Municipio ?? '') }}" readonly required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Colonia</label>
                        <select name="Colonia" id="colonia" class="form-select" required>
                            @if(isset($fila->Colonia))
                                <option value="{{ $fila->Colonia }}" selected>{{ $fila->Colonia }}</option>
                            @else
                                <option value="">Esperando CP...</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Calle</label>
                        <input type="text" name="Calle" class="form-control"
                               value="{{ old('Calle', $fila->Calle ?? '') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label>No. Ext.</label>
                        <input type="text" name="Num_Exterior" class="form-control" maxlength="10"
                               oninput="this.value=this.value.toUpperCase()" value="{{ old('Num_Exterior', $fila->Num_Exterior ?? '') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label>No. Int.</label>
                        <input type="text" name="Num_Interior" class="form-control" maxlength="10"
                               oninput="this.value=this.value.toUpperCase()" value="{{ old('Num_Interior', $fila->Num_Interior ?? '') }}">
                    </div>
                </div>

                <h6 class="text-muted border-bottom pb-2 mb-3">Control Administrativo</h6>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Responsable</label>
                        <select class="form-select select-buscador" name="Id_Usuario" required>
                            <option value=""></option>
                            @foreach ($usuarios as $u)
                                <option value="{{ $u->Id_Usuario }}" {{ old('Id_Usuario', $fila->Id_Usuario ?? '') == $u->Id_Usuario ? 'selected' : '' }}>
                                    {{ $u->Nombres }} {{ $u->Apellido_Paterno }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Estatus</label>
                        <select class="form-select" name="Id_Estatus" required>
                            <option value="">Seleccione estatus</option>
                            @foreach ($estatus as $e)
                                <option value="{{ $e->Id_Estatus }}" {{ old('Id_Estatus', $fila->Id_Estatus ?? '') == $e->Id_Estatus ? 'selected' : '' }}>
                                    {{ $e->Estatus }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <a href="{{ route('Ejidatarios.index') }}" class="btn btn-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-ejidal">
                        <i class="fas fa-save me-1"></i> Guardar Ejidatario
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Scripts --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.select-buscador').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: "Buscar...",
                allowClear: true
            });

            $('#codigo_postal').on('input', function() {
                let cp = $(this).val();
                let status = $('#cp_status');

                if (cp.length === 5) {
                    status.html('<i class="fas fa-spinner fa-spin"></i> Cargando...').css('color', '#198754');
                    $.ajax({
                        url: "{{ url('admon/Ejidatarios/api/cp') }}/" + cp,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            if (data.length > 0) {
                                $('#estado').val(data[0].estado);
                                $('#municipio').val(data[0].municipio);
                                let col = $('#colonia').empty().append('<option value="">Seleccione colonia...</option>');
                                data.forEach(item => col.append(`<option value="${item.colonia}">${item.colonia}</option>`));
                                status.html('<i class="fas fa-check"></i>').css('color', '#198754');
                            } else {
                                status.text('No encontrado').css('color', '#dc3545');
                                limpiarCamposCP();
                            }
                        }
                    });
                } else {
                    status.text('');
                    limpiarCamposCP();
                }
            });

            function limpiarCamposCP() {
                $('#estado, #municipio').val('');
                $('#colonia').empty().append('<option value="">Esperando CP...</option>');
            }

            $('.contador').on('input', function() {
                let currentLen = $(this).val().length;
                $(this).closest('div').find('.chars').text(currentLen);
            }).trigger('input');
        });
    </script>
@endsection