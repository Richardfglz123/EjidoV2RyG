<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActividadesController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\EjidatariosController;
use App\Http\Controllers\ReportesUController;
use App\Http\Controllers\ReportesEController;
use App\Http\Controllers\Auth\TwoFAController;
use App\Http\Middleware\CheckAuth;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\DatosHistoricosController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\RespaldoController;
use App\Http\Controllers\DescuentoController;
use App\Http\Controllers\ExpedienteController;
use App\Http\Controllers\FaenasController;
use App\Http\Controllers\ProgramaController;
use App\Http\Controllers\Reparto2Controller;
use App\Http\Controllers\RepartoController;
use App\Http\Controllers\MenuController;
// Modulos Ezequiel
use App\Http\Controllers\GastoController;
use App\Http\Controllers\ArticuloController;
use App\Http\Controllers\EntradaController;
use App\Http\Controllers\SalidaController;
use App\Http\Controllers\ParcelaController;

// --- RUTAS PÚBLICAS Y LOGIN ---
Route::get('/login', function () {
    return view('cpanel.login.sesion');
})->name('login.form');

Route::post('/login', [UsuariosController::class, 'login'])->name('login');

Route::get('/', function () {
    return redirect()->route('login.form');
});

Route::get('/2fa', [TwoFAController::class, 'showForm'])->name('2fa.form');
Route::post('/2fa/check', [TwoFAController::class, 'check'])->name('2fa.check');

Route::post('/logout', function () {
    \Log::info('Logout - Usuario: ' . session('nombre_completo'));
    session()->flush();
    return redirect()->route('login.form');
})->name('logout');

// --- RECUPERACIÓN DE CONTRASEÑA ---
Route::get('password/forgot', [UsuariosController::class, 'forgotForm'])->name('password.forgot');
// Corregido: nombre de ruta único para evitar conflictos
Route::post('password/forgot-send', [UsuariosController::class, 'sendResetCode'])->name('password.send');
Route::get('password/reset', [UsuariosController::class, 'resetForm'])->name('password.reset.form');
Route::post('password/reset', [UsuariosController::class, 'resetPassword'])->name('password.reset');

