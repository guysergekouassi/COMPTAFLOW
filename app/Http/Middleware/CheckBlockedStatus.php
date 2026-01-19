<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckBlockedStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // Ne pas vérifier pour les super admins
        if ($user && $user->role === 'super_admin') {
            return $next($request);
        }
        
        // Vérifier si l'utilisateur est bloqué
        if ($user && $user->is_blocked) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', "Votre compte a été bloqué. Raison : {$user->block_reason}");
        }
        
        // Vérifier si l'entreprise est bloquée
        if ($user && $user->company && $user->company->is_blocked) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', "L'accès à votre entreprise a été bloqué. Raison : {$user->company->block_reason}");
        }
        
        return $next($request);
    }
}
