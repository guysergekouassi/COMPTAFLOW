<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceHonoraire extends Model
{
    protected $fillable = [
        'company_id', 'service_name', 'description',
        'prix_mensuel', 'declarations', 'date_debut',
        'date_fin', 'statut_paiement', 'notes',
    ];

    protected $casts = [
        'prix_mensuel'  => 'float',
        'declarations'  => 'array',
        'date_debut'    => 'date',
        'date_fin'      => 'date',
    ];

    // ── Relations ─────────────────────────────────────────────────────────────
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // ── Catalogue des services et leurs déclarations associées ──────────────
    public static function catalogue(): array
    {
        return [
            'COMPTABILITE' => [
                'label'       => 'Comptabilité',
                'icon'        => 'fa-calculator',
                'color'       => '#0ea5e9',
                'declarations'=> [],
                'description' => 'Tenue de comptabilité, établissement du bilan et des états financiers SYSCOHADA.',
            ],
            'FISCALITE' => [
                'label'       => 'Fiscalité',
                'icon'        => 'fa-landmark',
                'color'       => '#f59e0b',
                'declarations'=> ['TE'],
                'description' => 'Déclarations fiscales : TVA, IS, IRPP, Taxe Emploi (TE).',
            ],
            'SOCIAL_RH' => [
                'label'       => 'Social & RH',
                'icon'        => 'fa-users-gear',
                'color'       => '#10b981',
                'declarations'=> ['CNPS', 'FNE', 'CMU', 'TE'],
                'description' => 'Déclarations sociales : CNPS, FNE, CMU, Taxe Emploi (TE). Gestion des cotisations sociales.',
            ],
            'JURIDIQUE' => [
                'label'       => 'Juridique',
                'icon'        => 'fa-gavel',
                'color'       => '#8b5cf6',
                'declarations'=> [],
                'description' => 'Conseil juridique, rédaction de contrats, statuts et immatriculation.',
            ],
            'DROIT' => [
                'label'       => 'Droit des Affaires',
                'icon'        => 'fa-scale-balanced',
                'color'       => '#6366f1',
                'declarations'=> [],
                'description' => 'Assistance en droit des sociétés, contentieux commercial et recouvrement.',
            ],
            'AUDIT' => [
                'label'       => 'Audit & Commissariat',
                'icon'        => 'fa-magnifying-glass-chart',
                'color'       => '#ef4444',
                'declarations'=> [],
                'description' => 'Audit légal, commissariat aux comptes et due diligence financière.',
            ],
            'CONSEIL' => [
                'label'       => 'Conseil & Stratégie',
                'icon'        => 'fa-lightbulb',
                'color'       => '#f97316',
                'declarations'=> [],
                'description' => 'Conseil en gestion d\'entreprise, stratégie financière et business plan.',
            ],
        ];
    }

    /** Libellés courts des déclarations */
    public static function declarationLabels(): array
    {
        return [
            'CNPS' => ['label' => 'Caisse Nationale de Prévoyance Sociale', 'badge' => 'bg-success'],
            'FNE'  => ['label' => 'Fonds National de l\'Emploi',             'badge' => 'bg-info'],
            'CMU'  => ['label' => 'Couverture Maladie Universelle',          'badge' => 'bg-primary'],
            'TE'   => ['label' => 'Taxe Emploi',                             'badge' => 'bg-warning'],
        ];
    }

    public function isActif(): bool
    {
        return is_null($this->date_fin) || $this->date_fin->isFuture();
    }

    public function moisEcoules(): int
    {
        $fin = $this->date_fin && $this->date_fin->isPast() ? $this->date_fin : now();
        return max(1, (int) $this->date_debut->diffInMonths($fin));
    }

    public function totalDu(): float
    {
        return ($this->prix_mensuel ?? 0) * $this->moisEcoules();
    }
}
