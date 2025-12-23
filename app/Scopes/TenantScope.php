<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class TenantScope implements Scope
{
    /**
     * Appliquer le scope à une requête Eloquent donnée.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $companyId = $this->getTenantId();

        if ($companyId) {
            $builder->where($model->getTable() . '.company_id', $companyId);
        }
    }

    /**
     * Récupère l'ID de l'entreprise active.
     */
    protected function getTenantId()
    {
        // 1. Privilégier la session (changement de contexte)
        if (Session::has('current_company_id')) {
            return Session::get('current_company_id');
        }

        // 2. Repli sur la compagnie de l'utilisateur connecté
        if (Auth::check()) {
            return Auth::user()->company_id;
        }

        return null;
    }
}
