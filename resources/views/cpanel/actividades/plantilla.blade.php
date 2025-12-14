<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')Registro de actividades - Ejido San Rafael Ixtapalucan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/multas.css') }}">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-ejidal">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="#">
            Sistema Ejidal San Rafael Ixtapalucan
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                       data-bs-toggle="dropdown">
                        <img src="https://cdn-icons-png.flaticon.com/512/64/64572.png" alt="User Avatar"
                             style="width: 30px; height: 30px;">
                        Nombre Usuario
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Perfil</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Configuración</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-sign-out-alt me-2"></i>Cerrar sesión</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 d-md-block sidebar bg-dark sidebar collapse" id="sidebarMenu">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">
                            <i class="fas fa-home"></i> Inicio
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="collapse" href="#actividadesMenu">
                            <i class="fas fa-tasks"></i> Registro de Actividades
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>
                        <div class="collapse show" id="actividadesMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#">
                                        <i class="fas fa-plus-circle"></i> Nueva Actividad
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="fas fa-list-ul"></i> Listado Histórico
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="fas fa-file-export"></i> Reportes
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#usuariosMenu">
                            <i class="fas fa-users"></i> Usuarios
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>
                        <div class="collapse" id="usuariosMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="far fa-address-card"></i> Nuevo Usuario
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="fas fa-search"></i> Buscar Usuario
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="encabezado.html">
                                        <i class="fas fa-list"></i> Listado Completo
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="fas fa-file-export"></i> Reportes
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#ejidatariosMenu" role="button"
                           aria-expanded="false" aria-controls="ejidatariosMenu">
                            <i class="fas fa-users"></i> Ejidatarios
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>
                        <div class="collapse" id="ejidatariosMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="far fa-address-card"></i> Nuevo Ejidatario
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="fas fa-search"></i> Buscar Ejidatario
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="fas fa-list"></i> Listado Completo
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="fas fa-file-export"></i> Reportes
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#faenasMenu">
                            <i class="fas fa-clipboard-check"></i> Faenas
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>
                        <div class="collapse" id="faenasMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item">
                                    <a class="nav-link" href="#"><i class="fas fa-plus-circle"></i> Nueva Faena</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#"><i class="fas fa-calendar-alt"></i> Calendario</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#"><i class="fas fa-list"></i> Listado</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#asambleasMenu">
                            <i class="fas fa-clipboard-check"></i> Asambleas
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>
                        <div class="collapse" id="asambleasMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item">
                                    <a class="nav-link" href="#"><i class="fas fa-plus-circle"></i> Nueva Asamblea</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#"><i class="fas fa-calendar-alt"></i> Calendario</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#"><i class="fas fa-calendar-alt"></i> Acta de Asambleas</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#"><i class="fas fa-list"></i> Listado</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#asistenciaMenu">
                            <i class="fas fa-list-check"></i> Pase de Lista
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>
                        <div class="collapse" id="asistenciaMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item">
                                    <a class="nav-link" href="#"><i class="fas fa-plus-circle"></i> Nuevo Pase</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#"><i class="fas fa-history"></i> Historial</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#"><i class="fas fa-chart-bar"></i> Estadísticas</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#descuentosMenu">
                            <i class="fas fa-money-bill-wave"></i> Descuentos
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>
                        <div class="collapse" id="descuentosMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item">
                                    <a class="nav-link" href="#"><i class="fas fa-plus-circle"></i> Nuevo Descuento</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#"><i class="fas fa-list"></i> Registros</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#"><i class="fas fa-file-invoice-dollar"></i> Reportes</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#utilidadesMenu">
                            <i class="fas fa-hand-holding-usd"></i> Reparto Utilidades
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>
                        <div class="collapse" id="utilidadesMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-calculator"></i> Calcular</a></li>
                                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-money-bill-trend-up"></i> Distribuir</a></li>
                                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-history"></i> Historial</a></li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#prestamosMenu">
                            <i class="fas fa-hand-holding-heart"></i> Préstamos
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>
                        <div class="collapse" id="prestamosMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-plus-circle"></i> Nuevo Préstamo</a></li>
                                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-list"></i> Activos</a></li>
                                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-history"></i> Historial</a></li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#parcelasMenu">
                            <i class="fas fa-map-marked-alt"></i> Parcelas
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>
                        <div class="collapse" id="parcelasMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-plus-circle"></i> Nueva Parcela</a></li>
                                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-map"></i> Mapa</a></li>
                                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-list"></i> Listado</a></li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#inventarioMenu">
                            <i class="fas fa-warehouse"></i> Inventario
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>
                        <div class="collapse" id="inventarioMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-plus-circle"></i> Nuevo Artículo</a></li>
                                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-list"></i> Listado</a></li>
                                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-exchange-alt"></i> Movimientos</a></li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#apoyosMenu">
                            <i class="fas fa-hands-helping"></i> Apoyos Sociales
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>
                        <div class="collapse" id="apoyosMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-plus-circle"></i> Nuevo Apoyo</a></li>
                                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-list"></i> Registros</a></li>
                                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-chart-pie"></i> Reportes</a></li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-chart-bar"></i> Reportes</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-cogs"></i> Configuración</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-database"></i> Respaldo</a>
                    </li>
                </ul>
            </div>
        </div>

        <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-tasks me-2"></i> **Registro de Nueva Actividad Ejidal**</h1>
            </div>

            <div class="container mt-4">
                <div class="card card-activity">
                    <form>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tipo_actividad" class="form-label">Tipo de Actividad:</label>
                                <select class="form-select" id="tipo_actividad" name="tipo_actividad" required>
                                    <option value="">Selecciona un tipo...</option>
                                    <option value="faena">Faena (Trabajo Comunitario)</option>
                                    <option value="reunion">Reunión de Comité/Asamblea Extraordinaria</option>
                                    <option value="proyecto">Ejecución de Proyecto (Obra, Mantenimiento)</option>
                                    <option value="capacitacion">Capacitación/Taller</option>
                                    <option value="gestion">Trámite/Gestión Administrativa</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="fecha_actividad" class="form-label">Fecha de Realización:</label>
                                <input type="date" class="form-control" id="fecha_actividad" name="fecha_actividad" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="nombre_actividad" class="form-label">Nombre/Título de la Actividad:</label>
                            <input type="text" class="form-control" id="nombre_actividad" name="nombre_actividad" placeholder="Ej: Limpieza de canales de riego, Reunión de planeación" required>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion_actividad" class="form-label">Descripción Detallada y Objetivos:</label>
                            <textarea class="form-control" id="descripcion_actividad" name="descripcion_actividad" rows="3" placeholder="Describe brevemente el propósito, el lugar y el alcance de la actividad..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="responsable" class="form-label">Responsable o Comité a Cargo:</label>
                                <input type="text" class="form-control" id="responsable" name="responsable" placeholder="Ej: Comisariado, Comité de Riego" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="ejidatarios_participantes" class="form-label">Ejidatarios/Participantes (Cantidad Estimada):</label>
                                <input type="number" class="form-control" id="ejidatarios_participantes" name="ejidatarios_participantes" min="1" placeholder="Ej: 50" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="observaciones" class="form-label">Observaciones y Resultados (al finalizar la actividad):</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="3" placeholder="Escribe aquí los resultados logrados, problemas encontrados, o notas importantes..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i> Guardar Actividad</button>
                        <button type="reset" class="btn btn-secondary"><i class="fas fa-redo me-2"></i> Limpiar Formulario</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<footer class="footer mt-auto py-3 bg-ejidal-light">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <span class="text-muted">Sistema de Gestión Ejidal &copy; 2025</span>
            </div>
            <div class="col-md-6 text-md-end">
                <span class="text-muted">Versión 1.1.0</span>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
