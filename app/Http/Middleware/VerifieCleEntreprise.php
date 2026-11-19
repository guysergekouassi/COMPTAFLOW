<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * La clé `X-Company-Key` désigne le dossier, et lui seul.
 *
 * Avant ce lot, les points d'entrée externes s'authentifiaient par le seul
 * `EXTERNAL_SYNC_SECRET` — la même valeur pour toutes les entreprises. Ce
 * secret ne dit pas **qui** appelle : le corps de la requête l'annonçait
 * (`selflow_company_id`, `comptaflow_company_id`) et Comptaflow le croyait sur
 * parole. Quiconque détenait le secret pouvait donc écrire dans les livres de
 * n'importe quelle entreprise en changeant un entier dans un JSON.
 *
 * Ce filtre résout le dossier depuis la clé présentée, le pose sur la requête
 * (`entreprise_liee`) pour que le contrôleur n'ait plus à faire confiance au
 * corps, et refuse ce qui doit l'être.
 *
 * ── 401 et 403 ne disent pas la même chose ───────────────────────────────────
 *
 * - **401** : clé absente ou inconnue. L'appelant n'est pas authentifié — le
 *   cas d'un déploiement mal configuré, d'une clé jamais transmise, d'un `.env`
 *   oublié.
 * - **403** : clé valide, mais qui désigne un autre dossier que celui annoncé
 *   dans le corps. L'appelant est connu, et il écrit chez quelqu'un d'autre.
 *
 * Les confondre rendrait le journal inutilisable : on ne saurait plus
 * distinguer une mise en service ratée d'une tentative d'écriture croisée, et
 * c'est la seconde qu'on veut voir arriver. D'où la journalisation des **deux**
 * identifiants sur le refus 403.
 *
 * ── La clé précédente ────────────────────────────────────────────────────────
 *
 * Depuis que la clé se renouvelle (`companies/rotate-key`), celle qu'elle
 * remplace reste acceptée cinq minutes. Un déversement **déjà parti** au moment
 * du renouvellement porte encore l'ancienne et arrive après elle : sans cette
 * grâce, il échouerait une fois par mois, au hasard, pour une raison qu'aucun
 * journal ne nommerait. Elle est essayée **en second**, jamais en premier.
 */
class VerifieCleEntreprise
{
    public function handle(Request $request, Closure $next): Response
    {
        $cle = $request->header('X-Company-Key');

        if (!is_string($cle) || trim($cle) === '') {
            // ══════════════════════════════════════════════════════════════
            // LA PORTE EST FERMÉE.
            //
            // Ces lignes acceptaient l'appel sur le seul secret partagé, le
            // temps que les deux applications soient déployées ensemble. Tant
            // qu'elles étaient là, **le secret partagé suffisait à écrire dans
            // n'importe quel dossier** : la porte que la clé par entreprise
            // devait fermer restait entrouverte, et les deux épreuves de garde
            // passaient au vert par-dessus.
            //
            // Elles tombent avec leurs trois jumelles — le repli de
            // `ExternalSyncController::entrepriseDeLaRequete()`, celui de
            // `ExternalCompanyController::entrepriseDeLaRequete()`, et la
            // tolérance de Selflow. Le journal de Comptaflow a été relu avant
            // de les retirer : **aucun appel réel n'en dépendait**, les 160
            // passages enregistrés venaient tous de sa propre suite d'épreuves.
            //
            // L'ordre de déploiement fait le reste, et il n'est pas
            // indifférent : **Selflow d'abord, Comptaflow ensuite**. Selflow
            // présente déjà la clé, et un Comptaflow d'avant ce lot ignore
            // simplement l'en-tête. Dans l'autre sens, un Comptaflow à jour
            // refuserait un Selflow qui ne l'envoie pas encore.
            // ══════════════════════════════════════════════════════════════
            return self::refus($request, 401, 'Clé de synchronisation absente : '
                . 'l\'en-tête X-Company-Key est requis.');
        }

        // La recherche porte sur le haché, jamais sur la valeur : la clé n'est
        // lisible dans aucune colonne, et l'index unique évite un balayage de
        // la table des dossiers à chaque écriture reçue.
        $hache = hash('sha256', $cle);
        $entreprise = Company::where('selflow_sync_key_hash', $hache)->first();
        $origine = $entreprise ? 'SELFLOW' : null;
        if (!$entreprise) {
            $entreprise = Company::where('gestionsites_sync_key_hash', $hache)->first();
            $origine = $entreprise ? 'GESTION_DE_SITES' : null;
        }
        $parGrace = false;

        // ── La clé précédente, essayée **en second** ──
        //
        // L'ordre n'est pas une commodité. Une clé révoquée puis reprovisionnée
        // ne doit jamais redevenir valide par l'arrière : la clé courante d'un
        // dossier prime toujours sur la clé périmée d'un autre, sans quoi une
        // valeur qu'on croit remplacée continuerait de désigner quelqu'un.
        if (!$entreprise) {
            $grace = self::parLaCleDeGrace($hache);
            $entreprise = $grace['entreprise'];
            $parGrace   = $grace['grace'];

            if (!$entreprise && $grace['expiree']) {
                // Nommer la cause, comme pour la révocation : « clé inconnue »
                // enverrait chercher une panne de réseau alors que la réponse
                // tient en une ligne — le renouvellement du mois est passé, et
                // c'est la clé qu'il a rendue qu'il faut présenter.
                return self::refus($request, 401, 'Clé de synchronisation renouvelée : la période de '
                    . 'grâce de l\'ancienne clé est expirée. Présentez la clé rendue par le dernier '
                    . 'renouvellement (companies/rotate-key).');
            }
        }

        if (!$entreprise) {
            return self::refus($request, 401, 'Clé de synchronisation inconnue.');
        }

        $dateRevocation = $origine === 'GESTION_DE_SITES'
            ? $entreprise->gestionsites_sync_key_revoked_at
            : $entreprise->selflow_sync_key_revoked_at;
        if ($dateRevocation) {
            return self::refus($request, 401, sprintf(
                'Clé de synchronisation révoquée le %s.',
                $dateRevocation->format('d/m/Y')
            ));
        }

        // ── L'écriture croisée ──
        //
        // La clé authentifie un dossier ; le corps en annonce un. Sans cette
        // comparaison, la clé ne sert à rien : il suffirait de la présenter et
        // d'annoncer l'entreprise du voisin.
        $croisement = self::croisement($request, $entreprise, $origine);

        if ($croisement !== null) {
            Log::warning('Liaison Selflow : écriture croisée refusée', [
                'route'                     => $request->path(),
                'ip'                        => $request->ip(),
                'dossier_de_la_cle'         => $entreprise->id,
                'selflow_de_la_cle'         => $entreprise->selflow_company_id,
                'dossier_annonce_dans_corps' => $request->input('comptaflow_company_id'),
                'selflow_annonce_dans_corps' => $request->input('selflow_company_id'),
            ]);

            return self::refus($request, 403, $croisement);
        }

        $request->attributes->set('entreprise_liee', $entreprise);
        $request->attributes->set('integration_origine', $origine);
        // `rotate-key` en a besoin : une demande de renouvellement présentée
        // avec la clé **précédente** est un renouvellement rejoué — Selflow n'a
        // pas reçu la réponse du premier. Il faut lui rendre la clé courante,
        // pas en tirer une troisième.
        $request->attributes->set('cle_de_liaison_en_grace', $parGrace);

        return $next($request);
    }