// --- GRUPO PROTEGIDO (AUTH Y 2FA) ---
Route::middleware([CheckAuth::class, '2fa'])->group(function () {

    // Dashboard e Inicio
    Route::get('/admon', [InicioController::class, 'index'])->name('inicio');

    // Perfil
    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil.index');
    Route::put('/perfil', [PerfilController::class, 'update'])->name('perfil.update');

    // --- MÓDULO: USUARIOS ---
    Route::prefix('admon/Usuarios')->group(function () {
        Route::middleware(['permiso:usuarios_ver'])->group(function () {
            Route::get('/buscar', [UsuariosController::class, 'buscar'])->name('usuarios.buscar');
            Route::get('/', [UsuariosController::class, 'index'])->name('Usuarios.index');
        });

        Route::middleware(['permiso:usuarios_crear'])->group(function () {
            Route::get('/create', [UsuariosController::class, 'create'])->name('Usuarios.create');
            Route::post('/', [UsuariosController::class, 'store'])->name('Usuarios.store');
            Route::get('/{Usuario}/edit', [UsuariosController::class, 'edit'])->name('Usuarios.edit');
            Route::put('/{Usuario}', [UsuariosController::class, 'update'])->name('Usuarios.update');
            Route::delete('/{Usuario}', [UsuariosController::class, 'destroy'])->name('Usuarios.destroy');
        });
    });

// --- MÓDULO: EJIDATARIOS ---
    Route::prefix('admon/Ejidatarios')->group(function () {

        // 1. RUTAS DE AJAX (Deben ir primero para que no las confunda con el ID de un ejidatario)
        Route::get('/buscar-json', [RepartoController::class, 'buscarEjidatario'])->name('ejidatarios.buscar');
        // Para el JSON del saldo disponible
        Route::get('ejidatarios/{id_ejidatario}/saldo-json', [RepartoController::class, 'obtenerSaldo'])
            ->name('ejidatarios.saldo');
        // 2. CRUD ESTÁNDAR
        Route::middleware(['permiso:ejidatarios_crear'])->group(function () {
            Route::get('/create', [EjidatariosController::class, 'create'])->name('Ejidatarios.create');
            Route::post('/', [EjidatariosController::class, 'store'])->name('Ejidatarios.store');
            Route::get('/{Ejidatario}/edit', [EjidatariosController::class, 'edit'])->name('Ejidatarios.edit');
            Route::put('/{Ejidatario}', [EjidatariosController::class, 'update'])->name('Ejidatarios.update');
            Route::delete('/{Ejidatario}', [EjidatariosController::class, 'destroy'])->name('Ejidatarios.destroy');
        });

        Route::middleware(['permiso:ejidatarios_ver'])->group(function () {
            Route::get('/', [EjidatariosController::class, 'index'])->name('Ejidatarios.index');
            Route::get('/{Ejidatario}', [EjidatariosController::class, 'show'])->name('Ejidatarios.show');
        });
    });

    Route::post('/prestamo/guardar', [RepartoController::class, 'agregarPrestamo'])->name('prestamo.agregar');
    // Rutas para la fecha límite (Primer Reparto)
    Route::get('/reparto/obtener-fecha', [RepartoController::class, 'obtenerFechaLimite'])->name('reparto.primer.obtenerFecha');
    Route::post('/reparto/fijar-fecha', [RepartoController::class, 'fijarFechaLimite'])->name('reparto.primer.fijarFecha');

    // --- MÓDULO: ACTIVIDADES ---
    Route::prefix('admon/actividades')->group(function () {
        Route::middleware(['permiso:actividades_crear'])->group(function () {
            Route::get('/create', [ActividadesController::class, 'create'])->name('actividades.create');
            Route::post('/', [ActividadesController::class, 'store'])->name('actividades.store');
            Route::get('/{actividade}/edit', [ActividadesController::class, 'edit'])->name('actividades.edit');
            Route::put('/{actividade}', [ActividadesController::class, 'update'])->name('actividades.update');
            Route::delete('/{actividade}', [ActividadesController::class, 'destroy'])->name('actividades.destroy');
        });

        Route::middleware(['permiso:actividades_ver'])->group(function () {
            Route::get('/reportes/pdf', [ActividadesController::class, 'reportePDF'])->name('actividades.reporte.pdf');
            Route::get('/reportes/excel', [ActividadesController::class, 'reporteExcel'])->name('actividades.reporte.excel');
            Route::get('/', [ActividadesController::class, 'index'])->name('actividades.index');
            Route::get('/{actividade}', [ActividadesController::class, 'show'])->name('actividades.show');
        });
    });

    // --- REPORTES ---
    Route::middleware(['permiso:usuarios_ver'])->prefix('admon/reportes/usuarios')->group(function () {
        Route::get('/pdf', [ReportesUController::class, 'GenerarPDF'])->name('reportes.usuarios.pdf');
        Route::get('/excel', [ReportesUController::class, 'GenerarExcel'])->name('reportes.usuarios.excel');
    });

    Route::middleware(['permiso:ejidatarios_ver'])->prefix('admon/reportes/ejidatarios')->group(function () {
        Route::get('/pdf', [ReportesEController::class, 'GenerarPDF'])->name('reportes.ejidatarios.pdf');
        Route::get('/excel', [ReportesEController::class, 'GenerarExcel'])->name('reportes.ejidatarios.excel');
    });

    // --- MÓDULO: DATOS HISTÓRICOS ---
    Route::middleware(['permiso:historicos_ver'])->prefix('admon/DatosHistoricos')->group(function () {
        Route::get('/reportes/pdf', [DatosHistoricosController::class, 'reportePDF'])->name('datos_historicos.reporte.pdf');
        Route::get('/reportes/excel', [DatosHistoricosController::class, 'reporteExcel'])->name('datos_historicos.reporte.excel');
        Route::get('/', [DatosHistoricosController::class, 'index'])->name('datos_historicos.index');

        Route::middleware(['permiso:historicos_crear'])->group(function () {
            Route::get('/create', [DatosHistoricosController::class, 'create'])->name('datos_historicos.create');
            Route::post('/', [DatosHistoricosController::class, 'store'])->name('datos_historicos.store');
            Route::get('/{id}/edit', [DatosHistoricosController::class, 'edit'])->name('datos_historicos.edit');
            Route::put('/{id}', [DatosHistoricosController::class, 'update'])->name('datos_historicos.update');
            Route::delete('/{id}', [DatosHistoricosController::class, 'destroy'])->name('datos_historicos.destroy');
            Route::get('/{id}/eliminar-foto', [DatosHistoricosController::class, 'eliminarFoto'])->name('datos_historicos.foto.delete');
        });
    });

    // --- MÓDULO: PARCELAS ---
    Route::middleware(['permiso:parcelas_ver'])->group(function () {
        Route::get('/parcelas', [ParcelaController::class, 'index'])->name('parcelas.index');
        Route::get('/verParcela', [ParcelaController::class, 'verParcela'])->name('parcelas.ver');

        Route::middleware(['permiso:parcelas_crear'])->group(function () {
            Route::get('/nuevaParcela', [ParcelaController::class, 'create'])->name('parcelas.create');
            Route::post('/nuevaParcela', [ParcelaController::class, 'store'])->name('parcelas.store');
            Route::get('/editarParcela/{id}', [ParcelaController::class, 'editarParcela'])->name('parcelas.editar');
            Route::put('/parcela/actualizar/{id}', [ParcelaController::class, 'actualizarParcela'])->name('parcelas.actualizar');
            Route::delete('/parcela/eliminar/{id}', [ParcelaController::class, 'eliminarParcela'])->name('parcelas.eliminar');
        });
    });

    // --- MÓDULO: GASTOS ---
    Route::middleware(['permiso:gastos_ver'])->group(function () {
        Route::get('/gastos', [GastoController::class, 'index'])->name('gastos.index');
        Route::get('/gastos-pdf', [GastoController::class, 'generarPdf'])->name('gastos.pdf');
        Route::get('/gastos-excel', [GastoController::class, 'generarExcel'])->name('gastos.excel');
        Route::post('/gastos/buscar', [GastoController::class, 'buscar'])->name('gastos.buscar');

        Route::middleware(['permiso:gastos_crear'])->group(function () {
            Route::get('/gastos/nuevo', [GastoController::class, 'create'])->name('gastos.create');
            Route::post('/gastos', [GastoController::class, 'store'])->name('gastos.store');
            Route::get('/gastos/{id}/editar', [GastoController::class, 'edit'])->name('gastos.edit');
            Route::put('/gastos/{id}', [GastoController::class, 'update'])->name('gastos.update');
            Route::delete('/gastos/{id}/eliminar', [GastoController::class, 'destroy'])->name('gastos.destroy');
        });
    });

    // --- MÓDULO: INVENTARIO (Artículos, Entradas, Salidas) ---
    Route::middleware(['permiso:inventario_ver'])->group(function () {
        Route::get('/articulos', [ArticuloController::class, 'index'])->name('articulos.index');
        Route::get('/articulos/buscar', [ArticuloController::class, 'buscar'])->name('articulos.buscar');
        Route::get('/articulos-pdf', [ArticuloController::class, 'generarPdf'])->name('articulos.pdf');
        Route::get('/articulos-excel', [ArticuloController::class, 'generarExcel'])->name('articulos.excel');

        // ENTRADAS
        Route::get('/entradas', [EntradaController::class, 'index'])->name('entradas.index');
        Route::get('/entradas/pdf', [EntradaController::class, 'generarPdf'])->name('entradas.pdf');
        Route::get('/entradas/excel', [EntradaController::class, 'generarExcel'])->name('entradas.excel');

        // SALIDAS
        Route::get('/salidas', [SalidaController::class, 'index'])->name('salidas.index');
        Route::get('/salidas/pdf', [SalidaController::class, 'generarPdf'])->name('salidas.pdf');
        Route::get('/salidas/excel', [SalidaController::class, 'generarExcel'])->name('salidas.excel');

        Route::middleware(['permiso:inventario_crear'])->group(function () {
            // Artículos CRUD
            Route::get('/articulos/nuevo', [ArticuloController::class, 'create'])->name('articulos.create');
            Route::post('/articulos', [ArticuloController::class, 'store'])->name('articulos.store');
            Route::get('/articulos/{id}/editar', [ArticuloController::class, 'edit'])->name('articulos.edit');
            Route::put('/articulos/{articulo}', [ArticuloController::class, 'update'])->name('articulos.update');
            Route::post('/articulos/{id}/eliminar', [ArticuloController::class, 'destroy'])->name('articulos.destroy');

            // Entradas CRUD
            Route::get('/entradas/nueva', [EntradaController::class, 'create'])->name('entradas.create');
            Route::post('/entradas/guardar', [EntradaController::class, 'store'])->name('entradas.store');
            Route::get('/entradas/{id}/edit', [EntradaController::class, 'edit'])->name('entradas.edit');
            Route::put('/entradas/{id}', [EntradaController::class, 'update'])->name('entradas.update');
            Route::delete('/entradas/{id}', [EntradaController::class, 'destroy'])->name('entradas.destroy');

            // Salidas CRUD
            Route::get('/salidas/nueva', [SalidaController::class, 'create'])->name('salidas.create');
            Route::post('/salidas/guardar', [SalidaController::class, 'store'])->name('salidas.store');
            Route::get('/salidas/{id}/edit', [SalidaController::class, 'edit'])->name('salidas.edit');
            Route::put('/salidas/{id}', [SalidaController::class, 'update'])->name('salidas.update');
            Route::delete('/salidas/{id}', [SalidaController::class, 'destroy'])->name('salidas.destroy');
        });
    });

    // --- MÓDULO: Respaldo ---
    Route::middleware(['permiso:respaldo_ver'])->prefix('admon')->group(function () {
        Route::get('/Respaldos', [RespaldoController::class, 'index'])->name('Respaldos.index');
        Route::post('/Respaldos/generar', [RespaldoController::class, 'store'])->name('Respaldos.store');
        Route::get('/Respaldos/descargar/{filename}', [RespaldoController::class, 'download'])->name('Respaldos.download');
        Route::delete('/Respaldos/eliminar/{filename}', [RespaldoController::class, 'destroy'])->name('Respaldos.destroy');
    });

// --- MÓDULO: Config ---
    Route::middleware(['permiso:configuracion_ver'])->group(function () {
        Route::prefix('configuracion')->group(function () {

            Route::get('/permisos', [ConfiguracionController::class, 'permisos'])->name('configuracion.permisos');
            Route::get('/permisos/buscar/{id}', [ConfiguracionController::class, 'obtenerPermisosUsuario']);

            Route::middleware(['permiso:configuracion_crear'])->group(function () {
                Route::post('/permisos/guardar', [ConfiguracionController::class, 'guardarPermisos'])->name('configuracion.permisos.guardar');
                Route::get('/usuarios/buscar-ajax', [ConfiguracionController::class, 'buscarUsuariosAjax'])->name('configuracion.usuarios.buscar_ajax');
                Route::get('/permisos/rol/{id}', [ConfiguracionController::class, 'obtenerPermisosRol']);
            });
        });
    });
// --- MÓDULO: EXPEDIENTES ---
    Route::middleware(['permiso:expedientes_ver'])->prefix('admon/expedientes')->group(function () {
        Route::get('/', [ExpedienteController::class, 'index'])->name('expedientes.index');
        Route::get('/{id}', [ExpedienteController::class, 'show'])->name('expedientes.show');

        Route::middleware(['permiso:expedientes_crear'])->group(function () {
            Route::get('/nuevo', [ExpedienteController::class, 'create'])->name('expedientes.create');
            Route::post('/', [ExpedienteController::class, 'store'])->name('expedientes.store');
            Route::get('/{id}/editar', [ExpedienteController::class, 'edit'])->name('expedientes.edit');
            Route::put('/{id}', [ExpedienteController::class, 'update'])->name('expedientes.update');
            Route::delete('/{id}', [ExpedienteController::class, 'destroy'])->name('expedientes.destroy');
        });
    });

    // --- MÓDULO: FAENAS ---
    Route::middleware(['permiso:faenas_ver'])->prefix('admon/faenas')->group(function () {
        Route::get('/', [FaenasController::class, 'index'])->name('faenas.index');

        Route::middleware(['permiso:faenas_crear'])->group(function () {
            Route::get('/create', [FaenasController::class, 'create'])->name('faenas.create');
            Route::post('/', [FaenasController::class, 'store'])->name('faenas.store');
            Route::get('/{id}/edit', [FaenasController::class, 'edit'])->name('faenas.edit');
            Route::put('/{id}', [FaenasController::class, 'update'])->name('faenas.update');
            Route::delete('/{id}', [FaenasController::class, 'destroy'])->name('faenas.destroy');
        });
    });

    // --- MÓDULO: PROGRAMAS ---
    Route::middleware(['permiso:programas_ver'])->prefix('admon/programas')->group(function () {
        Route::get('/', [ProgramaController::class, 'index'])->name('programas.index');

        Route::middleware(['permiso:programas_crear'])->group(function () {
            Route::get('/nuevo', [ProgramaController::class, 'create'])->name('programas.create');
            Route::post('/', [ProgramaController::class, 'store'])->name('programas.store');
            Route::get('/{id}/edit', [ProgramaController::class, 'edit'])->name('programas.edit');
            Route::put('/{id}', [ProgramaController::class, 'update'])->name('programas.update');
            Route::delete('/{id}', [ProgramaController::class, 'destroy'])->name('programas.destroy');
        });
    });

    // --- MÓDULO: REPARTOS (Reparto ---
    Route::middleware(['permiso:repartos_ver'])->prefix('admon/repartos')->group(function () {
        Route::get('/', [RepartoController::class, 'mostrarPrimerReparto'])->name('repartos.index');

        Route::middleware(['permiso:repartos_crear'])->group(function () {
            Route::post('/guardar', [RepartoController::class, 'store'])->name('repartos.store');
            Route::post('/v2/guardar', [Reparto2Controller::class, 'store'])->name('repartos2.store');
        });
    });


    //DESCUENTOS
    Route::get('/descuentos', [DescuentoController::class, 'index'])->name('descuento.index');
    Route::get('/descuentos-asambleas', [DescuentoController::class, 'index'])->name('descuentos.asambleas');
    Route::get('/descuentos-faenas', [DescuentoController::class, 'faenas'])->name('descuentos.faenas');
    Route::post('/descuento/guardar', [DescuentoController::class, 'store'])->name('descuentos.store');
    Route::get('/asambleas/buscar', [DescuentoController::class, 'buscar'])
        ->name('asambleas.buscar');
    Route::get('/faenas/buscar', [DescuentoController::class, 'buscar'])
        ->name('faenas.buscar');
    Route::post('/faenas/aplicar', [DescuentoController::class, 'store'])
        ->name('faenas.aplicar');
// Vista para agregar/editar un descuento
    Route::get('/descuento', [DescuentoController::class, 'descuento'])->name('descuento.descuento');
        // El Dashboard de los cuadros
        Route::get('/dashboard-repartos', [RepartoController::class, 'menu'])->name('menu');

        // El formulario de edición (Botón Registro Repartos)
        Route::get('/configurar-montos', [RepartoController::class, 'index'])->name('monto.index');
        Route::get('/registro-repartos', [RepartoController::class, 'index'])->name('repartos.registro');

        // La tabla de préstamos (Botón Primer Reparto)
        Route::get('/tabla-primer-reparto', [RepartoController::class, 'mostrarPrimerReparto'])->name('reparto.primer');
        Route::get('/primer-reparto', [RepartoController::class, 'mostrarPrimerReparto'])->name('repartos.primero');

        // Acción de actualizar
        Route::patch('/update-monto/{id}', [RepartoController::class, 'update'])->name('monto.update');
    });

    // --- MÓDULO: EXPEDIENTES DIGITALES ---
    Route::middleware(['permiso:expedientes_ver'])->prefix('admon/expedientes')->group(function () {
        Route::get('/mi-expediente', [ExpedienteController::class, 'index'])->name('expedientes.index');
        Route::get('/ver/{id}', [ExpedienteController::class, 'show'])->name('expedientes.show');

        Route::middleware(['permiso:expedientes_crear'])->group(function () {
            Route::get('/nuevo', [ExpedienteController::class, 'create'])->name('expedientes.create');
            Route::post('/guardar', [ExpedienteController::class, 'store'])->name('expedientes.store');
        });
    });

