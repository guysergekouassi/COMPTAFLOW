<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\TreasuryCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminIntegrationController extends Controller
{
    private const PERMISSIONS = ['referentiel', 'ecritures', 'pieces_justificatives'];

    public function maison() { return $this->index('maison'); }
    public function externe() { return $this->index('externe'); }

    private function index(string $type)
    {
        $this->initialiserApplicationsMaison();
        $apps = DB::table('integration_applications')->where('type', $type)
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")->orderBy('name')->get();
        foreach ($apps as $app) {
            $app->links = DB::table('integration_application_companies as l')
                ->join('companies as c', 'c.id', '=', 'l.company_id')
                ->where('l.integration_application_id', $app->id)
                ->orderBy('c.company_name')
                ->get(['l.company_id', 'l.permissions', 'l.status', 'l.linked_at', 'c.company_name']);
        }
        $companies = Company::query()->where('is_active', true)->where('is_blocked', false)
            ->orderBy('company_name')->get(['id', 'company_name', 'email_adresse']);
        $events = DB::table('integration_api_events')
            ->leftJoin('integration_applications', 'integration_api_events.integration_application_id', '=', 'integration_applications.id')
            ->leftJoin('companies', 'integration_api_events.company_id', '=', 'companies.id')
            ->orderByDesc('integration_api_events.id')->limit(60)
            ->get(['integration_api_events.*', 'integration_applications.name as application_name', 'companies.company_name']);
        return view('superadmin.liaison_compta_apk', compact('type', 'apps', 'companies', 'events'));
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
        foreach (Company::query()->whereNotNull('selflow_company_id')->whereNull('selflow_sync_key_revoked_at')->get(['id']) as $company) {
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
            'owner_name' => 'nullable|string|max:160', 'owner_email' => 'nullable|email|max:191',
            'description' => 'nullable|string|max:2000',
        ]);
        if ($data['type'] === 'externe' && empty($data['owner_name'])) {
            return back()->withErrors(['owner_name' => 'Le propriétaire est obligatoire pour une application externe.'])->withInput();
        }
        $base = Str::slug($data['name']) ?: 'application';
        $code = $base; $suffix = 2;
        while (DB::table('integration_applications')->where('code', $code)->exists()) $code = $base . '-' . $suffix++;
        $clientId = 'cf_app_' . Str::lower(Str::random(28));
        $secret = 'cf_secret_' . Str::random(64);
        $now = now();
        $id = DB::table('integration_applications')->insertGetId([
            'code' => $code, 'name' => $data['name'], 'owner_name' => $data['owner_name'] ?? null,
            'owner_email' => $data['owner_email'] ?? null, 'description' => $data['description'] ?? null,
            'type' => $data['type'], 'status' => 'active', 'auth_mode' => 'bearer', 'client_id' => $clientId,
            'client_secret_hash' => Hash::make($secret), 'client_secret_expires_at' => $now->copy()->addYear(),
            'secret_rotated_at' => $now, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->journal($id, null, 'application.created', 201);
        return redirect()->route($data['type'] === 'maison' ? 'superadmin.liaison_compta.maison' : 'superadmin.liaison_compta.externe')
            ->with('integration_credentials', ['client_id' => $clientId, 'client_secret' => $secret])
            ->with('success', 'Application enregistrée. Copiez le secret maintenant : il ne sera plus affiché.');
    }

    public function update(Request $request, int $id)
    {
        $app = $this->application($id);
        abort_unless($app->status !== 'revoked' && $app->auth_mode !== 'planned', 422, 'Cette application est en attente ou définitivement révoquée.');
        $data = $request->validate([
            'name' => 'required|string|max:160', 'owner_name' => 'nullable|string|max:160',
            'owner_email' => 'nullable|email|max:191', 'description' => 'nullable|string|max:2000',
            'status' => 'required|in:active,suspended',
        ]);
        DB::table('integration_applications')->where('id', $id)->update($data + ['updated_at' => now()]);
        $this->journal($id, null, 'application.updated', 200);
        return back()->with('success', 'Application mise à jour.');
    }

    public function lier(Request $request, int $id)
    {
        $app = $this->application($id);
        abort_unless($app->status === 'active', 422, 'Cette application n’est pas active.');
        $data = $request->validate([
            'company_id' => 'required|integer|exists:companies,id',
            'permissions' => 'nullable|array', 'permissions.*' => 'in:referentiel,ecritures,pieces_justificatives',
        ]);
        $company = Company::query()->whereKey($data['company_id'])->where('is_active', true)->where('is_blocked', false)->firstOrFail();
        if ($app->type === 'externe' && DB::table('integration_application_companies')
            ->where('integration_application_id', $id)->where('status', 'active')->where('company_id', '<>', $company->id)->exists()) {
            return back()->with('error', 'Une application externe ne peut être liée qu’à son unique dossier dédié. Révoquez d’abord la liaison existante.');
        }
        DB::table('integration_application_companies')->updateOrInsert(
            ['integration_application_id' => $id, 'company_id' => $company->id],
            ['permissions' => json_encode(array_values(array_intersect($data['permissions'] ?? [], self::PERMISSIONS))),
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
            'permissions' => 'nullable|array', 'permissions.*' => 'in:referentiel,ecritures,pieces_justificatives',
        ]);
        $company = DB::transaction(function () use ($data, $id) {
            $company = Company::create([
                'company_name' => $data['company_name'], 'activity' => $data['activity'],
                'juridique_form' => $data['juridique_form'], 'social_capital' => 0,
                'adresse' => $data['adresse'] ?? '', 'code_postal' => '', 'city' => $data['city'] ?? 'Abidjan',
                'country' => $data['country'] ?? "Côte d'Ivoire", 'phone_number' => $data['phone_number'] ?? '',
                'email_adresse' => $data['email_adresse'], 'is_active' => true, 'user_id' => 0,
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
            'details' => json_encode(['actor_user_id' => auth()->id()]), 'created_at' => now(),
        ]);
    }
}
