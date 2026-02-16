@extends('cpanel.plantilla')
@section('title', 'Editar Parcela')
@section('content')

    {{-- CSS de Select2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
        <h1 class="h2 text-ejidal"><i class="fas fa-edit me-2"></i>Editar Parcela #{{ $parcela->No_Parcela }}</h1>
        <a href="{{ route('parcelas.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Volver al listado
        </a>
    </div>

    {{-- FORMULARIO PRINCIPAL --}}
    <form method="POST" action="{{ route('parcelas.actualizar', $parcela->Id_Parcela) }}">
        @csrf
        @method('PUT')

        {{-- SECCIÓN: BUSCADOR DE EJIDATARIO --}}
        <div class="card mb-4 border-start border-4 border-ejidal shadow-sm">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <i class="fas fa-user-search fa-2x text-ejidal"></i>
                    </div>
                    <div class="col">
                        <label class="form-label fw-bold text-ejidal small mb-1">Buscar y Asignar Ejidatario</label>
                        <select name="Id_Ejidatario" id="buscador-ejidatarios" class="form-select shadow-sm" required>
                            @foreach($todosLosEjidatarios as $e)
                                <option value="{{ $e->Id_Ejidatario }}"
                                        {{ old('Id_Ejidatario', $parcela->Id_Ejidatario) == $e->Id_Ejidatario ? 'selected' : '' }}>
                                    {{ $e->Num_Ejidatario }} - {{ $e->usuario->Nombres }} {{ $e->usuario->Apellido_Paterno }} {{ $e->usuario->Apellido_Materno }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small">Escriba el nombre o número de ejidatario para filtrar.</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECCIÓN: DATOS DE PARCELA --}}
        <div class="card perfil-card mb-4 shadow-sm">
            <div class="card-header card-header-ejidal fw-bold">
                <i class="fas fa-map me-2"></i> Información General de la Parcela
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">No. Parcela</label>
                        <input type="number" name="noParcela" class="form-control" value="{{ old('noParcela', $parcela->No_Parcela) }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Superficie (Ha)</label>
                        <input type="text" name="superficie" class="form-control" value="{{ old('superficie', $parcela->Superficie) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Uso de Suelo</label>
                        <select name="usoSuelo" class="form-select">
                            @foreach($usos as $uso)
                                <option value="{{ $uso->Id_Uso }}" {{ old('usoSuelo', $parcela->Id_Uso) == $uso->Id_Uso ? 'selected' : '' }}>
                                    {{ $uso->Nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">Ubicación / Paraje</label>
                        <input type="text" name="ubicacion" class="form-control" value="{{ old('ubicacion', $parcela->Ubicacion) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- SECCIÓN: COLINDANCIAS --}}
        <div class="card perfil-card mb-4 shadow-sm">
            <div class="card-header card-header-ejidal fw-bold">
                <i class="fas fa-border-style me-2"></i> Colindancias
            </div>
            <div class="card-body">
                <div class="row">
                    @php
                        $vientos = ['Norte','Sur','Este','Oeste','Noreste','Noroeste','Sureste','Suroeste'];
                        $col = $parcela->colindancia;
                    @endphp
                    @foreach($vientos as $c)
                        <div class="col-md-3 mb-3">
                            <label class="form-label text-capitalize small fw-bold text-muted">{{ $c }}</label>
                            @php $campo = strtolower($c); @endphp
                            <input type="text" name="{{ $campo }}" class="form-control form-control-sm"
                                   value="{{ old($campo, $col->$campo ?? '') }}">
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- SECCIÓN: COORDENADAS --}}
        <div class="card perfil-card mb-4 shadow-sm">
            <div class="card-header card-header-ejidal fw-bold">
                <i class="fas fa-draw-polygon me-2"></i> Vértices y Coordenadas (UTM)
            </div>
            <div class="card-body bg-light">
                @php $letrasPuntos = ['A', 'B', 'C', 'D', 'E', 'F', 'G']; @endphp
                @foreach($letrasPuntos as $index => $letra)
                    @php $registroCoordenada = $parcela->coordenadas->firstWhere('Punto', $letra); @endphp
                    <div class="row mb-2 align-items-end border-bottom pb-2">
                        <div class="col-md-2">
                            <label class="form-label small">Punto</label>
                            <input type="text" name="punto[]" class="form-control form-control-sm text-center fw-bold bg-white" value="{{ $letra }}" readonly>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small">Coordenada X (UTM)</label>
                            <input type="text" name="coordenadaX[]" class="form-control form-control-sm"
                                   value="{{ old('coordenadaX.'.$index, $registroCoordenada->CoordenadaX ?? '') }}">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small">Coordenada Y (UTM)</label>
                            <input type="text" name="coordenadaY[]" class="form-control form-control-sm"
                                   value="{{ old('coordenadaY.'.$index, $registroCoordenada->CoordenadaY ?? '') }}">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- SECCIÓN: INFO ADMINISTRATIVA --}}
        <div class="card perfil-card mb-4 shadow-sm">
            <div class="card-header card-header-ejidal fw-bold">
                <i class="fas fa-file-contract me-2"></i> Información Administrativa
            </div>
            <div class="card-body">
                @php $info = $parcela->infAdmin; @endphp
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Inscripción RAN</label>
                        <input type="text" name="num_inscripcionRAN" class="form-control"
                               value="{{ old('num_inscripcionRAN', $info->Num_InscripcionRAN ?? '') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Clave Núcleo Agrario</label>
                        <input type="text" name="claveNucleoAgrario" class="form-control"
                               value="{{ old('claveNucleoAgrario', $info->ClaveNucleoAgrario ?? '') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Comunidad</label>
                        <input type="text" name="comunidad" class="form-control"
                               value="{{ old('comunidad', $info->Comunidad ?? '') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Fecha de Expedición</label>
                        <input type="date" name="fechaExpedicion" class="form-control"
                               value="{{ old('fechaExpedicion', $info->FechaExpedicion ?? '') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end mb-5">
            <a href="{{ route('parcelas.index') }}" class="btn btn-secondary px-4 me-2">Cancelar</a>
            <button type="submit" class="btn btn-ejidal px-5 shadow-sm">
                <i class="fas fa-save me-2"></i> Guardar Cambios
            </button>
        </div>
    </form>

    {{-- SCRIPTS PARA EL BUSCADOR --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#buscador-ejidatarios').select2({
                theme: 'bootstrap-5',
                placeholder: 'Seleccione un ejidatario...',
                allowClear: true,
                language: {
                    noResults: function() {
                        return "No se encontraron resultados";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            });
        });
    </script>
@endsection