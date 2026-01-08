<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\UbicacionController;
use App\Http\Controllers\Api\PersonalController;
use App\Http\Controllers\Api\ReasignacionController;
use App\Http\Controllers\Api\ReporteController;
use App\Http\Controllers\Api\DepreciacionController;
use App\Http\Controllers\Api\TrazabilidadController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\SistemaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Assets
    Route::get('/assets', [AssetController::class, 'index']);
    Route::post('/assets', [AssetController::class, 'store']);
    Route::get('/assets/clasificaciones', [AssetController::class, 'clasificaciones']);
    Route::get('/assets/fuentes', [AssetController::class, 'fuentes']);
    Route::get('/assets/tipos', [AssetController::class, 'tipos']);
    Route::get('/assets/proveedores', [AssetController::class, 'proveedores']);
    Route::get('/assets/cheques', [AssetController::class, 'cheques']);
    Route::get('/assets/{id}', [AssetController::class, 'show']);
    Route::put('/assets/{id}', [AssetController::class, 'update']);
    Route::delete('/assets/{id}', [AssetController::class, 'destroy']);

    // Ubicaciones y Areas
    Route::get('/areas', [UbicacionController::class, 'areas']);
    Route::post('/areas', [UbicacionController::class, 'storeArea']);
    Route::put('/areas/{id}', [UbicacionController::class, 'updateArea']);
    Route::delete('/areas/{id}', [UbicacionController::class, 'deleteArea']);
    Route::get('/ubicaciones', [UbicacionController::class, 'ubicaciones']);
    Route::post('/ubicaciones', [UbicacionController::class, 'storeUbicacion']);
    Route::put('/ubicaciones/{id}', [UbicacionController::class, 'updateUbicacion']);
    Route::delete('/ubicaciones/{id}', [UbicacionController::class, 'deleteUbicacion']);

    // Personal y Cargos
    Route::get('/personal', [PersonalController::class, 'index']);
    Route::post('/personal', [PersonalController::class, 'store']);
    Route::put('/personal/{id}', [PersonalController::class, 'update']);
    Route::delete('/personal/{id}', [PersonalController::class, 'destroy']);
    Route::get('/cargos', [PersonalController::class, 'cargos']);
    Route::get('/cargos/all', [PersonalController::class, 'allCargos']);
    Route::post('/cargos', [PersonalController::class, 'storeCargo']);
    Route::put('/cargos/{id}', [PersonalController::class, 'updateCargo']);
    Route::delete('/cargos/{id}', [PersonalController::class, 'destroyCargo']);

    // Reasignaciones
    Route::get('/reasignaciones', [ReasignacionController::class, 'index']);
    Route::post('/reasignaciones', [ReasignacionController::class, 'store']);

    // Reportes
    Route::get('/reportes/resumen', [ReporteController::class, 'resumen']);
    Route::get('/reportes/activos-por-area', [ReporteController::class, 'activosPorArea']);
    Route::get('/reportes/activos-por-estado', [ReporteController::class, 'activosPorEstado']);
    Route::get('/reportes/activos-por-clasificacion', [ReporteController::class, 'activosPorClasificacion']);

    // Depreciacion
    Route::get('/depreciacion', [DepreciacionController::class, 'index']);
    Route::post('/depreciacion/{id}/calcular', [DepreciacionController::class, 'calcular']);

    // Trazabilidad
    Route::get('/trazabilidad', [TrazabilidadController::class, 'index']);
    Route::get('/trazabilidad/buscar-activo', [TrazabilidadController::class, 'buscarActivo']);

    // Usuarios
    Route::get('/usuarios', [UsuarioController::class, 'index']);
    Route::post('/usuarios', [UsuarioController::class, 'store']);
    Route::put('/usuarios/{id}', [UsuarioController::class, 'update']);
    Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy']);

    // Sistema
    Route::get('/sistema/auditoria', [SistemaController::class, 'auditoria']);
    Route::get('/sistema/respaldo', [SistemaController::class, 'respaldo']);
    Route::get('/sistema/seguridad', [SistemaController::class, 'seguridad']);

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});