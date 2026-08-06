<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Réserve une route aux ADMIN et SUPER ADMIN.
 *
 * À utiliser pour les écrans d'administration d'entreprise qui ne doivent
 * jamais être atteints par un COMPTABLE / USER, même par URL directe.
 *
 * Usage : ->middleware('admin')
 */
class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Non authentifié.'], 401)
                : redirect()->route('login');
        }

        // isAdmin() couvre déjà les rôles "admin" et "super_admin".
        if (!$user->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Accès réservé aux administrateurs.",
                ], 403);
            }

            abort(403, "Accès réservé aux administrateurs.");
        }

        return $next($request);
    }
}
