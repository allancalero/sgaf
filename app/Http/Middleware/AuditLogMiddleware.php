<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AuditLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Initialize Audit Context
        app(\App\Services\AuditContext::class)->set([
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => session()->getId(),
        ]);

        $response = $next($request);

        // Log denied access (403)
        if ($response->getStatusCode() === 403) {
            // Keep existing simple log for denials or upgrade it later
            // For now, we focus on the Observer for data changes
        }

        return $response;
    }

    private function logAction(Request $request, string $actionType)
    {
        try {
            AuditLog::create([
                'user_id' => Auth::id(), // Can be null if not authenticated
                'action' => $actionType,
                'method' => $request->method(),
                'path' => $request->path(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'context' => json_encode([
                    'params' => $request->except(['password', 'password_confirmation']),
                    'status' => $actionType === 'ACCESO_DENEGADO' ? 403 : 200
                ]),
            ]);
        } catch (\Exception $e) {
            // Prevent audit logging failure from blocking the request, but log to file
            \Log::error('AuditLog Error: ' . $e->getMessage());
        }
    }
}
