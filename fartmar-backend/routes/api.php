<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DispensacionController;
use App\Http\Controllers\Api\InventarioAlertaController;
use App\Http\Controllers\Api\InventarioController;
use App\Http\Controllers\Api\KardexController;
use App\Http\Controllers\Api\PrescripcionController;
use App\Http\Controllers\Api\TrasladoController;
use Illuminate\Support\Facades\Route;

// Rutas Públicas
Route::post('/login', [AuthController::class, 'login']);

// Rutas Protegidas por Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rutas de Prescripciones
    Route::get('/prescripciones/{id}', [PrescripcionController::class, 'show']);

    // Rutas de Inventario
    Route::get('/inventario', [InventarioController::class, 'index']);
    Route::get('/inventario/vencimientos', [InventarioController::class, 'alertasVencimiento']);

    // Rutas de Dispensación
    Route::post('/dispensaciones', [DispensacionController::class, 'store']);

    // Rutas de Traslados
    Route::get('/traslados', [TrasladoController::class, 'index']);
    Route::post('/traslados', [TrasladoController::class, 'store']);
    Route::post('/traslados/{id}/aprobar', [TrasladoController::class, 'aprobar']);
    Route::post('/traslados/{id}/despachar', [TrasladoController::class, 'despachar']);
    Route::post('/traslados/{id}/recibir', [TrasladoController::class, 'recibir']);

    // Rutas de Kardex
    Route::get('/kardex', [KardexController::class, 'index']);

    // Rutas de Alertas de Inventario
    Route::get('/inventario/alertas', [InventarioAlertaController::class, 'index']);
    Route::get('/inventario/lotes', [InventarioController::class, 'lotes']);
});
