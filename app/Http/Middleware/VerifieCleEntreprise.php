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
 */
class VerifieCleEntreprise
{
    public function handle(Request $request, Closure $next): Response
    {
        $cle = $request->header('X-Company-Key');

        if (!is_string($cle) || trim($cle) === '') {
            // ══════════════════════════════════════════════════════════════
            // TOLÉRANCE DE TRANSITION — À RETIRER, C'EST L'OBJET DU LOT.
            //
            // Selflow et Comptaflow ne sont pas déployés au même instant :
            // pendant la bascule, un Selflow d'avant ce lot appelle encore
            // sans en-tête, et refuser le ferait tomber la synchronisation
            // de toutes les entreprises déjà liées.
            //
            // TANT QUE CES LIGNES SONT LÀ, LE SECRET PARTAGÉ SUFFIT
            // TOUJOURS À ÉCRIRE DANS N'IMPORTE QUEL DOSSIER : la porte que
            // ce lot ferme reste entrouverte.
            //
            // IL Y A **TROIS** TOLÉRANCES, ET ELLES SE RETIRENT ENSEMBLE.
            // En retirer une ou deux laisse la porte ouverte du côté qu'on
            // n'a pas fermé :
            //   1. celle-ci ;
            //   2. le repli de `ExternalSyncController::entrepriseDeLaRequete()` ;
            //   3. celle de Selflow, marquée TOLÉRANCE DE TRANSITION dans
            //      son `ExternalSyncControleur::entrepriseDeLaCle()`.
            //
            // La ligne qui les remplace, une fois les deux applications
            // déployées :
            //
            // return self::refus($request, 401, 'Clé de synchronisation absente : '
            //     . 'l\'en-tête X-Company-Key est requis.');
            // ══════════════════════════════════════════════════════════════
            Log::info('Liaison Selflow : appel sans X-Company-Key, accepté par tolérance de transition', [
                'route' => $request->path(),
                'ip'    => $request->ip(),
            ]);

            return $next($request);
        }

        // La recherche porte sur le haché, jamais sur la valeur : la clé n'est
        // lisible dans aucune colonne, et l'index unique évite un balayage de
        // la table des dossiers à chaque écriture reçue.
        $entreprise = Company::where('selflow_sync_key_hash', hash('sha256', $cle))->first();

        if (!$entreprise) {
            return self::refus($request, 401, 'Clé de synchronisation inconnue.');
        }

        if ($entreprise->selflow_sync_key_revoked_at) {
            // Nommer la révocation, et la dater. « Clé inconnue » enverrait
            // l'entreprise chercher une panne de réseau pendant que la vraie
            // réponse — la liaison a été coupée, et quand — tient en une ligne.
            return self::refus($request, 401, sprintf(
                'Clé de synchronisation révoquée le %s. Reprovisionnez la liaison depuis Selflow '
                . 'pour obtenir une nouvelle clé ; l\'ancienne ne redeviendra pas valide.',
                $entreprise->selflow_sync_key_revoked_at->format('d/m/Y')
            ));
        }

        // ── L'écriture croisée ──
        //
        // La clé authentifie un dossier ; le corps en annonce un. Sans cette
        // comparaison, la clé ne sert à rien : il suffirait de la présenter et
        // d'annoncer l'entreprise du voisin.
        $croisement = self::croisement($request, $entreprise);

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

        return $next($request);
    }

    /**
     * Le corps annonce-t-il un dossier autre que celui de la clé ?
     *
     * @return string|null le motif du refus, ou `null` si les deux concordent
     */
    private static function croisement(Request $request, Company $entreprise): ?string
    {
        $annonceComptaflow = $request->input('comptaflow_company_id');

        if ($annonceComptaflow !== null && (int) $annonceComptaflow !== (int) $entreprise->id) {
            return sprintf(
                'La clé présentée désigne le dossier n° %d, la requête annonce le dossier n° %d.',
                $entreprise->id,
                (int) $annonceComptaflow
            );
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
