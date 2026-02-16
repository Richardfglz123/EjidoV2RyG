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
// Modulos Ezequiel
use App\Http\Controllers\GastoController;
use App\Http\Controllers\ArticuloController;
use App\Http\Controllers\RecursosController;
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
                Route::get('/configuracion/usuarios/buscar-ajax', [ConfiguracionController::class, 'buscarUsuariosAjax'])->name('configuracion.usuarios.buscar_ajax');
            });
        });
    });

    Route::get('/debug-auth', function () {
        return response()->json(session()->all());
    });
});