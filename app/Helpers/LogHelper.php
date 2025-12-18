<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

/**
 * Helper para simplificar el uso de canales de log organizados
 * 
 * Uso:
 * LogHelper::activos()->info('Activo creado', ['activo_id' => $id]);
 * LogHelper::catalogos()->warning('Catálogo duplicado');
 * LogHelper::sistema()->error('Error de permisos', $context);
 */
class LogHelper
{
    /**
     * Log para módulo de activos fijos
     */
    public static function activos(): LoggerInterface
    {
        return Log::channel('activos');
    }

    /**
     * Log para módulo de catálogos
     */
    public static function catalogos(): LoggerInterface
    {
        return Log::channel('catalogos');
    }

    /**
     * Log para módulo de cheques
     */
    public static function cheques(): LoggerInterface
    {
        return Log::channel('cheques');
    }

    /**
     * Log para módulo de sistema
     */
    public static function sistema(): LoggerInterface
    {
        return Log::channel('sistema');
    }

    /**
     * Log para reportes
     */
    public static function reportes(): LoggerInterface
    {
        return Log::channel('reportes');
    }

    /**
     * Log para errores generales
     */
    public static function errors(): LoggerInterface
    {
        return Log::channel('errors');
    }

    /**
     * Log para queries SQL
     */
    public static function queries(): LoggerInterface
    {
        return Log::channel('queries');
    }

    /**
     * Log con contexto del usuario actual
     */
    public static function withUser(string $channel = 'single'): LoggerInterface
    {
        $logger = Log::channel($channel);
        
        if (auth()->check()) {
            $logger->withContext([
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email,
            ]);
        }
        
        return $logger;
    }

    /**
     * Log de actividad importante con usuario y timestamp
     */
    public static function activity(string $channel, string $action, array $context = []): void
    {
        $logger = Log::channel($channel);
        
        $context = array_merge($context, [
            'action' => $action,
            'timestamp' => now()->toDateTimeString(),
            'ip' => request()->ip(),
        ]);
        
        if (auth()->check()) {
            $context['user_id'] = auth()->id();
            $context['user_email'] = auth()->user()->email;
        }
        
        $logger->info($action, $context);
    }
}
