<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserActivity
{
    /**
     * Gère une requête entrante.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifie si un utilisateur est actuellement connecté
        if (Auth::check()) {
            $user = Auth::user();

            // Vérifie le statut is_active
            if (!$user->is_active) {
                // 1. Déconnecter l'utilisateur
                Auth::logout();

                // 2. Invalider la session
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // 3. Rediriger vers la page de connexion avec un message d'erreur
                return redirect('/login')->withErrors([
                    'email_adresse' => 'Votre compte a été désactivé.'
                ]);
            }
        }

        return $next($request);
    }
}
