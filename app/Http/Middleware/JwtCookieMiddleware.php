<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cookie;

class JwtCookieMiddleware
{
    /**
     * Handle an incoming request.
     * Manually extracts the encrypted JWT from the cookie and injects it as a Bearer token.
     * This bridges the gap between Laravel's EncryptCookies and jwt-auth package.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If the request has the cookie but no Authorization header
        if ($request->hasCookie('jwt_token') && !$request->hasHeader('Authorization')) {
            try {
                // Laravel's 'EncryptCookies' middleware runs before this, 
                // so $request->cookie('jwt_token') should already be decrypted if valid.
                $token = $request->cookie('jwt_token'); 

                if ($token) {
                    $request->headers->set('Authorization', 'Bearer ' . $token);
                }
            } catch (\Exception $e) {
                // Silectly fail if cookie is invalid, let main Auth guard handle 401
            }
        }

        return $next($request);
    }
}