// --- MÓDULO: REPARTOS Y FINANZAS ---
    Route::middleware(['permiso:utilidades_ver'])->prefix('admon/finanzas')->group(function () {

        // El Menú Principal (Dashboard)
        Route::get('/menu-repartos', [RepartoController::class, 'menu'])->name('menu');

        // Registro/Configuración de Montos (Lo que pide el Sidebar y el botón "Agregar Monto")
        Route::get('/registro-repartos', [RepartoController::class, 'index'])->name('monto.index');
        Route::get('/registro-repartos-alias', [RepartoController::class, 'index'])->name('repartos.registro'); // Alias para que el sidebar no falle

        // Tablas de detalles (Lo que pide el Sidebar)
        Route::get('/primer-reparto', [RepartoController::class, 'mostrarPrimerReparto'])->name('reparto.primer');
        Route::get('/primer-reparto-alias', [RepartoController::class, 'mostrarPrimerReparto'])->name('repartos.primero'); // Alias

        Route::get('/segundo-reparto', [Reparto2Controller::class, 'mostrarSegundoReparto'])->name('reparto.segundo');
        Route::get('/segundo-reparto-alias', [Reparto2Controller::class, 'mostrarSegundoReparto'])->name('repartos.segundo'); // Alias

        // Acción de Guardar Cambios
        Route::patch('/configurar-monto/{id}', [RepartoController::class, 'update'])->name('monto.update');


        // Descuentos
        Route::get('/descuentos-asambleas', [DescuentoController::class, 'asambleas'])->name('descuentos.asambleas');
        Route::get('/descuentos-faenas', [DescuentoController::class, 'faenas'])->name('descuentos.faenas');

        Route::middleware(['permiso:utilidades_crear'])->group(function () {
            Route::post('/reparto/guardar', [RepartoController::class, 'store'])->name('repartos.store');
            Route::post('/descuento/guardar', [DescuentoController::class, 'store'])->name('descuentos.store');
        });
    });
    //prestamos
