<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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

        $foreignKeys = collect(Schema::getForeignKeys('integration_application_companies'))->pluck('name');
        $this->assertTrue($foreignKeys->contains('ia_companies_app_fk'));
        $this->assertTrue($foreignKeys->contains('ia_companies_company_fk'));
        $this->assertTrue(collect(Schema::getIndexes('integration_application_companies'))->contains(
            fn (array $index) => ($index['name'] ?? null) === 'integration_app_company_unique'
        ));

        $migration->down();
        Schema::dropIfExists('companies');
    }

    protected function tearDown(): void
    {
        foreach ($this->tables as $table) {
            Schema::dropIfExists($table);
        }
        Schema::dropIfExists('companies');

        parent::tearDown();
    }
}
