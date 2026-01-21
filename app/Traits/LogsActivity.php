<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    public static function bootLogsActivity()
    {
        static::created(function ($model) {
            $model->logActivity('CREATE', "Création de " . class_basename($model) . " #{$model->id}");
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            // Don't log if only timestamps changed
            unset($changes['updated_at']);
            if (empty($changes)) return;

            $model->logActivity('UPDATE', "Modification de " . class_basename($model) . " #{$model->id}", [
                'old' => array_intersect_key($model->getOriginal(), $changes),
                'new' => $changes
            ]);
        });

        static::deleted(function ($model) {
            $model->logActivity('DELETE', "Suppression de " . class_basename($model) . " #{$model->id}");
        });
    }

    public function logActivity($action, $description = null, $payload = null)
    {
        if (!Auth::check()) return;

        AuditLog::create([
            'user_id' => Auth::id(),
            'company_id' => session('current_company_id') ?? Auth::user()->company_id,
            'action' => $action,
            'model_type' => get_class($this),
            'model_id' => $this->id,
            'description' => $description,
            'payload' => $payload,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
