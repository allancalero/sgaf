<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CatalogosController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\UbicacionController;
use App\Http\Controllers\ClasificacionController;
use App\Http\Controllers\FuenteFinanciamientoController;
use App\Http\Controllers\TipoActivoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\ResponsableController;
use App\Http\Controllers\ActivoFijoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SistemaController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\ChequeController;
use App\Http\Controllers\RecursosHumanosController;
use App\Http\Controllers\UbicacionesController;
use App\Http\Controllers\ActivosFijoController;
use App\Http\Controllers\ReasignacionController;
use App\Http\Controllers\MisActivosController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\ImportController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

// Import Inventory (Queued)
Route::post('/import-inventory', [ImportController::class, 'store'])
    ->middleware(['auth', 'verified', 'permission:activos.manage'])
    ->name('import.inventory');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/catalogos', [CatalogosController::class, 'index'])
    ->middleware(['auth', 'verified', 'permission:catalogos.manage'])
    ->name('catalogos.index');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/areas', [AreaController::class, 'index'])->middleware('permission:catalogos.manage')->name('areas.index');
    Route::post('/areas', [AreaController::class, 'store'])->middleware(['permission:catalogos.manage', 'audit:areas.store'])->name('areas.store');
    Route::put('/areas/{area}', [AreaController::class, 'update'])->middleware(['permission:catalogos.manage', 'audit:areas.update'])->name('areas.update');
    Route::delete('/areas/{area}', [AreaController::class, 'destroy'])->middleware(['permission:catalogos.manage', 'audit:areas.destroy'])->name('areas.destroy');

    Route::get('/cargos', [CargoController::class, 'index'])->middleware('permission:catalogos.manage')->name('cargos.index');
    Route::post('/cargos', [CargoController::class, 'store'])->middleware(['permission:catalogos.manage', 'audit:cargos.store'])->name('cargos.store');
    Route::put('/cargos/{cargo}', [CargoController::class, 'update'])->middleware(['permission:catalogos.manage', 'audit:cargos.update'])->name('cargos.update');
    Route::delete('/cargos/{cargo}', [CargoController::class, 'destroy'])->middleware(['permission:catalogos.manage', 'audit:cargos.destroy'])->name('cargos.destroy');

    Route::get('/ubicaciones', [UbicacionController::class, 'index'])->middleware('permission:catalogos.manage')->name('ubicaciones.index');
    Route::post('/ubicaciones', [UbicacionController::class, 'store'])->middleware(['permission:catalogos.manage', 'audit:ubicaciones.store'])->name('ubicaciones.store');
    Route::put('/ubicaciones/{ubicacion}', [UbicacionController::class, 'update'])->middleware(['permission:catalogos.manage', 'audit:ubicaciones.update'])->name('ubicaciones.update');
    Route::delete('/ubicaciones/{ubicacion}', [UbicacionController::class, 'destroy'])->middleware(['permission:catalogos.manage', 'audit:ubicaciones.destroy'])->name('ubicaciones.destroy');

    Route::get('/clasificaciones', [ClasificacionController::class, 'index'])->middleware('permission:catalogos.manage')->name('clasificaciones.index');
    Route::post('/clasificaciones', [ClasificacionController::class, 'store'])->middleware(['permission:catalogos.manage', 'audit:clasificaciones.store'])->name('clasificaciones.store');
    Route::put('/clasificaciones/{clasificacion}', [ClasificacionController::class, 'update'])->middleware(['permission:catalogos.manage', 'audit:clasificaciones.update'])->name('clasificaciones.update');
    Route::delete('/clasificaciones/{clasificacion}', [ClasificacionController::class, 'destroy'])->middleware(['permission:catalogos.manage', 'audit:clasificaciones.destroy'])->name('clasificaciones.destroy');
    // Classification Fields Configuration
    Route::get('/clasificaciones/{clasificacion}/fields', [App\Http\Controllers\ClassificationFieldController::class, 'index'])
        ->middleware('permission:catalogos.manage')
        ->name('clasificaciones.fields.index');
    Route::post('/clasificaciones/{clasificacion}/fields', [App\Http\Controllers\ClassificationFieldController::class, 'store'])
        ->middleware('permission:catalogos.manage')
        ->name('clasificaciones.fields.store');
    Route::put('/classification-fields/{field}', [App\Http\Controllers\ClassificationFieldController::class, 'update'])
        ->middleware('permission:catalogos.manage')
        ->name('classification.fields.update');
    Route::delete('/classification-fields/{field}', [App\Http\Controllers\ClassificationFieldController::class, 'destroy'])
        ->middleware('permission:catalogos.manage')
        ->name('classification.fields.destroy');
    
    // API endpoint for getting fields (without auth middleware for frontend)
    Route::get('/api/clasificaciones/{clasificacion}/fields', [App\Http\Controllers\ClassificationFieldController::class, 'getFields'])
        ->name('api.clasificaciones.fields');

    Route::get('/fuentes', [FuenteFinanciamientoController::class, 'index'])->middleware('permission:catalogos.manage')->name('fuentes.index');
    Route::post('/fuentes', [FuenteFinanciamientoController::class, 'store'])->middleware(['permission:catalogos.manage', 'audit:fuentes.store'])->name('fuentes.store');
    Route::put('/fuentes/{fuente}', [FuenteFinanciamientoController::class, 'update'])->middleware(['permission:catalogos.manage', 'audit:fuentes.update'])->name('fuentes.update');
    Route::delete('/fuentes/{fuente}', [FuenteFinanciamientoController::class, 'destroy'])->middleware(['permission:catalogos.manage', 'audit:fuentes.destroy'])->name('fuentes.destroy');

    Route::get('/tipos-activos', [TipoActivoController::class, 'index'])->middleware('permission:catalogos.manage')->name('tipos.index');
    Route::post('/tipos-activos', [TipoActivoController::class, 'store'])->middleware(['permission:catalogos.manage', 'audit:tipos.store'])->name('tipos.store');
    Route::put('/tipos-activos/{tipo}', [TipoActivoController::class, 'update'])->middleware(['permission:catalogos.manage', 'audit:tipos.update'])->name('tipos.update');
    Route::delete('/tipos-activos/{tipo}', [TipoActivoController::class, 'destroy'])->middleware(['permission:catalogos.manage', 'audit:tipos.destroy'])->name('tipos.destroy');

    Route::get('/proveedores', [ProveedorController::class, 'index'])->middleware('permission:catalogos.manage')->name('proveedores.index');
    Route::post('/proveedores', [ProveedorController::class, 'store'])->middleware(['permission:catalogos.manage', 'audit:proveedores.store'])->name('proveedores.store');
    Route::put('/proveedores/{proveedore}', [ProveedorController::class, 'update'])->middleware(['permission:catalogos.manage', 'audit:proveedores.update'])->name('proveedores.update');
    Route::delete('/proveedores/{proveedore}', [ProveedorController::class, 'destroy'])->middleware(['permission:catalogos.manage', 'audit:proveedores.destroy'])->name('proveedores.destroy');

    Route::get('/cheques', [ChequeController::class, 'index'])->middleware('permission:catalogos.manage')->name('cheques.index');
    Route::post('/cheques', [ChequeController::class, 'store'])->middleware(['permission:catalogos.manage', 'audit:cheques.store'])->name('cheques.store');
    Route::put('/cheques/{cheque}', [ChequeController::class, 'update'])->middleware(['permission:catalogos.manage', 'audit:cheques.update'])->name('cheques.update');
    Route::delete('/cheques/{cheque}', [ChequeController::class, 'destroy'])->middleware(['permission:catalogos.manage', 'audit:cheques.destroy'])->name('cheques.destroy');

    Route::get('/personal', [PersonalController::class, 'index'])->middleware('permission:catalogos.manage')->name('personal.index');
    Route::post('/personal', [PersonalController::class, 'store'])->middleware(['permission:catalogos.manage', 'audit:personal.store'])->name('personal.store');
    Route::put('/personal/{personal}', [PersonalController::class, 'update'])->middleware(['permission:catalogos.manage', 'audit:personal.update'])->name('personal.update');
    Route::delete('/personal/{personal}', [PersonalController::class, 'destroy'])->middleware(['permission:catalogos.manage', 'audit:personal.destroy'])->name('personal.destroy');

    Route::get('/responsables', [ResponsableController::class, 'index'])->middleware('permission:catalogos.manage')->name('responsables.index');
    Route::post('/responsables', [ResponsableController::class, 'store'])->middleware(['permission:catalogos.manage', 'audit:responsables.store'])->name('responsables.store');
    Route::put('/responsables/{responsable}', [ResponsableController::class, 'update'])->middleware(['permission:catalogos.manage', 'audit:responsables.update'])->name('responsables.update');
    Route::delete('/responsables/{responsable}', [ResponsableController::class, 'destroy'])->middleware(['permission:catalogos.manage', 'audit:responsables.destroy'])->name('responsables.destroy');

    // Activos Fijo - Vista unificada
    Route::get('/activos-fijo-vista', [ActivosFijoController::class, 'index'])->middleware('permission:catalogos.manage')->name('activos-fijo-vista.index');

    // Ubicación - Vista unificada
    Route::get('/ubicaciones-vista', [UbicacionesController::class, 'index'])->middleware('permission:catalogos.manage')->name('ubicaciones-vista.index');

    // Recursos Humanos - Vista unificada
    Route::get('/recursos-humanos', [RecursosHumanosController::class, 'index'])->middleware('permission:catalogos.manage')->name('recursos-humanos.index');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/sistema', [SistemaController::class, 'index'])->middleware('permission:sistema.manage')->name('sistema.index');
    Route::get('/sistema/respaldo', [SistemaController::class, 'respaldo'])->middleware('permission:respaldos.download')->name('sistema.respaldo');
    Route::get('/sistema/seguridad', [SistemaController::class, 'seguridad'])->middleware('permission:seguridad.manage')->name('sistema.seguridad');
    Route::get('/sistema/parametros', [SystemSettingController::class, 'index'])->middleware('permission:sistema.manage')->name('sistema.parametros');
    Route::put('/sistema/parametros', [SystemSettingController::class, 'update'])->middleware(['permission:sistema.manage', 'audit:sistema.parametros'])->name('sistema.parametros.update');
    Route::post('/sistema/seguridad/asignar-super', [SistemaController::class, 'asignarTodosLosRoles'])->middleware(['permission:seguridad.manage', 'audit:seguridad.super'])->name('sistema.seguridad.asignar-super');
    Route::put('/sistema/seguridad/{user}/accesos', [SistemaController::class, 'actualizarAccesos'])->middleware(['permission:seguridad.manage', 'audit:seguridad.update'])->name('sistema.seguridad.actualizar');
    Route::get('/sistema/respaldo/descargar', [SistemaController::class, 'descargarRespaldo'])->middleware(['permission:respaldos.download', 'audit:respaldos.download'])->name('sistema.respaldo.descargar');
    
    // Auditoría
    Route::get('/sistema/auditoria', [AuditController::class, 'index'])->middleware('permission:sistema.manage')->name('sistema.auditoria');
    
    // New backup management routes
    Route::post('/sistema/respaldo/crear', [SistemaController::class, 'crearRespaldo'])->middleware(['permission:respaldos.download', 'audit:respaldos.create'])->name('sistema.respaldo.crear');
    Route::get('/sistema/respaldo/{backup}/descargar-sql', [SistemaController::class, 'descargarRespaldoSQL'])->middleware(['permission:respaldos.download', 'audit:respaldos.download-sql'])->name('sistema.respaldo.descargar-sql');
    Route::post('/sistema/respaldo/{backup}/restaurar', [SistemaController::class, 'restaurarRespaldo'])->middleware(['permission:respaldos.download', 'audit:respaldos.restore'])->name('sistema.respaldo.restaurar');
    Route::delete('/sistema/respaldo/{backup}', [SistemaController::class, 'eliminarRespaldo'])->middleware(['permission:respaldos.download', 'audit:respaldos.delete'])->name('sistema.respaldo.eliminar');
    
    // Reasignaciones de Activos
    Route::resource('reasignaciones', ReasignacionController::class)
        ->parameters(['reasignaciones' => 'reasignacion'])
        ->middleware('permission:activos.manage');
    // Mis Activos - Delegación de activos
    Route::get('/mis-activos', [MisActivosController::class, 'index'])->name('mis-activos.index');
    Route::post('/mis-activos/delegar', [MisActivosController::class, 'delegar'])->name('mis-activos.delegar');
    Route::get('/reasignaciones/{reasignacion}/acta-pdf', [ReasignacionController::class, 'generarActaPdf'])
        ->middleware('permission:activos.view')
        ->name('reasignaciones.acta-pdf');

    Route::get('/activos/resumen', function () {
        $stats = [
            'areas' => DB::table('areas')->count(),
            'ubicaciones' => DB::table('ubicaciones')->count(),
            'personal' => DB::table('personal')->count(),
            'activos' => DB::table('activos_fijos')->count(),
            'cheques' => DB::table('cheques')->count(),
        ];

        return Inertia::render('Activos/Resumen', [
            'stats' => $stats,
        ]);
    })->middleware('permission:activos.view')->name('activos.resumen');

    Route::get('/activos/trazabilidad', [ActivoFijoController::class, 'trazabilidad'])
        ->middleware('permission:activos.view')
        ->name('activos.trazabilidad');

    Route::get('/activos/reportes', [ActivoFijoController::class, 'reportes'])
        ->middleware('permission:activos.view')
        ->name('activos.reportes');

    Route::get('/activos/reportes/inventario/export', [ActivoFijoController::class, 'exportInventarioCsv'])
        ->middleware('permission:activos.view')
        ->name('activos.reportes.inventario.export');

    Route::get('/activos/reportes/trazabilidad/export', [ActivoFijoController::class, 'exportTrazabilidadCsv'])
        ->middleware('permission:activos.view')
        ->name('activos.reportes.trazabilidad.export');

    Route::get('/activos/reportes/inventario/pdf', [ActivoFijoController::class, 'exportInventarioPdf'])
        ->middleware('permission:activos.view')
        ->name('activos.reportes.inventario.pdf');

    Route::get('/activos/reportes/trazabilidad/pdf', [ActivoFijoController::class, 'exportTrazabilidadPdf'])
        ->middleware('permission:activos.view')
        ->name('activos.reportes.trazabilidad.pdf');

    Route::get('/activos/etiquetas-qr', [ActivoFijoController::class, 'etiquetasQr'])
        ->middleware('permission:activos.view')
        ->name('activos.etiquetas-qr');

    Route::get('/activos/etiquetas-qr/pdf', [ActivoFijoController::class, 'generarEtiquetasQrPdf'])
        ->middleware('permission:activos.view')
        ->name('activos.etiquetas-qr.pdf');

    Route::post('/activos/{activo}/trazabilidad', [ActivoFijoController::class, 'actualizarTrazabilidad'])
        ->middleware(['permission:activos.manage', 'audit:activos.trazabilidad'])
        ->name('activos.trazabilidad.actualizar');

    // Depreciación de Activos
    Route::get('/activos/depreciacion', [\App\Http\Controllers\DepreciacionController::class, 'index'])
        ->middleware('permission:activos.view')
        ->name('activos.depreciacion');
    
    Route::post('/activos/depreciacion/calcular', [\App\Http\Controllers\DepreciacionController::class, 'calcular'])
        ->middleware('permission:activos.manage')
        ->name('activos.depreciacion.calcular');
    
    Route::get('/activos/depreciacion/pdf', [\App\Http\Controllers\DepreciacionController::class, 'exportPdf'])
        ->middleware('permission:activos.view')
        ->name('activos.depreciacion.pdf');
    
    Route::put('/activos/{activo}/depreciacion-config', [\App\Http\Controllers\DepreciacionController::class, 'configurar'])
        ->middleware('permission:activos.manage')
        ->name('activos.depreciacion.configurar');
    
    Route::post('/activos/depreciacion/masivo', [\App\Http\Controllers\DepreciacionController::class, 'configurarMasivo'])
        ->middleware('permission:activos.manage')
        ->name('activos.depreciacion.masivo');

    // Acta de Asignación
    Route::get('/activos/{activo}/acta-asignacion', [ActivoFijoController::class, 'generarActaAsignacion'])
        ->middleware('permission:activos.view')
        ->name('activos.acta-asignacion');

    // Búsqueda Rápida
    Route::get('/activos/busqueda', [ActivoFijoController::class, 'busqueda'])
        ->middleware('permission:activos.view')
        ->name('activos.busqueda');

    Route::get('/activos', [ActivoFijoController::class, 'index'])->middleware('permission:activos.view')->name('activos.index');
    Route::post('/activos', [ActivoFijoController::class, 'store'])->middleware(['permission:activos.manage', 'audit:activos.store'])->name('activos.store');
    Route::put('/activos/{activo}', [ActivoFijoController::class, 'update'])->middleware(['permission:activos.manage', 'audit:activos.update'])->name('activos.update');
    Route::delete('/activos/{activo}', [ActivoFijoController::class, 'destroy'])->middleware(['permission:activos.manage', 'audit:activos.destroy'])->name('activos.destroy');
    Route::get('/activos/{activo}/qr', [ActivoFijoController::class, 'qr'])->middleware('permission:activos.view')->name('activos.qr');
    Route::get('/activos/{activo}', [ActivoFijoController::class, 'show'])->middleware('permission:activos.view')->name('activos.show');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Gestión de usuarios (solo administradores)
Route::middleware(['auth', 'verified', 'permission:usuarios.manage'])->group(function () {
    Route::get('/usuarios', [\App\Http\Controllers\UserController::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/crear', [\App\Http\Controllers\UserController::class, 'create'])->name('usuarios.create');
    Route::post('/usuarios', [\App\Http\Controllers\UserController::class, 'store'])->name('usuarios.store');
    Route::get('/usuarios/{usuario}/editar', [\App\Http\Controllers\UserController::class, 'edit'])->name('usuarios.edit');
    Route::put('/usuarios/{usuario}', [\App\Http\Controllers\UserController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{usuario}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('usuarios.destroy');
});
