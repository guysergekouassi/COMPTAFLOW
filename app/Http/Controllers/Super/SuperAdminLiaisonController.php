<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Models\TreasuryCategory;
use App\Support\LienDActivation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SuperAdminLiaisonController extends Controller
{
    /**
     * Dashboard des Liaisons (COMPTAFLOW ↔ SELFLOW).
     */
    public function index()
    {
        $companies = Company::orderBy('company_name')->get();
        $selflowCompanies = $this->fetchSelflowCompanies();

        return view('superadmin.liaisons', compact('companies', 'selflowCompanies'));
    }

    /**
     * CAS 1 : Créer une entreprise SELFLOW depuis une entreprise COMPTAFLOW existante
     * (utilise les MÊMES données et admin de COMPTAFLOW).
     */
    public function creerSurSelflow(Request $request)
    {
        $request->validate([
            'comptaflow_company_id' => 'required|exists:companies,id',
        ]);

        $company = Company::findOrFail($request->comptaflow_company_id);
        $adminUser = User::where('company_id', $company->id)->where('role', 'admin')->first();

        // L'entreprise doit retrouver **les mêmes accès** des deux côtés : c'est
        // l'empreinte du compte d'ici qui part là-bas, jamais un mot de passe —
        // et surtout pas un second, choisi par le superadministrateur pour le
        // compte d'un client, comme le formulaire le demandait jusqu'ici.
        // Sans compte administrateur, il n'y a pas d'accès à reprendre.
        if (!$adminUser) {
            return back()->with('error',
                "«{$company->company_name}» n'a pas de compte administrateur : créez-le d'abord, "
                . 'sinon le compte Selflow serait ouvert sans identifiants utilisables.');
        }

        $selflowUrl = config('app.selflow_api_url', 'http://127.0.0.1:8003');
        $secret     = config('external_sync.external_sync_secret');
        // La clé de liaison vient d'un seul endroit : elle est rangée hachée, et
        // un `sf_ . Str::random(32)` écrit en clair dans `selflow_sync_key` ne
        // serait plus reconnu par personne.
        $syncKey    = $company->poserUneCleDeLiaison();

        try {
            $response = Http::timeout(10)->post("{$selflowUrl}/api/external/register-enterprise", [
                'secret'                => $secret,
                'nom'                   => $company->company_name,
                'forme_juridique'       => $company->juridique_form ?? 'SARL',
                'email'                 => $company->email_adresse,
                'telephone'             => $company->phone_number,
                'adresse'               => $company->adresse,
                'ncc'                   => $company->ncc,
                'rccm'                  => $company->rccm,
                'compte_contribuable'   => $company->compte_contribuable,
                'regime_imposition'     => $company->regime,
                'gerant_nom'            => $adminUser->name,
                'gerant_prenom'         => $adminUser->last_name,
                'admin_password_hash'   => $adminUser->password,
                'comptaflow_company_id' => $company->id,
                'comptaflow_sync_key'   => $syncKey,
            ]);

            if ($response->successful() && $response->json('success')) {
                $sfCompanyId = $response->json('entreprise_id');

                // `poserUneCleDeLiaison()` a déjà rangé la clé et daté la liaison.
                // (`selflow_last_sync_at` n'existe ni en base ni dans `$fillable` :
                // cette ligne ne faisait rien. `selflow_linked_at` la remplace.)
                $company->update([
                    'selflow_company_id'  => $sfCompanyId,
                    'selflow_sync_status' => 'active',
                ]);

                return back()->with('success', "🚀 Entreprise SELFLOW créée et liée avec succès pour «{$company->company_name}» (ID Selflow: #{$sfCompanyId}). Le gérant s'y connecte avec les mêmes identifiants qu'ici.");
            } else {
                $msg = $response->json('message') ?? 'Erreur inconnue côté Selflow.';
                return back()->with('error', "Échec de création sur Selflow : {$msg}");
            }
        } catch (\Exception $e) {
            Log::error('[LIAISON COMPTAFLOW] Erreur création sur Selflow: ' . $e->getMessage());
            return back()->with('error', "Impossible de contacter le serveur Selflow : {$e->getMessage()}");
        }
    }

    /**
     * CAS 2 : Créer un compte COMPTAFLOW depuis une entreprise SELFLOW
     * (utilise les MÊMES données et admin de SELFLOW).
     */
    public function store(Request $request)
    {
        $request->validate([
            'selflow_company_id' => 'required|integer',
            'company_name'       => 'required|string|max:255',
            'email_adresse'      => 'required|email|max:255',
            // Plus d'`admin_password` : le superadministrateur choisissait ici le
            // mot de passe du compte d'un client. Le titulaire choisit le sien
            // depuis un lien envoyé à son adresse.
        ]);

        if (Company::where('company_name', $request->company_name)->exists()) {
            return back()->with('error', "Une entreprise nommée «{$request->company_name}» existe déjà dans COMPTAFLOW.");
        }
        if (User::where('email_adresse', $request->email_adresse)->exists()) {
            return back()->with('error', "Un compte utilisateur avec l'email «{$request->email_adresse}» existe déjà.");
        }

        DB::beginTransaction();
        try {
            // 1. Créer l'entreprise COMPTAFLOW
            $company = Company::create([
                'company_name'        => $request->company_name,
                'activity'            => $request->activity ?? 'Commercial',
                'juridique_form'      => $request->juridique_form ?? 'SARL',
                'social_capital'      => $request->social_capital ?? 0,
                'adresse'             => $request->adresse,
                'city'                => $request->city ?? 'Abidjan',
                'country'             => "Côte d'Ivoire",
                'phone_number'        => $request->phone_number,
                'email_adresse'       => $request->email_adresse,
                'ncc'                 => $request->ncc,
                'rccm'                => $request->rccm,
                'compte_contribuable' => $request->compte_contribuable,
                'regime'              => $request->regime,
                'is_active'           => true,
                'selflow_company_id'  => $request->selflow_company_id,
                'user_id'             => 0,
            ]);

            // La clé de liaison est générée ici — un seul endroit la produit, et
            // elle est rangée hachée. Le `'sf_' . Str::random(32)` écrit en clair
            // dans `selflow_sync_key` ne serait plus reconnu par personne.
            $syncKey = $company->poserUneCleDeLiaison();

            // 2. Créer l'utilisateur admin, **sans mot de passe utilisable**
            //
            // Le formulaire en demandait un, choisi par le superadministrateur
            // pour le compte d'un client. Le titulaire choisit le sien depuis le
            // lien envoyé plus bas ; personne d'autre ne le connaît.
            $adminUser = User::create([
                'name'          => $request->admin_nom ?? 'Admin',
                'last_name'     => $request->admin_prenom ?? '',
                'email_adresse' => $request->email_adresse,
                'password'      => Hash::make(Str::random(64)),
                'role'          => 'admin',
                'company_id'    => $company->id,
                'is_active'     => true,
            ]);

            $company->update(['user_id' => $adminUser->id]);

            // 3. Créer les catégories TFT par défaut
            foreach ([
                'I. Flux de trésorerie des activités opérationnelles',
                'II. Flux de trésorerie des activités d\'investissement',
                'III. Flux de trésorerie des activités de financement',
            ] as $catName) {
                TreasuryCategory::create(['name' => $catName, 'company_id' => $company->id]);
            }

            DB::commit();

            // 4. Le titulaire choisit son mot de passe lui-même.
            LienDActivation::envoyer($adminUser, $company);

            // 5. Notifier Selflow pour enregistrer la liaison
            $selflowUrl = config('app.selflow_api_url', 'http://127.0.0.1:8003');
            $secret     = config('external_sync.external_sync_secret');

            try {
                Http::timeout(10)->post("{$selflowUrl}/api/external/link-company", [
                    'secret'               => $secret,
                    'selflow_company_id'   => $request->selflow_company_id,
                    'comptaflow_company_id'=> $company->id,
                    'comptaflow_sync_key'  => $syncKey,
                ]);
            } catch (\Exception $e) {
                Log::warning('[LIAISON COMPTAFLOW] Auto-link Selflow notification failed: ' . $e->getMessage());
            }

            return redirect()->route('superadmin.liaisons.index')
                ->with('success', "✅ Compte COMPTAFLOW créé et lié avec succès pour «{$company->company_name}» (ID COMPTAFLOW: #{$company->id}). Un lien d'activation a été envoyé à {$adminUser->email_adresse} : le gérant y choisit son mot de passe.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[LIAISON COMPTAFLOW] Erreur création compte: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    /**
     * Liaison manuelle : on rapproche deux dossiers, **la clé est générée ici**.
     *
     * Le formulaire demandait la clé de synchronisation dans un champ de texte
     * libre. C'est exactement la faille que ce lot ferme de l'autre côté : coller
     * la clé d'une autre entreprise ouvrait la liaison vers ses livres. Et depuis
     * que la reconnaissance se fait par haché, une clé collée à la main n'aurait
     * de toute façon plus été reconnue par personne.
     *
     * Comptaflow génère, Selflow range : c'est le sens du modèle, et c'est déjà
     * ce que fait `store()` juste au-dessus.
     */
    public function lierManuellement(Request $request)
    {
        $request->validate([
            'comptaflow_company_id' => 'required|exists:companies,id',
            'selflow_company_id'    => 'required|integer',
        ]);

        $company = Company::findOrFail($request->comptaflow_company_id);

        // `selflow_company_id` est unique en base : deux dossiers rattachés à la
        // même entreprise Selflow rendraient le déversement indéterminé. On le
        // dit ici plutôt que de laisser remonter une erreur SQL.
        $deja = Company::where('selflow_company_id', $request->selflow_company_id)
            ->where('id', '!=', $company->id)
            ->first();

        if ($deja) {
            return back()->with('error',
                "L'entreprise Selflow n° {$request->selflow_company_id} est déjà rattachée à "
                . "«{$deja->company_name}». Déliez ce dossier avant de la rattacher ailleurs : "
                . 'deux dossiers pour la même entreprise se partageraient ses écritures.');
        }

        $company->update(['selflow_company_id' => $request->selflow_company_id]);
        $syncKey = $company->poserUneCleDeLiaison();

        // Selflow doit ranger la clé, sans quoi il n'aura rien à présenter.
        $selflowUrl = config('app.selflow_api_url', 'http://127.0.0.1:8003');

        try {
            $reponse = Http::timeout(10)->post("{$selflowUrl}/api/external/link-company", [
                'secret'                => config('external_sync.external_sync_secret'),
                'selflow_company_id'    => $request->selflow_company_id,
                'comptaflow_company_id' => $company->id,
                'comptaflow_sync_key'   => $syncKey,
            ]);

            if (!$reponse->successful() || !$reponse->json('success')) {
                return back()->with('error',
                    "Le dossier est rattaché ici, mais Selflow n'a pas rangé la clé ("
                    . ($reponse->json('message') ?? 'réponse inattendue')
                    . '). Relancez la liaison : sans la clé, aucun déversement ne passera.');
            }
        } catch (\Exception $e) {
            Log::warning('[LIAISON COMPTAFLOW] Selflow injoignable à la liaison manuelle: ' . $e->getMessage());

            return back()->with('error',
                "Le dossier est rattaché ici, mais Selflow est injoignable ({$e->getMessage()}). "
                . 'Relancez la liaison : sans la clé, aucun déversement ne passera.');
        }

        return back()->with('success', "🔌 Liaison établie avec succès pour «{$company->company_name}».");
    }

    /**
     * Supprimer la liaison d'une entreprise.
     *
     * **La clé est révoquée, pas effacée.** Vider `selflow_sync_key` ne suffisait
     * plus : depuis que la reconnaissance porte sur le haché, un dossier délié
     * ainsi aurait gardé une clé parfaitement valide, et Selflow aurait continué
     * d'écrire dans ses livres. On la date révoquée, comme le fait
     * `companies/revoke` — et un appel refusé peut alors dire « révoquée le … »
     * plutôt que « inconnue ».
     *
     * Le dossier et ses écritures restent : délier n'est pas supprimer.
     */
    public function destroy($id)
    {
        $company = Company::findOrFail($id);
        $name = $company->company_name;

        $company->forceFill([
            'selflow_company_id'          => null,
            'selflow_sync_key'            => null,
            'selflow_sync_key_revoked_at' => now(),
            'selflow_sync_status'         => 'revoked',
        ])->save();

        Log::info('Liaison Selflow : déliée depuis l\'écran superadministrateur', [
            'company_id' => $company->id,
        ]);

        return back()->with('success', "🔌 Liaison supprimée pour «{$name}». Les écritures déjà reçues sont conservées.");
    }

    /**
     * Récupère la liste des entreprises Selflow via API.
     *
     * **Sans `X-Company-Key`, et c'est voulu** : cet écran sert à rapprocher un
     * dossier qui n'est justement pas encore lié, donc il n'y a pas de clé à
     * présenter. Avec une clé, Selflow ne rendrait que ce dossier-là.
     *
     * Selflow a réduit ce que ce point d'entrée rend : il livrait **toutes les
     * entreprises de la plateforme** avec leur adresse, leur NCC, leur RCCM et
     * l'adresse électronique de leur administrateur, pour qui détenait le secret
     * partagé. Il ne rend plus que `{id, uuid, nom, created_at, is_linked,
     * comptaflow_status}` — de quoi rapprocher un dossier, rien de plus. Le
     * détail se demande par `company-info`, en présentant la clé, donc une fois
     * la liaison faite.
     */
    private function fetchSelflowCompanies(): array
    {
        $selflowUrl = config('app.selflow_api_url', 'http://127.0.0.1:8003');
        $secret     = config('external_sync.external_sync_secret');

        try {
            $response = Http::timeout(5)->post("{$selflowUrl}/api/external/list-companies", [
                'secret' => $secret,
            ]);

            if ($response->successful() && $response->json('success')) {
                return $response->json('companies', []);
            }
        } catch (\Exception $e) {
            Log::warning('[LIAISON COMPTAFLOW] Erreur récupération entreprises Selflow: ' . $e->getMessage());
        }

        return [];
    }
}
