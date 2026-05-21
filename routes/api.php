<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\EjidatariosController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\PaseListaController;
use App\Models\Evento;

Route::get('/ping', function () {
    return response()->json(['ok' => true]);
});

//Acceso Móvil
Route::post('/login', [ApiController::class, 'login']);
Route::post('/verifyCode', [ApiController::class, 'verifyCode']);

Route::get('/user-profile', [PerfilController::class, 'getPerfilApi']);
Route::post('/user-profile/update', [PerfilController::class, 'updatePerfilApi']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/perfil/{id}', [PerfilController::class, 'show']);

    // Gestión de Usuarios del Sistema
    Route::get('/usuarios', [UsuariosController::class, 'apiIndex']);
    Route::post('/usuarios', [UsuariosController::class, 'apiStore']);
    Route::put('/usuarios/{id}', [UsuariosController::class, 'apiUpdate']);
    Route::delete('/usuarios/{id}', [UsuariosController::class, 'apiDestroy']);

    // Módulo de Ejidatarios
    Route::get('/ejidatarios', [EjidatariosController::class, 'getEjidatariosApi']);

    // Módulo de Eventos
    Route::get('/eventos', function () {
        return response()->json(Evento::all());
    });
    Route::post('/eventos/nuevo', [EventoController::class, 'storeApi']);

    // Asistencias y Pase de Lista desde Móvil
    Route::match(['get', 'post'], '/asistencia/registrar-movil', [PaseListaController::class, 'marcarAsistencia']);

});