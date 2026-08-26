<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\LienActivationMail;
use App\Models\Company;
use App\Models\TreasuryCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
     * Le préfixe des clés de liaison.
     *
     * Il n'a pas d'usage technique : il permet de reconnaître un secret quand
     * il traîne — dans un journal applicatif, dans un presse-papiers, dans une
     * capture d'écran de support — et donc de savoir qu'il faut le révoquer.
     */
    private const PREFIXE_CLE = 'cptf_live_';

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
            'entreprise.admin_email'      => 'required|email|max:255',
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
                $cle = $this->poserUneCle($existante);
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
                $cle = $this->poserUneCle($existante);
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

            $admin = $this->creerLAdministrateur($company, $entreprise);
            $company->update(['user_id' => $admin->id]);

            foreach ([
                'I. Flux de trésorerie des activités opérationnelles',
                'II. Flux de trésorerie des activités d\'investissement',
                'III. Flux de trésorerie des activités de financement',
            ] as $categorie) {
                TreasuryCategory::create(['name' => $categorie, 'company_id' => $company->id]);
            }

            $cle = $this->poserUneCle($company);

            DB::commit();

            $this->envoyerLeLienDActivation($admin, $company);

            Log::info('Liaison Selflow : dossier provisionné', [
                'company_id'         => $company->id,
                'selflow_company_id' => $company->selflow_company_id,
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
     * Génère une clé, la range hachée et chiffrée, et rend sa valeur en clair.
     *
     * C'est le seul instant où la clé existe en clair côté Comptaflow — le
     * temps de la mettre dans la réponse.
     */
    private function poserUneCle(Company $company): string
    {
        $cle = self::PREFIXE_CLE . Str::random(40);

        $company->forceFill([
            'selflow_sync_key'            => null,
            'selflow_sync_key_hash'       => hash('sha256', $cle),
            'selflow_sync_key_chiffree'   => Crypt::encryptString($cle),
            'selflow_sync_key_revoked_at' => null,
            'selflow_sync_status'         => 'active',
            'selflow_linked_at'           => now(),
        ])->save();

        return $cle;
    }

    /**
     * Le compte administrateur du dossier — **sans mot de passe transmis**.
     *
     * `register-enterprise` exigeait `admin_password` : le superadministrateur
     * Selflow choisissait le mot de passe du compte d'un client, et cette
     * valeur traversait la passerelle en clair dans le corps de la requête.
     * Le compte reçoit ici un secret aléatoire que personne ne détient, et son
     * titulaire choisit le sien depuis un lien envoyé à son adresse.
     */
    private function creerLAdministrateur(Company $company, array $entreprise): User
    {
        $email = $entreprise['admin_email'];
        $existant = User::where('email_adresse', $email)->first();

        if ($existant) {
            // L'adresse sert déjà à un compte : on ne le réécrit pas, et on ne
            // le rattache pas ailleurs. Le dossier est créé quand même — c'est
            // le rôle de `provision` — et le rattachement se règle à la main.
            Log::warning('Liaison Selflow : l\'adresse de l\'administrateur est déjà prise', [
                'company_id' => $company->id,
                'email'      => $email,
            ]);

            return $existant;
        }

        $admin = User::create([
            'name'          => $entreprise['admin_nom'] ?? 'Administrateur',
            'last_name'     => '',
            'email_adresse' => $email,
            // Un secret aléatoire que personne ne connaît : le compte existe,
            // mais aucun mot de passe n'ouvre la session tant que son titulaire
            // n'a pas suivi le lien d'activation.
            'password'      => Hash::make(Str::random(64)),
            'role'          => 'admin',
            'company_id'    => $company->id,
            'is_active'     => true,
        ]);

        return $admin;
    }

    /**
     * Le lien d'activation, envoyé après le commit.
     *
     * Hors transaction, et sans faire échouer le provisionnement : un serveur
     * de messagerie indisponible ne doit pas annuler un dossier créé ni priver
     * Selflow de la clé qu'il attend. Le lien se renvoie depuis Comptaflow.
     */
    private function envoyerLeLienDActivation(User $admin, Company $company): void
    {
        try {
            $jeton = Str::random(64);

            $admin->forceFill([
                // Haché en base : la table `users` est lue par bien plus de code
                // que ce lot, et un jeton en clair vaut un mot de passe tant
                // qu'il n'a pas servi.
                'activation_token'            => hash('sha256', $jeton),
                'activation_token_expires_at' => now()->addDays(7),
            ])->save();

            Mail::to($admin->email_adresse)->send(new LienActivationMail($admin, $company, $jeton));
        } catch (\Throwable $e) {
            Log::error('Liaison Selflow : lien d\'activation non envoyé', [
                'company_id' => $company->id,
                'user_id'    => $admin->id,
                'error'      => $e->getMessage(),
            ]);
        }
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
