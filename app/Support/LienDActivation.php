<?php

namespace App\Support;

use App\Mail\LienActivationMail;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Le lien par lequel le titulaire d'un compte choisit son mot de passe.
 *
 * Deux chemins ouvrent un compte sans mot de passe utilisable et ont besoin de
 * ce lien — `companies/provision` quand Selflow n'envoie pas d'empreinte, et
 * l'écran de liaison du superadministrateur qui crée un dossier Comptaflow
 * depuis une entreprise Selflow. Les deux faisaient auparavant **choisir le mot
 * de passe du compte d'un client par quelqu'un d'autre que lui**, et le
 * transportaient en clair.
 */
class LienDActivation
{
    /** Sept jours : assez pour une adresse relevée une fois par semaine. */
    private const VALIDITE_EN_JOURS = 7;

    /**
     * Prépare le jeton et envoie le message, **sans jamais faire échouer l'appelant**.
     *
     * Un serveur de messagerie indisponible ne doit annuler ni un dossier créé
     * ni la clé de liaison que Selflow attend en retour. Le lien se renvoie
     * depuis Comptaflow.
     */
    public static function envoyer(User $utilisateur, Company $company): void
    {
        try {
            $jeton = Str::random(64);

            $utilisateur->forceFill([
                // Haché en base : la table `users` est lue par bien plus de code
                // que ce lot, et un jeton en clair vaut un mot de passe tant
                // qu'il n'a pas servi.
                'activation_token'            => hash('sha256', $jeton),
                'activation_token_expires_at' => now()->addDays(self::VALIDITE_EN_JOURS),
            ])->save();

            Mail::to($utilisateur->email_adresse)->send(
                new LienActivationMail($utilisateur, $company, $jeton)
            );
        } catch (\Throwable $e) {
            Log::error('Activation : lien non envoyé', [
                'company_id' => $company->id,
                'user_id'    => $utilisateur->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
