<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\TreasuryCategory;
use App\Models\User;
use App\Support\LienDActivation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Le cycle de vie de la liaison d'un dossier : provision, revoke, verify.
 *
 * **C'est Comptaflow qui génère la clé, pas Selflow.** La clé désigne un
 * dossier *ici* ; c'est donc ici qu'elle doit être reconnue. Selflow tirait
 * auparavant un `Str::random(40)` de son côté et déclarait cette valeur « clé
 * de synchronisation » : Comptaflow devait l'accepter sur parole, ce qui
 * revenait à laisser l'appelant choisir son propre laissez-passer.
 *
 * La procédure complète : l'entreprise demande un dossier depuis ses paramètres
 * Selflow → le superadministrateur Selflow vérifie son identité fiscale et
 * valide → Selflow appelle `provision` → Comptaflow crée le dossier et rend la
 * clé → Selflow la range chiffrée et déverse son référentiel. L'entreprise ne
 * voit jamais la clé.
 */
class ExternalCompanyController extends Controller
{
    /**
     * Le secret serveur fourni est-il celui que l'on attend ?
     *
     * - **`hash_equals` plutôt que `!==`** : une comparaison de chaînes
     *   ordinaire s'arrête au premier caractère différent, et le temps de
     *   réponse laisse alors deviner le secret caractère par caractère.
     * - **Un secret absent refuse tout.** Pas de valeur de repli écrite dans le
     *   code : ces routes créent des dossiers et rendent des clés. Mieux vaut
     *   une mise en service qui ne démarre pas qu'une porte ouverte.
     */
    private static function secretValide(?string $fourni, ?string $attendu): bool
    {
        if (empty($attendu) || empty($fourni)) {
            return false;
        }

        return hash_equals($attendu, $fourni);
    }

