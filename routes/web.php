<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;

/**
 * RUTAS DE REDIRECCIÓN A ANGULAR (FRONTEND)
 */

// Redirección inicial
Route::get('/', function () {
    return redirect('/SGAF2/#/dashboard');
});

// Redirección de login
Route::get('/login', function () {
    return redirect('/SGAF2/#/login');
})->name('login');

/**
 * RUTAS DE SERVICIOS BACKEND (NO INERTIA)
 * Estas rutas son llamadas por Angular o directamente para descargas.
 */

// Ruta pública para verificar activos (sin autenticación - acceso por QR)
Route::get('/verificar-activo/{codigo?}', function ($codigo = null) {
    return view('verificar-activo');
})->name('verificar.activo');

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Importación Excel
    Route::post('/import-inventory', [ImportController::class, 'store'])
        ->middleware('permission:activos.manage')
        ->name('import.inventory');

    // Exportaciones Activos (Directas)
    Route::get('/activos/reportes/inventario/export', [\App\Http\Controllers\ExportController::class, 'exportInventarioCsv'])
        ->middleware('permission:activos.view')
        ->name('activos.reportes.inventario.export');

    Route::get('/activos/reportes/inventario/pdf', [\App\Http\Controllers\ExportController::class, 'exportInventarioPdf'])
        ->middleware('permission:activos.view')
        ->name('activos.reportes.inventario.pdf');

    Route::get('/activos/{activo}/qr', [\App\Http\Controllers\ExportController::class, 'qr'])
        ->middleware('permission:activos.view')
        ->name('activos.qr');

    // Sistemas / Respaldos (Descargas directas si Angular no las maneja vía Blob)
    Route::get('/sistema/respaldo/descargar', [\App\Http\Controllers\ExportController::class, 'descargarRespaldo'])
        ->middleware('permission:respaldos.download')
        ->name('sistema.respaldo.descargar');

    // Perfil (Redirigir a Angular profile)
    Route::get('/profile', function() { return redirect('/SGAF2/#/perfil'); })->name('profile.edit');
});

// Auth Routes (Login/Logout logic typically doesn't need these files anymore if using API exclusively)
// require __DIR__.'/auth.php'; // Lo comentamos si Angular maneja todo el login vía API

/**
 * CATCH-ALL PARA ANGULAR (SPA)
 * Cualquier ruta que empiece con /SGAF2 será manejada por Angular.
 * Esto asegura que los refrescos de página (F5) funcionen.
 */
Route::any('/SGAF2/{any?}', function ($any = null) {
    if ($any) {
        $filePath = public_path("SGAF2/$any");
        if (file_exists($filePath)) {
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            $headers = [];
            if ($extension === 'js') {
                $headers['Content-Type'] = 'application/javascript';
            } elseif ($extension === 'css') {
                $headers['Content-Type'] = 'text/css';
            }
            return response()->file($filePath, $headers);
        }
    }

    $path = public_path('SGAF2/index.html');
    if (file_exists($path)) {
        return response()->file($path, [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
    abort(404);
})->where('any', '.*');

// Redirección de cualquier otra ruta vieja a la raíz de Angular
// Excluye rutas /api que son manejadas por api.php
Route::fallback(function () {
    $path = request()->path();
    if (str_starts_with($path, 'api')) {
        return response()->json(['error' => 'Endpoint not found'], 404);
    }
    return redirect('/SGAF2/');
});
