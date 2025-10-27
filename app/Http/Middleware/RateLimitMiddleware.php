<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $userId = $request->user() ? $request->user()->id : null;

        // Rate limiting par IP (100 requêtes par minute)
        if (!$this->checkRateLimit("ip:{$ip}:minute", 100, 60)) {
            Log::warning('Rate limit exceeded for IP', [
                'ip' => $ip,
                'limit' => '100 per minute'
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RATE_LIMIT_EXCEEDED',
                    'message' => 'Trop de requêtes. Veuillez réessayer plus tard.',
                    'details' => [
                        'limit' => '100 requêtes par minute',
                        'retry_after' => 60
                    ]
                ]
            ], 429);
        }

        // Rate limiting par utilisateur (10 requêtes par jour)
        if ($userId && !$this->checkRateLimit("user:{$userId}:day", 10, 86400)) {
            Log::warning('Rate limit exceeded for user', [
                'user_id' => $userId,
                'limit' => '10 per day'
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RATE_LIMIT_EXCEEDED',
                    'message' => 'Limite de requêtes journalière dépassée.',
                    'details' => [
                        'limit' => '10 requêtes par jour',
                        'retry_after' => 86400
                    ]
                ]
            ], 429);
        }

        $response = $next($request);

        // Ajouter les headers de rate limiting à la réponse
        $response->headers->set('X-RateLimit-IP-Limit', '100');
        $response->headers->set('X-RateLimit-IP-Remaining', $this->getRemainingRequests("ip:{$ip}:minute", 100));

        if ($userId) {
            $response->headers->set('X-RateLimit-User-Limit', '10');
            $response->headers->set('X-RateLimit-User-Remaining', $this->getRemainingRequests("user:{$userId}:day", 10));
        }

        return $response;
    }

    /**
     * Vérifier si la limite de taux n'est pas dépassée
     */
    private function checkRateLimit(string $key, int $maxRequests, int $decaySeconds): bool
    {
        $requests = Cache::get($key, 0);

        if ($requests >= $maxRequests) {
            return false;
        }

        // Incrémenter le compteur
        Cache::put($key, $requests + 1, $decaySeconds);

        return true;
    }

    /**
     * Obtenir le nombre de requêtes restantes
     */
    private function getRemainingRequests(string $key, int $maxRequests): int
    {
        $requests = Cache::get($key, 0);
        return max(0, $maxRequests - $requests);
    }
}
