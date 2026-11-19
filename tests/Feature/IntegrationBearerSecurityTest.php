<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Http\Controllers\Super\SuperAdminIntegrationController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class IntegrationBearerSecurityTest extends TestCase
{
    private string $token = 'test-bearer-token-unique-123456';
    private int $applicationId = 10;
    private int $companyId = 42;

    protected function setUp(): void
    {
        parent::setUp();
        $this->monterSchema();

        DB::table('companies')->insert([
            'id' => $this->companyId, 'company_name' => 'Dossier autorisé',
            'is_active' => true, 'is_blocked' => false,
        ]);
        DB::table('integration_applications')->insert([
            'id' => $this->applicationId, 'code' => 'app-test', 'name' => 'Application test',
            'type' => 'maison', 'status' => 'active', 'auth_mode' => 'bearer',
            'client_id' => 'cf_app_test', 'client_secret_hash' => Hash::make('secret-test'),
            'client_secret_expires_at' => now()->addYear(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('integration_application_companies')->insert([
            'integration_application_id' => $this->applicationId, 'company_id' => $this->companyId,
            'permissions' => json_encode(['referentiel']), 'status' => 'active',
            'linked_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('integration_api_tokens')->insert([
            'integration_application_id' => $this->applicationId, 'company_id' => $this->companyId,
            'token_hash' => hash('sha256', $this->token), 'expires_at' => now()->addHour(),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        Route::middleware('integration.auth')->post('/__integration/test', function (Request $request) {
            return response()->json(['company_id' => $request->attributes->get('entreprise_liee')->id]);
        });
        Route::middleware('integration.auth')->post('/__integration/ecritures/deverser', function () {
            return response()->json(['success' => true]);
        });
    }

    public function test_une_application_externe_ne_peut_pas_utiliser_le_rattachement_generique_a_un_dossier_existant(): void
    {
        DB::table('integration_applications')->where('id', $this->applicationId)->update(['type' => 'externe']);

        $this->expectException(HttpException::class);
        app(SuperAdminIntegrationController::class)->lier(Request::create('/__integration/link', 'POST'), $this->applicationId);
    }

    public function test_une_application_maison_ne_peut_demander_un_jeton_que_pour_un_dossier_lie(): void
    {
        DB::table('companies')->insert([
            'id' => 43, 'company_name' => 'Dossier hors périmètre',
            'is_active' => true, 'is_blocked' => false,
        ]);

        $this->postJson('/api/integration/token', [
            'client_id' => 'cf_app_test', 'client_secret' => 'secret-test', 'company_id' => 43,
        ])->assertStatus(422);

        $this->postJson('/api/integration/token', [
            'client_id' => 'cf_app_test', 'client_secret' => 'secret-test', 'company_id' => $this->companyId,
        ])->assertOk();
    }

    public function test_le_jeton_resout_le_dossier_depuis_la_liaison_et_non_depuis_le_corps(): void
    {
        $this->appelSigne(['company_id' => 999])->assertStatus(403);
        $this->appelSigne()->assertOk()->assertJson(['company_id' => $this->companyId]);
    }

    public function test_un_nonce_ne_peut_pas_etre_rejoue(): void
    {
        $headers = $this->headers('nonce-rejeu-000001');
        $this->withHeaders($headers)->postJson('/__integration/test')->assertOk();
        $this->withHeaders($headers)->postJson('/__integration/test')->assertStatus(409);
    }

    public function test_les_en_tetes_anti_rejeu_sont_obligatoires(): void
    {
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->postJson('/__integration/test')->assertStatus(401);
    }

    public function test_une_permission_absente_refuse_le_deversement(): void
    {
        $this->appelSigne([], '/__integration/ecritures/deverser')->assertStatus(403);
    }

    public function test_une_application_externe_ne_peut_pas_avoir_deux_dossiers_actifs(): void
    {
        DB::table('integration_applications')->where('id', $this->applicationId)->update(['type' => 'externe']);
        DB::table('companies')->insert([
            'id' => 43, 'company_name' => 'Dossier non autorisé',
            'is_active' => true, 'is_blocked' => false,
        ]);
        DB::table('integration_application_companies')->insert([
            'integration_application_id' => $this->applicationId, 'company_id' => 43,
            'permissions' => json_encode(['referentiel']), 'status' => 'active',
            'linked_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->appelSigne()->assertStatus(403);
    }

    public function test_un_jeton_bearer_ne_donne_pas_acces_a_la_liste_globale_des_entreprises(): void
    {
        config(['external_sync.external_sync_secret' => 'secret-global-test']);

        $this->withHeaders($this->headers(Str::random(24)))
            ->postJson('/api/external/list-companies')
            ->assertUnauthorized()
            ->assertJsonMissingPath('companies');
    }

    public function test_un_jeton_bearer_ne_permet_pas_de_choisir_un_dossier_via_company_info(): void
    {
        config(['external_sync.external_sync_secret' => 'secret-global-test']);

        $this->withHeaders($this->headers(Str::random(24)))
            ->postJson('/api/external/company-info', ['comptaflow_company_id' => 43])
            ->assertUnauthorized();
    }

    public function test_le_endpoint_emet_un_jeton_seulement_pour_un_dossier_lie(): void
    {
        $response = $this->postJson('/api/integration/token', [
            'client_id' => 'cf_app_test', 'client_secret' => 'secret-test',
        ])->assertOk()->assertJsonPath('token_type', 'Bearer')->assertJsonPath('expires_in', 3600);

        $plain = $response->json('access_token');
        $this->assertNotEmpty($plain);
        $this->assertSame(1, DB::table('integration_api_tokens')->where('token_hash', hash('sha256', $plain))->count());
        $this->assertSame(0, DB::table('integration_api_tokens')->where('token_hash', $plain)->count());
    }

    private function appelSigne(array $body = [], string $uri = '/__integration/test')
    {
        return $this->withHeaders($this->headers(Str::random(24)))->postJson($uri, $body);
    }

    private function headers(string $nonce): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->token,
            'X-Request-Timestamp' => (string) now()->timestamp,
            'X-Request-Nonce' => $nonce,
        ];
    }

    private function monterSchema(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('company_name');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_blocked')->default(false);
            $table->softDeletes();
        });
        Schema::create('integration_applications', function (Blueprint $table) {
            $table->id(); $table->string('code'); $table->string('name');
            $table->string('type'); $table->string('status'); $table->string('auth_mode');
            $table->string('client_id')->nullable(); $table->string('client_secret_hash')->nullable();
            $table->timestamp('client_secret_expires_at')->nullable();
            $table->timestamp('last_connected_at')->nullable(); $table->timestamp('last_deposit_at')->nullable();
            $table->timestamps();
        });
        Schema::create('integration_application_companies', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('integration_application_id');
            $table->unsignedBigInteger('company_id'); $table->json('permissions');
            $table->string('status'); $table->timestamp('linked_at')->nullable();
            $table->timestamp('revoked_at')->nullable(); $table->timestamps();
        });
        Schema::create('integration_api_tokens', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('integration_application_id');
            $table->unsignedBigInteger('company_id'); $table->char('token_hash', 64)->unique();
            $table->timestamp('expires_at'); $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable(); $table->timestamps();
        });
        Schema::create('integration_request_nonces', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('integration_application_id');
            $table->char('nonce_hash', 64); $table->timestamp('created_at');
            $table->unique(['integration_application_id', 'nonce_hash']);
        });
        Schema::create('integration_api_events', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('integration_application_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable(); $table->string('event');
            $table->string('route')->nullable(); $table->string('ip_address', 45)->nullable();
            $table->unsignedSmallInteger('http_status')->nullable(); $table->json('details')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }
}
