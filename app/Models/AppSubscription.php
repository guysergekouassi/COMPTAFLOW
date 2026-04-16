<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AppSubscription extends Model
{
    protected $fillable = [
        'company_id', 'app_name', 'pack_name',
        'prix_mensuel', 'date_debut', 'date_fin',
        'statut_paiement', 'notes',
    ];

    protected $casts = [
        'prix_mensuel' => 'float',
        'date_debut'   => 'date',
        'date_fin'     => 'date',
    ];

    // ── Relations ────────────────────────────────────────────────────────────
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** Vrai si l'abonnement est toujours actif */
    public function isActif(): bool
    {
        return is_null($this->date_fin) || $this->date_fin->isFuture();
    }

    /** Nombre de mois écoulés depuis le début (au moins 1) */
    public function moisEcoules(): int
    {
        $fin = $this->date_fin && $this->date_fin->isPast()
            ? $this->date_fin
            : now();
        return max(1, (int) $this->date_debut->diffInMonths($fin));
    }

    /** Montant total théoriquement dû */
    public function totalDu(): float
    {
        return $this->prix_mensuel * $this->moisEcoules();
    }

    // ── Packs RHFLOW (prix officiels) ─────────────────────────────────────────
    public static function prixRhflow(): array
    {
        return [
            'Basic'        => 5_000,
            'Pro'          => 10_000,
            'Basic Edge'   => 20_000,
            'Pro Edge'     => 35_000,
            'Pro Max'      => 50_000,
            'Pro Master'   => null, // sur mesure
            'Pro Day'      => null, // sur mesure
        ];
    }

    // ── Packs fictifs pour les autres apps ───────────────────────────────────
    public static function prixParApp(string $app): array
    {
        return match ($app) {
            'RHFLOW'     => static::prixRhflow(),
            'COMPTAFLOW' => ['Starter' => 15_000, 'Pro' => 30_000, 'Enterprise' => 60_000],
            'TASKFLOW'   => ['Starter' => 8_000,  'Pro' => 18_000, 'Enterprise' => 40_000],
            'SELFLOW'    => ['Starter' => 10_000, 'Pro' => 22_000, 'Enterprise' => 45_000],
            'LEGALFLOW'  => ['Starter' => 12_000, 'Pro' => 28_000, 'Enterprise' => 55_000],
            default      => [],
        };
    }
}
