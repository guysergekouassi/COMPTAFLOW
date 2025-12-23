<?php

namespace App\Traits;

use App\Scopes\TenantScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

trait BelongsToTenant
{
    /**
     * Boot du trait pour ajouter le scope global et l'event de création.
     */
    protected static function bootBelongsToTenant()
    {
        // Ajout du Scope Global pour le filtrage automatique (Read/Update/Delete)
        static::addGlobalScope(new TenantScope);

        // Assignation automatique du company_id lors de la création
        static::creating(function ($model) {
            if (!$model->company_id) {
                $model->company_id = static::getTenantId();
            }
            
            // On peut aussi assigner l'user_id si absent
            if (property_exists($model, 'user_id') && !$model->user_id && Auth::check()) {
                $model->user_id = Auth::id();
            }
        });
    }

    /**
     * Récupère l'ID de l'entreprise active pour l'assignation.
     */
    protected static function getTenantId()
    {
        if (Session::has('current_company_id')) {
            return Session::get('current_company_id');
        }

        if (Auth::check()) {
            return Auth::user()->company_id;
        }

        return null;
    }

    /**
     * Permet de bypasser le scope pour les admin si nécessaire.
     */
    public function scopeWithoutTenant($query)
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }
}
