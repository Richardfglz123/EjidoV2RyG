@extends('cpanel/plantilla')
@section('title', 'Home')
@section('content')

    <div class="hero-section rounded-3 text-center mb-4">
        <h1 class="display-4 fw-bold">Bienvenido al Sistema de Gestión Ejidal</h1>
        <p class="lead">Herramienta para la administracion de tu ejido</p>
    </div>

    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card card-ejidal h-100">
                <div class="card-header card-header-ejidal"><i class="fas fa-users me-2"></i> Ejidatarios</div>
                <div class="card-body">
                    <h5 class="card-title">{{ $totalEjidatarios ?? 0 }} Registrados</h5>
                    <p class="card-text">Gestiona la información de los ejidatarios.</p>
                    <a href="{{ url('/admon/Ejidatarios') }}" class="btn btn-sm btn-ejidal">Administrar</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card card-ejidal h-100">
                <div class="card-header card-header-ejidal"><i class="fas fa-clipboard-check me-2"></i> Faenas</div>
                <div class="card-body">
                    <h5 class="card-title">12 Pendientes</h5>
                    <p class="card-text">Organiza las faenas comunitarias y lleva control de asistencias.</p>
                    <a href="#" class="btn btn-sm btn-ejidal">Ver faenas</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card card-ejidal h-100">
                <div class="card-header card-header-ejidal"><i class="fas fa-map-marked-alt me-2"></i> Parcelas</div>
                <div class="card-body">
                    <h5 class="card-title">85 Registradas</h5>
                    <p class="card-text">Administra la información de parcelas y sus colindancias.</p>
                    <a href="#" class="btn btn-sm btn-ejidal">Ver parcelas</a>
                </div>
            </div>
        </div>
    </div>

    {{-- ACTIVIDAD RECIENTE (independiente de eventos) --}}
    <div class="card card-ejidal mb-4">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-history me-2"></i> Actividad Reciente
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Acción</th>
                        <th>Usuario</th>
                        <th>Detalles</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($actividadReciente ?? [] as $a)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($a->Fecha_Creo)->format('d/m/Y H:i') }}</td>
                            <td>{{ $a->Tipo }}</td>
                            <td>{{ $a->Usuario }}</td>
                            <td>{{ $a->Descripcion }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                Sin actividad reciente
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card card-ejidal h-100">
                <div class="card-header card-header-ejidal">
                    <i class="fas fa-calendar-alt me-2"></i> Eventos Proximos
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($proximosEventos as $e)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">{{ $e->Tipo }}</div>
                                    <small class="text-muted">{{ $e->Descripcion }}</small>
                                </div>
                                <span class="badge bg-primary rounded-pill">
                                {{ \Carbon\Carbon::parse($e->FechaInicio)->format('d/m/Y') }}
                            </span>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted">
                                No hay eventos próximos
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card card-ejidal h-100">
                <div class="card-header card-header-ejidal"><i class="fas fa-chart-pie me-2"></i> Estadísticas</div>
                <div class="card-body text-center">
                    <img src="https://imgs.search.brave.com/_OD2DCBAAx4-V7af3ZdEvtxq2esmTdk2hzd1NOKmkxQ/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9jeGNzL..."
                         alt="Gráficas de estadísticas" class="img-fluid mb-3">
                    <p>Ver resumen de actividades, asistencias y pagos del mes</p>
                    <a href="#" class="btn btn-sm btn-ejidal">Ver reportes completos</a>
                </div>
            </div>
        </div>
    </div>

@endsection
