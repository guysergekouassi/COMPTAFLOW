<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $this->addColumn('integration_applications', 'app_url', fn (Blueprint $t) => $t->string('app_url', 500)->nullable());
        $this->addColumn('integration_applications', 'pairing_code_hash', fn (Blueprint $t) => $t->string('pairing_code_hash', 64)->nullable());
        $this->addColumn('integration_applications', 'pairing_code_encrypted', fn (Blueprint $t) => $t->text('pairing_code_encrypted')->nullable());
        $this->addColumn('integration_applications', 'pairing_code_expires_at', fn (Blueprint $t) => $t->timestamp('pairing_code_expires_at')->nullable());
        $this->addColumn('integration_applications', 'pairing_code_used_at', fn (Blueprint $t) => $t->timestamp('pairing_code_used_at')->nullable());
        $this->addColumn('integration_applications', 'pairing_rotated_at', fn (Blueprint $t) => $t->timestamp('pairing_rotated_at')->nullable());

        $this->addColumn('integration_application_companies', 'partner_company_id', fn (Blueprint $t) => $t->string('partner_company_id', 100)->nullable());
        $this->addColumn('integration_application_companies', 'partner_company_name', fn (Blueprint $t) => $t->string('partner_company_name', 255)->nullable());

        $this->addColumn('integration_api_events', 'http_method', fn (Blueprint $t) => $t->string('http_method', 10)->nullable());
        $this->addColumn('integration_api_events', 'duration_ms', fn (Blueprint $t) => $t->unsignedInteger('duration_ms')->nullable());
        $this->addColumn('integration_api_events', 'request_id', fn (Blueprint $t) => $t->string('request_id', 64)->nullable());
    }

    private function addColumn(string $table, string $column, callable $definition): void
    {
        if (Schema::hasTable($table) && !Schema::hasColumn($table, $column)) {
            Schema::table($table, $definition);
        }
    }

    public function down(): void
    {
        foreach ([
            'integration_applications' => ['app_url', 'pairing_code_hash', 'pairing_code_encrypted', 'pairing_code_expires_at', 'pairing_code_used_at', 'pairing_rotated_at'],
            'integration_application_companies' => ['partner_company_id', 'partner_company_name'],
            'integration_api_events' => ['http_method', 'duration_ms', 'request_id'],
        ] as $table => $columns) {
            foreach ($columns as $column) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                    Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropColumn($column));
                }
            }
        }
    }
};
