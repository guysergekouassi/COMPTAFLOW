<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToTenant;

class RapprochementBancaire extends Model
{
    use BelongsToTenant;

    protected $table = 'rapprochements_bancaires';

    protected $fillable = [
        'company_id',
        'compte_tresorerie_id',
        'exercice_id',
        'code_journal_id',
        'date_debut',
        'date_fin',
        'solde_initial_banque',
        'solde_final_banque',
        'solde_initial_compta',
        'nom_fichier_releve',
        'statut',
        'note',
        'created_by',
    ];

    protected $casts = [
        'date_debut'           => 'date',
        'date_fin'             => 'date',
        'solde_initial_banque' => 'decimal:2',
        'solde_final_banque'   => 'decimal:2',
        'solde_initial_compta' => 'decimal:2',
    ];

    // ── Relations ──────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function compteTresorerie(): BelongsTo
    {
        return $this->belongsTo(CompteTresorerie::class, 'compte_tresorerie_id');
    }

    public function exercice(): BelongsTo
    {
        return $this->belongsTo(ExerciceComptable::class, 'exercice_id');
    }

    public function codeJournal(): BelongsTo
    {
        return $this->belongsTo(CodeJournal::class, 'code_journal_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lignesReleve(): HasMany
    {
        return $this->hasMany(LigneReleveBancaire::class, 'rapprochement_id')
                    ->orderBy('ordre');
    }

    public function pointages(): HasMany
    {
        return $this->hasMany(PointageRapprochement::class, 'rapprochement_id');
    }

    // ── Accesseurs utiles ──────────────────────────────────────────────────

    /** Nombre de lignes relevé pointées */
    public function getNbPointesAttribute(): int
    {
        return $this->lignesReleve()->where('statut', 'pointe')->count();
    }

    /** Nombre de lignes relevé non pointées */
    public function getNbNonPointesAttribute(): int
    {
        return $this->lignesReleve()->where('statut', 'non_pointe')->count();
    }

    /** Écart résiduel total = solde banque - solde compta (devrait être 0) */
    public function getEcartAttribute(): float
    {
        return (float) $this->solde_final_banque - (float) $this->solde_initial_compta;
    }
}
