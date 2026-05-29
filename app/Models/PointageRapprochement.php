<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointageRapprochement extends Model
{
    protected $table = 'pointages_rapprochement';

    protected $fillable = [
        'rapprochement_id',
        'ligne_releve_id',
        'ecriture_comptable_id',
        'type_pointage',
        'ecart',
        'note',
        'created_by',
    ];

    protected $casts = [
        'ecart' => 'decimal:2',
    ];

    // ── Relations ──────────────────────────────────────────────────────────

    public function rapprochement(): BelongsTo
    {
        return $this->belongsTo(RapprochementBancaire::class, 'rapprochement_id');
    }

    public function ligneReleve(): BelongsTo
    {
        return $this->belongsTo(LigneReleveBancaire::class, 'ligne_releve_id');
    }

    public function ecritureComptable(): BelongsTo
    {
        return $this->belongsTo(EcritureComptable::class, 'ecriture_comptable_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
