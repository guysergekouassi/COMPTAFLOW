<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Réserve une route aux SUPER ADMIN (primaires et secondaires).
 *
 * Remplace les anciennes classes `SuperAdminMiddleware` et
 * `authSuperAdminMiddleware`, qui étaient strictement identiques.
 *
 * Usage : ->middleware('superadmin')
 */
class EnsureIsSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Non authentifié.'], 401)
                : redirect()->route('login');
        }

        if (!$user->isSuperAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Accès non autorisé. Vous devez être un Super Admin.',
                ], 403);
            }

            abort(403, 'Accès non autorisé. Vous devez être un Super Admin.');
        }

        return $next($request);
    }
}
