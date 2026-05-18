<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Ejido San Rafael Ixtapalucan')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/principal.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        @media (max-width: 991.98px) {
            #sidebarMenu {
                position: fixed;
                top: 65px;
                left: 0;
                width: 100%;
                height: calc(100vh - 65px);
                z-index: 1000;
                background-color: #212529 !important;
                overflow-y: auto;
                display: none;
            }
            #sidebarMenu.show {
                display: block;
            }
            main {
                margin-top: 75px !important;
            }
        }

        .navbar-toggler {
            border: 1px solid rgba(255,255,255,0.2) !important;
        }

        .submenu {
            padding-left: 1.5rem;
            background: rgba(255,255,255,0.05);
        }
    </style>
</head>
<body>

<header class="navbar navbar-expand-lg navbar-dark fixed-top bg-dark navbar-ejidal" style="border-bottom: 4px solid #198754 !important; height: 65px;">
    <div class="container-fluid d-flex align-items-center justify-content-between">

        <div class="d-flex align-items-center gap-2">
            <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <a class="navbar-brand d-flex align-items-center gap-2 m-0 p-0" href="{{ route('inicio') }}">
                <img src="/app/public/snRafael.png" alt="Logo" height="35">
                <span class="fw-bold d-none d-sm-inline" style="font-size: 1rem;">Sistema Ejidal San Rafael Ixtapalucan</span>
            </a>
        </div>

        <div class="dropdown">
            <a class="dropdown-toggle d-flex align-items-center text-decoration-none" href="#" role="button" data-bs-toggle="dropdown">
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 35px; height: 35px; overflow: hidden; border-radius: 50%; border: 2px solid #fff; flex-shrink: 0;">
                        @if(session('usuario.foto'))
                            <img src="{{ asset('storage/' . session('usuario.foto')) }}?v={{ time() }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(session('usuario.nombre_completo', 'U')) }}&background=6c757d&color=fff" style="width: 100%; height: 100%; object-fit: cover;">
                        @endif
                    </div>
                    <div class="d-none d-md-flex flex-column text-start">
                        <span class="fw-semibold text-white" style="font-size: 0.85rem;">{{ session('usuario.nombre_completo') }}</span>
                        <span class="px-2 rounded-pill fw-bold text-white" style="font-size: 0.55rem; background: linear-gradient(135deg, #0d6efd, #198754);">
                            {{ session('usuario.rol', 'Usuario') }}
                        </span>
                    </div>
                </div>
            </a>

            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" style="border-radius: 12px; min-width: 180px;">
                <li><a class="dropdown-item py-2" href="{{ route('perfil.index') }}"><i class="fas fa-user-circle me-2 text-muted"></i> Mi Perfil</a></li>
                @if(session('usuario.rol') === 'Administrador' || in_array('configuracion_ver', (array)session('usuario.permisos', [])))
                    <li><a class="dropdown-item py-2" href="{{ route('configuracion.permisos') }}"><i class="fas fa-cog me-2 text-muted"></i> Configuración</a></li>
                @endif
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="dropdown-item py-2 text-danger fw-bold">
                            <i class="fas fa-sign-out-alt me-2"></i> Cerrar sesión
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>

