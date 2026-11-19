<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Les noms explicites restent courts pour respecter la limite MySQL
     * de 64 caractères. La migration est réexécutable après un échec DDL :
     * MySQL peut avoir créé une table avant d'échouer sur un ALTER TABLE FK.
     */
    public function up(): void
    {
        if (!Schema::hasTable('integration_applications')) {
            Schema::create('integration_applications', function (Blueprint $table) {
                $table->id();
                $table->string('code', 80)->unique();
                $table->string('name', 160);
                $table->string('owner_name', 160)->nullable();
                $table->string('owner_email', 191)->nullable();
                $table->string('owner_contact', 80)->nullable();
                $table->text('description')->nullable();
                $table->string('type', 20)->index();
                $table->string('status', 20)->default('active')->index();
                $table->string('auth_mode', 20)->default('bearer');
                $table->string('client_id', 100)->nullable()->unique();
                $table->string('client_secret_hash')->nullable();
                $table->timestamp('client_secret_expires_at')->nullable();
                $table->timestamp('secret_rotated_at')->nullable();
                $table->timestamp('revoked_at')->nullable();
                $table->timestamp('last_connected_at')->nullable();
                $table->timestamp('last_deposit_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('integration_application_companies')) {
            Schema::create('integration_application_companies', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('integration_application_id');
                $table->unsignedBigInteger('company_id');
                $table->json('permissions');
                $table->string('status', 20)->default('active')->index();
                $table->timestamp('linked_at')->nullable();
                $table->timestamp('revoked_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('integration_api_tokens')) {
            Schema::create('integration_api_tokens', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('integration_application_id');
                $table->unsignedBigInteger('company_id');
                $table->char('token_hash', 64)->unique();
                $table->timestamp('expires_at')->index();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('revoked_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('integration_request_nonces')) {
            Schema::create('integration_request_nonces', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('integration_application_id');
                $table->char('nonce_hash', 64);
                $table->timestamp('created_at')->useCurrent()->index();
            });
        }

        if (!Schema::hasTable('integration_api_events')) {
            Schema::create('integration_api_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('integration_application_id')->nullable();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->string('event', 100)->index();
                $table->string('route', 255)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->unsignedSmallInteger('http_status')->nullable();
                $table->json('details')->nullable();
                $table->timestamp('created_at')->useCurrent()->index();
            });
        }

        $this->ensureIndex('integration_application_companies', 'integration_app_company_unique', function (Blueprint $table) {
            $table->unique(['integration_application_id', 'company_id'], 'integration_app_company_unique');
        });
        $this->ensureIndex('integration_application_companies', 'ia_company_status_idx', function (Blueprint $table) {
            $table->index(['company_id', 'status'], 'ia_company_status_idx');
        });
        $this->ensureIndex('integration_api_tokens', 'ia_token_scope_idx', function (Blueprint $table) {
            $table->index(['integration_application_id', 'company_id', 'revoked_at'], 'ia_token_scope_idx');
        });
        $this->ensureIndex('integration_request_nonces', 'ia_nonce_unique', function (Blueprint $table) {
            $table->unique(['integration_application_id', 'nonce_hash'], 'ia_nonce_unique');
        });

        $this->ensureForeignKey('integration_application_companies', 'integration_application_id', 'integration_applications', 'ia_companies_app_fk', 'cascade');
        $this->ensureForeignKey('integration_application_companies', 'company_id', 'companies', 'ia_companies_company_fk', 'cascade');
        $this->ensureForeignKey('integration_api_tokens', 'integration_application_id', 'integration_applications', 'ia_tokens_app_fk', 'cascade');
        $this->ensureForeignKey('integration_api_tokens', 'company_id', 'companies', 'ia_tokens_company_fk', 'cascade');
        $this->ensureForeignKey('integration_request_nonces', 'integration_application_id', 'integration_applications', 'ia_nonces_app_fk', 'cascade');
        $this->ensureForeignKey('integration_api_events', 'integration_application_id', 'integration_applications', 'ia_events_app_fk', 'null');
        $this->ensureForeignKey('integration_api_events', 'company_id', 'companies', 'ia_events_company_fk', 'null');
    }

    private function ensureIndex(string $tableName, string $indexName, callable $definition): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        $exists = collect(Schema::getIndexes($tableName))
            ->contains(fn (array $index) => ($index['name'] ?? null) === $indexName);

        if (!$exists) {
            Schema::table($tableName, $definition);
        }
    }

    private function ensureForeignKey(string $tableName, string $column, string $referencedTable, string $name, string $deleteAction): void
    {
        if (!Schema::hasTable($tableName) || !Schema::hasTable($referencedTable)) {
            return;
        }

        $exists = collect(Schema::getForeignKeys($tableName))
            ->contains(fn (array $foreignKey) => ($foreignKey['name'] ?? null) === $name);

        if ($exists) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($column, $referencedTable, $name, $deleteAction) {
            $foreign = $table->foreign($column, $name)->references('id')->on($referencedTable);

            if ($deleteAction === 'null') {
                $foreign->nullOnDelete();
            } else {
                $foreign->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_api_events');
        Schema::dropIfExists('integration_request_nonces');
        Schema::dropIfExists('integration_api_tokens');
        Schema::dropIfExists('integration_application_companies');
        Schema::dropIfExists('integration_applications');
    }
};
