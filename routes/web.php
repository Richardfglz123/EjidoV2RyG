<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\EjidatariosController;
use App\Http\Controllers\ReportesUController;
use App\Http\Controllers\ReportesEController;
use App\Http\Controllers\Auth\TwoFAController;
use App\Http\Middleware\CheckAuth;
use App\Http\Controllers\PerfilController;
/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS
|--------------------------------------------------------------------------
*/

// Login
Route::get('/login', function () {
    return view('cpanel.login.sesion');
})->name('login.form');

Route::post('/login', [UsuariosController::class, 'login'])->name('login');

// Root
Route::get('/', function () {
    return redirect()->route('login.form');
});

/*
|--------------------------------------------------------------------------
| 2FA
|--------------------------------------------------------------------------
*/

Route::get('/2fa', [TwoFAController::class, 'showForm'])->name('2fa.form');
Route::post('/2fa/check', [TwoFAController::class, 'check'])->name('2fa.check');

/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

Route::post('/logout', function () {
    \Log::info('Logout - Usuario: ' . session('nombre_completo'));
    session()->flush();
    return redirect()->route('login.form');
})->name('logout');

/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS
|--------------------------------------------------------------------------
*/

Route::middleware([CheckAuth::class])->group(function () {

    // Dashboard
    Route::get('/admon', function () {
        return view('cpanel.inicio', [
            'user_name'  => session('nombre_completo'),
            'user_email' => session('user_email'),
            'rol'        => session('rol'),
        ]);
    })->name('inicio');
    //Perfil
    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil.index');
    Route::put('/perfil', [PerfilController::class, 'update'])->name('perfil.update');

    // CRUD Usuarios
    Route::resource('admon/Usuarios', UsuariosController::class);

    // CRUD Ejidatarios
    Route::resource('admon/Ejidatarios', EjidatariosController::class);

    // Reportes
    Route::prefix('admon/reportes')->group(function () {

        // Usuarios
        Route::get('usuarios/pdf', [ReportesUController::class, 'GenerarPDF'])
            ->name('reportes.usuarios.pdf');

        Route::get('usuarios/excel', [ReportesUController::class, 'GenerarExcel'])
            ->name('reportes.usuarios.excel');

        // Ejidatarios
        Route::get('ejidatarios/pdf', [ReportesEController::class, 'GenerarPDF'])
            ->name('reportes.ejidatarios.pdf');

        Route::get('ejidatarios/excel', [ReportesEController::class, 'GenerarExcel'])
            ->name('reportes.ejidatarios.excel');
    });
});

Route::get('admon/usuarios/buscar', [EjidatariosController::class, 'buscarUsuarios'])->name('usuarios.buscar');


/*
|--------------------------------------------------------------------------
| DEBUG
|--------------------------------------------------------------------------
*/
Route::get('/debug-auth', function () {
    return response()->json(session()->all());
});
