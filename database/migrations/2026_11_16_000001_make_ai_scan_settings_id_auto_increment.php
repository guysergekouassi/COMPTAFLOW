<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Cette correction de structure est spécifique à MySQL. SQLite (tests)
        // gère l'auto-incrément via la définition initiale de la table.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        if (!Schema::hasTable('ai_scan_settings') || !Schema::hasColumn('ai_scan_settings', 'id')) {
            return;
        }

        // Corrige les bases où l'ancienne migration a déjà été exécutée.
        DB::statement('ALTER TABLE ai_scan_settings MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
    }

    public function down(): void
    {
        // AUTO_INCREMENT est conservé volontairement pour éviter de bloquer les insertions.
    }
};
