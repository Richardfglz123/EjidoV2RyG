<div class="card card-ejidal">
    <div class="card-header card-header-ejidal">
        <span>
            <i class="fas fa-user-edit me-2"></i>
            {{ isset($fila) ? 'Editar Ejidatario' : 'Registrar Nuevo Ejidatario' }}
        </span>
    </div>

    <div class="card-body">
        <h6 class="text-muted border-bottom pb-2 mb-3">Datos Generales</h6>
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label fw-bold">Número de Ejidatario</label>
                        <input type="text" class="form-control"
                               name="Num_Ejidatario"
                               value="{{ old('Num_Ejidatario', $fila->Num_Ejidatario ?? '') }}"
                               required oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                    </div>

            <div class="col-md-4">
                <label class="form-label fw-bold">Fecha de Ingreso</label>
                <input type="date" class="form-control" name="Fecha_Ingreso"
                       value="{{ old('Fecha_Ingreso', $fila->Fecha_Ingreso ?? '') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Fecha de Nacimiento</label>
                <input type="date" class="form-control" name="Fecha_Nacimiento"
                       value="{{ old('Fecha_Nacimiento', $fila->Fecha_Nacimiento ?? '') }}" required>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <label class="form-label fw-bold">CURP</label>
                <input type="text" class="form-control" name="CURP"
                       value="{{ old('CURP', $fila->CURP ?? '') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">RFC</label>
                <input type="text" class="form-control" name="RFC"
                       value="{{ old('RFC', $fila->RFC ?? '') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Clave de Elector</label>
                <input type="text" class="form-control" name="Clave_Elector"
                       value="{{ old('Clave_Elector', $fila->Clave_Elector ?? '') }}" required>
            </div>
        </div>

        <h6 class="text-muted border-bottom pb-2 mb-3">Domicilio</h6>
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label fw-bold">Calle</label>
                <input type="text" class="form-control" name="Calle"
                       value="{{ old('Calle', $fila->Calle ?? '') }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">No. Ext.</label>
                <input type="text" class="form-control" name="Num_Exterior"
                       value="{{ old('Num_Exterior', $fila->Num_Exterior ?? '') }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">No. Int.</label>
                <input type="text" class="form-control" name="Num_Interior"
                       value="{{ old('Num_Interior', $fila->Num_Interior ?? '') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Colonia</label>
                <input type="text" class="form-control" name="Colonia"
                       value="{{ old('Colonia', $fila->Colonia ?? '') }}" required>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <label class="form-label fw-bold">Municipio</label>
                <input type="text" class="form-control" name="Municipio"
                       value="{{ old('Municipio', $fila->Municipio ?? '') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Estado</label>
                <input type="text" class="form-control" name="Estado"
                       value="{{ old('Estado', $fila->Estado ?? '') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Código Postal</label>
                <input type="text" class="form-control" name="Codigo_Postal"
                       value="{{ old('Codigo_Postal', $fila->Codigo_Postal ?? '') }}" required>
            </div>
        </div>

        <h6 class="text-muted border-bottom pb-2 mb-3">Control Administrativo</h6>
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">Responsable</label>
                <select class="form-select" name="Id_Usuario" required>
                    <option value="">Seleccione una opción</option>
                    @foreach ($usuarios as $u)
                        <option value="{{ $u->Id_Usuario }}"
                            {{ old('Id_Usuario', $fila->Id_Usuario ?? '') == $u->Id_Usuario ? 'selected' : '' }}>
                            {{ $u->Nombres }} {{ $u->Apellido_Paterno }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">Estatus</label>
                <select class="form-select" name="Id_Estatus" required>
                    <option value="">Seleccione estatus</option>
                    @foreach ($estatus as $e)
                        <option value="{{ $e->Id_Estatus }}"
                            {{ old('Id_Estatus', $fila->Id_Estatus ?? '') == $e->Id_Estatus ? 'selected' : '' }}>
                            {{ $e->Estatus }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="card-footer bg-light text-end">
        <a href="{{ route('Ejidatarios.index') }}" class="btn btn-secondary me-2">
            <i class="fas fa-times me-1"></i> Cancelar
        </a>
        <button type="submit" class="btn btn-ejidal">
            <i class="fas fa-save me-1"></i>
            {{ isset($fila) ? 'Actualizar Registro' : 'Guardar Registro' }}
        </button>
    </div>
</div>
