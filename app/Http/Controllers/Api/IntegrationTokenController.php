<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class IntegrationTokenController extends Controller
{
    public function issue(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|string|max:100',
            'client_secret' => 'required|string|max:255',
            'company_id' => 'nullable|integer|min:1',
        ]);

        $app = DB::table('integration_applications')->where('client_id', $data['client_id'])->first();
        $valid = $app
            && $app->status === 'active'
            && $app->auth_mode === 'bearer'
            && $app->client_secret_hash
            && (!$app->client_secret_expires_at || now()->lessThanOrEqualTo($app->client_secret_expires_at))
            && Hash::check($data['client_secret'], $app->client_secret_hash);

        if (!$valid) {
            DB::table('integration_api_events')->insert([
                'integration_application_id' => $app->id ?? null,
                'company_id' => null,
                'event' => 'auth.failed',
                'route' => $request->path(),
                'ip_address' => $request->ip(),
                'http_status' => 401,
                'details' => json_encode(['reason' => 'invalid_client_credentials']),
                'created_at' => now(),
            ]);
            return response()->json(['message' => 'Identifiants applicatifs invalides, expirés ou révoqués.'], 401);
        }

        $links = DB::table('integration_application_companies')
            ->where('integration_application_id', $app->id)->where('status', 'active')->get();

        if ($app->type === 'externe' && $links->count() !== 1) {
            return response()->json(['message' => 'Le dossier dédié de cette application externe n’est pas configuré de manière unique.'], 403);
        }

        if (isset($data['company_id'])) {
            $link = $links->firstWhere('company_id', (int) $data['company_id']);
        } else {
            $link = $links->count() === 1 ? $links->first() : null;
        }

        if (!$link) {
            return response()->json([
                'message' => $links->isEmpty()
                    ? 'Aucun dossier actif n’est lié à cette application.'
                    : 'Cette application maison est liée à plusieurs dossiers : sélectionnez un dossier parmi ses liaisons autorisées.',
            ], 422);
        }

        $company = DB::table('companies')->where('id', $link->company_id)->whereNull('deleted_at')
            ->where('is_active', true)->where('is_blocked', false)->first();
        if (!$company) {
            return response()->json(['message' => 'Le dossier lié est inactif ou indisponible.'], 403);
        }

        $plainToken = 'cf_tok_' . Str::random(72);
        $expiresAt = now()->addHour();
        DB::table('integration_api_tokens')->insert([
            'integration_application_id' => $app->id, 'company_id' => $company->id,
            'token_hash' => hash('sha256', $plainToken), 'expires_at' => $expiresAt,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('integration_applications')->where('id', $app->id)->update([
            'last_connected_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('integration_api_events')->insert([
            'integration_application_id' => $app->id, 'company_id' => $company->id,
            'event' => 'auth.token_issued', 'route' => $request->path(), 'ip_address' => $request->ip(),
            'http_status' => 200, 'details' => json_encode(['expires_at' => $expiresAt->toIso8601String()]),
            'created_at' => now(),
        ]);

        return response()->json([
            'access_token' => $plainToken, 'token_type' => 'Bearer', 'expires_in' => 3600,
            'company' => ['id' => $company->id, 'name' => $company->company_name],
        ]);
    }
}
