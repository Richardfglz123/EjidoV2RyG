<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::middleware('api')->group(function () {

    Route::get('/ping', function () {
        return response()->json(['ok' => true]);
    });

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/verify-code', [AuthController::class, 'verifyCode']);

});
