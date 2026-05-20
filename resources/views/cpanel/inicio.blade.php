@extends('cpanel/plantilla')
@section('title', 'Panel de Control')
@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 15px; background-color: #dcfce7; color: #15803d;">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-3 fs-4"></i>
                <div>
                    <strong class="d-block">¡Listo!</strong>
                    <span class="small">{{ session('success') }}</span>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 15px; background-color: #fee2e2; color: #991b1b;">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle me-3 fs-4"></i>
                <div>
                    <strong class="d-block">Hubo un errror</strong>
                    <span class="small">{{ session('error') }}</span>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <style>
        .card-stats {
            background: #ffffff !important;
            border: 1px solid rgba(0,0,0,0.03) !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.07), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
        }

        .card-stats:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
        }

        .hero-section {
            background: linear-gradient(135deg, #1a1d20 0%, #198754 100%);
            color: white;
            padding: 3.5rem 2.5rem;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(25, 135, 84, 0.15);
            margin-bottom: 2.5rem;
            position: relative;
            overflow: hidden;
        }

        .hero-section::after {
            content: '\f1bb';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: -30px;
            bottom: -40px;
            font-size: 14rem;
            opacity: 0.08;
            transform: rotate(-15deg);
        }

        .icon-shape {
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .table thead th {
            background-color: #f8fafc;
            text-transform: uppercase;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 1px;
            color: #64748b;
            padding: 1rem;
            border: none;
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #f1f5f9;
        }

        .event-date-box {
            text-align: center;
            min-width: 50px;
            padding: 8px;
            border-radius: 12px;
            background: rgba(25, 135, 84, 0.1);
            color: #198754;
        }

        .fw-extrabold { font-weight: 800; }
    </style>

    <div class="hero-section">
        <div class="position-relative" style="z-index: 2;">
            <h1 class="display-5 fw-extrabold mb-2">¡Bienvenide, {{ explode(' ', session('usuario.nombre_completo'))[0] }}!</h1>
            <p class="lead opacity-75 mb-0">Gestión para la comunidad de San Rafael Ixtapalucan.</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4 mb-4">
            <div class="card card-stats h-100 shadow-sm" style="background: linear-gradient(to right, #ffffff, #f0fdf4);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="icon-shape shadow-sm" style="background: #198754; color: white; width: 55px; height: 55px; border-radius: 15px;">
                            <i class="fas fa-users fs-4"></i>
                        </div>
                        <div class="text-end">
                            <span class="badge rounded-pill fw-bold" style="background: #dcfce7; color: #15803d; font-size: 0.7rem;">
                                <i class="fas fa-check-circle me-1"></i>ACTIVOS
                            </span>
                        </div>
                    </div>
                    <h6 class="text-muted mb-1 text-uppercase small fw-extrabold" style="letter-spacing: 1px;">Ejidatarios Registrados</h6>
                    <h2 class="display-6 fw-extrabold mb-0" style="color: #1e293b;">{{ $totalEjidatarios ?? 0 }}</h2>
                    <div class="mt-3 pt-3 border-top border-light">
                        <a href="{{ url('/admon/Ejidatarios') }}" class="text-success fw-bold text-decoration-none small d-flex align-items-center">
                            Administrar <i class="fas fa-chevron-right ms-2" style="font-size: 0.7rem;"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card card-stats h-100 shadow-sm" style="background: linear-gradient(to right, #ffffff, #eff6ff);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="icon-shape shadow-sm" style="background: #3b82f6; color: white; width: 55px; height: 55px; border-radius: 15px;">
                            <i class="fas fa-tasks fs-4"></i>
                        </div>
                        <div class="text-end">
                            <span class="badge rounded-pill fw-bold" style="background: #dbeafe; color: #1d4ed8; font-size: 0.7rem;">
                                <i class="fas fa-clock me-1"></i>PENDIENTES
                            </span>
                        </div>
                    </div>
                    <h6 class="text-muted mb-1 text-uppercase small fw-extrabold" style="letter-spacing: 1px;">Faenas del Mes</h6>
                    <h2 class="display-6 fw-extrabold mb-0" style="color: #1e293b;">{{ $faenasDelMes ?? 0 }}</h2>
                    <div class="mt-3 pt-3 border-top border-light">
                        <a href="{{ route('eventos.index') }}" class="text-primary fw-bold text-decoration-none small d-flex align-items-center">
                            Ver Módulo Eventos <i class="fas fa-chevron-right ms-2" style="font-size: 0.7rem;"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card card-stats h-100 shadow-sm" style="background: linear-gradient(to right, #ffffff, #fffbeb);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="icon-shape shadow-sm" style="background: #f59e0b; color: white; width: 55px; height: 55px; border-radius: 15px;">
                            <i class="fas fa-map-marked-alt fs-4"></i>
                        </div>
                        <div class="text-end">
                            <span class="badge rounded-pill fw-bold" style="background: #fef3c7; color: #b45309; font-size: 0.7rem;">
                                <i class="fas fa-mountain me-1"></i>Registros
                            </span>
                        </div>
                    </div>
                    <h6 class="text-muted mb-1 text-uppercase small fw-extrabold" style="letter-spacing: 1px;">Parcelas Totales</h6>
                    <h2 class="display-6 fw-extrabold mb-0" style="color: #1e293b;">{{ $totalParcelas ?? 0 }}</h2>
                    <div class="mt-3 pt-3 border-top border-light">
                        <a href="{{ route('parcelas.index') }}" class="text-warning fw-bold text-decoration-none small d-flex align-items-center">
                            Ver parcelas <i class="fas fa-chevron-right ms-2" style="font-size: 0.7rem;"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden;">
                <div class="card-header bg-white py-4 border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-extrabold mb-0"><i class="fas fa-history me-2 text-success"></i> Actividad Reciente</h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Movimiento</th>
                                <th>Realizado por</th>
                                <th>Detalles</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($actividadReciente ?? [] as $a)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark" style="font-size: 0.85rem;">{{ \Carbon\Carbon::parse($a->Fecha_Creo)->format('d/m/Y') }}</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">{{ \Carbon\Carbon::parse($a->Fecha_Creo)->format('H:i') }} hrs</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-success border border-success-subtle px-2 py-1 fw-bold">
                                            {{ $a->Tipo ?? 'Modificación' }}
                                        </span>
                                    </td>
                                    <td class="small fw-semibold">{{ $a->NombreUsuario ?? 'Sistema' }}</td>
                                    <td class="text-muted small">{{ Str::limit($a->Descripcion ?? 'Sin descripción', 50) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <i class="fas fa-folder-open fa-3x text-light mb-3 d-block"></i>
                                        <span class="text-muted">No se han registrado acciones recientes</span>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
                <div class="card-header bg-white py-4 border-0">
                    <h5 class="fw-extrabold mb-0 text-primary"><i class="fas fa-calendar-check me-2"></i> Próximos Eventos</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($proximosEventos ?? [] as $e)
                            <div class="list-group-item px-4 py-3 border-0">
                                <div class="d-flex align-items-center">
                                    <div class="event-date-box me-3">
                                        <div class="fw-extrabold" style="font-size: 1.1rem; line-height: 1;">{{ \Carbon\Carbon::parse($e->Fecha_Creo)->format('d') }}</div>
                                        <div class="small fw-bold text-uppercase" style="font-size: 0.6rem;">{{ \Carbon\Carbon::parse($e->Fecha_Creo)->translatedFormat('M') }}</div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 fw-bold small">{{ $e->Nombre_Evento }}</h6>
                                        <p class="mb-0 text-muted" style="font-size: 0.75rem;">Categoría: {{ $e->Nombre_Categoria }}</p>
                                    </div>
                                    <a href="{{ route('eventos.index') }}" class="text-decoration-none">
                                        <i class="fas fa-chevron-right text-muted"></i>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted small">Sin eventos próximos</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 20px; background: #1a1d20;">
                <div class="card-body p-4 text-center">
                    <div class="mb-3">
                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold" style="font-size: 0.65rem;">
                            <i class="fas fa-chart-line me-1"></i>REPORTES {{ $anioActual }}
                        </span>
                    </div>
                    <h5 class="text-white fw-extrabold mb-3">Estadísticas de Gestión</h5>
                    <p class="text-secondary small mb-4">Visualiza el avance de los repartos, descuentos y faenas del periodo.</p>

                    <a href="{{ route('concentrado.excel') }}" class="btn btn-success w-100 py-2 fw-bold" style="border-radius: 12px; box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);">
                        <i class="fas fa-file-excel me-2"></i>Generar Reporte Completo
                    </a>
                </div>
            </div>
        </div>
    </div>

@endsection