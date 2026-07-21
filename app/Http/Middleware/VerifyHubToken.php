<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * VerifyHubToken
 *
 * Middleware de sécurité : vérifie que la requête provient
 * bien du FlowHub via le header X-Hub-Token.
 *
 * Déclarer dans app/Http/Kernel.php (ou bootstrap/app.php Laravel 12) :
 *   'verify.hub.token' => \App\Http\Middleware\VerifyHubToken::class
 *
 * Configurer dans .env :
 *   HUB_SHARED_TOKEN=super_secret_123
 */
class VerifyHubToken
{
    public function handle(Request $request, Closure $next)
    {
        $expectedToken = config('services.flowhub.shared_token', env('HUB_SHARED_TOKEN', 'super_secret_123'));
        $providedToken = $request->header('X-Hub-Token');

        if (empty($providedToken) || $providedToken !== $expectedToken) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Accès non autorisé. Token Hub invalide.',
            ], 401);
        }

        return $next($request);
    }
}