    /**
     * Le dossier dont la clé vient d'être remplacée, si la grâce court encore.
     *
     * Un déversement parti juste avant un renouvellement porte l'ancienne clé et
     * arrive après elle : le refuser ferait perdre des écritures une fois par
     * mois, au hasard, pour une raison qu'aucun journal ne nommerait.
     *
     * Passé la grâce, l'ancienne ne vaut plus rien — et on la retire de la base
     * au passage plutôt que de la garder « au cas où ».
     *
     * @return array{entreprise: Company|null, grace: bool, expiree: bool}
     */
    private static function parLaCleDeGrace(string $hache): array
    {
        $entreprise = Company::where('selflow_sync_key_hash_precedente', $hache)->first();

        if (!$entreprise) {
            return ['entreprise' => null, 'grace' => false, 'expiree' => false];
        }

        if (!$entreprise->graceEncoreOuverte()) {
            $entreprise->oublierLaCleDeGrace();

            return ['entreprise' => null, 'grace' => false, 'expiree' => true];
        }

        Log::info('Liaison Selflow : appel accepté avec la clé précédente, dans la période de grâce', [
            'company_id' => $entreprise->id,
            'expire_a'   => $entreprise->selflow_sync_key_precedente_expire_at->toIso8601String(),
        ]);

        return ['entreprise' => $entreprise, 'grace' => true, 'expiree' => false];
    }

    /**
     * Un identifiant historique facultatif du corps contredit-il la clé ?
     *
     * @return string|null le motif du refus, ou `null` si les deux concordent
     */
    private static function croisement(Request $request, Company $entreprise, ?string $origine = 'SELFLOW'): ?string
    {
        $annonceComptaflow = $request->input('comptaflow_company_id');

        if ($annonceComptaflow !== null && (int) $annonceComptaflow !== (int) $entreprise->id) {
            return sprintf(
                'La clé présentée désigne le dossier n° %d, la requête annonce le dossier n° %d.',
                $entreprise->id,
                (int) $annonceComptaflow
            );
        }

        if ($origine === 'GESTION_DE_SITES') {
            $annonce = $request->input('gestionsites_company_id');
            if ($annonce !== null && $entreprise->gestionsites_company_id !== null
                && (int) $annonce !== (int) $entreprise->gestionsites_company_id) {
                return 'La clé présentée ne correspond pas à l’entreprise GESTION-DE-SITES annoncée.';
            }
            return null;
        }

        $annonceSelflow = $request->input('selflow_company_id');

        // Un dossier sans `selflow_company_id` n'est rattaché à personne : il
        // n'y a rien à comparer, et refuser bloquerait le tout premier appel
        // qui établit justement le rattachement.
        if ($annonceSelflow !== null
            && $entreprise->selflow_company_id !== null
            && (int) $annonceSelflow !== (int) $entreprise->selflow_company_id) {
            return sprintf(
                'La clé présentée désigne l\'entreprise Selflow n° %d, la requête annonce l\'entreprise n° %d.',
                (int) $entreprise->selflow_company_id,
                (int) $annonceSelflow
            );
        }

        return null;
    }

    private static function refus(Request $request, int $code, string $message): Response
    {
        if ($code === 401) {
            Log::warning('Liaison Selflow : clé refusée', [
                'route'   => $request->path(),
                'ip'      => $request->ip(),
                'motif'   => $message,
            ]);
        }

        return response()->json(['success' => false, 'message' => $message], $code);
    }
}
