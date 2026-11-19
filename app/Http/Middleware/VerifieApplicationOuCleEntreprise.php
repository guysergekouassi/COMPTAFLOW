<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class VerifieApplicationOuCleEntreprise
{
    public function handle(Request $request, Closure $next): Response
    {
        $bearer = $request->bearerToken();
        if (!$bearer) {
            // Compatibilité stricte avec les connecteurs qui utilisent encore X-Company-Key.
            return app(VerifieCleEntreprise::class)->handle($request, $next);
        }

        $path = '/' . ltrim($request->path(), '/');
        // La gestion des clés de dossier reste réservée au parcours d'administration.
        if (str_ends_with($path, '/companies/revoke') || str_ends_with($path, '/companies/rotate-key')) {
            return response()->json(['success' => false, 'message' => 'Cette opération de gestion de clé n’est pas autorisée avec un jeton de déversement.'], 403);
        }

        $token = DB::table('integration_api_tokens')->where('token_hash', hash('sha256', $bearer))->first();
        if (!$token || $token->revoked_at || now()->greaterThanOrEqualTo($token->expires_at)) {
            return response()->json(['success' => false, 'message' => 'Jeton applicatif invalide, révoqué ou expiré.'], 401);
        }

        $app = DB::table('integration_applications')->where('id', $token->integration_application_id)->first();
        if (!$app || $app->status !== 'active' || $app->auth_mode !== 'bearer'
            || ($app->client_secret_expires_at && now()->greaterThan($app->client_secret_expires_at))) {
            return response()->json(['success' => false, 'message' => 'Application suspendue ou révoquée.'], 401);
        }

        $link = DB::table('integration_application_companies')
            ->where('integration_application_id', $app->id)->where('company_id', $token->company_id)
            ->where('status', 'active')->first();
        if (!$link) {
            return response()->json(['success' => false, 'message' => 'Liaison applicative révoquée ou inexistante.'], 403);
        }

        if ($app->type === 'externe' && DB::table('integration_application_companies')
            ->where('integration_application_id', $app->id)->where('status', 'active')->count() !== 1) {
            return response()->json(['success' => false, 'message' => 'Le périmètre de cette application externe n’est pas unique.'], 403);
        }

        $company = Company::query()->whereKey($token->company_id)
            ->where('is_active', true)->where('is_blocked', false)->first();
        if (!$company) {
            return response()->json(['success' => false, 'message' => 'Dossier lié inactif ou indisponible.'], 403);
        }

        // Les identifiants éventuels dans le corps ne choisissent jamais le dossier.
        foreach (['company_id', 'comptaflow_company_id'] as $field) {
            if ($request->input($field) !== null && (int) $request->input($field) !== (int) $company->id) {
                return response()->json(['success' => false, 'message' => 'Le dossier demandé ne correspond pas à la liaison authentifiée.'], 403);
            }
        }

        $permissions = json_decode($link->permissions, true) ?: [];
        $permission = str_contains($path, '/pieces-justificatives')
            ? 'pieces_justificatives'
            : ((str_contains($path, '/referentiel/') || str_contains($path, '/tiers/lister') || str_contains($path, '/companies/verify'))
                ? 'referentiel'
                : (str_contains($path, '/ecritures/') ? 'ecritures' : null));
        if ($permission && !in_array($permission, $permissions, true)) {
            return response()->json(['success' => false, 'message' => 'Permission applicative insuffisante : ' . $permission . '.'], 403);
        }

        // Anti-rejeu : horodatage court + nonce unique par application.
        $timestamp = $request->header('X-Request-Timestamp');
        $nonce = $request->header('X-Request-Nonce');
        if (!is_string($timestamp) || !ctype_digit($timestamp)
            || abs(now()->timestamp - (int) $timestamp) > 300
            || !is_string($nonce) || !preg_match('/^[A-Za-z0-9._~-]{16,128}$/', $nonce)) {
            return response()->json(['success' => false, 'message' => 'En-têtes anti-rejeu absents ou hors fenêtre : X-Request-Timestamp et X-Request-Nonce sont requis.'], 401);
        }
        DB::table('integration_request_nonces')->where('created_at', '<', now()->subMinutes(10))->delete();
        try {
            DB::table('integration_request_nonces')->insert([
                'integration_application_id' => $app->id,
                'nonce_hash' => hash('sha256', $nonce),
                'created_at' => now(),
            ]);
        } catch (\Illuminate\Database\QueryException $exception) {
            return response()->json(['success' => false, 'message' => 'Rejeu de requête détecté : nonce déjà utilisé.'], 409);
        }

        DB::table('integration_api_tokens')->where('id', $token->id)->update(['last_used_at' => now(), 'updated_at' => now()]);
        DB::table('integration_applications')->where('id', $app->id)->update(['last_connected_at' => now(), 'updated_at' => now()]);
        $request->attributes->set('entreprise_liee', $company);
        $request->attributes->set('integration_origine', strtoupper($app->code));
        $request->attributes->set('integration_application_id', $app->id);
        $request->attributes->set('integration_permissions', $permissions);

        $response = $next($request);
        $status = $response->getStatusCode();
        $event = str_contains($path, '/pieces-justificatives') ? 'deposit.justificatif'
            : ((str_contains($path, '/referentiel/') || str_contains($path, '/tiers/lister') || str_contains($path, '/companies/verify'))
                ? 'deposit.referentiel'
                : (str_contains($path, '/ecritures/') ? 'deposit.ecritures' : 'api.call'));
        DB::table('integration_api_events')->insert([
            'integration_application_id' => $app->id, 'company_id' => $company->id,
            'event' => $event, 'route' => $request->path(), 'ip_address' => $request->ip(),
            'http_status' => $status, 'details' => json_encode(['method' => $request->method()]),
            'created_at' => now(),
        ]);
        if ($status >= 200 && $status < 300 && str_starts_with($event, 'deposit.')) {
            DB::table('integration_applications')->where('id', $app->id)->update(['last_deposit_at' => now(), 'updated_at' => now()]);
        }
        return $response;
    }
}
