<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('ncc')->nullable()->after('identification_TVA');
            $table->string('rccm')->nullable()->after('ncc');
            $table->string('cnps')->nullable()->after('rccm');
            $table->string('siege_social')->nullable()->after('adresse');
            $table->string('rattachement_dgi')->nullable()->after('cnps'); // Centre des impôts
            $table->string('expert_comptable_nom')->nullable();
            $table->string('expert_comptable_ncc')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['ncc', 'rccm', 'cnps', 'siege_social', 'rattachement_dgi', 'expert_comptable_nom', 'expert_comptable_ncc']);
        });
    }
};
