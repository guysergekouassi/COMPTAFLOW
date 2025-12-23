<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToTenant;

class CompteTresorerie extends Model
{
    use HasFactory, BelongsToTenant;


    protected $fillable = [
        'name',
        'type',
        'solde_initial',
        'solde_actuel', // Maintenu par les mouvements
        'plan_comptable_id',
        'company_id',
    ];


    protected $casts = [
        'solde_initial' => 'decimal:2',
        'solde_actuel' => 'decimal:2',
    ];


    public function mouvements(): HasMany
    {
        // On ordonne par date de mouvement pour l'affichage du journal
        return $this->hasMany(MouvementTresorerie::class)->orderBy('date_mouvement', 'asc');
    }


    public function getStatutAttribute(): string
    {
        return $this->solde_actuel >= 0 ? 'Créditeur' : 'Débiteur';
    }

    public function compteComptable(): BelongsTo // Nom de la relation
    {
        // La clé plan_comptable_id est utilisée par défaut si on suit les conventions de nommage
        return $this->belongsTo(PlanComptable::class, 'plan_comptable_id');
    }
}