    /** @return \Illuminate\Http\JsonResponse|null la réponse de refus, ou `null` si le secret passe */
    private function refusDeSecret(Request $request, string $route)
    {
        $attendu = config('external_sync.external_sync_secret');
        $fourni  = $request->input('secret') ?? $request->header('X-Sync-Secret');

        if (self::secretValide($fourni, $attendu)) {
            return null;
        }

        Log::warning('Liaison Selflow : secret serveur invalide', [
            'ip'    => $request->ip(),
            'route' => $route,
        ]);

        return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 401);
    }

    // ═════════════════════════════════════════════════════════════════════════
    // provision
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Ouvre le dossier comptable d'une entreprise et rend sa clé de liaison.
     *
     * POST /api/external/companies/provision — **secret serveur seul**, car il
     * n'y a pas encore de clé à présenter. D'où la limitation de débit serrée
     * posée sur la route : c'est le seul appel de la passerelle qui crée des
     * dossiers.
     */
    public function provision(Request $request)
    {
        if ($refus = $this->refusDeSecret($request, 'api/external/companies/provision')) {
            return $refus;
        }

        $validator = Validator::make($request->all(), [
            'selflow_company_id'          => 'required|integer|min:1',
            'entreprise'                  => 'required|array',
            'entreprise.nom'              => 'required|string|max:255',
            'entreprise.forme_juridique'  => 'nullable|string|max:50',
            'entreprise.ncc'              => 'nullable|string|max:50',
            'entreprise.rccm'             => 'nullable|string|max:100',
            'entreprise.regime_imposition' => 'nullable|string|max:80',
            'entreprise.adresse'          => 'nullable|string|max:255',
            'entreprise.telephone'        => 'nullable|string|max:30',
            'entreprise.email'            => 'nullable|email|max:255',
            'entreprise.admin_nom'        => 'nullable|string|max:150',
            'entreprise.admin_prenom'     => 'nullable|string|max:150',
            'entreprise.admin_email'      => 'required|email|max:255',
            // L'empreinte bcrypt du compte Selflow, telle qu'elle est en base
            // là-bas. Jamais un mot de passe : `regex` refuse tout ce qui n'a
            // pas la forme d'une empreinte, pour qu'un connecteur mal réglé qui
            // enverrait le mot de passe en clair soit rejeté plutôt que rangé
            // tel quel dans la colonne `password`.
            'entreprise.admin_password_hash' => ['nullable', 'string', 'max:255', 'regex:/^\$2[aby]?\$\d{2}\$.{53}$/'],
            'numerotation_tiers'          => 'nullable|in:numeric,alphanumeric',
            'longueur_tiers'              => 'nullable|integer|min:3|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // ── Idempotence ──
        //
        // Une validation cliquée deux fois, ou un appel rejoué après un délai
        // réseau, ouvrirait sinon un **second** dossier pour la même entreprise :
        // les écritures suivantes se partageraient entre deux livres, et aucun
        // des deux ne porterait la comptabilité complète. Le rattachement est
        // par `selflow_company_id`, désormais unique en base.
        $existante = Company::where('selflow_company_id', $request->input('selflow_company_id'))->first();

        if ($existante && !$existante->selflow_sync_key_revoked_at && $existante->selflow_sync_key_chiffree) {
            try {
                $cle = Crypt::decryptString($existante->selflow_sync_key_chiffree);
            } catch (\Throwable $e) {
                // `APP_KEY` a changé depuis : la clé rangée est illisible, et
                // aucune des deux applications ne peut plus s'en servir. On en
                // pose une neuve sur le même dossier plutôt que d'échouer.
                Log::warning('Liaison Selflow : clé existante illisible, elle est remplacée', [
                    'company_id' => $existante->id,
                ]);
                $cle = $existante->poserUneCleDeLiaison();
            }

            Log::info('Liaison Selflow : provision rejouée, même dossier et même clé', [
                'company_id'         => $existante->id,
                'selflow_company_id' => $existante->selflow_company_id,
            ]);

            return response()->json([
                'success'    => true,
                'company_id' => $existante->id,
                'sync_key'   => $cle,
            ]);
        }

        DB::beginTransaction();
        try {
            if ($existante) {
                // Dossier déjà là mais clé révoquée : on relie, sans jamais
                // ressusciter l'ancienne clé. Le dossier et ses écritures
                // restent en place — délier n'est pas supprimer.
                $cle = $existante->poserUneCleDeLiaison();
                DB::commit();

                return response()->json([
                    'success'    => true,
                    'company_id' => $existante->id,
                    'sync_key'   => $cle,
                ]);
            }

            $entreprise = $request->input('entreprise');
            $company = Company::create([
                'company_name'        => $entreprise['nom'],
                'activity'            => 'Commercial',
                'juridique_form'      => $entreprise['forme_juridique'] ?? 'SARL',
                'social_capital'      => 0,
                'adresse'             => $entreprise['adresse'] ?? null,
                'code_postal'         => '',
                'city'                => 'Abidjan',
                'country'             => "Côte d'Ivoire",
                'phone_number'        => $entreprise['telephone'] ?? null,
                'email_adresse'       => $entreprise['email'] ?? $entreprise['admin_email'],
                'ncc'                 => $entreprise['ncc'] ?? null,
                'rccm'                => $entreprise['rccm'] ?? null,
                'regime'              => $entreprise['regime_imposition'] ?? null,
                'is_active'           => true,
                'selflow_company_id'  => $request->input('selflow_company_id'),
                // Selflow numérote ses tiers sur six caractères, préfixe
                // compris — `411001` en numérique, `41KON1` en alphanumérique.
                // Deux longueurs différentes de part et d'autre empêchent de
                // retrouver un tiers par son numéro exact, et chaque écriture
                // retombe alors sur le compte collectif.
                'tier_digits'         => $request->input('longueur_tiers', 6),
                'tier_id_type'        => $request->input('numerotation_tiers', 'numeric'),
            ]);

            $activationRequise = false;
            $admin = $this->creerLAdministrateur($company, $entreprise, $activationRequise);
            $company->update(['user_id' => $admin->id]);

            foreach ([
                'I. Flux de trésorerie des activités opérationnelles',
                'II. Flux de trésorerie des activités d\'investissement',
                'III. Flux de trésorerie des activités de financement',
            ] as $categorie) {
                TreasuryCategory::create(['name' => $categorie, 'company_id' => $company->id]);
            }

            $cle = $company->poserUneCleDeLiaison();

            DB::commit();

            // Le lien d'activation n'a lieu d'être que dans le repli : avec
            // l'empreinte Selflow, le client se connecte avec les identifiants
            // qu'il utilise déjà, et lui envoyer « choisissez un mot de passe »
            // l'inviterait à en poser un second sans le vouloir.
            if ($activationRequise) {
                LienDActivation::envoyer($admin, $company);
            }

            Log::info('Liaison Selflow : dossier provisionné', [
                'company_id'         => $company->id,
                'selflow_company_id' => $company->selflow_company_id,
                // Jamais l'empreinte, jamais l'adresse : ce journal est lu par
                // du monde, et une empreinte bcrypt s'attaque hors ligne.
                'acces_selflow_repris' => !$activationRequise,
            ]);

            return response()->json([
                'success'    => true,
                'company_id' => $company->id,
                'sync_key'   => $cle,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Liaison Selflow : échec du provisionnement', ['error' => $e->getMessage()]);

            // Pas de `sync_key` dans la réponse : Selflow y lit l'échec et
            // laisse la demande en attente plutôt que d'annoncer une liaison.
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du provisionnement : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Le compte administrateur du dossier — **le compte Selflow du client**.
     *
     * Même adresse, même mot de passe des deux côtés : l'utilisateur n'en
     * apprend pas un second, et le jour où il change celui de Selflow il n'a pas
     * deux endroits où penser.
     *
     * Ce qui voyage n'est pourtant **jamais le mot de passe**, mais son empreinte
     * `bcrypt` telle qu'elle est en base chez Selflow. Elle est rangée **telle
     * quelle** dans `password` : la re-hacher rendrait le compte inaccessible
     * avec le mot de passe que l'utilisateur connaît déjà. Les deux applications
     * sont sous Laravel, le format est identique (`$2y$…`).
     *
     * Ce que cela remplace : `register-enterprise` exigeait `admin_password` —
     * le superadministrateur Selflow choisissait le mot de passe du compte d'un
     * client, et cette valeur traversait la passerelle **en clair** dans le corps
     * de la requête. Personne ne choisit plus de mot de passe pour personne, et
     * personne ne le lit.
     *
     * @param  bool  $activationRequise  vrai si aucune empreinte n'est venue —
     *                                   le compte est alors ouvert sans mot de
     *                                   passe utilisable et attend son lien
     */
    private function creerLAdministrateur(Company $company, array $entreprise, bool &$activationRequise): User
    {
        $email = $entreprise['admin_email'];
        $existant = User::where('email_adresse', $email)->first();

        if ($existant) {
            // L'adresse sert déjà à un compte : on ne le réécrit pas — ni son
            // mot de passe — et on ne le rattache pas ailleurs. Le dossier est
            // créé quand même, c'est le rôle de `provision`, et le rattachement
            // se règle à la main.
            Log::warning('Liaison Selflow : l\'adresse de l\'administrateur est déjà prise', [
                'company_id' => $company->id,
                'email'      => $email,
            ]);

            $activationRequise = false;

            return $existant;
        }

        $empreinte = $entreprise['admin_password_hash'] ?? null;

        // Le repli, pas le cas normal : un connecteur Selflow antérieur à ce
        // contrat n'envoie pas d'empreinte. Le compte reçoit alors un secret
        // aléatoire que personne ne détient, et son titulaire choisit le sien
        // depuis un lien envoyé à son adresse.
        $activationRequise = empty($empreinte);

        return User::create([
            'name'          => $entreprise['admin_nom'] ?? 'Administrateur',
            'last_name'     => $entreprise['admin_prenom'] ?? '',
            'email_adresse' => $email,
            'password'      => $empreinte ?: Hash::make(Str::random(64)),
            'role'          => 'admin',
            'company_id'    => $company->id,
            'is_active'     => true,
        ]);
    }

    // ═════════════════════════════════════════════════════════════════════════
    // revoke
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Coupe la liaison : la clé est datée révoquée, **le dossier reste**.
     *
     * POST /api/external/companies/revoke — secret serveur + `X-Company-Key`.
     *
     * Délier n'est pas supprimer : les écritures déjà reçues sont la
     * comptabilité de l'entreprise, et elle en répond devant l'administration
     * fiscale bien après la fin de son abonnement à Selflow.
     */
    public function revoke(Request $request)
    {
        if ($refus = $this->refusDeSecret($request, 'api/external/companies/revoke')) {
            return $refus;
        }

        $company = $this->entrepriseDeLaRequete($request);

        if (!$company) {
            return response()->json(['success' => false, 'message' => 'Entreprise introuvable.'], 404);
        }

        $company->forceFill([
            // La clé n'est pas effacée : un appel ultérieur doit pouvoir
            // répondre « révoquée le … » plutôt que « inconnue ».
            'selflow_sync_key_revoked_at' => now(),
            'selflow_sync_status'         => 'revoked',
            // Couper la liaison coupe aussi la grâce d'un renouvellement récent.
            // Le filtre refuserait de toute façon un dossier révoqué, quelle que
            // soit la clé présentée ; mais laisser un haché dont plus personne ne
            // veut ne sert qu'à le faire fuiter un jour.
            'selflow_sync_key_hash_precedente'      => null,
            'selflow_sync_key_precedente_expire_at' => null,
        ])->save();

        Log::info('Liaison Selflow : clé révoquée', [
            'company_id'         => $company->id,
            'selflow_company_id' => $company->selflow_company_id,
        ]);

        return response()->json([
            'success'    => true,
            'company_id' => $company->id,
            'revoked_at' => $company->selflow_sync_key_revoked_at->toIso8601String(),
            'message'    => 'Liaison coupée. Le dossier comptable et ses écritures sont conservés.',
        ]);
    }

    // ═════════════════════════════════════════════════════════════════════════
    // rotate-key
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Renouvelle la clé de liaison. La clé actuelle authentifie sa propre relève.
     *
     * POST /api/external/companies/rotate-key — secret serveur + `X-Company-Key`.
     *
     * **Une clé éternelle ouvre le dossier comptable d'une entreprise aussi
     * longtemps qu'il existe.** Un prestataire qui a vu passer une requête, une
     * sauvegarde égarée, un journal mal purgé : rien ne referme derrière eux. La
     * rotation n'empêche pas une fuite, elle borne sa durée de vie à un mois.
     *
     * Comme au provisionnement, **c'est Comptaflow qui génère** : la clé désigne
     * un dossier *ici*. Selflow appelle depuis deux endroits — un bouton du
     * superadministrateur, et une tâche mensuelle sur les clés de plus de trente
     * jours — et n'écrit rien tant qu'il n'a pas la nouvelle en main. Un appel
     * qui échoue laisse donc l'ancienne clé active : le déversement continue.
     *
     * ── Pourquoi pas de tolérance de transition ici ──────────────────────────
     *
     * Les autres points d'entrée acceptent encore un appel sans en-tête, le
     * temps que les deux applications soient déployées. Pas celui-ci, et c'est
     * délibéré : un renouvellement sans clé serait une **prise de liaison en un
     * appel** — l'appelant repart avec la clé neuve, et le détenteur légitime se
     * fait couper cinq minutes plus tard. Personne n'en a besoin non plus : on
     * ne renouvelle que ce qu'on détient déjà. Refuser ici ne coûte rien et
     * ferme la pire chose que la tolérance laisserait passer.
     */
    public function rotateKey(Request $request)
    {
        if ($refus = $this->refusDeSecret($request, 'api/external/companies/rotate-key')) {
            return $refus;
        }

        $company = $request->attributes->get('entreprise_liee');

        if (!$company instanceof Company) {
            Log::warning('Liaison Selflow : renouvellement demandé sans clé', [
                'ip'                 => $request->ip(),
                'selflow_company_id' => $request->input('selflow_company_id'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Le renouvellement exige la clé actuelle en en-tête X-Company-Key : '
                    . 'c\'est elle qui authentifie sa propre relève.',
            ], 401);
        }

        // ── Un renouvellement rejoué ──
        //
        // La demande présentée avec la clé **précédente** ne peut vouloir dire
        // qu'une chose : Selflow n'a pas reçu la réponse du premier appel — un
        // délai réseau, une coupure — et il rejoue avec la seule clé qu'il ait.
        // Lui en tirer une troisième condamnerait sur-le-champ celle qu'il vient
        // de recevoir sans le savoir, et les écritures encore en vol avec elle.
        // On lui rend celle qui est en place : la grâce garde **une** clé
        // précédente, pas une pile.
        if ($request->attributes->get('cle_de_liaison_en_grace') === true) {
            $courante = $company->cleDeLiaisonEnClair();

            if ($courante !== null) {
                Log::info('Liaison Selflow : renouvellement rejoué, la clé en place est rendue', [
                    'company_id' => $company->id,
                ]);

                return $this->reponseDeRenouvellement($company, $courante, true);
            }
            // `APP_KEY` a changé : la clé en place est illisible et ne sert plus
            // à personne. On tombe dans le renouvellement ordinaire, qui en pose
            // une neuve — c'est la seule issue qui rétablisse la liaison.
        }

        DB::beginTransaction();
        try {
            $cle = $company->renouvelerLaCleDeLiaison();
            DB::commit();
        } catch (\Throwable $e) {
            // La transaction remet l'ancienne clé : Selflow garde la sienne, qui
            // fonctionne toujours, et il réessaiera. Un renouvellement à moitié
            // écrit couperait la liaison jusqu'à une intervention manuelle.
            DB::rollBack();
            Log::error('Liaison Selflow : échec du renouvellement de clé', [
                'company_id' => $company->id,
                'error'      => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du renouvellement de la clé.',
            ], 500);
        }

        Log::info('Liaison Selflow : clé renouvelée', [
            'company_id'         => $company->id,
            'selflow_company_id' => $company->selflow_company_id,
            'grace_jusqu_a'      => $company->selflow_sync_key_precedente_expire_at?->toIso8601String(),
        ]);

        return $this->reponseDeRenouvellement($company, $cle, false);
    }

    private function reponseDeRenouvellement(Company $company, string $cle, bool $rejoue)
    {
        return response()->json([
            'success'    => true,
            'company_id' => $company->id,
            'sync_key'   => $cle,
            // De quoi savoir, côté Selflow, jusqu'à quand un déversement parti
            // avec l'ancienne clé sera encore accepté.
            'previous_key_valid_until' => $company->graceEncoreOuverte()
                ? $company->selflow_sync_key_precedente_expire_at->toIso8601String()
                : null,
            'replayed'   => $rejoue,
        ]);
    }

    // ═════════════════════════════════════════════════════════════════════════
    // verify
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * L'état réel de la liaison.
     *
     * POST /api/external/companies/verify — secret serveur + `X-Company-Key`.
     *
     * Le bouton « vérifier la liaison » de Selflow se contentait d'écrire la
     * date du jour et d'afficher « Liaison active » sans interroger personne :
     * il annonçait donc « active » pour un dossier supprimé, une clé révoquée
     * ou un Comptaflow éteint. Il appelle maintenant ce point d'entrée.
     */
    public function verify(Request $request)
    {
        if ($refus = $this->refusDeSecret($request, 'api/external/companies/verify')) {
            return $refus;
        }

        $company = $this->entrepriseDeLaRequete($request);

        if (!$company) {
            return response()->json(['success' => false, 'message' => 'Entreprise introuvable.'], 404);
        }

        $revoquee = (bool) $company->selflow_sync_key_revoked_at;

        return response()->json([
            'success'            => true,
            'company_id'         => $company->id,
            'selflow_company_id' => $company->selflow_company_id,
            'nom'                => $company->company_name,
            'active'             => !$revoquee,
            'revoked_at'         => $revoquee ? $company->selflow_sync_key_revoked_at->toIso8601String() : null,
            'linked_at'          => $company->selflow_linked_at?->toIso8601String(),
            'last_deposit_at'    => $company->selflow_last_deposit_at?->toIso8601String(),
            // La date du dernier renouvellement : c'est elle que la tâche
            // mensuelle de Selflow regarde pour décider quelles clés ont passé
            // trente jours. `linked_at`, elle, se remet à zéro pour de tout
            // autres raisons — un reprovisionnement, par exemple.
            'key_rotated_at'     => $company->selflow_sync_key_rotated_at?->toIso8601String(),
            'message'            => $revoquee
                ? 'Liaison révoquée le ' . $company->selflow_sync_key_revoked_at->format('d/m/Y') . '.'
                : 'Liaison active.',
        ]);
    }

    /**
     * Le dossier visé par la requête.
     *
     * `entreprise_liee` est posé par le filtre `cle.entreprise` depuis la clé
     * d'en-tête, seule source digne de foi. Le repli sur le corps est la
     * **tolérance de transition** : il se retire en même temps que celle du
     * filtre, les deux vont par paire.
     */
    private function entrepriseDeLaRequete(Request $request): ?Company
    {
        $entreprise = $request->attributes->get('entreprise_liee');

        if ($entreprise instanceof Company) {
            return $entreprise;
        }

        // TOLÉRANCE DE TRANSITION — à retirer avec celle de VerifieCleEntreprise.
        if ($request->filled('comptaflow_company_id')) {
            return Company::find($request->input('comptaflow_company_id'));
        }

        return Company::where('selflow_company_id', $request->input('selflow_company_id'))->first();
    }
}
