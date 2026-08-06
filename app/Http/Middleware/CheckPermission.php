<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contrôle d'accès par habilitation.
 *
 * Jusqu'ici, les habilitations n'étaient vérifiées QUE dans les vues (sidebar),
 * ce qui masquait les liens mais laissait les URL `/admin/...` accessibles à
 * n'importe quel utilisateur connecté (un comptable pouvait ouvrir
 * /admin/habilitations et modifier des droits). Ce middleware applique la même
 * règle côté serveur.
 *
 * Usage : ->middleware('permission:admin.config.hub')
 *         ->middleware('permission:admin.config.hub,admin.config.plan_comptable')
 *         (accès accordé si l'utilisateur possède AU MOINS UNE des clés)
 *
 * Les clés correspondent à celles de config/accounting_permissions.php.
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Non authentifié.'], 401)
                : redirect()->route('login');
        }

        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => "Vous n'avez pas l'habilitation requise pour cette action.",
            ], 403);
        }

        abort(403, "Vous n'avez pas l'habilitation requise pour accéder à cette page.");
    }
}
