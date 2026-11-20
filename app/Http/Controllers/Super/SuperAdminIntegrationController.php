<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ExerciceComptable;
use App\Models\TreasuryCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use App\Support\LienDActivation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminIntegrationController extends Controller
{
    private const PERMISSIONS = ['referentiel', 'ecritures', 'pieces_justificatives'];

    public function maison(Request $request) { return $this->index('maison', $request->integer('application')); }
    public function externe() { return $this->index('externe'); }

    private function index(string $type, ?int $selectedApplicationId = null)
    {
        $this->initialiserApplicationsMaison();
        $apps = DB::table('integration_applications')->where('type', $type)
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")->orderBy('name')->get();
        $allApps = $apps;
        if ($type === 'maison') {
            if ($selectedApplicationId) {
                $apps = $apps->where('id', $selectedApplicationId)->values();
                abort_if($apps->isEmpty(), 404, 'Application introuvable dans cette catégorie.');
            } else {
                $apps = collect();
            }
        }
        foreach ($apps as $app) {
            $app->links = DB::table('integration_application_companies as l')
                ->join('companies as c', 'c.id', '=', 'l.company_id')
                ->where('l.integration_application_id', $app->id)
                ->orderBy('c.company_name')
                ->get(['l.company_id', 'l.partner_company_id', 'l.partner_company_name', 'l.permissions', 'l.status', 'l.linked_at', 'c.company_name']);
        }
        $companies = Company::query()->where('is_active', true)->where('is_blocked', false)
            ->orderBy('company_name')->get(['id', 'company_name', 'email_adresse']);
        $events = DB::table('integration_api_events')
            ->leftJoin('integration_applications', 'integration_api_events.integration_application_id', '=', 'integration_applications.id')
            ->leftJoin('companies', 'integration_api_events.company_id', '=', 'companies.id')
            ->orderByDesc('integration_api_events.id')->limit(60)
            ->get(['integration_api_events.*', 'integration_applications.name as application_name', 'companies.company_name']);
        $companyRequests = DB::table('integration_company_requests as r')
            ->join('integration_applications as a', 'a.id', '=', 'r.integration_application_id')
            ->where('r.status', 'pending')->orderBy('r.created_at')
            ->get(['r.*', 'a.name as application_name']);
        return view('superadmin.liaison_compta_apk', compact('type', 'apps', 'allApps', 'selectedApplicationId', 'companies', 'events', 'companyRequests'));
    }

    /** SELFLOW est repris en mode historique ; RH-FLOW est référencé comme prévu,
     * sans modifier son dépôt ni prétendre que son connecteur est livré. */
    private function initialiserApplicationsMaison(): void
    {
        $now = now();
        if (!DB::table('integration_applications')->where('code', 'selflow')->exists()) {
            DB::table('integration_applications')->insert([
                'code' => 'selflow', 'name' => 'SELFLOW', 'type' => 'maison',
                'status' => 'active', 'auth_mode' => 'legacy',
                'description' => 'Application maison existante. Clés par dossier conservées pendant la migration contrôlée vers Bearer.',
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        if (!DB::table('integration_applications')->where('code', 'rh-flow')->exists()) {
            DB::table('integration_applications')->insert([
                'code' => 'rh-flow', 'name' => 'RH-FLOW', 'type' => 'maison',
                'status' => 'pending', 'auth_mode' => 'planned',
                'description' => 'Application prévue : contrat de données documenté, connecteur non livré dans ce lot.',
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $selflow = DB::table('integration_applications')->where('code', 'selflow')->first();
        foreach (Company::query()->whereNotNull('selflow_company_id')->whereNull('selflow_sync_key_revoked_at')
            ->whereNotNull('selflow_sync_key_hash')
            ->where(function ($query) {
                $query->whereNull('selflow_sync_status')->orWhere('selflow_sync_status', 'active');
            })->get(['id']) as $company) {
            $exists = DB::table('integration_application_companies')->where('integration_application_id', $selflow->id)->where('company_id', $company->id)->exists();
            if (!$exists) DB::table('integration_application_companies')->insert([
                'integration_application_id' => $selflow->id, 'company_id' => $company->id,
                'permissions' => json_encode(self::PERMISSIONS), 'status' => 'active', 'linked_at' => $now,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:160', 'type' => 'required|in:maison,externe',
            'app_url' => ['required', 'url', 'starts_with:https://', 'max:500'],
            'owner_name' => 'nullable|string|max:160', 'owner_email' => 'nullable|email|max:191',
            'description' => 'nullable|string|max:2000',
            'validity_days' => 'nullable|in:90,180,365,730',
        ]);
        if ($data['type'] === 'externe' && empty($data['owner_name'])) {
            return back()->withErrors(['owner_name' => 'Le propriétaire est obligatoire pour une application externe.'])->withInput();
        }
        $base = Str::slug($data['name']) ?: 'application';
        $code = $base; $suffix = 2;
        while (DB::table('integration_applications')->where('code', $code)->exists()) $code = $base . '-' . $suffix++;
        $clientId = 'cf_app_' . Str::lower(Str::random(28));
        $secret = 'cf_secret_' . Str::random(64);
        $validityDays = $data['type'] === 'maison' ? (int) ($data['validity_days'] ?? 365) : 365;
        $now = now();
        $id = DB::table('integration_applications')->insertGetId([
            'code' => $code, 'name' => $data['name'], 'owner_name' => $data['owner_name'] ?? null,
            'owner_email' => $data['owner_email'] ?? null, 'description' => $data['description'] ?? null,
            'type' => $data['type'], 'status' => 'active', 'auth_mode' => 'bearer', 'client_id' => $clientId,
            'app_url' => rtrim($data['app_url'], '/'),
            'pairing_code_hash' => hash('sha256', $pairingCode = 'cf_link_' . Str::random(48)),
            'pairing_code_encrypted' => Crypt::encryptString($pairingCode),
            'pairing_code_expires_at' => $data['type'] === 'maison' ? $now->copy()->addDays($validityDays) : null, 'pairing_rotated_at' => $now,
            'client_secret_hash' => Hash::make($secret), 'client_secret_expires_at' => $now->copy()->addYear(),
            'secret_rotated_at' => $now, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->journal($id, null, 'application.created', 201);
        $redirect = redirect()->route($data['type'] === 'maison' ? 'superadmin.liaison_compta.maison' : 'superadmin.liaison_compta.externe');
        if ($data['type'] === 'maison') {
            return $redirect
                ->with('integration_pairing_code', ['application' => $data['name'], 'code' => $pairingCode, 'expires_at' => $now->copy()->addDays($validityDays)->toDateTimeString(), 'validity_days' => $validityDays])
                ->with('success', 'Application enregistrée. Copiez le code dans l’application partenaire.');
        }
        return $redirect
            ->with('integration_credentials', ['client_id' => $clientId, 'client_secret' => $secret])
            ->with('success', 'Application enregistrée. Copiez les identifiants API maintenant : le secret ne sera plus affiché.');
    }

    public function update(Request $request, int $id)
    {
        $app = $this->application($id);
        abort_unless($app->status !== 'revoked', 422, 'Cette application est définitivement révoquée.');
        $data = $request->validate([
            'name' => 'required|string|max:160', 'app_url' => ['nullable', 'url', 'starts_with:https://', 'max:500'], 'owner_name' => 'nullable|string|max:160',
            'owner_email' => 'nullable|email|max:191', 'description' => 'nullable|string|max:2000',
            'status' => 'required|in:active,pending,suspended',
        ]);
        if (array_key_exists('app_url', $data)) $data['app_url'] = $data['app_url'] ? rtrim($data['app_url'], '/') : null;
        DB::table('integration_applications')->where('id', $id)->update($data + ['updated_at' => now()]);
        $this->journal($id, null, 'application.updated', 200);
        return back()->with('success', 'Application mise à jour.');
    }

    public function lier(Request $request, int $id)
    {
        $app = $this->application($id);
        abort_unless($app->type === 'maison', 403, 'Une application externe doit disposer de son propre dossier créé depuis son parcours dédié.');
        abort_unless($app->status === 'active', 422, 'Cette application n’est pas active.');
        $data = $request->validate([
            'company_id' => 'required|integer|exists:companies,id',
            'partner_company_id' => 'required|string|max:100',
            'partner_company_name' => 'required|string|max:255',
            'permissions' => 'nullable|array', 'permissions.*' => 'in:referentiel,ecritures,pieces_justificatives',
        ]);
        try {
            $remote = collect($this->chargerCataloguePartenaire($app))->firstWhere('id', (string) $data['partner_company_id']);
        } catch (\Throwable $e) {
            return back()->with('error', 'Impossible de vérifier l’entreprise partenaire. La liaison n’a pas été enregistrée.');
        }
        if (!$remote || !hash_equals((string) $remote['name'], (string) $data['partner_company_name'])) {
            return back()->with('error', 'L’entreprise partenaire sélectionnée n’existe plus dans le catalogue distant.');
        }
        $remoteAlreadyLinked = DB::table('integration_application_companies')
            ->where('integration_application_id', $id)
            ->where('partner_company_id', $data['partner_company_id'])
            ->where('status', 'active')
            ->where('company_id', '<>', $data['company_id'])
            ->exists();
        if ($remoteAlreadyLinked) {
            return back()->with('error', 'Cette entreprise partenaire est déjà associée à un autre dossier COMPTAFLOW.');
        }
        $company = Company::query()->whereKey($data['company_id'])->where('is_active', true)->where('is_blocked', false)->firstOrFail();
        if ($app->code === 'selflow' && (
            !$company->selflow_company_id
            || ($company->selflow_sync_status && $company->selflow_sync_status !== 'active')
            || $company->selflow_sync_key_revoked_at
            || !$company->selflow_sync_key_hash
        )) {
            return back()->with('error', 'Pour SELFLOW, établissez ou rétablissez d’abord la liaison depuis le parcours SELFLOW. Le registre central reprend ensuite automatiquement le dossier et son identifiant source.');
        }
        if ($app->type === 'externe' && DB::table('integration_application_companies')
            ->where('integration_application_id', $id)->where('status', 'active')->where('company_id', '<>', $company->id)->exists()) {
            return back()->with('error', 'Une application externe ne peut être liée qu’à son unique dossier dédié. Révoquez d’abord la liaison existante.');
        }
        DB::table('integration_application_companies')->updateOrInsert(
            ['integration_application_id' => $id, 'company_id' => $company->id],
            ['partner_company_id' => $data['partner_company_id'], 'partner_company_name' => $data['partner_company_name'],
                'permissions' => json_encode(array_values(array_intersect($data['permissions'] ?? self::PERMISSIONS, self::PERMISSIONS))),
                'status' => 'active', 'linked_at' => now(), 'revoked_at' => null, 'created_at' => now(), 'updated_at' => now()]
        );
        $this->journal($id, $company->id, 'link.created', 200);
        return back()->with('success', 'Entreprise liée. Le périmètre est contrôlé côté serveur.');
    }

    public function creerEntrepriseEtLier(Request $request, int $id)
    {
        $app = $this->application($id);
        abort_unless($app->type === 'externe', 403);
        abort_unless($app->status === 'active', 422);
        if (DB::table('integration_application_companies')->where('integration_application_id', $id)->where('status', 'active')->exists()) {
            return back()->with('error', 'Cette application externe possède déjà un dossier actif.');
        }
        $data = $request->validate([
            'company_name' => 'required|string|max:255|unique:companies,company_name',
            'activity' => 'required|string|max:255', 'juridique_form' => 'required|string|max:50',
            'email_adresse' => 'required|email|max:191|unique:companies,email_adresse|unique:users,email_adresse',
            'adresse' => 'nullable|string|max:255', 'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100', 'phone_number' => 'nullable|string|max:30',
            'owner_contact' => 'nullable|string|max:150',
            'exercice_debut' => 'required|date',
            'exercice_fin' => 'required|date|after:exercice_debut',
            'exercice_intitule' => 'nullable|string|max:150',
            'permissions' => 'nullable|array', 'permissions.*' => 'in:referentiel,ecritures,pieces_justificatives',
        ]);
        $company = DB::transaction(function () use ($data, $id) {
            $company = Company::create([
                'company_name' => $data['company_name'], 'activity' => $data['activity'],
                'juridique_form' => $data['juridique_form'], 'social_capital' => 0,
                'adresse' => $data['adresse'] ?? '', 'code_postal' => '', 'city' => $data['city'] ?? 'Abidjan',
                'country' => $data['country'] ?? "Côte d'Ivoire", 'phone_number' => $data['phone_number'] ?? '',
                'email_adresse' => $data['email_adresse'], 'is_active' => true,
            ]);
            $admin = User::create([
                'name' => $data['owner_contact'] ?: 'Compte intégration', 'last_name' => 'Technique',
                'email_adresse' => $data['email_adresse'], 'password' => Hash::make(Str::random(80)),
                'role' => 'admin', 'company_id' => $company->id, 'is_active' => true,
            ]);
            $company->update(['user_id' => $admin->id]);
            foreach ([
                'I. Flux de trésorerie des activités opérationnelles',
                'II. Flux de trésorerie des activités d’investissement',
                'III. Flux de trésorerie des activités de financement',
            ] as $name) TreasuryCategory::create(['name' => $name, 'company_id' => $company->id]);

            // Un dossier sans exercice ne peut pas recevoir d'écritures.
            // Les dates sont donc demandées explicitement au Super Admin ;
            // aucune période comptable n'est inventée.
            ExerciceComptable::create([
                'company_id' => $company->id,
                'parent_company_id' => $company->parent_company_id ?: $company->id,
                'user_id' => $admin->id,
                'date_debut' => $data['exercice_debut'],
                'date_fin' => $data['exercice_fin'],
                'intitule' => filled($data['exercice_intitule'] ?? null)
                    ? $data['exercice_intitule']
                    : ('Exercice ' . substr((string) $data['exercice_debut'], 0, 4)),
                'is_active' => true,
            ]);

            DB::table('integration_application_companies')->insert([
                'integration_application_id' => $id, 'company_id' => $company->id,
                'permissions' => json_encode(array_values(array_intersect($data['permissions'] ?? self::PERMISSIONS, self::PERMISSIONS))),
                'status' => 'active', 'linked_at' => now(), 'created_at' => now(), 'updated_at' => now(),
            ]);
            return $company;
        });
        $this->journal($id, $company->id, 'company.created_and_linked', 201);
        return back()->with('success', 'Dossier dédié créé et lié. Le compte technique d’import possède un mot de passe aléatoire non communiqué.');
    }

    /**
     * Validation manuelle d'une demande externe. Le dossier et son compte
     * sont créés par COMPTAFLOW ; le titulaire reçoit un lien d'activation
     * temporaire pour choisir lui-même son mot de passe.
     */
    public function validerDemandeEntreprise(Request $request, int $requestId)
    {
        $data = $request->validate([
            'exercice_debut' => 'required|date',
            'exercice_fin' => 'required|date|after:exercice_debut',
            'exercice_intitule' => 'nullable|string|max:150',
        ]);
        $companyRequest = DB::table('integration_company_requests')->where('id', $requestId)->where('status', 'pending')->first();
        abort_unless($companyRequest, 404, 'Demande inexistante ou déjà traitée.');
        $app = $this->application((int) $companyRequest->integration_application_id);
        abort_unless($app->type === 'externe' && $app->status === 'active', 403);
        if (DB::table('integration_application_companies')->where('integration_application_id', $app->id)->where('status', 'active')->exists()) {
            return back()->with('error', 'Un dossier actif existe déjà pour cette application externe.');
        }

        $validator = \Illuminate\Support\Facades\Validator::make([
            'company_name' => $companyRequest->company_name,
            'email_adresse' => $companyRequest->manager_email,
        ], [
            'company_name' => 'required|string|max:255|unique:companies,company_name',
            'email_adresse' => 'required|email|max:191|unique:companies,email_adresse|unique:users,email_adresse',
        ]);
        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        [$company, $admin] = DB::transaction(function () use ($data, $companyRequest, $app, $requestId) {
            $company = Company::create([
                'company_name' => $companyRequest->company_name,
                'activity' => $companyRequest->activity ?: 'Activité à préciser',
                'juridique_form' => $companyRequest->legal_form ?: 'Autre',
                'social_capital' => 0, 'adresse' => '', 'code_postal' => '',
                'city' => 'Abidjan', 'country' => "Côte d'Ivoire",
                'phone_number' => $companyRequest->phone_number ?: '',
                'email_adresse' => $companyRequest->manager_email, 'is_active' => true,
            ]);
            $admin = User::create([
                'name' => $companyRequest->manager_name, 'last_name' => '',
                'email_adresse' => $companyRequest->manager_email,
                'password' => Hash::make(Str::random(96)),
                'role' => 'admin', 'company_id' => $company->id, 'is_active' => true,
            ]);
            $company->update(['user_id' => $admin->id]);
            foreach ([
                'I. Flux de trésorerie des activités opérationnelles',
                'II. Flux de trésorerie des activités d’investissement',
                'III. Flux de trésorerie des activités de financement',
            ] as $name) TreasuryCategory::create(['name' => $name, 'company_id' => $company->id]);
            ExerciceComptable::create([
                'company_id' => $company->id,
                'parent_company_id' => $company->parent_company_id ?: $company->id,
                'user_id' => $admin->id,
                'date_debut' => $data['exercice_debut'], 'date_fin' => $data['exercice_fin'],
                'intitule' => filled($data['exercice_intitule'] ?? null) ? $data['exercice_intitule'] : ('Exercice ' . substr((string) $data['exercice_debut'], 0, 4)),
                'is_active' => true,
            ]);
            DB::table('integration_application_companies')->insert([
                'integration_application_id' => $app->id, 'company_id' => $company->id,
                'permissions' => json_encode(self::PERMISSIONS), 'status' => 'active',
                'linked_at' => now(), 'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('integration_company_requests')->where('id', $requestId)->where('status', 'pending')->update([
                'status' => 'approved', 'company_id' => $company->id,
                'reviewed_by' => auth()->id(), 'reviewed_at' => now(), 'updated_at' => now(),
            ]);
            return [$company, $admin];
        });

        LienDActivation::envoyer($admin, $company);
        $this->journal((int) $app->id, (int) $company->id, 'company.request.approved', 201);
        return back()->with('success', 'Demande acceptée : dossier dédié créé, liaison établie et lien d’activation envoyé au gérant.');
    }

    public function rejeterDemandeEntreprise(Request $request, int $requestId)
    {
        $data = $request->validate(['rejection_reason' => 'required|string|max:1000']);
        $row = DB::table('integration_company_requests')->where('id', $requestId)->where('status', 'pending')->first();
        abort_unless($row, 404, 'Demande inexistante ou déjà traitée.');
        DB::table('integration_company_requests')->where('id', $requestId)->update([
            'status' => 'rejected', 'rejection_reason' => $data['rejection_reason'],
            'reviewed_by' => auth()->id(), 'reviewed_at' => now(), 'updated_at' => now(),
        ]);
        $this->journal((int) $row->integration_application_id, null, 'company.request.rejected_by_admin', 200);
        return back()->with('success', 'Demande rejetée et motif enregistré.');
    }

    public function delier(int $id, int $companyId)
    {
        $this->application($id);
        DB::table('integration_application_companies')->where('integration_application_id', $id)->where('company_id', $companyId)
            ->update(['status' => 'revoked', 'revoked_at' => now(), 'updated_at' => now()]);
        DB::table('integration_api_tokens')->where('integration_application_id', $id)->where('company_id', $companyId)
            ->whereNull('revoked_at')->update(['revoked_at' => now(), 'updated_at' => now()]);
        $this->journal($id, $companyId, 'link.revoked', 200);
        return back()->with('success', 'Liaison révoquée. Les écritures déjà reçues sont conservées.');
    }

    /**
     * Génère le code que le responsable colle dans l'application partenaire.
     * Le code est montré une seule fois ; le hash sert à la validation distante
     * et la valeur chiffrée est réservée aux appels sortants de découverte.
     */
    public function genererCodeLiaison(Request $request, int $id)
    {
        $data = $request->validate([
            'validity_days' => 'required|in:90,180,365,730',
        ]);
        $validityDays = (int) $data['validity_days'];
        $app = $this->application($id);
        abort_unless($app->type === 'maison', 403, 'Le code de liaison maison ne concerne que la Suite FLOW.');
        abort_unless($app->status !== 'revoked', 422, 'Cette application est révoquée.');
        abort_unless(!empty($app->app_url), 422, 'Enregistrez d’abord l’URL de l’application partenaire.');
        $code = 'cf_link_' . Str::random(48);
        $now = now();
        DB::table('integration_applications')->where('id', $id)->update([
            'status' => 'active', 'auth_mode' => 'bearer',
            'pairing_code_hash' => hash('sha256', $code),
            'pairing_code_encrypted' => Crypt::encryptString($code),
            'pairing_code_expires_at' => $now->copy()->addDays($validityDays),
            'pairing_code_used_at' => null, 'pairing_rotated_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('integration_api_tokens')->where('integration_application_id', $id)
            ->whereNull('revoked_at')->update(['revoked_at' => $now, 'updated_at' => $now]);
        $this->journal($id, null, 'pairing_code.generated', 200);
        return back()->with('integration_pairing_code', ['application' => $app->name, 'code' => $code, 'expires_at' => $now->copy()->addDays($validityDays)->toDateTimeString(), 'validity_days' => $validityDays])
            ->with('success', 'Code de liaison généré. Copiez-le dans l’application partenaire ; validité : ' . $validityDays . ' jours.');
    }

    /**
     * Proxy sécurisé vers le catalogue de dossiers du partenaire.
     * Contrat attendu côté application partenaire :
     * GET /api/integration/companies, header X-Integration-Pairing-Code,
     * réponse JSON { data: [{ id, name }] }.
     */
    public function entreprisesPartenaire(int $id)
    {
        $app = $this->application($id);
        abort_unless($app->type === 'maison' && $app->status === 'active', 403);
        abort_unless($app->app_url && $app->pairing_code_encrypted
            && (!$app->pairing_code_expires_at || now()->lessThanOrEqualTo($app->pairing_code_expires_at)), 422,
            'URL ou code de liaison absent/expiré. Régénérez le code de liaison.');
        try {
            $companies = $this->chargerCataloguePartenaire($app);
            $this->journal($id, null, 'partner.companies.listed', 200);
            return response()->json(['data' => $companies]);
        } catch (\Throwable $e) {
            $this->journal($id, null, 'partner.companies.error', 502);
            return response()->json(['message' => 'Impossible de joindre le catalogue de l’application partenaire. Vérifiez son URL et son connecteur API.'], 502);
        }
    }

    private function chargerCataloguePartenaire(object $app): array
    {
        $parts = parse_url((string) $app->app_url);
        if (!$parts || strtolower($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])) {
            throw new \RuntimeException('URL partenaire HTTPS invalide.');
        }
        $host = strtolower($parts['host']);
        $ip = filter_var($host, FILTER_VALIDATE_IP);
        if ($host === 'localhost' || str_ends_with($host, '.local') || ($ip && !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))) {
            throw new \RuntimeException('Hôte partenaire non autorisé.');
        }
        if (!$app->pairing_code_encrypted || ($app->pairing_code_expires_at && now()->greaterThan($app->pairing_code_expires_at))) {
            throw new \RuntimeException('Code de liaison expiré.');
        }
        $code = Crypt::decryptString($app->pairing_code_encrypted);
        $response = Http::acceptJson()->timeout(12)->withoutRedirecting()->withHeaders([
            'X-Integration-Pairing-Code' => $code,
            'X-Request-Id' => (string) Str::uuid(),
        ])->get(rtrim($app->app_url, '/') . '/api/integration/companies');
        if (!$response->successful()) throw new \RuntimeException('Catalogue partenaire indisponible.');
        $rows = $response->json('data');
        if (!is_array($rows)) throw new \RuntimeException('Format du catalogue partenaire invalide.');
        return collect($rows)->filter(fn ($row) => is_array($row) && isset($row['id'], $row['name']))
            ->map(fn ($row) => ['id' => (string) $row['id'], 'name' => (string) $row['name']])->values()->all();
    }

    public function renouvelerSecret(int $id)
    {
        $app = $this->application($id);
        abort_unless($app->status === 'active' && $app->auth_mode !== 'planned', 422, 'Cette application est en attente ou inactive.');
        $secret = 'cf_secret_' . Str::random(64); $now = now();
        $clientId = $app->client_id ?: ('cf_app_' . Str::lower(Str::random(28)));
        DB::table('integration_applications')->where('id', $id)->update([
            'client_id' => $clientId,
            'client_secret_hash' => Hash::make($secret), 'client_secret_expires_at' => $now->copy()->addYear(),
            'secret_rotated_at' => $now, 'auth_mode' => 'bearer', 'updated_at' => $now,
        ]);
        DB::table('integration_api_tokens')->where('integration_application_id', $id)->whereNull('revoked_at')
            ->update(['revoked_at' => $now, 'updated_at' => $now]);
        $this->journal($id, null, 'secret.rotated', 200);
        return back()->with('integration_credentials', ['client_id' => $clientId, 'client_secret' => $secret])
            ->with('success', 'Secret renouvelé ; tous les jetons existants sont révoqués. Copiez le nouveau secret maintenant.');
    }

    public function revoquer(int $id)
    {
        $this->application($id); $now = now();
        DB::table('integration_applications')->where('id', $id)->update(['status' => 'revoked', 'revoked_at' => $now, 'updated_at' => $now]);
        DB::table('integration_application_companies')->where('integration_application_id', $id)->where('status', 'active')
            ->update(['status' => 'revoked', 'revoked_at' => $now, 'updated_at' => $now]);
        DB::table('integration_api_tokens')->where('integration_application_id', $id)->whereNull('revoked_at')
            ->update(['revoked_at' => $now, 'updated_at' => $now]);
        $this->journal($id, null, 'application.revoked', 200);
        return back()->with('success', 'Application révoquée : ses liaisons et jetons ne donnent plus accès.');
    }

    private function application(int $id): object
    {
        $app = DB::table('integration_applications')->where('id', $id)->first();
        abort_unless($app, 404);
        return $app;
    }

    private function journal(?int $appId, ?int $companyId, string $event, int $status): void
    {
        DB::table('integration_api_events')->insert([
            'integration_application_id' => $appId, 'company_id' => $companyId, 'event' => $event,
            'route' => request()->path(), 'ip_address' => request()->ip(), 'http_status' => $status,
            'http_method' => request()->method(), 'request_id' => (string) Str::uuid(),
            'details' => json_encode(['actor_user_id' => auth()->id()]), 'created_at' => now(),
        ]);
    }
}