<div class="container-fluid">
    <div class="row">
        <!-- SIDEBAR MENU -->
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">

                    <li class="nav-item">
                        <a class="nav-link text-white" href="{{ route('inicio') }}">
                            <i class="fas fa-home me-2"></i> Inicio
                        </a>
                    </li>

                    {{-- USUARIOS --}}
                    @if(in_array('usuarios_ver', session('usuario.permisos', [])))
                        <li class="nav-item">
                            <a class="nav-link text-white" data-bs-toggle="collapse" href="#usuariosMenu">
                                <i class="fas fa-users me-2"></i> Usuarios <i class="fas fa-angle-down float-end mt-1"></i>
                            </a>
                            <div class="collapse" id="usuariosMenu">
                                <ul class="nav flex-column submenu">
                                    @if(in_array('usuarios_crear', session('usuario.permisos', [])))
                                        <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('Usuarios.create') }}"><i class="far fa-address-card me-2"></i> Nuevo</a></li>
                                    @endif
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('Usuarios.index') }}"><i class="fas fa-list me-2"></i> Listado</a></li>
                                </ul>
                            </div>
                        </li>
                    @endif

                    {{-- EJIDATARIOS --}}
                    @if(in_array('ejidatarios_ver', session('usuario.permisos', [])))
                        <li class="nav-item">
                            <a class="nav-link text-white" data-bs-toggle="collapse" href="#ejidatariosMenu">
                                <i class="fas fa-person-digging me-2"></i> Ejidatarios <i class="fas fa-angle-down float-end mt-1"></i>
                            </a>
                            <div class="collapse" id="ejidatariosMenu">
                                <ul class="nav flex-column submenu">
                                    @if(in_array('ejidatarios_crear', session('usuario.permisos', [])))
                                        <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('Ejidatarios.create') }}"><i class="far fa-address-card me-2"></i> Nuevo</a></li>
                                    @endif
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('Ejidatarios.index') }}"><i class="fas fa-list me-2"></i> Listado</a></li>
                                </ul>
                            </div>
                        </li>
                    @endif

                    {{-- ACTIVIDADES --}}
                    @if(in_array('actividades_ver', session('usuario.permisos', [])))
                        <li class="nav-item">
                            <a class="nav-link text-white" data-bs-toggle="collapse" href="#actividadesMenu">
                                <i class="fas fa-clipboard-check me-2"></i> Actividades <i class="fas fa-angle-down float-end mt-1"></i>
                            </a>
                            <div class="collapse" id="actividadesMenu">
                                <ul class="nav flex-column submenu">
                                    @if(in_array('actividades_crear', session('usuario.permisos', [])))
                                        <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('actividades.create') }}"><i class="fas fa-plus-circle me-2"></i> Nueva</a></li>
                                    @endif
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('actividades.index') }}"><i class="fas fa-calendar-alt me-2"></i> Faenas</a></li>
                                </ul>
                            </div>
                        </li>
                    @endif

                    {{-- EXPEDIENTES --}}
                    @if(in_array('expedientes_ver', session('usuario.permisos', [])) || in_array('expedientes_crear', session('usuario.permisos', [])))
                        <li class="nav-item">
                            <a class="nav-link text-white" data-bs-toggle="collapse" href="#expedientesMenu">
                                <i class="fas fa-folder-open me-2"></i> Expedientes <i class="fas fa-angle-down float-end mt-1"></i>
                            </a>
                            <div class="collapse" id="expedientesMenu">
                                <ul class="nav flex-column submenu">
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('expedientes.index') }}"><i class="fas fa-user-tag me-2"></i> Listado</a></li>
                                </ul>
                            </div>
                        </li>
                    @endif

                    {{-- PARCELAS --}}
                    @if(in_array('parcelas_ver', session('usuario.permisos', [])))
                        <li class="nav-item">
                            <a class="nav-link text-white" data-bs-toggle="collapse" href="#parcelasMenu">
                                <i class="fas fa-map-marked-alt me-2"></i> Parcelas <i class="fas fa-angle-down float-end mt-1"></i>
                            </a>
                            <div class="collapse" id="parcelasMenu">
                                <ul class="nav flex-column submenu">
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('parcelas.create') }}"><i class="fas fa-plus-circle me-2"></i> Nueva</a></li>
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('parcelas.index') }}"><i class="fas fa-list me-2"></i> Listado</a></li>
                                </ul>
                            </div>
                        </li>
                    @endif

                    {{-- FINANZAS --}}
                    @if(in_array('utilidades_ver', session('usuario.permisos', [])))
                        <li class="nav-item">
                            <a class="nav-link text-white" data-bs-toggle="collapse" href="#utilidadesMenu">
                                <i class="fas fa-hand-holding-usd me-2"></i> Finanzas <i class="fas fa-angle-down float-end mt-1"></i>
                            </a>
                            <div class="collapse" id="utilidadesMenu">
                                <ul class="nav flex-column submenu">
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('menu') }}"><i class="fas fa-cash-register me-2"></i> Registro Repartos</a></li>
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('reparto.primer') }}"><i class="fas fa-coins me-2"></i> Primer Reparto</a></li>
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('reparto.segundo') }}"><i class="fas fa-coins me-2"></i> Segundo Reparto</a></li>
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('descuentos.asambleas') }}"><i class="fas fa-gavel me-2"></i> Descuentos Asambleas</a></li>
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('descuentos.faenas') }}"><i class="fas fa-tools me-2"></i> Descuentos Faenas</a></li>
                                </ul>
                            </div>
                        </li>
                    @endif

                    {{-- GASTOS --}}
                    @if(in_array('gastos_ver', session('usuario.permisos', [])))
                        <li class="nav-item">
                            <a class="nav-link text-white" data-bs-toggle="collapse" href="#gastosMenu">
                                <i class="fas fa-wallet me-2"></i> Gastos <i class="fas fa-angle-down float-end mt-1"></i>
                            </a>
                            <div class="collapse" id="gastosMenu">
                                <ul class="nav flex-column submenu">
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('gastos.create') }}"><i class="fas fa-plus-circle me-2"></i> Nuevo Gasto</a></li>
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('gastos.index') }}"><i class="fas fa-eye me-2"></i> Ver Gastos</a></li>
                                </ul>
                            </div>
                        </li>
                    @endif

                    {{-- INVENTARIO --}}
                    @if(in_array('inventario_ver', session('usuario.permisos', [])))
                        <li class="nav-item">
                            <a class="nav-link text-white" data-bs-toggle="collapse" href="#inventarioMenu">
                                <i class="fas fa-warehouse me-2"></i> Inventario <i class="fas fa-angle-down float-end mt-1"></i>
                            </a>
                            <div class="collapse" id="inventarioMenu">
                                <ul class="nav flex-column submenu">
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('articulos.index') }}"><i class="fas fa-boxes me-2"></i> Artículos</a></li>
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('entradas.create') }}"><i class="fas fa-sign-in-alt me-2"></i> Entradas</a></li>
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('salidas.create') }}"><i class="fas fa-sign-out-alt me-2"></i> Salidas</a></li>
                                </ul>
                            </div>
                        </li>
                    @endif

                    {{-- PASE DE LISTA --}}
                    <li class="nav-item">
                        <a class="nav-link text-white" data-bs-toggle="collapse" href="#paseListaMenu">
                            <i class="fas fa-list-check me-2"></i> Pase de lista <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>
                        <div class="collapse" id="paseListaMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('asistencia.index') }}"><i class="fas fa-check-double me-2"></i> Registrar Asistencia</a></li>
                            </ul>
                        </div>
                    </li>

                    {{-- EVENTOS --}}
                    <li class="nav-item">
                        <a class="nav-link text-white" data-bs-toggle="collapse" href="#eventosMenu">
                            <i class="fas fa-calendar-day me-2"></i> Eventos <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>
                        <div class="collapse" id="eventosMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('eventos.create') }}"><i class="fas fa-calendar-plus me-2"></i> Nuevo Evento</a></li>
                                <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('eventos.index') }}"><i class="fas fa-eye me-2"></i> Ver todos</a></li>
                            </ul>
                        </div>
                    </li>

                    {{-- Datos historicos --}}
                    <li class="nav-item">
                        <a class="nav-link text-white" data-bs-toggle="collapse" href="#historicosMenu">
                            <i class="fas fa-scroll"></i> Datos Históricos <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>
                        <div class="collapse" id="historicosMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('datos_historicos.create') }}"><i class="fas fa-plus-circle"></i> Nuevo registro</a></li>
                                <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('datos_historicos.index') }}"><i class="fas fa-list"></i> Listado</a></li>
                            </ul>
                        </div>
                    </li>

                    {{-- MULTAS --}}
                    <li class="nav-item">
                        <a class="nav-link text-white" data-bs-toggle="collapse" href="#multasMenu">
                            <i class="fas fa-file-invoice-dollar me-2"></i> Multas <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>
                        <div class="collapse" id="multasMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('multas.create') }}"><i class="fas fa-file-signature me-2"></i> Nueva Multa</a></li>
                                <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('multas.index') }}"><i class="fas fa-table me-2"></i> Listado</a></li>
                            </ul>
                        </div>
                    </li>

                    {{-- RESPALDO --}}
                    @if(in_array('respaldo_ver', session('usuario.permisos', [])))
                        <li class="nav-item mt-3 border-top border-secondary pt-2">
                            <a class="nav-link text-white" href="{{ route('Respaldos.index') }}">
                                <i class="fas fa-database me-2"></i> Respaldo
                            </a>
                        </li>
                    @endif

                    {{-- CONFIGURACIÓN --}}
                    @if(in_array('configuracion_ver', session('usuario.permisos', [])))
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ route('configuracion.permisos') }}">
                                <i class="fas fa-cogs me-2"></i> Configuración
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pb-5">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">@yield('title', 'Inicio del Sistema')</h1>
            </div>

            @yield('content')
        </main>
    </div>
