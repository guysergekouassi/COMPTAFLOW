<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class UserIsolationScope implements Scope
{
    /**
     * Appliquer le scope à une requête Eloquent donnée.
     */
    public function apply(Builder $builder, Model $model)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Si l'utilisateur n'est pas Admin ou SuperAdmin, on applique les restrictions
            if (!$user->isSuperAdmin() && !$user->isAdmin()) {
                
                // 1. Isolation par utilisateur pour les écritures et rapports (Grand Livre, Balance, etc.)
                // On exclut les modèles de configuration qui doivent être partagés au sein de l'entreprise
                $isSharedModel = ($model instanceof \App\Models\ExerciceComptable) || 
                                 ($model instanceof \App\Models\JournalSaisi) ||
                                 ($model instanceof \App\Models\CodeJournal) ||
                                 ($model instanceof \App\Models\tresoreries\Tresoreries) ||
                                 ($model instanceof \App\Models\PlanComptable) ||
                                 ($model instanceof \App\Models\PlanTiers);

                if (!$isSharedModel && $this->hasUserIdColumn($model)) {
                    $builder->where($model->getTable() . '.user_id', $user->id);
                }

                // 2. Filtre sur l'exercice en cours pour les exercices comptables
                if ($model instanceof \App\Models\ExerciceComptable) {
                    $builder->where($model->getTable() . '.cloturer', 0);
                }
            }
        }
    }

    /**
     * Vérifie si le modèle possède une colonne user_id
     */
    protected function hasUserIdColumn(Model $model)
    {
        // On pourrait utiliser Schema::hasColumn mais c'est coûteux en requêtes
        // Utilisons plutôt la propriété fillable ou un tableau prédéfini si nécessaire
        // Par sécurité, on regarde si user_id est dans fillable (cas général dans ce projet)
        return in_array('user_id', $model->getFillable());
    }
}
