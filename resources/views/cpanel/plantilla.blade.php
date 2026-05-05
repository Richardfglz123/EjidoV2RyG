<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Ejido San Rafael Ixtapalucan')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/principal.css') }}">
</head>
<body>

<header class="navbar navbar-expand-lg navbar-dark fixed-top bg-dark navbar-ejidal" style="border-bottom: 4px solid #198754 !important; height: 65px;">
    <div class="container-fluid d-flex align-items-center justify-content-between">

        <div class="d-flex align-items-center gap-2">
            <button class="navbar-toggler d-md-none border-0 p-0" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false">
                <span class="navbar-toggler-icon" style="width: 24px; height: 24px;"></span>
            </button>

            <a class="navbar-brand d-flex align-items-center gap-2 m-0 p-0" href="{{ route('inicio') }}">
                <img src="/snRafael.png" alt="Logo" height="35" style="object-fit: contain;">
                <span class="fw-bold d-none d-lg-inline" style="font-size: 1rem;">Sistema Ejidal San Rafael Ixtapalucan</span>
                <span class="fw-bold d-inline d-lg-none" style="font-size: 0.9rem;">S.E. San Rafael</span>
            </a>
        </div>

        <div class="dropdown">
            <a class="dropdown-toggle d-flex align-items-center text-decoration-none"
               href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"
               style="width: auto; height: 45px; padding: 0;">

                <div class="d-flex align-items-center gap-2">
                    <div style="width: 35px; height: 35px; min-width: 35px; max-width: 35px; min-height: 35px; max-height: 35px; overflow: hidden; border-radius: 50%; border: 2px solid #fff; flex-shrink: 0; position: relative;">
                        @if(session('usuario.foto'))
                            <img src="{{ asset('storage/' . session('usuario.foto')) }}" alt="Foto" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(session('usuario.nombre_completo')) }}&background=6c757d&color=fff&size=35" alt="Avatar">
                        @endif
                    </div>

                    <div class="d-none d-md-flex flex-column text-start" style="line-height: 1;">
                        <span class="fw-semibold text-white" style="font-size: 0.85rem;">{{ session('usuario.nombre_completo') }}</span>
                        <span class="px-2 rounded-pill fw-bold text-white text-center" style="font-size: 0.55rem; background: linear-gradient(135deg, #0d6efd, #198754); width: fit-content; margin-top: 2px;">
                            {{ session('usuario.rol', 'SIN ROL') }}
                        </span>
                    </div>
                </div>
            </a>

            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" style="border-radius: 12px; width: 220px; overflow: hidden;">
                <li class="px-3 py-3 border-bottom bg-light">
                    <small class="text-muted d-block" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Usuario</small>
                    <strong class="text-dark d-block" style="font-size: 0.95rem;">{{ session('usuario.nombre_completo') }}</strong>
                    <span class="badge bg-success mt-1" style="font-size: 0.65rem;">{{ session('usuario.rol') }}</span>
                </li>
                <li><a class="dropdown-item py-2" href="{{ route('perfil.index') }}"><i class="fas fa-user-circle me-2 text-muted"></i> Mi Perfil</a></li>
                @if(in_array('configuracion_ver', session('usuario.permisos', [])))
                    <li><a class="dropdown-item py-2" href="{{ route('configuracion.permisos') }}"><i class="fas fa-cog me-2 text-muted"></i> Configuración</a></li>
                @endif
                <li><hr class="dropdown-divider m-0"></li>
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
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">

                    <li class="nav-item">
                        <a class="nav-link text-white" href="{{ route('inicio') }}">
                            <i class="fas fa-home"></i>
                        </a>
                    </li>

                    {{-- USUARIOS --}}
                    @if(in_array('usuarios_ver', session('usuario.permisos', [])))
                        <li class="nav-item">
                            <a class="nav-link text-white" data-bs-toggle="collapse" href="#usuariosMenu">
                                <i class="fas fa-users"></i> Usuarios <i class="fas fa-angle-down float-end mt-1"></i>
                            </a>
                            <div class="collapse" id="usuariosMenu">
                                <ul class="nav flex-column submenu">
                                    @if(in_array('usuarios_crear', session('usuario.permisos', [])))
                                        <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('Usuarios.create') }}"><i class="far fa-address-card"></i> Nuevo Usuario</a></li>
                                    @endif
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('Usuarios.index') }}"><i class="fas fa-list"></i> Listado</a></li>
                                </ul>
                            </div>
                        </li>
                    @endif

                    {{-- EJIDATARIOS --}}
                    @if(in_array('ejidatarios_ver', session('usuario.permisos', [])))
                        <li class="nav-item">
                            <a class="nav-link text-white" data-bs-toggle="collapse" href="#ejidatariosMenu">
                                <i class="fas fa-person-digging"></i> Ejidatarios <i class="fas fa-angle-down float-end mt-1"></i>
                            </a>
                            <div class="collapse" id="ejidatariosMenu">
                                <ul class="nav flex-column submenu">
                                    @if(in_array('ejidatarios_crear', session('usuario.permisos', [])))
                                        <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('Ejidatarios.create') }}"><i class="far fa-address-card"></i> Nuevo Ejidatario</a></li>
                                    @endif
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('Ejidatarios.index') }}"><i class="fas fa-list"></i> Listado Completo</a></li>
                                </ul>
                            </div>
                        </li>
                    @endif

                    {{-- ACTIVIDADES --}}
                    @if(in_array('actividades_ver', session('usuario.permisos', [])))
                        <li class="nav-item">
                            <a class="nav-link text-white" data-bs-toggle="collapse" href="#actividadesMenu">
                                <i class="fas fa-clipboard-check"></i> Actividades <i class="fas fa-angle-down float-end mt-1"></i>
                            </a>
                            <div class="collapse" id="actividadesMenu">
                                <ul class="nav flex-column submenu">
                                    @if(in_array('actividades_crear', session('usuario.permisos', [])))
                                        <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('actividades.create') }}"><i class="fas fa-plus-circle"></i> Nueva Actividad</a></li>
                                    @endif
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('actividades.index') }}"><i class="fas fa-calendar-alt"></i> Consulta de Faenas</a></li>
                                </ul>
                            </div>
                        </li>
                    @endif


                    {{-- ASAMBLEAS
                    @if(in_array('asambleas_ver', session('usuario.permisos', [])))
                        <li class="nav-item">
                            <a class="nav-link text-white" data-bs-toggle="collapse" href="#asambleasMenu">
                                <i class="fas fa-gavel"></i> Asambleas <i class="fas fa-angle-down float-end mt-1"></i>
                            </a>
                            <div class="collapse" id="asambleasMenu">
                                <ul class="nav flex-column submenu">
                                    <li class="nav-item"><a class="nav-link text-white-50" href="#"><i class="fas fa-plus-circle"></i> Nueva Asamblea</a></li>
                                    <li class="nav-item"><a class="nav-link text-white-50" href="#"><i class="fas fa-file-alt"></i> Acta de Asambleas</a></li>
                                </ul>
                            </div>
                        </li>
                    @endif
                    --}}

                    {{-- EXPEDIENTES --}}
                    @if(
                        in_array('expedientes_ver', session('usuario.permisos', [])) ||
                        in_array('expedientes_crear', session('usuario.permisos', []))
                    )
                        <li class="nav-item">
                            <a class="nav-link text-white" data-bs-toggle="collapse" href="#expedientesMenu">
                                <i class="fas fa-folder-open"></i> Expedientes
                                <i class="fas fa-angle-down float-end mt-1"></i>
                            </a>

                            <div class="collapse" id="expedientesMenu">
                                <ul class="nav flex-column submenu">
                                    <li class="nav-item">
                                        <a class="nav-link text-white-50" href="{{ route('expedientes.index') }}">
                                            <i class="fas fa-user-tag"></i> Listado de Expedientes
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    @endif

                    {{-- PARCELAS --}}
                    @if(in_array('parcelas_ver', session('usuario.permisos', [])))
                        <li class="nav-item">
                            <a class="nav-link text-white" data-bs-toggle="collapse" href="#parcelasMenu">
                                <i class="fas fa-map-marked-alt"></i> Parcelas <i class="fas fa-angle-down float-end mt-1"></i>
                            </a>
                            <div class="collapse" id="parcelasMenu">
                                <ul class="nav flex-column submenu">
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('parcelas.create') }}"><i class="fas fa-plus-circle"></i> Nueva Parcela</a></li>
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('parcelas.index') }}"><i class="fas fa-list"></i> Listado</a></li>
                                </ul>
                            </div>
                        </li>
                    @endif

                    {{-- UTILIDADES / FINANZAS --}}
                    @if(in_array('utilidades_ver', session('usuario.permisos', [])))
                        <li class="nav-item">
                            <a class="nav-link text-white d-flex align-items-center" data-bs-toggle="collapse" href="#utilidadesMenu">
                                <i class="fas fa-hand-holding-usd me-2"></i>
                                <span class="flex-grow-1" style="font-size: 0.85rem;">Finanzas</span>
                                <i class="fas fa-angle-down ms-1"></i>
                            </a>
                            <div class="collapse" id="utilidadesMenu">
                                <ul class="nav flex-column submenu">
                                    <li class="nav-item">
                                        <a class="nav-link text-white-50" href="{{ route('menu') }}">
                                            <i class="fas fa-file-invoice-dollar"></i> Registro Repartos
                                        </a>
                                    </li>
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('reparto.primer') }}"><i class="fas fa-coins"></i> Primer Reparto</a></li>
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('reparto.segundo') }}"><i class="fas fa-coins"></i> Segundo Reparto</a></li>
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('descuentos.asambleas') }}"><i class="fas fa-user-minus"></i> Descuentos Asambleas</a></li>
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('descuentos.faenas') }}"><i class="fas fa-tools"></i> Descuentos Faenas</a></li>
                                </ul>
                            </div>
                        </li>
                    @endif

                    {{-- GASTOS --}}
                    @if(in_array('gastos_ver', session('usuario.permisos', [])))
                        <li class="nav-item">
                            <a class="nav-link text-white" data-bs-toggle="collapse" href="#gastosMenu">
                                <i class="fas fa-wallet"></i> Gastos <i class="fas fa-angle-down float-end mt-1"></i>
                            </a>
                            <div class="collapse" id="gastosMenu">
                                <ul class="nav flex-column submenu">
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('gastos.create') }}"><i class="fas fa-plus-circle"></i> Nuevo Gasto</a></li>
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('gastos.index') }}"><i class="fas fa-list"></i> Ver Gastos</a></li>
                                </ul>
                            </div>
                        </li>
                    @endif

                    {{-- INVENTARIO --}}
                    @if(in_array('inventario_ver', session('usuario.permisos', [])))
                        <li class="nav-item">
                            <a class="nav-link text-white" data-bs-toggle="collapse" href="#inventarioMenu">
                                <i class="fas fa-warehouse"></i> Inventario <i class="fas fa-angle-down float-end mt-1"></i>
                            </a>
                            <div class="collapse" id="inventarioMenu">
                                <ul class="nav flex-column submenu">
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('articulos.index') }}"><i class="fas fa-list"></i> Artículos</a></li>
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('entradas.create') }}"><i class="fas fa-arrow-right"></i> Entradas</a></li>
                                    <li class="nav-item"><a class="nav-link text-white-50" href="{{ route('salidas.create') }}"><i class="fas fa-arrow-left"></i> Salidas</a></li>
                                </ul>
                            </div>
                        </li>
                    @endif


                    {{-- HISTÓRICOS --}}
                    @if(in_array('historicos_ver', session('usuario.permisos', [])))
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
                    @endif

                    <!-- Módulo de Pase de Lista -->
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admon/asistencia*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#paseListaMenu">
                            <i class="fas fa-list-check"></i> Pase de lista
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>
                        <div class="collapse {{ request()->is('admon/asistencia*') ? 'show' : '' }}" id="paseListaMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item">
                                    <a class="nav-link text-white-50" href="{{ route('asistencia.index') }}">
                                        <i class="fas fa-plus-circle"></i> Registrar Asistencia
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- Módulo de Eventos -->
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admon/eventos*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#eventosMenu">
                            <i class="fas fa-calendar-day"></i> Eventos
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>
                        <div class="collapse {{ request()->is('admon/eventos*') ? 'show' : '' }}" id="eventosMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item">
                                    <a class="nav-link text-white-50" href="{{ route('eventos.create') }}">
                                        <i class="fas fa-plus-circle"></i> Nuevo Evento
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-white-50" href="{{ route('eventos.index') }}">
                                        <i class="fas fa-list"></i> Ver todos los Eventos
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- Módulo de Multa -->
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admon/multas*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#multasMenu">
                            <i class="fas fa-file-invoice-dollar"></i> Multas
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>
                        <div class="collapse {{ request()->is('admon/multas*') ? 'show' : '' }}" id="multasMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item">
                                    <a class="nav-link text-white-50" href="{{ route('multas.create') }}">
                                        <i class="fas fa-plus-circle"></i> Nueva Multa
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-white-50" href="{{ route('multas.index') }}">
                                        <i class="fas fa-list"></i> Listado de Multas
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    {{-- RESPALDO --}}
                    @if(in_array('respaldo_ver', session('usuario.permisos', [])))
                        <li class="nav-item mt-3 border-top border-secondary pt-2">
                            <a class="nav-link text-white {{ request()->routeIs('Respaldos.index') ? 'active bg-secondary' : '' }}" href="{{ route('Respaldos.index') }}">
                                <i class="fas fa-database me-2"></i> Respaldo
                            </a>
                        </li>
                    @endif

                    {{-- CONFIGURACIÓN --}}
                    @if(in_array('configuracion_ver', session('usuario.permisos', [])))
                        <li class="nav-item">
                            <a class="nav-link text-white {{ request()->routeIs('configuracion.permisos') ? 'active bg-secondary' : '' }}" href="{{ route('configuracion.permisos') }}">
                                <i class="fas fa-cogs"></i> Configuración
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