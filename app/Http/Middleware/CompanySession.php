<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CompanySession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Si l'utilisateur est authentifié et que current_company_id n'est pas défini
        if (Auth::check() && !session('current_company_id')) {
            // Utiliser la company_id de l'utilisateur par défaut
            $user = Auth::user();
            if ($user && $user->company_id) {
                session(['current_company_id' => $user->company_id]);
            }
        }
        
        return $next($request);
    }
}
