<div class="row">
    <div class="col-md-8 mb-3">
        <label class="form-label fw-bold">Título</label>
        <input type="text" name="Titulo"
               class="form-control @error('Titulo') is-invalid @enderror"
               value="{{ old('Titulo', $registro->Titulo ?? '') }}"
               placeholder="Ej: Reforestación" required>
        @error('Titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Fecha</label>
        <input type="date" name="Fecha"
               class="form-control @error('Fecha') is-invalid @enderror"
               value="{{ old('Fecha', $registro->Fecha ?? '') }}" required>
        @error('Fecha')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row">
    <div class="col-12 mb-3">
        <label class="form-label fw-bold">Descripción</label>
        <textarea name="Descripcion"
                  class="form-control @error('Descripcion') is-invalid @enderror"
                  rows="4"
                  placeholder="Escriba detalles de la actividad"
                  required>{{ old('Descripcion', $registro->Descripcion ?? '') }}</textarea>
        @error('Descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-3">
        <label class="form-label fw-bold">Evidencias (Imágenes o PDF - Máximo 20)</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-file-upload"></i></span>
            <input type="file" name="Evidencia[]" id="inputEvidencia"
                   class="form-control @error('Evidencia') is-invalid @enderror @error('Evidencia.*') is-invalid @enderror"
                   accept="image/*,.pdf" multiple>
        </div>
        <small class="text-muted">Formatos permitidos: JPG, PNG, PDF (Máx 10MB por archivo)</small>

        @error('Evidencia')<div class="text-danger small">{{ $message }}</div>@enderror
        @error('Evidencia.*')<div class="text-danger small">Uno de los archivos no es válido o supera el tamaño permitido</div>@enderror

        <div id="previewEvidencias" class="d-flex flex-wrap gap-3 mt-3"></div>

        @if(isset($registro) && $registro->Evidencia)
            @php
                $evidencias = json_decode($registro->Evidencia, true);
                if (json_last_error() !== JSON_ERROR_NONE) { $evidencias = [$registro->Evidencia]; }
                $evidencias = is_array($evidencias) ? array_filter($evidencias) : [];
            @endphp

            @if(isset($evidencias) && count($evidencias) > 0)
                <div class="mt-3 p-2 border rounded bg-light">
                    <p class="small mb-2 text-primary fw-bold">Archivos cargados (haz clic en X para eliminar):</p>
                    <div class="d-flex flex-wrap gap-3">
                        @foreach($evidencias as $foto)
                            <div class="position-relative">
                                @if(Str::endsWith(strtolower($foto), '.pdf'))
                                    <div class="img-thumbnail d-flex flex-column align-items-center justify-content-center bg-white" style="width: 100px; height: 100px; border: 1px solid #dee2e6;">
                                        <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                        <a href="{{ url('ver-archivo/' . $foto) }}" target="_blank" class="small text-truncate w-100 px-1 text-center text-decoration-none text-dark">
                                            Ver PDF
                                        </a>
                                    </div>
                                @else
                                    <img src="{{ url('ver-archivo/' . $foto) }}"
                                         class="img-thumbnail"
                                         style="width: 100px; height: 100px; object-fit: cover;"
                                         onerror="this.src='https://placehold.co/100?text=Error'">
                                @endif

                                <a href="{{ route('datos_historicos.foto.delete', [$registro->Id_DatosH, 'foto' => $foto]) }}"
                                   class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                   onclick="return confirm('¿Eliminar este archivo de forma permanente?')">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>

<script>
    const input = document.getElementById('inputEvidencia');
    const preview = document.getElementById('previewEvidencias');

    let archivosSeleccionados = [];

    input.addEventListener('change', function () {
        archivosSeleccionados = Array.from(this.files);

        if (archivosSeleccionados.length > 20) {
            alert("No puedes seleccionar más de 20 archivos.");
            this.value = "";
            archivosSeleccionados = [];
            preview.innerHTML = '';
            return;
        }

        renderPreviews();
    });

    function renderPreviews() {
        preview.innerHTML = '';

        archivosSeleccionados.forEach((file, index) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'position-relative';
            wrapper.style.width = '100px';

            let content;
            if (file.type === 'application/pdf') {
                content = document.createElement('div');
                content.className = 'img-thumbnail d-flex flex-column align-items-center justify-content-center bg-light';
                content.style.height = '100px';
                content.innerHTML = '<i class="fas fa-file-pdf fa-2x text-danger"></i><span class="small text-truncate w-100 text-center">PDF</span>';
            } else {
                const url = URL.createObjectURL(file);
                content = document.createElement('img');
                content.src = url;
                content.className = 'img-thumbnail';
                content.style.width = '100px';
                content.style.height = '100px';
                content.style.objectFit = 'cover';
            }

            const btn = document.createElement('span');
            btn.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
            btn.style.cursor = 'pointer';
            btn.style.zIndex = '10';
            btn.innerHTML = '<i class="fas fa-times"></i>';

            btn.addEventListener('click', () => {
                archivosSeleccionados.splice(index, 1);
                sincronizarInput();
                renderPreviews();
            });

            wrapper.appendChild(content);
            wrapper.appendChild(btn);
            preview.appendChild(wrapper);
        });
    }

    function sincronizarInput() {
        const dataTransfer = new DataTransfer();
        archivosSeleccionados.forEach(file => dataTransfer.items.add(file));
        input.files = dataTransfer.files;
    }
</script>