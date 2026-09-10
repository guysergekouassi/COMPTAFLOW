<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'payload',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'payload' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function auditable()
    {
        return $this->morphTo('model');
    }

    /** Libellés lisibles des évènements tracés. */
    public const ACTIONS = [
        'CREATE'        => 'Création',
        'UPDATE'        => 'Modification',
        'DELETE'        => 'Suppression',
        'LOGIN'         => 'Connexion',
        'LOGOUT'        => 'Déconnexion',
        'REIMPUTATION'  => 'Réimputation',
        'BULK_DELETE'   => 'Suppression en lot',
        'IMPORT'        => 'Importation',
        'EXPORT'        => 'Exportation',
        'RESTORE'       => 'Restauration',
    ];

    /** Libellés lisibles des modules (modèles) tracés. */
    public const MODULES = [
        'EcritureComptable' => 'Écriture comptable',
        'PlanComptable'     => 'Plan comptable',
        'PlanTiers'         => 'Plan tiers',
        'CodeJournal'       => 'Journal',
        'ExerciceComptable' => 'Exercice comptable',
        'Company'           => 'Entreprise',
        'User'              => 'Utilisateur',
        'Immobilisation'    => 'Immobilisation',
    ];

    public function libelleAction(): string
    {
        return self::ACTIONS[strtoupper($this->action)] ?? ucfirst(mb_strtolower($this->action));
    }

    public function libelleModule(): string
    {
        $base = class_basename($this->model_type);
        return self::MODULES[$base] ?? $base;
    }

    /**
     * Détail lisible des champs modifiés : [['champ' => ..., 'avant' => ..., 'apres' => ...], ...]
     * Le payload d'une modification contient les clés "old" et "new".
     */
    public function changements(): array
    {
        $payload = $this->payload;
        if (!is_array($payload) || !isset($payload['new']) || !is_array($payload['new'])) {
            return [];
        }

        $anciens = is_array($payload['old'] ?? null) ? $payload['old'] : [];
        $lignes = [];

        foreach ($payload['new'] as $champ => $apres) {
            if (in_array($champ, ['updated_at', 'created_at', 'remember_token', 'password'], true)) {
                continue;
            }

            $lignes[] = [
                'champ' => $this->libelleChamp($champ),
                'avant' => $this->formaterValeur($anciens[$champ] ?? null),
                'apres' => $this->formaterValeur($apres),
            ];
        }

        return $lignes;
    }

    /** Nom lisible d'un champ, en réutilisant les libellés de validation. */
    public function libelleChamp(string $champ): string
    {
        $traduction = trans('validation.attributes.' . $champ);

        return $traduction === 'validation.attributes.' . $champ
            ? str_replace('_', ' ', $champ)
            : $traduction;
    }

    private function formaterValeur($valeur): string
    {
        if (is_null($valeur) || $valeur === '') {
            return '(vide)';
        }
        if (is_bool($valeur)) {
            return $valeur ? 'Oui' : 'Non';
        }
        if (is_array($valeur)) {
            return json_encode($valeur, JSON_UNESCAPED_UNICODE);
        }

        $texte = (string) $valeur;
        return mb_strlen($texte) > 80 ? mb_substr($texte, 0, 80) . '…' : $texte;
    }

    /**
     * Phrase récapitulative affichée dans la colonne « Détails de l'action ».
     */
    public function resume(): string
    {
        if ($this->description) {
            return $this->description;
        }

        $module = $this->libelleModule();
        $reference = $this->model_id ? ' #' . $this->model_id : '';

        return $this->libelleAction() . ' — ' . $module . $reference;
    }
}
