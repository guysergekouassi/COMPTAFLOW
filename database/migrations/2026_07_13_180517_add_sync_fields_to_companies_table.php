<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Champs fiscaux manquants
            if (!Schema::hasColumn('companies', 'compte_contribuable')) {
                $table->string('compte_contribuable', 100)->nullable()->after('cnps');
            }
            if (!Schema::hasColumn('companies', 'regime')) {
                $table->string('regime', 80)->nullable()->after('compte_contribuable');
            }
            // Liaison Selflow
            if (!Schema::hasColumn('companies', 'selflow_company_id')) {
                $table->unsignedBigInteger('selflow_company_id')->nullable()->after('regime');
            }
            if (!Schema::hasColumn('companies', 'selflow_sync_key')) {
                $table->string('selflow_sync_key', 100)->nullable()->after('selflow_company_id');
            }
            if (!Schema::hasColumn('companies', 'selflow_sync_status')) {
                $table->string('selflow_sync_status', 30)->nullable()->after('selflow_sync_key');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'compte_contribuable',
                'regime',
                'selflow_company_id',
                'selflow_sync_key',
                'selflow_sync_status',
            ]);
        });
    }
};