Route::post('/prestamo/agregar', [RepartoController::class, 'agregarPrestamo'])->name('prestamo.agregar');
Route::patch('/prestamo/actualizar/{id}', [RepartoController::class, 'actualizarPrestamo'])->name('prestamo.actualizar');
Route::delete('/prestamo/{id}', [RepartoController::class, 'eliminarPrestamo'])->name('prestamo.eliminar');
Route::post('/prestamo/abonar/{id}', [RepartoController::class, 'agregarAbono'])->name('prestamo.abonar');
Route::get('/prestamo/{id}/ticket', [RepartoController::class, 'generarTicketPDF'])->name('prestamo.ticket');
    Route::get('/primer-reparto/pdf', [RepartoController::class, 'generarPDF'])->name('reparto.primer.pdf');
    Route::post('/reparto/primer/fijar-fecha', [RepartoController::class, 'fijarFechaLimite'])->name('reparto.primer.fijarFecha');
    Route::get('/reparto/primer/obtener-fecha', [RepartoController::class, 'obtenerFechaLimite'])->name('reparto.primer.obtenerFecha');
    Route::post('/reparto/primer/abono/{id}', [RepartoController::class, 'agregarAbono'])->name('reparto.primer.abono');
    Route::get('/menu', [MenuController::class, 'index'])->name('menu');
