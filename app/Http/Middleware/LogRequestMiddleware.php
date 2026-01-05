<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\LogHelper;

/**
 * Middleware para registrar peticiones HTTP en el canal de log apropiado
 * 
 * Para activar este middleware:
 * 1. Registrar en app/Http/Kernel.php
 * 2. Aplicar a rutas específicas o grupos de rutas
 */
class LogRequestMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        // Determinar el canal según la ruta
        $channel = $this->getChannelFromRoute($request);
        
        // Log de la petición
        LogHelper::activity($channel, 'HTTP Request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'route' => $request->route()?->getName(),
        ]);
        
        $response = $next($request);
        
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        
        // Log de la respuesta
        LogHelper::activity($channel, 'HTTP Response', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status' => $response->status(),
            'duration_ms' => $duration,
        ]);
        
        // Log queries lentas
        if ($duration > 1000) { // Mayor a 1 segundo
            LogHelper::queries()->warning('Petición lenta detectada', [
                'url' => $request->fullUrl(),
                'duration_ms' => $duration,
            ]);
        }
        
        return $response;
    }
    
    /**
     * Determinar el canal de log según la ruta
     */
    private function getChannelFromRoute(Request $request): string
    {
        $path = $request->path();
        
        if (str_starts_with($path, 'activos')) {
            return 'activos';
        }
        
        if (str_starts_with($path, 'cheques')) {
            return 'cheques';
        }
        
        if (str_starts_with($path, 'reportes')) {
            return 'reportes';
        }
        
        if (str_starts_with($path, 'sistema') || str_starts_with($path, 'usuarios')) {
            return 'sistema';
        }
        
        // Catálogos (áreas, ubicaciones, tipos, cargos, etc.)
        if (preg_match('/\/(areas|ubicaciones|tipos-activo|cargos|clasificaciones|fuentes-financiamiento|responsables|proveedores|personal)/', $path)) {
            return 'catalogos';
        }
        
        return 'single'; // Canal por defecto
    }
}
