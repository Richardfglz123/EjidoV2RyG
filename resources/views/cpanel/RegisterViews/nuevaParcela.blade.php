@extends('cpanel.plantilla')
@section('title', 'Nueva Parcela')
@section('content')

    {{-- ALERTAS DE SISTEMA --}}
    @if(session('status') === 'success')
        <div class="alert alert-success">Información guardada correctamente.</div>
    @endif
    @if(session('status') === 'error' || $error)
        <div class="alert alert-danger">{{ session('mensaje') ?? $error }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
        <h1 class="h2 text-ejidal"><i class="fas fa-map-marked-alt me-2"></i>Gestión de Parcelas</h1>
        <a href="{{ route('parcelas.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-list me-1"></i> Ir al listado
        </a>
    </div>

    {{-- SECCIÓN DE BÚSQUEDA --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card perfil-card h-100">
                <div class="card-header card-header-ejidal">
                    <i class="fas fa-search me-2"></i> Consultar Parcela
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('parcelas.ver') }}">
                        <div class="input-group">
                            <input type="number" class="form-control border-ejidal" name="noParcela"
                                   placeholder="No. de Parcela..." value="{{ request('noParcela') }}" required>
                            <button class="btn btn-ejidal">Buscar</button>
                        </div>
                    </form>
                    @if(session('parcela_error'))
                        <div class="text-danger small mt-2"><i class="fas fa-exclamation-circle"></i> {{ session('parcela_error') }}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card perfil-card h-100">
                <div class="card-header card-header-ejidal">
                    <i class="fas fa-user-search me-2"></i> Asignar Ejidatario
                </div>
                <div class="card-body">
                    <form method="GET">
                        <div class="input-group">
                            <input type="number" class="form-control border-ejidal" name="numeroEjidatario"
                                   placeholder="Número de Ejidatario..." value="{{ request('numeroEjidatario') }}" required>
                            <button class="btn btn-ejidal">Buscar</button>
                        </div>
                    </form>
                    @if($Ejidatario)
                        <div class="alert alert-success py-2 mt-2 mb-0">
                            <i class="fas fa-check-circle"></i>
                            <strong>
                                {{ $Ejidatario->usuario->Nombres }}
                                {{ $Ejidatario->usuario->Apellido_Paterno }}
                                {{ $Ejidatario->usuario->Apellido_Materno }}
                            </strong>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    {{-- FORMULARIO PRINCIPAL --}}
    @if(!$Ejidatario)
        <div class="alert alert-warning border-start border-4 border-warning">
            <i class="fas fa-arrow-up me-2"></i> Por favor, busque y seleccione un ejidatario para habilitar el registro de la parcela.
        </div>
    @endif

    <form method="POST" action="{{ route('parcelas.store') }}">
        @csrf
        <input type="hidden" name="numeroEjidatario" value="{{ $Ejidatario->Num_Ejidatario ?? '' }}">
        <div class="card perfil-card">
            <div class="card-header card-header-ejidal">
                <i class="fas fa-file-invoice me-2"></i> Datos de la Nueva Parcela
            </div>
            <div class="card-body">

                {{-- DATOS GENERALES --}}
                <h6 class="text-ejidal fw-bold mb-3 mt-2"><i class="fas fa-info-circle me-1"></i> Información General</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">No. Parcela</label>
                        <input type="number" name="noParcela" class="form-control" {{ $Ejidatario ? '' : 'disabled' }} required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Superficie (Ha)</label>
                        <input type="text" name="superficie" class="form-control" placeholder="0.0000" {{ $Ejidatario ? '' : 'disabled' }}>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ubicación / Paraje</label>
                        <input type="text" name="ubicacion" class="form-control" {{ $Ejidatario ? '' : 'disabled' }}>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Uso de Suelo</label>
                        <select name="usoSuelo" class="form-select" {{ $Ejidatario ? '' : 'disabled' }}>
                            @foreach($usos as $uso)
                                <option value="{{ $uso->idUso }}">{{ $uso->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr class="my-4">

                {{-- COLINDANCIAS --}}
                <h6 class="text-ejidal fw-bold mb-3"><i class="fas fa-border-all me-1"></i> Colindancias</h6>
                <div class="row">
                    @foreach(['norte','sur','este','oeste','noreste','noroeste','sureste','suroeste'] as $c)
                        <div class="col-md-3 mb-3">
                            <label class="form-label text-capitalize">{{ $c }}</label>
                            <input type="text" name="{{ $c }}" class="form-control form-control-sm" {{ $Ejidatario ? '' : 'disabled' }}>
                        </div>
                    @endforeach
                </div>

                <hr class="my-4">

                {{-- COORDENADAS --}}
                <h6 class="text-ejidal fw-bold mb-3"><i class="fas fa-map-pin me-1"></i> Vértices y Coordenadas (UTM)</h6>
                <div class="bg-light p-3 rounded mb-3">
                    @foreach(range('A','G') as $p)
                        <div class="row mb-2 align-items-center">
                            <div class="col-md-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-ejidal text-white">Punto</span>
                                    <input type="text" name="punto[]" value="{{ $p }}" class="form-control text-center fw-bold" readonly>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <input type="number" step="0.000001" name="coordenadaX[]" class="form-control form-control-sm" placeholder="Coordenada X" {{ $Ejidatario ? '' : 'disabled' }}>
                            </div>
                            <div class="col-md-5">
                                <input type="number" step="0.000001" name="coordenadaY[]" class="form-control form-control-sm" placeholder="Coordenada Y" {{ $Ejidatario ? '' : 'disabled' }}>
                            </div>
                        </div>
                    @endforeach
                </div>

                <hr class="my-4">

                {{-- INFO ADMINISTRATIVA --}}
                <h6 class="text-ejidal fw-bold mb-3"><i class="fas fa-gavel me-1"></i> Información Administrativa (Certificado)</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Número de inscripción RAN</label>
                        <input type="text" name="num_inscripcionRAN" class="form-control" {{ $Ejidatario ? '' : 'disabled' }}>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Clave núcleo agrario</label>
                        <input type="text" name="claveNucleoAgrario" class="form-control" {{ $Ejidatario ? '' : 'disabled' }}>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Comunidad / Municipio</label>
                        <input type="text" name="comunidad" class="form-control" {{ $Ejidatario ? '' : 'disabled' }}>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Fecha de expedición</label>
                        <input type="date" name="fechaExpedicion" class="form-control" {{ $Ejidatario ? '' : 'disabled' }}>
                    </div>
                </div>

                <div class="text-end mt-4 border-top pt-3">
                    <button type="submit" class="btn btn-ejidal px-5" {{ $Ejidatario ? '' : 'disabled' }}>
                        <i class="fas fa-save me-2"></i> Guardar Información de Parcela
                    </button>
                </div>
            </div>
        </div>
    </form>

@endsection
