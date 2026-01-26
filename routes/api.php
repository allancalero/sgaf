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
use App\Http\Controllers\Api\CatalogoActivosController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);


// Public route for QR verification
Route::get('/assets/verify/{codigo}', [AssetController::class, 'verifyByCodigo']);

Route::middleware(['auth:api_jwt', \App\Http\Middleware\AuditLogMiddleware::class])->group(function () {
    // Auth Management
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user()->load('roles', 'permissions'); // Load roles/permissions for frontend
    });

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Assets
    Route::get('/assets/quick-search', [AssetController::class, 'quickSearch']);
    Route::get('/assets', [AssetController::class, 'index']);
    Route::post('/assets', [AssetController::class, 'store']);
    Route::get('/assets/next-code/{clasificacionId}', [AssetController::class, 'getNextCode']);
    Route::get('/assets/clasificaciones', [AssetController::class, 'clasificaciones']);
    Route::get('/assets/fuentes', [AssetController::class, 'fuentes']);
    Route::get('/assets/tipos', [AssetController::class, 'tipos']);
    Route::get('/assets/proveedores', [AssetController::class, 'proveedores']);
    Route::get('/assets/cheques', [AssetController::class, 'cheques']);
    Route::get('/assets/{id}', [AssetController::class, 'show']);
    Route::put('/assets/{id}', [AssetController::class, 'update']);
    Route::delete('/assets/{id}', [AssetController::class, 'destroy']);
    Route::get('/assets/{id}/qr', [AssetController::class, 'generateQr']);
    Route::get('/assets/{id}/acta', [AssetController::class, 'downloadActa']);

    // Ubicaciones y Areas
    Route::get('/areas', [UbicacionController::class, 'areas']);
    Route::get('/areas/all', [UbicacionController::class, 'allAreas']);
    Route::post('/areas', [UbicacionController::class, 'storeArea']);
    Route::put('/areas/{id}', [UbicacionController::class, 'updateArea']);
    Route::delete('/areas/{id}', [UbicacionController::class, 'deleteArea']);
    Route::get('/ubicaciones', [UbicacionController::class, 'ubicaciones']);
    Route::get('/ubicaciones/all', [UbicacionController::class, 'allUbicaciones']);
    Route::post('/ubicaciones', [UbicacionController::class, 'storeUbicacion']);
    Route::put('/ubicaciones/{id}', [UbicacionController::class, 'updateUbicacion']);
    Route::delete('/ubicaciones/{id}', [UbicacionController::class, 'deleteUbicacion']);

    // Catálogos de Activos Fijo
    Route::get('/proveedores', [CatalogoActivosController::class, 'proveedores']);
    Route::post('/proveedores', [CatalogoActivosController::class, 'storeProveedor']);
    Route::put('/proveedores/{id}', [CatalogoActivosController::class, 'updateProveedor']);
    Route::delete('/proveedores/{id}', [CatalogoActivosController::class, 'deleteProveedor']);

    Route::get('/clasificaciones', [CatalogoActivosController::class, 'clasificaciones']);
    Route::post('/clasificaciones', [CatalogoActivosController::class, 'storeClasificacion']);
    Route::put('/clasificaciones/{id}', [CatalogoActivosController::class, 'updateClasificacion']);
    Route::delete('/clasificaciones/{id}', [CatalogoActivosController::class, 'deleteClasificacion']);
    Route::post('/clasificaciones/bulk-delete', [CatalogoActivosController::class, 'bulkDeleteClasificaciones']);

    Route::get('/fuentes', [CatalogoActivosController::class, 'fuentes']);
    Route::post('/fuentes', [CatalogoActivosController::class, 'storeFuente']);
    Route::put('/fuentes/{id}', [CatalogoActivosController::class, 'updateFuente']);
    Route::delete('/fuentes/{id}', [CatalogoActivosController::class, 'deleteFuente']);

    Route::get('/tipos', [CatalogoActivosController::class, 'tipos']);
    Route::post('/tipos', [CatalogoActivosController::class, 'storeTipo']);
    Route::put('/tipos/{id}', [CatalogoActivosController::class, 'updateTipo']);
    Route::delete('/tipos/{id}', [CatalogoActivosController::class, 'deleteTipo']);

    Route::get('/cheques', [CatalogoActivosController::class, 'cheques']);
    Route::post('/cheques', [CatalogoActivosController::class, 'storeCheque']);
    Route::put('/cheques/{id}', [CatalogoActivosController::class, 'updateCheque']);
    Route::delete('/cheques/{id}', [CatalogoActivosController::class, 'deleteCheque']);

    // Personal y Cargos
    Route::get('/personal', [PersonalController::class, 'index']);
    Route::get('/personal/all', [PersonalController::class, 'all']);
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
    Route::get('/reasignaciones/{id}/acta', [ReasignacionController::class, 'downloadActa']);

    // Reportes
    Route::get('/reportes/resumen', [ReporteController::class, 'resumen']);
    Route::get('/reportes/activos-por-area', [ReporteController::class, 'activosPorArea']);
    Route::get('/reportes/activos-por-estado', [ReporteController::class, 'activosPorEstado']);
    Route::get('/reportes/activos-por-clasificacion', [ReporteController::class, 'activosPorClasificacion']);
    Route::get('/reportes/{reportId}/download', [ReporteController::class, 'download']);

    // Depreciacion
    Route::get('/depreciacion', [DepreciacionController::class, 'index']);
    Route::get('/depreciacion/catalogo', [DepreciacionController::class, 'getCatalogo']);
    Route::get('/depreciacion/sin-configurar', [DepreciacionController::class, 'getSinConfigurar']);
    Route::post('/depreciacion/reset', [DepreciacionController::class, 'resetear']);
    Route::post('/depreciacion/limpiar-todo', [DepreciacionController::class, 'limpiarTodo']);
    Route::post('/depreciacion/procesar', [DepreciacionController::class, 'procesarMasivo']);
    Route::post('/depreciacion/{id}/configurar', [DepreciacionController::class, 'configurar']);
    Route::post('/depreciacion/{id}/calcular', [DepreciacionController::class, 'calcular']);

    // Trazabilidad
    Route::get('/trazabilidad', [TrazabilidadController::class, 'index']);
    Route::get('/trazabilidad/buscar-activo', [TrazabilidadController::class, 'buscarActivo']);

    // Usuarios (Admin Only for modifications)
    Route::get('/usuarios', [UsuarioController::class, 'index']); // Read-only might be allowed for others, or restrict all? Assuming read-only for CONSULTA.
    
    Route::middleware(['role:ADMIN'])->group(function () {
        Route::post('/usuarios', [UsuarioController::class, 'store']);
        Route::put('/usuarios/{id}', [UsuarioController::class, 'update']);
        Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy']);
        Route::post('/usuarios/{id}/reset-password', [UsuarioController::class, 'resetPassword']);
        
        // System wide sensitive actions
        Route::post('/sistema/respaldo', [SistemaController::class, 'generarRespaldo']);
        Route::delete('/sistema/respaldo/{filename}', [SistemaController::class, 'eliminarRespaldo']);
    });

    // Sistema
    Route::get('/sistema/auditoria', [SistemaController::class, 'auditoria']);
    Route::get('/sistema/respaldo', [SistemaController::class, 'respaldo']);
    Route::get('/sistema/respaldo/{filename}', [SistemaController::class, 'descargarRespaldo']);
    Route::get('/sistema/seguridad', [SistemaController::class, 'seguridad']);
});