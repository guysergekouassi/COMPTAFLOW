<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class IntegrationCompanyRequestController extends Controller
{
    /**
     * Une application externe demande la création de son dossier dédié.
     * Aucune entreprise existante n'est recherchée ou sélectionnable par l'appelant.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|string|max:100',
            'client_secret' => 'required|string|max:255',
            'request_id' => 'required|string|max:100|regex:/^[A-Za-z0-9._:-]+$/',
            'company_name' => 'required|string|max:255',
            'manager_name' => 'required|string|max:160',
            'manager_email' => 'required|email|max:191',
            'activity' => 'nullable|string|max:255',
            'legal_form' => 'nullable|string|max:80',
            'phone_number' => 'nullable|string|max:30',
        ]);

        $app = DB::table('integration_applications')->where('client_id', $data['client_id'])->first();
        $valid = $app && $app->type === 'externe' && $app->status === 'active'
            && $app->auth_mode === 'bearer' && $app->client_secret_hash
            && (!$app->client_secret_expires_at || now()->lessThanOrEqualTo($app->client_secret_expires_at))
            && Hash::check($data['client_secret'], $app->client_secret_hash);

        if (!$valid) {
            $this->journal($request, $app->id ?? null, null, 'company.request.rejected', 401, ['reason' => 'invalid_external_application']);
            return response()->json(['message' => 'Application externe non reconnue, inactive ou identifiants expirés.'], 401);
        }

        if (DB::table('integration_application_companies')->where('integration_application_id', $app->id)->where('status', 'active')->exists()) {
            $this->journal($request, $app->id, null, 'company.request.rejected', 409, ['reason' => 'dedicated_company_already_exists']);
            return response()->json(['message' => 'Un dossier dédié existe déjà pour cette application.'], 409);
        }

        $existing = DB::table('integration_company_requests')
            ->where('integration_application_id', $app->id)
            ->where('external_request_id', $data['request_id'])->first();

        if ($existing) {
            return response()->json([
                'success' => true, 'request_id' => $existing->external_request_id,
                'status' => $existing->status, 'message' => 'Demande déjà reçue : aucun doublon créé.',
            ], 200);
        }

        $id = DB::table('integration_company_requests')->insertGetId([
            'integration_application_id' => $app->id,
            'external_request_id' => $data['request_id'],
            'company_name' => $data['company_name'],
            'manager_name' => $data['manager_name'],
            'manager_email' => $data['manager_email'],
            'activity' => $data['activity'] ?? null,
            'legal_form' => $data['legal_form'] ?? null,
            'phone_number' => $data['phone_number'] ?? null,
            'payload' => json_encode([
                'company_name' => $data['company_name'], 'manager_name' => $data['manager_name'],
                'manager_email' => $data['manager_email'], 'activity' => $data['activity'] ?? null,
                'legal_form' => $data['legal_form'] ?? null, 'phone_number' => $data['phone_number'] ?? null,
            ], JSON_UNESCAPED_UNICODE),
            'status' => 'pending', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->journal($request, $app->id, null, 'company.request.received', 202, [
            'request_id' => $data['request_id'], 'company_name' => $data['company_name'],
            'manager_name' => $data['manager_name'], 'manager_email' => $data['manager_email'],
            'activity' => $data['activity'] ?? null, 'legal_form' => $data['legal_form'] ?? null,
        ]);

        return response()->json([
            'success' => true, 'request_id' => $data['request_id'], 'status' => 'pending',
            'message' => 'Demande reçue par COMPTAFLOW. Le dossier sera créé après validation.',
        ], 202);
    }

    /** Consultation d'une demande précise : aucune énumération globale n'est possible. */
    public function show(Request $request, string $requestId)
    {
        $data = $request->validate([
            'client_id' => 'required|string|max:100',
            'client_secret' => 'required|string|max:255',
        ]);
        $app = DB::table('integration_applications')->where('client_id', $data['client_id'])->first();
        $valid = $app && $app->type === 'externe' && $app->status === 'active'
            && $app->client_secret_hash
            && (!$app->client_secret_expires_at || now()->lessThanOrEqualTo($app->client_secret_expires_at))
            && Hash::check($data['client_secret'], $app->client_secret_hash);
        if (!$valid) return response()->json(['message' => 'Identifiants applicatifs invalides.'], 401);

        $row = DB::table('integration_company_requests')
            ->where('integration_application_id', $app->id)
            ->where('external_request_id', $requestId)->first();
        if (!$row) return response()->json(['message' => 'Demande introuvable pour cette application.'], 404);

        return response()->json([
            'request_id' => $row->external_request_id,
            'status' => $row->status,
            'company' => $row->status === 'approved' && $row->company_id
                ? ['id' => (int) $row->company_id]
                : null,
            'rejection_reason' => $row->status === 'rejected' ? $row->rejection_reason : null,
        ]);
    }

    private function journal(Request $request, ?int $appId, ?int $companyId, string $event, int $status, array $details): void
    {
        DB::table('integration_api_events')->insert([
            'integration_application_id' => $appId, 'company_id' => $companyId,
            'event' => $event, 'route' => $request->path(), 'ip_address' => $request->ip(),
            'http_status' => $status, 'http_method' => $request->method(),
            'request_id' => $request->header('X-Request-Id'),
            'details' => json_encode($details, JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
        ]);
    }
}
