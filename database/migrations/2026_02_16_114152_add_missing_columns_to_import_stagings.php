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
        Schema::table('import_stagings', function (Blueprint $table) {
            if (!Schema::hasColumn('import_stagings', 'batch_id')) {
                $table->string('batch_id')->nullable()->after('id')->index();
            }
            if (!Schema::hasColumn('import_stagings', 'exercice_id')) {
                $table->unsignedBigInteger('exercice_id')->nullable()->after('user_id')->index();
            }
            if (!Schema::hasColumn('import_stagings', 'metadata')) {
                $table->text('metadata')->nullable()->after('mapping');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('import_stagings', function (Blueprint $table) {
            $table->dropColumn(['exercice_id', 'batch_id', 'metadata']);
        });
    }
};
