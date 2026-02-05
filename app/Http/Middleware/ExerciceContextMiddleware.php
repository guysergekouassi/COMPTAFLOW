<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Models\ExerciceComptable;

class ExerciceContextMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Gère le contexte d'exercice comptable pour l'utilisateur connecté.
     * Si un exercice est sélectionné en session, il devient le contexte actif.
     * Sinon, utilise l'exercice actif par défaut de l'entreprise.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        
        if (!$user) {
            return $next($request);
        }

        // Récupérer l'ID de la société (peut être switchée)
        $companyId = session('current_company_id', $user->company_id);

        if (!$companyId) {
            return $next($request);
        }

        // Vérifier si un exercice spécifique est sélectionné en session
        $exerciceContextId = session('current_exercice_id');
        
        $exerciceActif = null;

        if ($exerciceContextId) {
            // Utiliser l'exercice sélectionné par l'utilisateur
            $exerciceActif = ExerciceComptable::where('id', $exerciceContextId)
                ->where('company_id', $companyId)
                ->first();
            
            // Si l'exercice n'existe plus ou n'appartient pas à l'entreprise, le retirer de la session
            if (!$exerciceActif) {
                session()->forget('current_exercice_id');
                $exerciceContextId = null;
            }
        }

        // Si aucun exercice en contexte, utiliser l'exercice actif par défaut
        if (!$exerciceActif) {
            $exerciceActif = ExerciceComptable::where('company_id', $companyId)
                ->where('is_active', 1)
                ->first();
            
            // Si aucun exercice actif, prendre le plus récent
            if (!$exerciceActif) {
                $exerciceActif = ExerciceComptable::where('company_id', $companyId)
                    ->orderBy('date_debut', 'desc')
                    ->first();
            }
        }

        // Partager l'exercice actif avec toutes les vues
        if ($exerciceActif) {
            View::share('exerciceActif', $exerciceActif);
            View::share('exerciceEnContexte', $exerciceContextId ? true : false);
            
            // Stocker également en session pour une utilisation dans les contrôleurs
            session(['exercice_actif_id' => $exerciceActif->id]);
        }

        return $next($request);
    }
}
