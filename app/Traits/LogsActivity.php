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
            // Archive : le contenu supprime est conserve 30 jours avant purge.
            // Un lot en cours (voir ArchivedRecord::nouveauLot) regroupe les lignes.
            try {
                \App\Models\ArchivedRecord::archiver(
                    $model,
                    app()->bound('archive.batch_id') ? app('archive.batch_id') : null,
                    app()->bound('archive.batch_size') ? app('archive.batch_size') : null
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Archivage impossible : ' . $e->getMessage());
            }

            $model->logActivity(
                'DELETE',
                'Suppression de ' . \App\Models\ArchivedRecord::libelle($model),
                ['supprime' => $model->attributesToArray()]
            );
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
