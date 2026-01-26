<?php

namespace App\Traits;

use App\Scopes\UserIsolationScope;
use Illuminate\Support\Facades\Auth;

trait BelongsToUser
{
    /**
     * Boot du trait pour ajouter le scope d'isolation par utilisateur.
     */
    protected static function bootBelongsToUser()
    {
        static::addGlobalScope(new UserIsolationScope);

        static::creating(function ($model) {
            if (property_exists($model, 'user_id') && !$model->user_id && Auth::check()) {
                $model->user_id = Auth::id();
            }
        });
    }
}