</div>

<footer class="footer bg-dark text-light py-4 border-top border-primary">
    <div class="container">
        <div class="row align-items-center">

            <div class="col-md-4 text-center text-md-start">
                <img src="/snRafael.png" alt="Logo" height="50" class="mb-2"> <h6 class="text-uppercase fw-bold mb-0">Sistema de Gestión Ejidal</h6>
                <small class="text-secondary">v1.4.1</small>
            </div>

            <div class="col-md-4 text-center my-3 my-md-0">
                <p class="mb-2 small text-secondary">Síguenos en:</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="https://www.facebook.com/vallede.luciernagas/" target="_blank"
                       class="btn btn-outline-light rounded-circle d-flex align-items-center justify-content-center"
                       style="width: 45px; height: 45px; font-size: 1.2rem;" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://www.instagram.com/valle_de_luciernagas_esri/" target="_blank"
                       class="btn btn-outline-light rounded-circle d-flex align-items-center justify-content-center"
                       style="width: 45px; height: 45px; font-size: 1.2rem;" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
            </div>

            <div class="col-md-4 text-center text-md-end">
                <div style="font-size: 0.7rem;" class="text-secondary">
                    <p class="mb-1">&copy; 2026 Todos los Derechos Reservados D.R.A.</p>
                    <p class="mb-0">Prohibida su reproducción total o parcial sin autorización escrita.</p>
                    <p class="mb-0 font-italic text-lowercase">All rights reserved 2026.</p>
                </div>
            </div>

        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>