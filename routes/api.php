<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BitacoraController;
use App\Http\Controllers\Api\PerfilController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\SeccionController;
use App\Http\Controllers\Api\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('seccion:productos')->prefix('productos')->group(function () {
        Route::get('/', [ProductoController::class, 'index']);
        Route::post('/', [ProductoController::class, 'store']);
        Route::get('/export/pdf', [ProductoController::class, 'exportPdf']);
        Route::get('/export/excel', [ProductoController::class, 'exportExcel']);
        Route::get('/{id}', [ProductoController::class, 'show']);
        Route::put('/{id}', [ProductoController::class, 'update']);
        Route::delete('/{id}', [ProductoController::class, 'destroy']);
    });

    Route::middleware('seccion:usuarios')->prefix('usuarios')->group(function () {
        Route::get('/', [UsuarioController::class, 'index']);
        Route::post('/', [UsuarioController::class, 'store']);
        Route::get('/export/pdf', [UsuarioController::class, 'exportPdf']);
        Route::get('/export/excel', [UsuarioController::class, 'exportExcel']);
        Route::get('/{id}', [UsuarioController::class, 'show']);
        Route::put('/{id}', [UsuarioController::class, 'update']);
        Route::delete('/{id}', [UsuarioController::class, 'destroy']);
    });

    Route::middleware('seccion:perfiles')->prefix('perfiles')->group(function () {
        Route::get('/', [PerfilController::class, 'index']);
        Route::post('/', [PerfilController::class, 'store']);
        Route::get('/export/pdf', [PerfilController::class, 'exportPdf']);
        Route::get('/export/excel', [PerfilController::class, 'exportExcel']);
        Route::get('/{id}', [PerfilController::class, 'show']);
        Route::put('/{id}', [PerfilController::class, 'update']);
        Route::delete('/{id}', [PerfilController::class, 'destroy']);
    });

    Route::middleware('seccion:secciones')->prefix('secciones')->group(function () {
        Route::get('/', [SeccionController::class, 'index']);
        Route::post('/', [SeccionController::class, 'store']);
        Route::get('/{id}', [SeccionController::class, 'show']);
        Route::put('/{id}', [SeccionController::class, 'update']);
        Route::delete('/{id}', [SeccionController::class, 'destroy']);
    });

    Route::middleware('seccion:bitacora')->prefix('bitacora')->group(function () {
        Route::get('/', [BitacoraController::class, 'index']);
        Route::get('/{id}', [BitacoraController::class, 'show']);
    });
});