Route::get('primer-reparto/ejidatario/{id}/saldo', [RepartoController::class, 'obtenerSaldo'])->name('prestamo.saldo');
    Route::get('/primer-reparto', [RepartoController::class, 'mostrarPrimerReparto'])->name('reparto.primer');
    Route::get('/segundo-reparto', [Reparto2Controller::class, 'mostrarSegundoReparto'])->name('reparto.segundo');
    Route::get('/reporte/concentrado', [RepartoController::class, 'descargarConcentrado'])->name('reporte.concentrado');
Route::get('/primer-reparto/ejidatario/{id}/saldo', [RepartoController::class, 'obtenerSaldo'])->name('prestamo.saldo');
Route::patch('/prestamo/actualizar/{id}', [RepartoController::class, 'actualizarPrestamo'])->name('prestamo.actualizar');
Route::post('/prestamo/abonar/{id}', [RepartoController::class, 'agregarAbono'])->name('prestamo.abonar');

// Rutas para el Segundo Reparto
Route::get('/segundo-reparto', [Reparto2Controller::class, 'mostrarSegundoReparto'])->name('reparto.segundo');
Route::get('/segundo-reparto/pdf', [Reparto2Controller::class, 'generarPDF'])->name('reparto.segundo.pdf');

// CRUD Préstamos Segundo Reparto
Route::post('/prestamo2/agregar', [Reparto2Controller::class, 'agregarPrestamo'])->name('prestamo2.agregar');
Route::patch('/prestamo2/actualizar/{id}', [Reparto2Controller::class, 'actualizarPrestamo'])->name('prestamo2.actualizar');
Route::delete('/prestamo2/eliminar/{id}', [Reparto2Controller::class, 'eliminarPrestamo'])->name('prestamo2.eliminar');
Route::post('/prestamo2/abonar/{id}', [Reparto2Controller::class, 'agregarAbono'])->name('prestamo2.abonar');

