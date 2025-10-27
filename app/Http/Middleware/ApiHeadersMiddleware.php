<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ApiHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Générer un ID de requête unique si non fourni
        $requestId = $request->header('X-Request-ID', Str::uuid()->toString());

        // Ajouter les headers standards
        $response->headers->set('X-Request-ID', $requestId);
        $response->headers->set('X-API-Version', 'v1');
        $response->headers->set('X-Powered-By', 'Laravel-Banque-API');
        $response->headers->set('X-Response-Time', now()->toISOString());

        // Headers de cache pour les API
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        // Headers de sécurité
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        return $response;
    }
}
