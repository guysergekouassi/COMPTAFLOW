<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compte_tresoreries', function (Blueprint $table) {
            // Modifie la colonne pour qu'elle accepte NULL
            $table->decimal('solde_initial', 15, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('compte_tresoreries', function (Blueprint $table) {
            // Reverse la modification (la rend obligatoire à nouveau)
            // Note: cela peut échouer s'il y a déjà des valeurs NULL
            $table->decimal('solde_initial', 15, 2)->nullable(false)->change();
        });
    }
};
