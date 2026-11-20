<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class IntegrationRegistryMigrationTest extends TestCase
{
    private array $tables = [
        'integration_api_events',
        'integration_request_nonces',
        'integration_api_tokens',
        'integration_application_companies',
        'integration_applications',
    ];

    public function test_la_migration_cree_les_tables_du_registre_et_les_champs_attendus(): void
    {
        foreach ($this->tables as $table) {
            Schema::dropIfExists($table);
        }
        Schema::dropIfExists('integration_company_requests');
        Schema::dropIfExists('companies');

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
        });

        $migration = require database_path('migrations/2026_11_19_000001_create_integration_registry_tables.php');
        $migration->up();
        // Un second passage simule la reprise après une interruption DDL.
        $migration->up();

        foreach ($this->tables as $table) {
            $this->assertTrue(Schema::hasTable($table), "La table {$table} doit être créée.");
        }

        $this->assertTrue(Schema::hasColumn('integration_applications', 'owner_contact'));
        $this->assertTrue(Schema::hasColumn('integration_application_companies', 'permissions'));
        $this->assertTrue(Schema::hasColumn('integration_api_tokens', 'token_hash'));
        $this->assertTrue(Schema::hasColumn('integration_request_nonces', 'nonce_hash'));
        $this->assertTrue(Schema::hasColumn('integration_api_events', 'http_status'));

        $migration->down();
        Schema::dropIfExists('companies');
    }

    public function test_la_migration_repare_les_deux_premieres_tables_deja_creees_sans_cles_etrangeres(): void
    {
        Schema::dropIfExists('integration_application_companies');
        Schema::dropIfExists('integration_applications');
        Schema::dropIfExists('companies');

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
        });
        Schema::create('integration_applications', function (Blueprint $table) {
            $table->id();
        });
        // État laissé par MySQL lorsque CREATE TABLE réussit mais ALTER TABLE FK échoue.
        Schema::create('integration_application_companies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('integration_application_id');
            $table->unsignedBigInteger('company_id');
            $table->json('permissions');
            $table->string('status', 20)->default('active');
            $table->timestamp('linked_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

        $migration = require database_path('migrations/2026_11_19_000001_create_integration_registry_tables.php');
        $migration->up();

        if (DB::connection()->getDriverName() === 'mysql') {
            $foreignKeys = collect(Schema::getForeignKeys('integration_application_companies'))->pluck('name');
            $this->assertTrue($foreignKeys->contains('ia_companies_app_fk'));
            $this->assertTrue($foreignKeys->contains('ia_companies_company_fk'));
        }
        $this->assertTrue(collect(Schema::getIndexes('integration_application_companies'))->contains(
            fn (array $index) => ($index['name'] ?? null) === 'integration_app_company_unique'
        ));

        $migration->down();
        Schema::dropIfExists('companies');
    }

    public function test_les_migrations_complementaires_ajoutent_pairing_audit_et_demandes_externes(): void
    {
        foreach ($this->tables as $table) Schema::dropIfExists($table);
        Schema::dropIfExists('companies');
        Schema::create('companies', function (Blueprint $table) { $table->id(); });

        $base = require database_path('migrations/2026_11_19_000001_create_integration_registry_tables.php');
        $base->up();
        $pairing = require database_path('migrations/2026_11_20_000001_extend_integration_pairing_and_audit.php');
        $pairing->up();
        $pairing->up();
        $requests = require database_path('migrations/2026_11_20_000002_create_integration_company_requests.php');
        $requests->up();
        $requests->up();

        $this->assertTrue(Schema::hasColumn('integration_applications', 'app_url'));
        $this->assertTrue(Schema::hasColumn('integration_applications', 'pairing_code_hash'));
        $this->assertTrue(Schema::hasColumn('integration_application_companies', 'partner_company_id'));
        $this->assertTrue(Schema::hasColumn('integration_api_events', 'duration_ms'));
        $this->assertTrue(Schema::hasColumn('integration_api_events', 'request_id'));
        $this->assertTrue(Schema::hasTable('integration_company_requests'));
        $this->assertTrue(Schema::hasColumn('integration_company_requests', 'external_request_id'));

        $requests->down();
        $pairing->down();
        $base->down();
        Schema::dropIfExists('companies');
    }

    protected function tearDown(): void
    {
        foreach ($this->tables as $table) {
            Schema::dropIfExists($table);
        }
        Schema::dropIfExists('integration_company_requests');
        Schema::dropIfExists('companies');

        parent::tearDown();
    }
}
