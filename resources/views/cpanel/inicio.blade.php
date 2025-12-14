@extends('cpanel/plantilla')
@section('title', 'Home')
@section('content')

    <div class="hero-section rounded-3 text-center mb-4">
        <h1 class="display-4 fw-bold">Bienvenido al Sistema de Gestión Ejidal</h1>
        <p class="lead">Herramienta integral para la administración de tu ejido</p>
    </div>

    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card card-ejidal h-100">
                <div class="card-header card-header-ejidal"><i class="fas fa-users me-2"></i> Ejidatarios</div>
                <div class="card-body">
                    <h5 class="card-title">150 Registrados</h5>
                    <p class="card-text">Gestiona la información de los ejidatarios y sus familias.</p>
                    <a href="#" class="btn btn-sm btn-ejidal">Administrar</a>
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

    <div class="card card-ejidal mb-4">
        <div class="card-header card-header-ejidal"><i class="fas fa-history me-2"></i> Actividad Reciente</div>
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
                    <tr><td>20/05/2025</td><td>Nueva faena registrada</td><td>Admin</td><td>Limpieza de canales</td></tr>
                    <tr><td>20/05/2025</td><td>Pago registrado</td><td>Secretaría</td><td>Pago de utilidades a Gabriel Palacios García</td></tr>
                    <tr><td>20/05/2025</td><td>Asistencia registrada</td><td>Consejo</td><td>Pase de lista faena #45</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card card-ejidal h-100">
                <div class="card-header card-header-ejidal"><i class="fas fa-calendar-alt me-2"></i> Próximos Eventos</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Reunión de ejidatarios
                            <span class="badge bg-primary rounded-pill">10/04/2025</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Faena: Limpieza de caminos
                            <span class="badge bg-primary rounded-pill">12/04/2025</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Vencimiento de pagos
                            <span class="badge bg-primary rounded-pill">15/04/2025</span>
                        </li>
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
                    <p>Ver resumen de actividades, asistencias y pagos del mes.</p>
                    <a href="#" class="btn btn-sm btn-ejidal">Ver reportes completos</a>
                </div>
            </div>
        </div>
    </div>

@endsection
