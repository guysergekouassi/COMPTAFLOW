<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogPrice extends Model
{
    protected $fillable = [
        'type', 'app_name', 'pack_name',
        'prix_mensuel', 'sur_mesure', 'actif', 'notes',
    ];

    protected $casts = [
        'prix_mensuel' => 'float',
        'sur_mesure'   => 'boolean',
        'actif'        => 'boolean',
    ];

    // ── Catalogue des apps avec leurs packs ──────────────────────────────────
    public static function catalogueApps(): array
    {
        return [
            'RHFLOW'     => ['Basic', 'Pro', 'Basic Edge', 'Pro Edge', 'Pro Max', 'Pro Master', 'Pro Day'],
            'COMPTAFLOW' => ['Starter', 'Pro', 'Enterprise'],
            'TASKFLOW'   => ['Starter', 'Pro', 'Enterprise'],
            'SELFLOW'    => ['Starter', 'Pro', 'Enterprise'],
            'LEGALFLOW'  => ['Starter', 'Pro', 'Enterprise'],
        ];
    }

    // ── Catalogue des services ────────────────────────────────────────────────
    public static function catalogueServices(): array
    {
        return ['COMPTABILITE', 'FISCALITE', 'DROIT', 'JURIDIQUE', 'SOCIAL', 'AUDIT', 'CONSEIL'];
    }

    // ── Récupérer les prix depuis la DB (ou les prix par défaut si vide) ─────
    public static function prixParApp(string $app): array
    {
        $rows = static::where('type', 'app')->where('app_name', $app)->where('actif', true)->get();

        if ($rows->isEmpty()) {
            // Prix par défaut (fallback hardcodé)
            return match ($app) {
                'RHFLOW'     => [
                    'Basic'      => 5000,  'Pro'        => 10000,
                    'Basic Edge' => 20000, 'Pro Edge'   => 35000,
                    'Pro Max'    => 50000, 'Pro Master' => null, 'Pro Day' => null,
                ],
                'COMPTAFLOW' => ['Starter' => 15000, 'Pro' => 30000, 'Enterprise' => 60000],
                'TASKFLOW'   => ['Starter' => 8000,  'Pro' => 18000, 'Enterprise' => 40000],
                'SELFLOW'    => ['Starter' => 10000, 'Pro' => 22000, 'Enterprise' => 45000],
                'LEGALFLOW'  => ['Starter' => 12000, 'Pro' => 28000, 'Enterprise' => 55000],
                default      => [],
            };
        }

        return $rows->mapWithKeys(fn($r) => [
            $r->pack_name => $r->sur_mesure ? null : $r->prix_mensuel,
        ])->all();
    }
}
