<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
      // 1. Vérifie si l'utilisateur est authentifié
        if (!auth()->check()) {
            return redirect()->route('login');
        }

         // 2. Vérifie si l'utilisateur est un Super Admin
        // La méthode isSuperAdmin() est définie dans votre modèle App\Models\User.php
        if (auth()->user()->isSuperAdmin()) {
            return $next($request);
        }

           // 3. Si l'utilisateur n'est pas Super Admin, il est redirigé (ou reçoit une 403)
        // Nous allons utiliser une réponse 403 - Accès refusé.
        abort(403, 'Accès non autorisé. Vous devez être un Super Admin.');
    }
}
