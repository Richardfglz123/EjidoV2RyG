@extends('cpanel/plantilla')
@section('title', 'Gestión de Ejidatarios')

@section('content')
    {{-- Librerías --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        /* Ajustes Select2 para que combine con el estilo de Usuarios */
        .select2-container--bootstrap-5 .select2-selection {
            border: 1px solid #ced4da !important;
            min-height: 38px !important;
        }
        .select2-container--bootstrap-5.select2-container--focus .select2-selection {
            border-color: #86b7fe !important;
            box-shadow: 0 0 0 .2rem rgba(13,110,253,.25);
        }
    </style>

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-person-digging me-2"></i> Ejidatarios
        </h1>
    </div>

    <div class="card card-ejidal">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-user-edit me-2"></i>
            {{ isset($fila) ? 'Editar Ejidatario' : 'Registrar Nuevo Ejidatario' }}
        </div>

        <form action="{{ isset($fila) ? route('Ejidatarios.update', $fila->Id_Ejidatario) : route('Ejidatarios.store') }}" method="POST" id="formEjidatario">
            @csrf
            @if(isset($fila)) @method('PUT') @endif

            <div class="card-body">

                <h6 class="text-muted border-bottom pb-2 mb-3">Datos Generales</h6>

                {{-- No. Ejidatario, Fecha Ingreso, Fecha Nacimiento --}}
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
                        <input type="date" name="Fecha_Nacimiento" id="fecha_nacimiento"
                               class="form-control" max="{{ date('Y-m-d') }}"
                               value="{{ old('Fecha_Nacimiento', $fila->Fecha_Nacimiento ?? '') }}" required>
                        <small class="text-muted">Ingresa una fecha valida</small>
                    </div>
                </div>

                {{-- CURP, RFC, Elector --}}
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label>CURP</label>
                        <input type="text" name="CURP" class="form-control contador"
                               placeholder="GARC900101HDFLNS09" maxlength="18"
                               oninput="this.value=this.value.toUpperCase()"
                               value="{{ old('CURP', $fila->CURP ?? '') }}" required>
                        <small class="text-muted">
                            18 caracteres · Ej: <code>GARC900101HDFLNS09</code> · <span class="chars">0</span>/18
                        </small>
                    </div>

                    <div class="col-md-4">
                        <label>RFC</label>
                        <input type="text" name="RFC" class="form-control contador"
                               placeholder="GARC9001019A1" maxlength="13"
                               oninput="this.value=this.value.toUpperCase()"
                               value="{{ old('RFC', $fila->RFC ?? '') }}" required>
                        <small class="text-muted">
                            12–13 caracteres · Ej: <code>GARC9001019A1</code> · <span class="chars">0</span>/13
                        </small>
                    </div>

                    <div class="col-md-4">
                        <label>Clave de Elector</label>
                        <input type="text" name="Clave_Elector" class="form-control contador"
                               placeholder="GARC900101HDFLNS09" maxlength="18"
                               oninput="this.value=this.value.toUpperCase()"
                               value="{{ old('Clave_Elector', $fila->Clave_Elector ?? '') }}" required>
                        <small class="text-muted">
                            18 caracteres exactos · Ej: <code>GARC900101HDFLNS09</code> · <span class="chars">0</span>/18
                        </small>
                    </div>
                </div>

                <h6 class="text-muted border-bottom pb-2 mb-3">Domicilio</h6>

                {{-- Calle, No Ext, No Int, Colonia --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Calle</label>
                        <input type="text" name="Calle" class="form-control"
                               value="{{ old('Calle', $fila->Calle ?? '') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label>No. Ext.</label>
                        <input type="text" name="Num_Exterior" class="form-control" maxlength="5"
                               oninput="this.value=this.value.toUpperCase()"
                               value="{{ old('Num_Exterior', $fila->Num_Exterior ?? '') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label>No. Int.</label>
                        <input type="text" name="Num_Interior" class="form-control" maxlength="5"
                               oninput="this.value=this.value.toUpperCase()"
                               value="{{ old('Num_Interior', $fila->Num_Interior ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label>Colonia</label>
                        <input type="text" name="Colonia" class="form-control"
                               value="{{ old('Colonia', $fila->Colonia ?? '') }}" required>
                    </div>
                </div>

                {{-- Municipio, Estado, CP --}}
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label>Municipio</label>
                        <input type="text" name="Municipio" class="form-control"
                               value="{{ old('Municipio', $fila->Municipio ?? '') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label>Estado</label>
                        <select class="form-select" name="Estado" required>
                            <option value="">Seleccione estado</option>
                            @php
                                $estados = ['Aguascalientes','Baja California','Baja California Sur','Campeche','Chiapas','Chihuahua','Ciudad de México','Coahuila','Colima','Durango','Guanajuato','Guerrero','Hidalgo','Jalisco','Estado de México','Michoacán','Morelos','Nayarit','Nuevo León','Oaxaca','Puebla','Querétaro','Quintana Roo','San Luis Potosí','Sinaloa','Sonora','Tabasco','Tamaulipas','Tlaxcala','Veracruz','Yucatán','Zacatecas'];
                            @endphp
                            @foreach ($estados as $estado)
                                <option value="{{ $estado }}" {{ old('Estado', $fila->Estado ?? '') == $estado ? 'selected' : '' }}>{{ $estado }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Código Postal</label>
                        <input type="text" name="Codigo_Postal" class="form-control"
                               maxlength="5" oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                               value="{{ old('Codigo_Postal', $fila->Codigo_Postal ?? '') }}" required>
                    </div>
                </div>

                <h6 class="text-muted border-bottom pb-2 mb-3">Control Administrativo</h6>

                {{-- Responsable y Estatus --}}
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

                {{-- Botones (alineados como en Usuarios) --}}
                <div class="text-end">
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
            // Buscador Select2
            $('.select-buscador').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: "Buscar...",
                allowClear: true
            });

            // Contadores de caracteres
            document.querySelectorAll('.contador').forEach(input => {
                const info = input.parentElement.querySelector('.chars');
                const max = input.getAttribute('maxlength');
                const update = () => { if (info) info.textContent = input.value.length; };
                input.addEventListener('input', update);
                update();
            });

// Validación de decga de nacImineTo
            $('#formEjidatario').on('submit', function(e) {
                const fechaVal = $('#fecha_nacimiento').val();

                if(fechaVal) {
                    const fechaNac = new Date(fechaVal);
                    const hoy = new Date();
                    const anioMinimo = hoy.getFullYear() - 110;
                    const minFecha = new Date();
                    minFecha.setFullYear(anioMinimo);
                    if (fechaNac > hoy) {
                        alert("Error: La fecha de nacimiento no puede ser una fecha futura.");
                        e.preventDefault();
                        return false;
                    }
                    if (fechaNac < minFecha) {
                        alert("Error: La fecha de nacimiento es demasiado antigua (el límite son 110 años).");
                        e.preventDefault();
                        return false;
                    }
                    const anioIngresado = fechaNac.getFullYear();
                    if (anioIngresado < 1900) {
                        alert("Error: Por favor ingrese un año válido de 4 dígitos (mayor a 1900).");
                        e.preventDefault();
                        return false;
                    }
                }
            });
        });
    </script>
@endsection