// Búsqueda y saldos
Route::get('/ejidatarios/buscar', [Reparto2Controller::class, 'buscarEjidatarios'])->name('ejidatarios.buscar');
Route::get('/primer-reparto/ejidatario/{id}/saldo', [Reparto2Controller::class, 'obtenerSaldoEjidatario'])->name('prestamo.saldo');

// Detalles y gestión de descuentos
Route::get('/reparto2/detalle-prestamos/{id_ejidatario}', [Reparto2Controller::class, 'obtenerDetallePrestamos'])->name('reparto2.detalle.prestamos');
Route::get('/reparto2/detalle-faenas/{id_ejidatario}', [Reparto2Controller::class, 'obtenerDetalleFaenas'])->name('reparto2.detalle.faenas');
Route::delete('/reparto2/faena-perdonar/{id_descuento}', [Reparto2Controller::class, 'perdonarFaena'])->name('reparto2.faena.perdonar');
Route::get('/reparto2/detalle-asambleas/{id_ejidatario}', [Reparto2Controller::class, 'obtenerDetalleAsambleas'])->name('reparto2.detalle.asambleas');
Route::delete('/reparto2/asamblea-perdonar/{id_descuento}', [Reparto2Controller::class, 'perdonarAsamblea'])->name('reparto2.asamblea.perdonar');

// Gestión de fechas límite
Route::post('/reparto/segundo/fijar-fecha', [Reparto2Controller::class, 'fijarFechaLimite'])->name('reparto.segundo.fijarFecha');
Route::get('/reparto/segundo/obtener-fecha', [Reparto2Controller::class, 'obtenerFechaLimite'])->name('reparto.segundo.obtenerFecha');
//
Route::get('/ejidatarios/buscar', [Reparto2Controller::class, 'buscarEjidatarios'])->name('ejidatarios.buscar');
// Tickets
Route::get('/prestamo2/{id}/ticket', [Reparto2Controller::class, 'generarTicketPDF'])->name('prestamo2.ticket');
Route::get('/reparto2/ticket-general/{id}', [Reparto2Controller::class, 'generarTicketGeneral'])->name('reparto2.ticket.general');

    Route::get('/debug-auth', function () {
        return response()->json(session()->all());
    });