<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('integration_applications', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('name', 160);
            $table->string('owner_name', 160)->nullable();
            $table->string('owner_email', 191)->nullable();
            $table->text('description')->nullable();
            $table->string('type', 20)->index(); // maison | externe
            $table->string('status', 20)->default('active')->index(); // active | pending | suspended | revoked
            $table->string('auth_mode', 20)->default('bearer'); // bearer | legacy | planned
            $table->string('client_id', 100)->nullable()->unique();
            $table->string('client_secret_hash')->nullable();
            $table->timestamp('client_secret_expires_at')->nullable();
            $table->timestamp('secret_rotated_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('last_connected_at')->nullable();
            $table->timestamp('last_deposit_at')->nullable();
            $table->timestamps();
        });

        Schema::create('integration_application_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_application_id')->constrained('integration_applications')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->json('permissions');
            $table->string('status', 20)->default('active')->index();
            $table->timestamp('linked_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->unique(['integration_application_id', 'company_id'], 'integration_app_company_unique');
            $table->index(['company_id', 'status']);
        });

        Schema::create('integration_api_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_application_id')->constrained('integration_applications')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->char('token_hash', 64)->unique();
            $table->timestamp('expires_at')->index();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->index(['integration_application_id', 'company_id', 'revoked_at'], 'integration_token_scope_idx');
        });

        Schema::create('integration_request_nonces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_application_id')->constrained('integration_applications')->cascadeOnDelete();
            $table->char('nonce_hash', 64);
            $table->timestamp('created_at')->useCurrent()->index();
            $table->unique(['integration_application_id', 'nonce_hash'], 'integration_nonce_unique');
        });

        Schema::create('integration_api_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_application_id')->nullable()->constrained('integration_applications')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('event', 100)->index();
            $table->string('route', 255)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->json('details')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
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
