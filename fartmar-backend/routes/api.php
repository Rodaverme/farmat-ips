<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InventarioController;
use Illuminate\Support\Facades\Route;

// Rutas Públicas
Route::post('/login', [AuthController::class, 'login']);

// Rutas Protegidas por Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rutas de Inventario
    Route::get('/inventario', [InventarioController::class, 'index']);
    Route::get('/inventario/vencimientos', [InventarioController::class, 'alertasVencimiento']);

});
