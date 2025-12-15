<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MouvementTresorerie extends Model
{
    use HasFactory;

    /**
     * Les attributs qui peuvent être massivement assignés.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'compte_tresorerie_id',
        'date_mouvement',
        'reference_piece',
        'libelle',
        'montant_debit',
        'montant_credit',
    ];

    /**
     * Les attributs qui doivent être castés en types natifs.
     *
     * @var array
     */
    protected $casts = [
        'date_mouvement' => 'date',
        'montant_debit' => 'decimal:2',
        'montant_credit' => 'decimal:2',
    ];

    public function compte(): BelongsTo
    {
        return $this->belongsTo(CompteTresorerie::class, 'compte_tresorerie_id');
    }

    
    public function getTypeOperationAttribute(): string
    {
        if ($this->montant_credit > 0) {
            return 'Encaissement (Crédit)';
        } elseif ($this->montant_debit > 0) {
            return 'Décaissement (Débit)';
        }
        return 'Neutre';
    }
}
