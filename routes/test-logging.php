<?php

use Illuminate\Support\Facades\Route;
use App\Helpers\LogHelper;

// Ruta de prueba para el sistema de logging
Route::get('/test-logging', function () {
    
    // Probar cada canal de log
    LogHelper::activos()->info('✅ Test log de activos', [
        'test' => true,
        'timestamp' => now()->toDateTimeString(),
    ]);
    
    LogHelper::catalogos()->info('✅ Test log de catálogos', [
        'test' => true,
        'timestamp' => now()->toDateTimeString(),
    ]);
    
    LogHelper::cheques()->info('✅ Test log de cheques', [
        'test' => true,
        'timestamp' => now()->toDateTimeString(),
    ]);
    
    LogHelper::sistema()->info('✅ Test log de sistema', [
        'test' => true,
        'timestamp' => now()->toDateTimeString(),
    ]);
    
    LogHelper::reportes()->info('✅ Test log de reportes', [
        'test' => true,
        'timestamp' => now()->toDateTimeString(),
    ]);
    
    LogHelper::errors()->error('✅ Test log de errores', [
        'test' => true,
        'timestamp' => now()->toDateTimeString(),
    ]);
    
    LogHelper::queries()->debug('✅ Test log de queries', [
        'test' => true,
        'sql' => 'SELECT * FROM test',
        'timestamp' => now()->toDateTimeString(),
    ]);
    
    // Test con usuario (si hay autenticación)
    if (auth()->check()) {
        LogHelper::withUser('sistema')->info('✅ Test log con usuario', [
            'test' => true,
        ]);
    }
    
    // Test de actividad
    LogHelper::activity('sistema', 'Test de logging completado', [
        'canales_probados' => 7,
    ]);
    
    return response()->json([
        'success' => true,
        'message' => 'Logs de prueba generados exitosamente',
        'instrucciones' => 'Revisa las carpetas en storage/logs/',
        'carpetas' => [
            'storage/logs/activos/',
            'storage/logs/catalogos/',
            'storage/logs/cheques/',
            'storage/logs/sistema/',
            'storage/logs/reportes/',
            'storage/logs/errors/',
            'storage/logs/queries/',
        ],
    ]);
});
