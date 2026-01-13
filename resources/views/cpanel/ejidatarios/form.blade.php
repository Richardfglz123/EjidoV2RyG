@extends('Cpanel/plantilla')
@section('title','Registro usuario')
@section('content')

    <!-- Encabezado --> <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal"> <i class="fas fa-users me-2"></i> Registro de Ejidatarios </h1> </div>
    <!-- CRUD --> <div class="card card-ejidal"> <div class="card-header card-header-ejidal">
            <i class="fas fa-edit me-2"></i> Nuevo Ejidatario </div> <div class="card-body">
            <form action="{{ route('Ejidatarios.store') }}" method="POST">
                @csrf

                <!-- DATOS GENERALES -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Número de Ejidatario</label>
                        <input type="text" class="form-control"
                               name="Num_Ejidatario"
                               value="{{ old('Num_Ejidatario') }}"
                               required oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Fecha de Ingreso</label>
                        <input type="date" class="form-control"
                               name="Fecha_Ingreso"
                               value="{{ old('Fecha_Ingreso') }}"
                               required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Fecha de Nacimiento</label>
                        <input type="date" class="form-control"
                               name="Fecha_Nacimiento"
                               value="{{ old('Fecha_Nacimiento') }}"
                               required>
                    </div>
                </div>

                <!-- DOCUMENTOS -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">CURP</label>
                        <input type="text" class="form-control"
                               name="CURP"
                               value="{{ old('CURP') }}"
                               maxlength="20" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">RFC</label>
                        <input type="text" class="form-control"
                               name="RFC"
                               value="{{ old('RFC') }}"
                               maxlength="15" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Clave de Elector</label>
                        <input type="text" class="form-control"
                               name="Clave_Elector"
                               value="{{ old('Clave_Elector') }}"
                               maxlength="20" required>
                    </div>
                </div>

                <!-- DIRECCIÓN -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Calle</label>
                        <input type="text" class="form-control"
                               name="Calle"
                               value="{{ old('Calle') }}"
                               required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">No. Exterior</label>
                        <input type="text" class="form-control"
                               name="Num_Exterior"
                               value="{{ old('Num_Exterior') }}"
                               required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">No. Interior</label>
                        <input type="text" class="form-control"
                               name="Num_Interior"
                               value="{{ old('Num_Interior') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Colonia</label>
                        <input type="text" class="form-control"
                               name="Colonia"
                               value="{{ old('Colonia') }}"
                               required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Municipio</label>
                        <input type="text" class="form-control"
                               name="Municipio"
                               value="{{ old('Municipio') }}"
                               required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <input type="text" class="form-control"
                               name="Estado"
                               value="{{ old('Estado') }}"
                               required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Código Postal</label>
                        <input type="text" class="form-control"
                               name="Codigo_Postal"
                               value="{{ old('Codigo_Postal') }}"
                               maxlength="10"
                               required>
                    </div>
                </div>

                <!-- RELACIONES -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Responsable</label>
                        <select class="form-select" name="Id_Usuario" required>
                            <option value="">Seleccione una opción</option>
                            @foreach ($usuarios as $u)
                                <option value="{{ $u->Id_Usuario }}"
                                    {{ old('Id_Usuario') == $u->Id_Usuario ? 'selected' : '' }}>
                                    {{ $u->Nombres }} {{ $u->Apellido_Paterno }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Estatus</label>
                        <select class="form-select" name="Id_Estatus" required>
                            <option value="">Seleccione estatus</option>
                            @foreach ($estatus as $e)
                                <option value="{{ $e->Id_Estatus }}"
                                    {{ old('Id_Estatus') == $e->Id_Estatus ? 'selected' : '' }}>
                                    {{ $e->Nombre_Estatus }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- BOTONES -->
                <div class="text-end">
                    <a href="{{ route('Ejidatarios.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-ejidal">
                        <i class="fas fa-save me-1"></i> Guardar Ejidatario
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
