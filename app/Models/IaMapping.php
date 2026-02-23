<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IaMapping extends Model
{
    protected $fillable = [
        'company_id',
        'tiers_nom',
        'tiers_nif',
        'plan_tiers_id',
        'compte_numero',
        'compte_libelle',
        'confiance',
        'utilisations',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function planTiers()
    {
        return $this->belongsTo(PlanTiers::class);
    }

    /**
     * Trouve ou crée un mapping pour un tiers donné.
     * Incrémente le compteur d'utilisations si trouvé.
     */
    public static function findOrCreateForTiers(int $companyId, string $tiersNom, string $compteNumero, string $compteLibelle = null): self
    {
        $mapping = self::where('company_id', $companyId)
            ->where('tiers_nom', 'LIKE', '%' . $tiersNom . '%')
            ->first();

        if ($mapping) {
            $mapping->increment('utilisations');
            $mapping->update(['compte_numero' => $compteNumero, 'compte_libelle' => $compteLibelle]);
            return $mapping;
        }

        return self::create([
            'company_id' => $companyId,
            'tiers_nom' => $tiersNom,
            'compte_numero' => $compteNumero,
            'compte_libelle' => $compteLibelle,
            'confiance' => 1,
            'utilisations' => 1,
        ]);
    }
}
