<div class="row">
    <div class="col-md-8 mb-3">
        <label class="form-label fw-bold">Título</label>
        <input type="text" name="Titulo" class="form-control @error('Titulo') is-invalid @enderror"
               value="{{ old('Titulo', $registro->Titulo ?? '') }}" placeholder="Ej: Reforestacion" required>
        @error('Titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Fecha</label>
        <input type="date" name="Fecha" class="form-control @error('Fecha') is-invalid @enderror"
               value="{{ old('Fecha', $registro->Fecha ?? '') }}" required>
        @error('Fecha')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row">
    <div class="col-12 mb-3">
        <label class="form-label fw-bold">Descripción</label>
        <textarea name="Descripcion" class="form-control @error('Descripcion') is-invalid @enderror"
                  rows="4" placeholder="Escriba destalles de la actividad" required>{{ old('Descripcion', $registro->Descripcion ?? '') }}</textarea>
        @error('Descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Evidencia (Imagen o Documento)</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-upload"></i></span>
            <input type="file" name="Evidencia" class="form-control @error('Evidencia') is-invalid @enderror">
        </div>
        @if(isset($registro) && $registro->Evidencia)
            <div class="form-text text-success">
                <i class="fas fa-check-circle"></i> Actualmente hay un archivo cargado.
            </div>
        @endif
        @error('Evidencia')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
