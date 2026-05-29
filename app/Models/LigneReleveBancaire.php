<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LigneReleveBancaire extends Model
{
    protected $table = 'lignes_releve_bancaire';

    protected $fillable = [
        'rapprochement_id',
        'date_operation',
        'date_valeur',
        'libelle',
        'reference',
        'debit',
        'credit',
        'solde',
        'statut',
        'ordre',
    ];

    protected $casts = [
        'date_operation' => 'date',
        'date_valeur'    => 'date',
        'debit'          => 'decimal:2',
        'credit'         => 'decimal:2',
        'solde'          => 'decimal:2',
    ];

    // ── Relations ──────────────────────────────────────────────────────────

    public function rapprochement(): BelongsTo
    {
        return $this->belongsTo(RapprochementBancaire::class, 'rapprochement_id');
    }

    public function pointages(): HasMany
    {
        return $this->hasMany(PointageRapprochement::class, 'ligne_releve_id');
    }

    // ── Accesseurs ─────────────────────────────────────────────────────────

    /**
     * Montant net de la ligne (positif = crédit banque = entrée, négatif = débit banque = sortie)
     * Note : en banque, Crédit = argent entrant POUR nous (= Débit en comptabilité)
     */
    public function getMontantNetAttribute(): float
    {
        return (float) $this->credit - (float) $this->debit;
    }

    /**
     * Montant absolu pour la comparaison avec la comptabilité
     */
    public function getMontantAbsAttribute(): float
    {
        return (float) $this->credit > 0 ? (float) $this->credit : (float) $this->debit;
    }

    /**
     * Sens en comptabilité : si la banque crédite → notre compta débite (encaissement)
     *                        si la banque débite  → notre compta crédite (paiement)
     */
    public function getSensComptaAttribute(): string
    {
        return $this->credit > 0 ? 'debit' : 'credit';
    }
}
