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
     * Délai pendant lequel un code déjà échangé peut l'être de nouveau.
     *
     * L'application partenaire échange le code puis enregistre les identifiants
     * reçus. Si elle échoue entre les deux (coupure, erreur d'enregistrement,
     * appel de vérification suivi de l'appel réel), un code strictement à usage
     * unique la bloquait pour de bon : « Code de liaison invalide, expiré ou
     * déjà utilisé », et il fallait régénérer le code chez COMPTAFLOW. Pendant
     * ce délai, un nouvel échange réémet un secret neuf, qui annule le
     * précédent : un seul jeu d'identifiants reste valable à la fois.
     */
    private const DELAI_REPRISE_MINUTES = 15;

    /**
     * L'application maison colle le code généré dans COMPTAFLOW.
     * Le code ne donne aucun accès à un dossier : il ne fait qu'activer
     * l'identité applicative. Les jetons de dépôt restent liés à un dossier
     * explicite et autorisé.
     */
    public function redeem(Request $request)
    {
        $data = $request->validate([
            'pairing_code' => 'required|string|max:200',
            'application_code' => 'nullable|string|max:80',
        ]);

        $code = self::normaliserCode($data['pairing_code']);

        $app = DB::table('integration_applications')
            ->where('pairing_code_hash', hash('sha256', $code))
            ->first();

        $raison = $this->raisonDuRefus($app, $data['application_code'] ?? null);

        if ($raison !== null) {
            $this->journaliser($request, $app->id ?? null, 'pairing.rejected', 401, ['reason' => $raison]);

            return response()->json([
                'message' => self::messageDuRefus($raison),
                'reason' => $raison,
            ], 401);
        }

        $reprise = (bool) $app->pairing_code_used_at;
        $clientId = $app->client_id ?: ('cf_app_' . Str::lower(Str::random(28)));
        $secret = 'cf_secret_' . Str::random(64);
        $now = now();

        DB::table('integration_applications')->where('id', $app->id)->update([
            'client_id' => $clientId,
            'client_secret_hash' => Hash::make($secret),
            'client_secret_expires_at' => $now->copy()->addYear(),
            'secret_rotated_at' => $now,
            // Le délai de reprise court depuis le premier échange, pas depuis le
            // dernier : rejouer le code ne le prolonge pas.
            'pairing_code_used_at' => $app->pairing_code_used_at ?: $now,
            'auth_mode' => 'bearer',
            'updated_at' => $now,
        ]);

        $this->journaliser($request, $app->id, $reprise ? 'pairing.replayed' : 'pairing.accepted', 200, [
            'client_id' => $clientId,
            'secret_expires_at' => $now->copy()->addYear()->toIso8601String(),
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

    /**
     * Un code copié depuis l'écran ou un message arrive souvent avec un retour
     * à la ligne, une espace insécable ou des guillemets : le code lui-même ne
     * contient que des lettres, des chiffres et « _ », on retire tout le reste
     * de ce qui l'entoure ou s'y glisse.
     */
    public static function normaliserCode(string $brut): string
    {
        $code = preg_replace('/[\s\x{00A0}\x{200B}-\x{200D}\x{2060}\x{FEFF}]+/u', '', $brut) ?? '';

        return trim($code, "\"'`«»“”‘’");
    }

    /**
     * Le code d'application (« rh-flow ») est un garde-fou contre le code collé
     * dans la mauvaise application, pas un secret : RH-FLOW peut l'envoyer en
     * « RH-FLOW » ou « rh_flow ». On le compare donc sous forme de slug.
     */
    private static function memeApplication(string $attendu, string $recu): bool
    {
        $recu = Str::slug(str_replace('_', '-', $recu));
        $attendu = Str::slug($attendu);

        // Le registre suffixe « -2 », « -3 »… un code déjà pris (la fiche RH-FLOW
        // créée d'office occupait `rh-flow`) : l'application partenaire, elle,
        // ne connaît que son nom de base.
        return hash_equals($attendu, $recu)
            || hash_equals(preg_replace('/-\d+$/', '', $attendu), $recu);
    }

    private function raisonDuRefus(?object $app, ?string $applicationCode): ?string
    {
        if (!$app) {
            return 'unknown_code';
        }
        if ($app->type !== 'maison') {
            return 'not_house_application';
        }
        if ($app->status !== 'active') {
            return 'application_inactive';
        }
        if (!$app->pairing_code_expires_at || now()->greaterThan($app->pairing_code_expires_at)) {
            return 'expired';
        }
        if ($app->pairing_code_used_at
            && now()->greaterThan(\Illuminate\Support\Carbon::parse($app->pairing_code_used_at)->addMinutes(self::DELAI_REPRISE_MINUTES))) {
            return 'already_used';
        }
        if (filled($applicationCode) && !self::memeApplication((string) $app->code, $applicationCode)) {
            return 'application_mismatch';
        }

        return null;
    }

    /**
     * Le détail n'est donné qu'à qui détient un code réel : un code inconnu
     * reçoit le message générique, sans indice.
     */
    private static function messageDuRefus(string $raison): string
    {
        return match ($raison) {
            'expired' => 'Code de liaison expiré. Générez un nouveau code dans COMPTAFLOW.',
            'already_used' => 'Ce code de liaison a déjà été échangé. Générez un nouveau code dans COMPTAFLOW.',
            'application_mismatch' => 'Ce code de liaison a été généré pour une autre application dans COMPTAFLOW.',
            'application_inactive' => 'Cette application est désactivée ou révoquée dans COMPTAFLOW.',
            'not_house_application' => 'Ce code ne concerne pas une application de la Suite FLOW.',
            default => 'Code de liaison invalide, expiré ou déjà utilisé.',
        };
    }

    private function journaliser(Request $request, ?int $applicationId, string $event, int $status, array $details): void
    {
        DB::table('integration_api_events')->insert([
            'integration_application_id' => $applicationId,
            'company_id' => null,
            'event' => $event,
            'route' => $request->path(),
            'ip_address' => $request->ip(),
            'http_status' => $status,
            'http_method' => $request->method(),
            'details' => json_encode($details),
            'created_at' => now(),
        ]);
    }
}
