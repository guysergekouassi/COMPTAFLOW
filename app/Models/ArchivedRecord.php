<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Une ligne = une donnée supprimée, conservée 30 jours avant purge.
 */
class ArchivedRecord extends Model
{
    use HasFactory;

    /** Durée de conservation, en jours. */
    public const RETENTION_JOURS = 30;

    protected $fillable = [
        'company_id', 'user_id', 'model_type', 'model_id',
        'label', 'data', 'batch_id', 'batch_size', 'ip_address',
        'deleted_at', 'expires_at',
    ];

    protected $casts = [
        'data'       => 'json',
        'deleted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /** Archives encore dans la fenêtre de conservation. */
    public function scopeEnConservation($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Archive une suppression. Utilisable pour une ligne isolée comme pour un
     * lot : passer le même $batchId à toutes les lignes d'un même lot.
     */
    public static function archiver(Model $model, ?string $batchId = null, ?int $batchSize = null): self
    {
        $donnees = $model->attributesToArray();
        unset($donnees['password'], $donnees['remember_token']);

        return self::create([
            'company_id' => $model->company_id ?? session('current_company_id', Auth::user()?->company_id),
            'user_id'    => Auth::id(),
            'model_type' => get_class($model),
            'model_id'   => $model->getKey(),
            'label'      => self::libelle($model),
            'data'       => $donnees,
            'batch_id'   => $batchId,
            'batch_size' => $batchSize ?? 1,
            'ip_address' => request()->ip(),
            'deleted_at' => now(),
            'expires_at' => now()->addDays(self::RETENTION_JOURS),
        ]);
    }

    /** Identifiant de lot à partager entre les lignes d'une même suppression groupée. */
    public static function nouveauLot(): string
    {
        return (string) Str::uuid();
    }

    /**
     * Description lisible de l'élément supprimé, selon son type.
     */
    public static function libelle(Model $model): string
    {
        $base = class_basename($model);

        return match ($base) {
            'EcritureComptable' => trim(sprintf(
                'Écriture %s du %s — %s (D %s / C %s)',
                $model->n_saisie ?? '#' . $model->getKey(),
                $model->date ? \Carbon\Carbon::parse($model->date)->format('d/m/Y') : '?',
                $model->description_operation ?? 'sans libellé',
                number_format((float) ($model->debit ?? 0), 0, ',', ' '),
                number_format((float) ($model->credit ?? 0), 0, ',', ' ')
            )),
            'PlanComptable'     => 'Compte ' . ($model->numero_de_compte ?? '') . ' — ' . ($model->intitule ?? ''),
            'PlanTiers'         => 'Tiers ' . ($model->numero_de_tiers ?? '') . ' — ' . ($model->intitule ?? ''),
            'CodeJournal'       => 'Journal ' . ($model->code_journal ?? '') . ' — ' . ($model->intitule ?? ''),
            'ExerciceComptable' => 'Exercice ' . ($model->intitule ?? '#' . $model->getKey()),
            'Company'           => 'Entreprise ' . ($model->company_name ?? '#' . $model->getKey()),
            'User'              => 'Utilisateur ' . trim(($model->name ?? '') . ' ' . ($model->last_name ?? '')),
            'Immobilisation'    => 'Immobilisation ' . ($model->libelle ?? '#' . $model->getKey()),
            default             => $base . ' #' . $model->getKey(),
        };
    }

    public function libelleModule(): string
    {
        $base = class_basename($this->model_type);
        return AuditLog::MODULES[$base] ?? $base;
    }

    /** Jours restants avant purge définitive. */
    public function joursRestants(): int
    {
        return $this->expires_at ? max(0, (int) now()->diffInDays($this->expires_at, false)) : 0;
    }
}
