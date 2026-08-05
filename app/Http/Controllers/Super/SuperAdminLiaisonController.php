<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Models\TreasuryCategory;
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
            'admin_password'        => 'required|string|min:8',
        ]);

        $company = Company::findOrFail($request->comptaflow_company_id);
        $adminUser = User::where('company_id', $company->id)->where('role', 'admin')->first();

        $selflowUrl = config('app.selflow_api_url', 'http://127.0.0.1:8003');
        $secret     = config('app.selflow_api_secret', 'selflow-comptaflow-secret-2026');
        $syncKey    = 'sf_' . Str::random(32);

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
                'gerant_nom'            => $adminUser ? $adminUser->name : 'Admin',
                'gerant_prenom'         => $adminUser ? $adminUser->last_name : '',
                'admin_password'        => $request->admin_password,
                'comptaflow_company_id' => $company->id,
                'comptaflow_sync_key'   => $syncKey,
            ]);

            if ($response->successful() && $response->json('success')) {
                $sfCompanyId = $response->json('entreprise_id');

                $company->update([
                    'selflow_company_id'  => $sfCompanyId,
                    'selflow_sync_key'    => $syncKey,
                    'selflow_sync_status' => 'active',
                    'selflow_last_sync_at'=> now(),
                ]);

                return back()->with('success', "🚀 Entreprise SELFLOW créée et liée avec succès pour «{$company->company_name}» (ID Selflow: #{$sfCompanyId}).");
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
            'admin_password'     => 'required|string|min:8',
        ]);

        if (Company::where('company_name', $request->company_name)->exists()) {
            return back()->with('error', "Une entreprise nommée «{$request->company_name}» existe déjà dans COMPTAFLOW.");
        }
        if (User::where('email_adresse', $request->email_adresse)->exists()) {
            return back()->with('error', "Un compte utilisateur avec l'email «{$request->email_adresse}» existe déjà.");
        }

        $syncKey = 'sf_' . Str::random(32);

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
                'selflow_sync_key'    => $syncKey,
                'selflow_sync_status' => 'active',
                'user_id'             => 0,
            ]);

            // 2. Créer l'utilisateur admin
            $adminUser = User::create([
                'name'          => $request->admin_nom ?? 'Admin',
                'last_name'     => $request->admin_prenom ?? '',
                'email_adresse' => $request->email_adresse,
                'password'      => Hash::make($request->admin_password),
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

            // 4. Notifier Selflow pour enregistrer la liaison
            $selflowUrl = config('app.selflow_api_url', 'http://127.0.0.1:8003');
            $secret     = config('app.selflow_api_secret', 'selflow-comptaflow-secret-2026');

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
                ->with('success', "✅ Compte COMPTAFLOW créé et lié avec succès pour «{$company->company_name}» (ID COMPTAFLOW: #{$company->id}).");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[LIAISON COMPTAFLOW] Erreur création compte: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    /**
     * Liaison manuelle (ID COMPTAFLOW + Clé Sync).
     */
    public function lierManuellement(Request $request)
    {
        $request->validate([
            'comptaflow_company_id' => 'required|exists:companies,id',
            'selflow_company_id'    => 'required|integer',
            'selflow_sync_key'      => 'required|string',
        ]);

        $company = Company::findOrFail($request->comptaflow_company_id);
        $company->update([
            'selflow_company_id'  => $request->selflow_company_id,
            'selflow_sync_key'    => $request->selflow_sync_key,
            'selflow_sync_status' => 'active',
            'selflow_last_sync_at'=> now(),
        ]);

        return back()->with('success', "🔌 Liaison établie avec succès pour «{$company->company_name}».");
    }

    /**
     * Supprimer la liaison d'une entreprise.
     */
    public function destroy($id)
    {
        $company = Company::findOrFail($id);
        $name = $company->company_name;

        $company->update([
            'selflow_company_id'  => null,
            'selflow_sync_key'    => null,
            'selflow_sync_status' => null,
            'selflow_last_sync_at'=> null,
        ]);

        return back()->with('success', "🔌 Liaison supprimée pour «{$name}».");
    }

    /**
     * Récupère la liste des entreprises Selflow via API.
     */
    private function fetchSelflowCompanies(): array
    {
        $selflowUrl = config('app.selflow_api_url', 'http://127.0.0.1:8003');
        $secret     = config('app.selflow_api_secret', 'selflow-comptaflow-secret-2026');

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
