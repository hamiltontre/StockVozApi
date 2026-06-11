<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\ReporteController;
use App\Http\Controllers\Api\SyncController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API StockVoz
|--------------------------------------------------------------------------
| Todas las rutas tienen prefijo /api automáticamente.
| Endpoints públicos: register, login, health.
| Resto requiere autenticación Sanctum (Bearer token).
*/

// Health check — útil para que la app móvil sepa si el backend está vivo
Route::get('/health', fn () => response()->json([
    'ok' => true,
    'servicio' => 'StockVoz API',
    'version' => '1.0.0',
    'hora' => now()->toIso8601String(),
]));

// Autenticación pública
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login',    [AuthController::class, 'login']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    // Sesión
    Route::get('/auth/me',      [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Sincronización desde la app
    Route::post('/sync', [SyncController::class, 'sync']);

    // Productos (lectura desde dashboard)
    Route::get('/productos',             [ProductoController::class, 'index']);
    Route::get('/productos/stock-bajo',  [ProductoController::class, 'stockBajo']);
    Route::get('/productos/{id}',        [ProductoController::class, 'show']);

    // Reportes
    Route::get('/reportes/resumen',         [ReporteController::class, 'resumen']);
    Route::get('/reportes/ventas-por-dia',  [ReporteController::class, 'ventasPorDia']);
    Route::get('/reportes/top-productos',   [ReporteController::class, 'topProductos']);
    Route::get('/reportes/alertas',         [ReporteController::class, 'alertas']);
});
