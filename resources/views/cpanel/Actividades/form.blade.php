<!-- ENCABEZADO -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2 text-ejidal">
        <i class="fas fa-clipboard-list me-2"></i>
        {{ isset($fila) ? 'Editar Actividad' : 'Registro de Actividades' }}
    </h1>
</div>

<div class="card card-ejidal mb-4">
    <div class="card-header card-header-ejidal">
        <i class="fas fa-calendar-alt me-2"></i>
        {{ isset($fila) ? 'Modificar Actividad' : 'Registrar Actividad' }}
    </div>

    <div class="card-body">

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="Tipo" class="form-label">Tipo de Actividad</label>
                <select id="Tipo" name="Tipo" class="form-select" required>
                    <option value="">Selecciona un tipo...</option>

                    <option value="Faena" {{ old('Tipo', $fila->Tipo ?? '') == 'Faena' ? 'selected' : '' }}>Faena (Trabajo Comunitario)</option>
                    <option value="Reunión" {{ old('Tipo', $fila->Tipo ?? '') == 'Reunión' ? 'selected' : '' }}>Reunión / Asamblea</option>
                    <option value="Proyecto" {{ old('Tipo', $fila->Tipo ?? '') == 'Proyecto' ? 'selected' : '' }}>Proyecto Ejidal</option>
                    <option value="Capacitación" {{ old('Tipo', $fila->Tipo ?? '') == 'Capacitación' ? 'selected' : '' }}>Capacitación / Taller</option>
                    <option value="Gestión" {{ old('Tipo', $fila->Tipo ?? '') == 'Gestión' ? 'selected' : '' }}>Gestión Administrativa</option>
                    <option value="Otro" {{ old('Tipo', $fila->Tipo ?? '') == 'Otro' ? 'selected' : '' }}>Otro</option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="FechaInicio" class="form-label">Fecha de Inicio</label>
                <input type="date"
                       id="FechaInicio"
                       name="FechaInicio"
                       value="{{ old('FechaInicio', $fila->FechaInicio ?? '') }}"
                       class="form-control"
                       required>
            </div>

            <div class="col-md-3">
                <label for="FechaFin" class="form-label">Fecha de Fin</label>
                <input type="date"
                       id="FechaFin"
                       name="FechaFin"
                       value="{{ old('FechaFin', $fila->FechaFin ?? '') }}"
                       class="form-control"
                       required>
            </div>
        </div>

        <div class="mb-3">
            <label for="Descripcion" class="form-label">Descripción / Objetivos</label>
            <textarea id="Descripcion"
                      name="Descripcion"
                      class="form-control"
                      rows="3"
                      maxlength="200"
                      required>{{ old('Descripcion', $fila->Descripcion ?? '') }}</textarea>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="Estado_Actividad" class="form-label">Estado de la Actividad</label>
                <select id="Estado_Actividad" name="Estado_Actividad" class="form-select" required>
                    <option value="">Selecciona...</option>
                    <option value="Pendiente" {{ old('Estado_Actividad', $fila->Estado_Actividad ?? '') == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="En curso" {{ old('Estado_Actividad', $fila->Estado_Actividad ?? '') == 'En curso' ? 'selected' : '' }}>En curso</option>
                    <option value="Finalizado" {{ old('Estado_Actividad', $fila->Estado_Actividad ?? '') == 'Finalizado' ? 'selected' : '' }}>Finalizado</option>
                </select>
            </div>

            <div class="col-md-4">
                <label for="Registro_Original" class="form-label">Registro Original</label>
                <input type="date"
                       id="Registro_Original"
                       name="Registro_Original"
                       value="{{ old('Registro_Original', $fila->Registro_Original ?? '') }}"
                       class="form-control"
                       required>
            </div>

            <div class="col-md-4">
                <label for="Nueva_Fecha" class="form-label">Nueva Fecha (si aplica)</label>
                <input type="date"
                       id="Nueva_Fecha"
                       name="Nueva_Fecha"
                       value="{{ old('Nueva_Fecha', $fila->Nueva_Fecha ?? '') }}"
                       class="form-control">
            </div>
        </div>

        <div class="mb-3">
            <label for="Fecha_Realizo" class="form-label">Fecha en que se realizó</label>
            <input type="date"
                   id="Fecha_Realizo"
                   name="Fecha_Realizo"
                   value="{{ old('Fecha_Realizo', $fila->Fecha_Realizo ?? '') }}"
                   class="form-control">
        </div>

        <div class="row justify-content-end">
            <div class="col-2">
                <a href="{{ route('actividades.index') }}" class="btn btn-secondary">
                    Cancelar
                </a>
            </div>

            <div class="col-2">
                <button type="submit" class="btn btn-ejidal">
                    <i class="fas fa-save me-1"></i>
                    {{ isset($fila) ? 'Actualizar' : 'Guardar' }}
                </button>
            </div>
        </div>

    </div>
</div>
