<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class IntegrationPairingController extends Controller
{
    /**
     * L'application maison colle le code généré dans COMPTAFLOW.
     * Le code ne donne aucun accès à un dossier : il ne fait qu'activer
     * l'identité applicative. Les jetons de dépôt restent liés à un dossier
     * explicite et autorisé.
     */
    public function redeem(Request $request)
    {
        $data = $request->validate([
            'pairing_code' => 'required|string|max:120',
            'application_code' => 'nullable|string|max:80',
        ]);

        $app = DB::table('integration_applications')
            ->where('pairing_code_hash', hash('sha256', $data['pairing_code']))
            ->first();

        $valid = $app
            && $app->type === 'maison'
            && $app->status === 'active'
            && $app->pairing_code_expires_at
            && now()->lessThanOrEqualTo($app->pairing_code_expires_at)
            && !$app->pairing_code_used_at
            && (empty($data['application_code']) || hash_equals($app->code, $data['application_code']));

        if (!$valid) {
            DB::table('integration_api_events')->insert([
                'integration_application_id' => $app->id ?? null,
                'company_id' => null,
                'event' => 'pairing.rejected',
                'route' => $request->path(),
                'ip_address' => $request->ip(),
                'http_status' => 401,
                'http_method' => $request->method(),
                'details' => json_encode(['reason' => 'invalid_expired_or_used_pairing_code']),
                'created_at' => now(),
            ]);
            return response()->json(['message' => 'Code de liaison invalide, expiré ou déjà utilisé.'], 401);
        }

        $clientId = $app->client_id ?: ('cf_app_' . Str::lower(Str::random(28)));
        $secret = 'cf_secret_' . Str::random(64);
        $now = now();

        DB::table('integration_applications')->where('id', $app->id)->update([
            'client_id' => $clientId,
            'client_secret_hash' => Hash::make($secret),
            'client_secret_expires_at' => $now->copy()->addYear(),
            'secret_rotated_at' => $now,
            'pairing_code_used_at' => $now,
            'auth_mode' => 'bearer',
            'updated_at' => $now,
        ]);

        DB::table('integration_api_events')->insert([
            'integration_application_id' => $app->id,
            'company_id' => null,
            'event' => 'pairing.accepted',
            'route' => $request->path(),
            'ip_address' => $request->ip(),
            'http_status' => 200,
            'http_method' => $request->method(),
            'details' => json_encode(['client_id' => $clientId, 'secret_expires_at' => $now->copy()->addYear()->toIso8601String()]),
            'created_at' => $now,
        ]);

        return response()->json([
            'success' => true,
            'application' => ['code' => $app->code, 'name' => $app->name],
            'api_base_url' => rtrim(config('app.url'), '/'),
            'client_id' => $clientId,
            'client_secret' => $secret,
            'secret_expires_at' => $now->copy()->addYear()->toIso8601String(),
            'next_step' => 'Associez au moins un dossier autorisé avant de demander un jeton Bearer.',
        ]);
    }
}
