<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\EjidatariosController;
use App\Http\Controllers\EventoController;
use App\Models\Evento;

// RUTAS PÚBLICAS (Sin autenticación para pruebas rápidas)
Route::get('/ping', function () {
    return response()->json(['ok' => true]);
});

Route::post('/login', [ApiController::class, 'login']);
Route::post('/verifyCode', [ApiController::class, 'verifyCode']);

// Esta es la que necesitamos para tu lista
Route::get('/ejidatarios', [EjidatariosController::class, 'getEjidatariosApi']);
Route::get('/eventos', function () {
    return response()->json(Evento::all());
});
// RUTAS PROTEGIDAS (Requieren Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user-profile', [PerfilController::class, 'getPerfilApi']);
});