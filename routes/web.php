<?php
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\SocialController;
use App\Models\Ejidatario;
use App\Http\Controllers\AsambleaController;
use App\Http\Controllers\ConcentradoController;
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
use App\Http\Controllers\EventoController;
use App\Http\Controllers\MultaController;
use App\Http\Controllers\PaseListaController;
use App\Http\Controllers\CategoriaEventoController;
// Modulos Ezequiel
use App\Http\Controllers\GastoController;
use App\Http\Controllers\ArticuloController;
use App\Http\Controllers\EntradaController;
use App\Http\Controllers\SalidaController;
use App\Http\Controllers\ParcelaController;

// RUTAS PÚBLICAS Y AUTENTICACIÓN
Route::get('/auth/google/redirect', [SocialController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/auth/google/callback', [SocialController::class, 'handleGoogleCallback']);

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
Route::post('password/forgot-send', [UsuariosController::class, 'sendResetCode'])->name('password.send');
Route::get('password/reset', [UsuariosController::class, 'resetForm'])->name('password.reset.form');
Route::post('password/reset', [UsuariosController::class, 'resetPassword'])->name('password.reset');

// Ruta de limpieza
Route::get('/limpiar', function () {
    \Artisan::call('cache:clear');
    \Artisan::call('route:clear');
    \Artisan::call('config:clear');
    \Artisan::call('view:clear');
    return "¡Caché del sistema completamente limpia!";
});


// GRUPO PROTEGIDO (AUTH Y 2FA)

// Dashboard e Inicio
Route::get('/admon', [InicioController::class, 'index'])->name('inicio');

// Perfil
Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil.index');
Route::put('/perfil', [PerfilController::class, 'update'])->name('perfil.update');


// --- MÓDULO: USUARIOS
Route::prefix('admon/usuarios')->group(function () {

    // PERMISO: VER
    Route::middleware(['permiso:usuarios_ver'])->group(function () {
        Route::get('/', [UsuariosController::class, 'index'])->name('usuarios.index');
        Route::get('/buscar', [UsuariosController::class, 'buscar'])->name('usuarios.buscar');
        Route::get('/usuarios', [UsuariosController::class, 'index'])->name('Usuarios.index'); // Legacy
    });

    // PERMISO: CREAR
    Route::middleware(['permiso:usuarios_crear'])->group(function () {
        Route::get('/create', [UsuariosController::class, 'create'])->name('usuarios.create');
        Route::get('/create-legacy', [UsuariosController::class, 'create'])->name('Usuarios.create'); // Legacy
        Route::post('/', [UsuariosController::class, 'store'])->name('usuarios.store');
        Route::post('/store-legacy', [UsuariosController::class, 'store'])->name('Usuarios.store'); // Legacy
    });

    // PERMISO: EDITAR
    Route::middleware(['permiso:usuarios_editar'])->group(function () {
        Route::get('/{id}/edit', [UsuariosController::class, 'edit'])->name('usuarios.edit');
        Route::get('/{id}/edit-legacy', [UsuariosController::class, 'edit'])->name('Usuarios.edit'); // Legacy
        Route::put('/{id}', [UsuariosController::class, 'update'])->name('usuarios.update');
        Route::put('/{id}-legacy', [UsuariosController::class, 'update'])->name('Usuarios.update'); // Legacy
    });

    // PERMISO: ELIMINAR
    Route::middleware(['permiso:usuarios_eliminar'])->group(function () {
        Route::delete('/{id}', [UsuariosController::class, 'destroy'])->name('usuarios.destroy');
        Route::delete('/{id}-legacy', [UsuariosController::class, 'destroy'])->name('Usuarios.destroy'); // Legacy
    });
});

// Modulo Ejidatarios
Route::prefix('admon/Ejidatarios')->group(function () {
    Route::get('/buscar-json', [RepartoController::class, 'buscarEjidatario'])->name('ejidatarios.buscar');
    Route::get('ejidatarios/{id_ejidatario}/saldo-json', [RepartoController::class, 'obtenerSaldo'])->name('ejidatarios.saldo');
    Route::get('/api/cp/{cp}', [EjidatariosController::class, 'buscarCP'])->name('api.cp');

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


// --- MÓDULO: DESCUENTOS Y AJAX (CORREGIDO PARA REPARTO 2) ---
Route::get('admon/finanzas/segundo-reparto/detalle-asambleas/{id}', [Reparto2Controller::class, 'obtenerDetalleAsambleas']);
Route::get('admon/finanzas/segundo-reparto/detalle-faenas/{id}', [Reparto2Controller::class, 'obtenerDetalleFaenas']);
Route::post('admon/finanzas/segundo-reparto/reprogramar-falta', [Reparto2Controller::class, 'reprogramarFalta'])->name('reprogramar.falta');
Route::get('/descuentos-asambleas', [AsambleaController::class, 'index'])->name('descuentos.asambleas');
Route::get('/ejidatarios/buscar-descuentos', [DescuentoController::class, 'buscar'])->name('descuentos.buscar_ejidatario');
Route::post('/descuento/guardar', [DescuentoController::class, 'store'])->name('descuento.store');
Route::get('/descuento-configuracion', [DescuentoController::class, 'descuento'])->name('descuento.descuento');
Route::patch('/descuento-update/{id}', [DescuentoController::class, 'update'])->name('descuento.update');

// Ruta para el boton de "Abonar/Pagar" en el Segundo Reparto
Route::post('/prestamo/abonar-r2/{id}', [Reparto2Controller::class, 'abonarPrestamo'])->name('prestamo.abonar.r2');

Route::get('/descuentos/asambleas', [AsambleaController::class, 'index'])->name('descuentos.asambleas');
Route::get('/descuentos-faenas', [FaenasController::class, 'index'])->name('descuentos.faenas');
Route::post('/faenas/aplicar', [FaenasController::class, 'aplicarDescuento'])->name('faenas.aplicar');
//EXPOR FINAL
Route::get('/concentrado/excel', [ConcentradoController::class, 'exportarExcel'])->name('concentrado.excel');

// MÓDULO: ACTIVIDADES
Route::prefix('admon/actividades')->group(function () {
    Route::middleware(['permiso:actividades_ver'])->group(function () {
        Route::get('/', [ActividadesController::class, 'index'])->name('actividades.index');
        Route::get('/reportes/pdf', [ActividadesController::class, 'reportePDF'])->name('actividades.reporte.pdf');
        Route::get('/reportes/excel', [ActividadesController::class, 'reporteExcel'])->name('actividades.reporte.excel');
    });

    Route::middleware(['permiso:actividades_crear'])->group(function () {
        Route::get('/create', [ActividadesController::class, 'create'])->name('actividades.create');
        Route::post('/', [ActividadesController::class, 'store'])->name('actividades.store');
    });

    Route::middleware(['permiso:actividades_editar'])->group(function () {
        Route::get('/{actividade}/edit', [ActividadesController::class, 'edit'])->name('actividades.edit');
        Route::match(['put', 'patch'], '/{actividade}', [ActividadesController::class, 'update'])->name('actividades.update');
    });

    Route::middleware(['permiso:actividades_eliminar'])->group(function () {
        Route::delete('/{actividade}', [ActividadesController::class, 'destroy'])->name('actividades.destroy');
    });

    Route::middleware(['permiso:actividades_ver'])->group(function () {
        Route::get('/{actividade}', [ActividadesController::class, 'show'])->name('actividades.show');
    });
});


// REPORTES
Route::middleware(['permiso:usuarios_ver'])->prefix('admon/reportes/usuarios')->group(function () {
    Route::get('/pdf', [ReportesUController::class, 'GenerarPDF'])->name('reportes.usuarios.pdf');
    Route::get('/excel', [ReportesUController::class, 'GenerarExcel'])->name('reportes.usuarios.excel');
});

Route::middleware(['permiso:ejidatarios_ver'])->prefix('admon/reportes/ejidatarios')->group(function () {
    Route::get('/pdf', [ReportesEController::class, 'GenerarPDF'])->name('reportes.ejidatarios.pdf');
    Route::get('/excel', [ReportesEController::class, 'GenerarExcel'])->name('reportes.ejidatarios.excel');
});


//  RUTA GLOBAL PARA VER ARCHIVOS (Indispensable para Hostinger)
Route::get('ver-archivo/{path}', function ($path) {
    $path = urldecode($path);
    $cleanPath = str_replace('storage/', '', $path);

    if (!Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)) {
        abort(404, 'Archivo no encontrado: ' . $cleanPath);
    }

    return Illuminate\Support\Facades\Storage::disk('public')->response($cleanPath);
})->where('path', '.*')->name('ver.archivo');

// MÓDULO: DATOS HISTÓRICOS
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


// MÓDULO: PARCELAS
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


// MÓDULO: GASTOS
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


// MÓDULO: INVENTARIO
Route::middleware(['permiso:inventario_ver'])->group(function () {
    Route::get('/articulos', [ArticuloController::class, 'index'])->name('articulos.index');
    Route::get('/articulos/buscar', [ArticuloController::class, 'buscar'])->name('articulos.buscar');
    Route::get('/articulos-pdf', [ArticuloController::class, 'generarPdf'])->name('articulos.pdf');
    Route::get('/articulos-excel', [ArticuloController::class, 'generarExcel'])->name('articulos.excel');
    Route::get('/entradas', [EntradaController::class, 'index'])->name('entradas.index');
    Route::get('/entradas/pdf', [EntradaController::class, 'generarPdf'])->name('entradas.pdf');
    Route::get('/entradas/excel', [EntradaController::class, 'generarExcel'])->name('entradas.excel');
    Route::get('/salidas', [SalidaController::class, 'index'])->name('salidas.index');
    Route::get('/salidas/pdf', [SalidaController::class, 'generarPdf'])->name('salidas.pdf');
    Route::get('/salidas/excel', [SalidaController::class, 'generarExcel'])->name('salidas.excel');

    Route::middleware(['permiso:inventario_crear'])->group(function () {
        Route::get('/articulos/nuevo', [ArticuloController::class, 'create'])->name('articulos.create');
        Route::post('/articulos', [ArticuloController::class, 'store'])->name('articulos.store');
        Route::get('/articulos/{id}/editar', [ArticuloController::class, 'edit'])->name('articulos.edit');
        Route::put('/articulos/{articulo}', [ArticuloController::class, 'update'])->name('articulos.update');
        Route::post('/articulos/{id}/eliminar', [ArticuloController::class, 'destroy'])->name('articulos.destroy');
        Route::get('/entradas/nueva', [EntradaController::class, 'create'])->name('entradas.create');
        Route::post('/entradas/guardar', [EntradaController::class, 'store'])->name('entradas.store');
        Route::get('/entradas/{id}/edit', [EntradaController::class, 'edit'])->name('entradas.edit');
        Route::put('/entradas/{id}', [EntradaController::class, 'update'])->name('entradas.update');
        Route::delete('/entradas/{id}', [EntradaController::class, 'destroy'])->name('entradas.destroy');
        Route::get('/salidas/nueva', [SalidaController::class, 'create'])->name('salidas.create');
        Route::post('/salidas/guardar', [SalidaController::class, 'store'])->name('salidas.store');
        Route::get('/salidas/{id}/edit', [SalidaController::class, 'edit'])->name('salidas.edit');
        Route::put('/salidas/{id}', [SalidaController::class, 'update'])->name('salidas.update');
        Route::delete('/salidas/{id}', [SalidaController::class, 'destroy'])->name('salidas.destroy');
    });
});


// MÓDULO: RESPALDO
Route::middleware(['permiso:respaldo_ver'])->prefix('admon')->group(function () {
    Route::get('/Respaldos', [RespaldoController::class, 'index'])->name('Respaldos.index');
    Route::post('/Respaldos/generar', [RespaldoController::class, 'store'])->name('Respaldos.store');
    Route::get('/Respaldos/descargar/{filename}', [RespaldoController::class, 'download'])->name('Respaldos.download');
    Route::delete('/Respaldos/eliminar/{filename}', [RespaldoController::class, 'destroy'])->name('Respaldos.destroy');
});


// MÓDULO: CONFIGURACIÓN Y PERMISOS
Route::prefix('configuracion')->group(function () {
    Route::get('/usuarios/buscar-ajax', [ConfiguracionController::class, 'buscarUsuariosAjax'])->name('configuracion.usuarios.buscar_ajax');

    Route::middleware(['permiso:configuracion_ver'])->group(function () {
        Route::get('/permisos', [ConfiguracionController::class, 'permisos'])->name('configuracion.permisos');
        Route::get('/permisos/buscar/{id}', [ConfiguracionController::class, 'obtenerPermisosUsuario']);
        Route::get('/permisos/rol/{id}', [ConfiguracionController::class, 'obtenerPermisosRol']);

        Route::middleware(['permiso:configuracion_crear'])->group(function () {
            Route::post('/permisos/guardar', [ConfiguracionController::class, 'guardarPermisos'])->name('configuracion.permisos.guardar');
        });
    });
});


// --- MÓDULO: EXPEDIENTES ---
Route::prefix('admon/expedientes')->group(function () {
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

Route::get('expedientes/{slug}/{archivo}', function ($slug, $archivo) {
    $path = "expedientes/{$slug}/{$archivo}";
    $fullPath = storage_path('app/public/' . $path);

    if (!file_exists($fullPath)) {
        abort(404, 'Archivo no encontrado');
    }

    return response()->file($fullPath);
})->where('archivo', '.*');

// MÓDULO: ADMINISTRACIÓN DE FAENAS
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


// MÓDULO: REPARTOS
Route::middleware(['permiso:repartos_ver'])->prefix('admon/repartos')->group(function () {
    Route::get('/', [RepartoController::class, 'mostrarPrimerReparto'])->name('repartos.index');

    Route::middleware(['permiso:repartos_crear'])->group(function () {
        Route::post('/guardar', [RepartoController::class, 'store'])->name('repartos.store');
        Route::post('/v2/guardar', [Reparto2Controller::class, 'store'])->name('repartos2.store');
    });
});


// MÓDULO: REPARTOS Y FINANZAS (UTILIDADES)
Route::middleware(['permiso:utilidades_ver'])->prefix('admon/finanzas')->group(function () {
    Route::get('/menu-repartos', [RepartoController::class, 'menu'])->name('menu');
    Route::get('/registro-repartos', [RepartoController::class, 'index'])->name('monto.index');
    Route::get('/registro-repartos-alias', [RepartoController::class, 'index'])->name('repartos.registro');
    Route::patch('/configurar-monto/{id}', [RepartoController::class, 'update'])->name('monto.update');

    Route::prefix('primer-reparto')->group(function () {
        Route::get('/', [RepartoController::class, 'mostrarPrimerReparto'])->name('reparto.primer');
        Route::get('/alias', [RepartoController::class, 'mostrarPrimerReparto'])->name('repartos.primero');
        Route::get('/pdf', [RepartoController::class, 'generarPDF'])->name('reparto.primer.pdf');
        Route::get('/obtener-fecha', [RepartoController::class, 'obtenerFechaLimite'])->name('reparto.primer.obtenerFecha');
        Route::post('/fijar-fecha', [RepartoController::class, 'fijarFechaLimite'])->name('reparto.primer.fijarFecha');
        Route::get('/buscar-ejidatario', [RepartoController::class, 'buscarEjidatario'])->name('ejidatarios.buscar_primer');
        Route::get('/ejidatario/{id}/saldo', [RepartoController::class, 'obtenerSaldo'])->name('prestamo.saldo');
    });

    Route::prefix('prestamo')->group(function () {
        Route::post('/agregar', [RepartoController::class, 'agregarPrestamo'])->name('prestamo.agregar');
        Route::patch('/actualizar/{id}', [RepartoController::class, 'actualizarPrestamo'])->name('prestamo.actualizar');
        Route::delete('/{id}', [RepartoController::class, 'eliminarPrestamo'])->name('prestamo.eliminar');
        Route::post('/abonar/{id}', [RepartoController::class, 'agregarAbono'])->name('prestamo.abonar');
        Route::get('/{id}/ticket', [RepartoController::class, 'generarTicketPDF'])->name('prestamo.ticket');
        Route::get('/ticket/{id}', [RepartoController::class, 'generarTicket'])->name('prestamo.ticket.alias');
    });

    Route::prefix('segundo-reparto')->group(function () {
        Route::get('/', [Reparto2Controller::class, 'mostrarSegundoReparto'])->name('reparto.segundo');
        Route::get('/alias', [Reparto2Controller::class, 'mostrarSegundoReparto'])->name('repartos.segundo');
        Route::get('/pdf', [Reparto2Controller::class, 'generarPDF'])->name('reparto.segundo.pdf');
        Route::post('/fijar-fecha', [Reparto2Controller::class, 'fijarFechaLimite'])->name('reparto.segundo.fijarFecha');
        Route::get('/obtener-fecha', [Reparto2Controller::class, 'obtenerFechaLimite'])->name('reparto.segundo.obtenerFecha');

        Route::post('/posponer/{id}', [Reparto2Controller::class, 'posponerSiguienteAnio'])->name('reparto.segundo.posponer');
        Route::get('/ticket/{id}', [Reparto2Controller::class, 'generarTicketPDFSegundo'])->name('reparto.segundo.ticket');
    });

    Route::prefix('prestamo2')->group(function () {
        Route::post('/agregar', [Reparto2Controller::class, 'agregarPrestamo'])->name('prestamo2.agregar');
        Route::patch('/actualizar/{id}', [Reparto2Controller::class, 'actualizarPrestamo'])->name('prestamo2.actualizar');
        Route::delete('/eliminar/{id}', [Reparto2Controller::class, 'eliminarPrestamo'])->name('prestamo2.eliminar');

        Route::post('/abonar/{id}', [Reparto2Controller::class, 'abonarPrestamo'])->name('prestamo2.abonar');
    });

    Route::get('/ejidatarios/buscar', [Reparto2Controller::class, 'buscarEjidatarios'])->name('ejidatarios.buscar_segundo');
});

// RECURSOS AUTOMÁTICOS (EVENTOS, CATEGORÍAS Y MULTAS) ---
Route::resource('eventos', EventoController::class);
Route::resource('categorias', CategoriaEventoController::class);
Route::resource('multas', MultaController::class);


// MÓDULO: PASE DE LISTA Y ASISTENCIA
Route::prefix('asistencia')->group(function () {
    Route::get('/', [PaseListaController::class, 'index'])->name('asistencia.index');
    Route::delete('/asistencia/{id}', [PaseListaController::class, 'destroy'])->name('asistencia.destroy');
    Route::post('/registrar', [PaseListaController::class, 'registrarAsistencia'])->name('asistencia.registrar');
    Route::post('/marcar', [PaseListaController::class, 'marcarAsistencia'])->name('asistencia.marcar');
    Route::get('/exportar-excel/{id}', [PaseListaController::class, 'exportarExcel'])->name('asistencia.excel');
    Route::get('/exportar-pdf/{id}', [PaseListaController::class, 'exportarPdf'])->name('asistencia.pdf');
});

Route::post('/social/unlink/{provider}', [SocialController::class, 'unlink'])->name('social.unlink');

// RUTAS DE PRUEBAS
Route::get('/test-qr', function () {
    $ejidatarios = \App\Models\Ejidatario::with('usuario')->get();
    return view('test_qr', compact('ejidatarios'));
});
// NO BORRAR (NO SE QUE HACE PERO SI SE ELIMINA TRUENA)
Route::get('storage/perfiles/{filename}', function ($filename) {
    $path = 'perfiles/' . $filename;

    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }

    $file = Storage::disk('public')->get($path);
    $type = Storage::disk('public')->mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
});

Route::get('/debug-auth', function () {
    return response()->json(session()->all());
});