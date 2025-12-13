<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditLogger
{
    public function handle(Request $request, Closure $next, string $action = null): Response
    {
        $response = $next($request);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => $action ?: 'request',
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'context' => [
                'status' => $response->getStatusCode(),
            ],
        ]);

        return $response;
    }
}
