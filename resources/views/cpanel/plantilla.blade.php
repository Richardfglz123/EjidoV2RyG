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

<header class="navbar navbar-expand-lg navbar-dark fixed-top bg-dark navbar-ejidal">
    <div class="container-fluid">
        <button class="navbar-toggler d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="{{ route('inicio') }}">
            <i class="fas fa-tractor me-2"></i> Sistema Ejidal San Rafael Ixtapalucan
        </a>

        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-circle me-1"></i>
                    {{ session('nombre_completo') ?? session('username') }}
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('perfil.index') }}">
                            <i class="fas fa-user me-2"></i>Perfil
                        </a>
                    </li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Configuración</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" id="logout-form">
                            @csrf
                            <button type="submit" class="dropdown-item border-0 bg-transparent w-100 text-start">
                                <i class="fas fa-sign-out-alt me-2"></i>Cerrar sesión
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</header>

<div class="container-fluid">
    <div class="row">
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">

                    <li class="nav-item">
                        <a class="nav-link text-white" href="{{ route('inicio') }}">
                            <i class="fas fa-home"></i>Inicio
                        </a>

                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-white" data-bs-toggle="collapse" href="#usuariosMenu" role="button" aria-expanded="false" aria-controls="usuariosMenu">
                            <i class="fas fa-users"></i> Usuarios
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>
                        <div class="collapse" id="usuariosMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item"><a class="nav-link text-white-50" href="{{ url('admon/Usuarios/create') }}"><i class="far fa-address-card"></i> Nuevo Usuario</a></li>
                                <li class="nav-item"><a class="nav-link text-white-50" href="#"><i class="fas fa-search"></i> Buscar Usuario</a></li>
                                <li class="nav-item"><a class="nav-link text-white-50" href="{{ url('/admon/Usuarios') }}"><i class="fas fa-list"></i> Listado Completo</a></li>
                                <li class="nav-item"><a class="nav-link text-white-50" href="#"><i class="fas fa-file-export"></i> Reportes</a></li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-white" data-bs-toggle="collapse" href="#ejidatariosMenu" role="button" aria-expanded="false" aria-controls="ejidatariosMenu">
                            <i class="fas fa-person-digging"></i> Ejidatarios
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>
                        <div class="collapse" id="ejidatariosMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item"><a class="nav-link text-white-50" href="{{ url('admon/Ejidatarios/create') }}"><i class="far fa-address-card"></i> Nuevo Ejidatario</a></li>
                                <li class="nav-item"><a class="nav-link text-white-50" href="#"><i class="fas fa-search"></i> Buscar Ejidatario</a></li>
                                <li class="nav-item"><a class="nav-link text-white-50" href="{{ url('/admon/Ejidatarios') }}"><i class="fas fa-list"></i> Listado Completo</a></li>
                                <li class="nav-item"><a class="nav-link text-white-50" href="#"><i class="fas fa-file-export"></i> Reportes</a></li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-white" data-bs-toggle="collapse" href="#actividadesMenu" role="button" aria-expanded="false" aria-controls="actividadesMenu">
                            <i class="fas fa-clipboard-check"></i> Actividades
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>
                        <div class="collapse" id="actividadesMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item"><a class="nav-link text-white-50" href="#"><i class="fas fa-plus-circle"></i> Nueva Actividad</a></li>
                                <li class="nav-item"><a class="nav-link text-white-50" href="#"><i class="fas fa-calendar-alt"></i> Consulta de actividades</a></li>
                                <li class="nav-item"><a class="nav-link text-white-50" href="#"><i class="fas fa-list"></i> Listado de actividades</a></li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-white" data-bs-toggle="collapse" href="#paseListaMenu" role="button" aria-expanded="false" aria-controls="paseListaMenu">
                            <i class="fas fa-list-check"></i> Pase de lista
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>
                        <div class="collapse" id="paseListaMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item"><a class="nav-link text-white-50" href="#"><i class="fas fa-plus-circle"></i> Nueva Asamblea</a></li>
                                <li class="nav-item"><a class="nav-link text-white-50" href="#"><i class="fas fa-calendar-alt"></i> Calendario</a></li>
                                <li class="nav-item"><a class="nav-link text-white-50" href="#"><i class="fas fa-calendar-alt"></i> Acta de Asambleas</a></li>
                                <li class="nav-item"><a class="nav-link text-white-50" href="#"><i class="fas fa-list"></i> Listado</a></li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-white" data-bs-toggle="collapse" href="#multasMenu" role="button" aria-expanded="false" aria-controls="multasMenu">
                            <i class="fas fa-dollar-sign"></i> Multas
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>
                        <div class="collapse" id="multasMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item"><a class="nav-link text-white-50" href="#"><i class="fas fa-plus-circle"></i> Nuevo Pase</a></li>
                                <li class="nav-item"><a class="nav-link text-white-50" href="#"><i class="fas fa-history"></i> Historial</a></li>
                                <li class="nav-item"><a class="nav-link text-white-50" href="#"><i class="fas fa-chart-bar"></i> Estadísticas</a></li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-white" data-bs-toggle="collapse" href="#historicosMenu" role="button" aria-expanded="false" aria-controls="historicosMenu">
                            <i class="fas fa-scroll"></i> Datos históricos
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>
                        <div class="collapse" id="historicosMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item"><a class="nav-link text-white-50" href="#"><i class="fas fa-plus-circle"></i> Nuevo Descuento</a></li>
                                <li class="nav-item"><a class="nav-link text-white-50" href="#"><i class="fas fa-list"></i> Registros</a></li>
                                <li class="nav-item"><a class="nav-link text-white-50" href="#"><i class="fas fa-file-invoice-dollar"></i> Reportes</a></li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-white" href="#">
                            <i class="fas fa-cogs"></i> Configuración
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#">
                            <i class="fas fa-database"></i> Respaldo
                        </a>
                    </li>

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

<footer class="footer py-3 bg-light">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 text-start">
                <span class="text-muted">Sistema de Gestión Ejidal &copy; 2026</span>
            </div>
            <div class="col-md-6 text-end">
                <span class="text-muted">Versión 1.1.1</span>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
