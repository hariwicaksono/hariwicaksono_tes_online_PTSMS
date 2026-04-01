<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJwtHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Jika token tidak terdeteksi, tapi Authorization tersedia di $_SERVER
        if (!$request->headers->has('Authorization') && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $request->headers->set('Authorization', $_SERVER['HTTP_AUTHORIZATION']);
        }
        
        return $next($request);
    }
}
