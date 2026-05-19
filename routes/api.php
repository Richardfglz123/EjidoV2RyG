<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\EjidatariosController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\PaseListaController;

use App\Models\Evento;

// RUTAS PÚBLICAS
Route::get('/ping', function () { return response()->json(['ok' => true]); });
Route::post('/login', [ApiController::class, 'login']);
Route::post('/verifyCode', [ApiController::class, 'verifyCode']);
Route::get('/ejidatarios', [EjidatariosController::class, 'getEjidatariosApi']);
Route::get('/eventos', function () { return response()->json(\App\Models\Evento::all()); });
Route::post('/asistencia/registrar', [PaseListaController::class, 'marcarAsistencia']);

// RUTAS PROTEGIDAS (Requieren obligatoriamente el Token que guardamos en UserDefaults)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user-profile', [PerfilController::class, 'getPerfilApi']);
    Route::get('/usuarios', [UsuariosController::class, 'apiIndex']);
    Route::post('/usuarios', [UsuariosController::class, 'apiStore']);
    Route::put('/usuarios/{id}', [UsuariosController::class, 'apiUpdate']);
    Route::delete('/usuarios/{id}', [UsuariosController::class, 'apiDestroy']);
});