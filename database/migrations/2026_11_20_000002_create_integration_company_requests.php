<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('integration_company_requests')) {
            Schema::create('integration_company_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('integration_application_id')->index();
                $table->string('external_request_id', 100);
                $table->string('company_name', 255);
                $table->string('manager_name', 160);
                $table->string('manager_email', 191);
                $table->string('activity', 255)->nullable();
                $table->string('legal_form', 80)->nullable();
                $table->string('phone_number', 30)->nullable();
                $table->json('payload')->nullable();
                $table->string('status', 20)->default('pending')->index();
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->unsignedBigInteger('company_id')->nullable()->index();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();
                $table->unique(['integration_application_id', 'external_request_id'], 'ia_company_req_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_company_requests');
    }
};
