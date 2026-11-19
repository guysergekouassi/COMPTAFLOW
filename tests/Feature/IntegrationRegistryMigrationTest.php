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

    protected function tearDown(): void
    {
        foreach ($this->tables as $table) {
            Schema::dropIfExists($table);
        }
        Schema::dropIfExists('companies');

        parent::tearDown();
    }
}
