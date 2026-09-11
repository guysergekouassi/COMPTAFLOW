<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('exercices_comptables', 'parent_company_id')) {
            Schema::table('exercices_comptables', function (Blueprint $table) {
                // Add the parent_company_id column
                $table->unsignedBigInteger('parent_company_id')->nullable()->after('company_id');

                // Add foreign key constraint
                $table->foreign('parent_company_id')
                      ->references('id')
                      ->on('companies')
                      ->onDelete('cascade');
            });

            // Rattacher les exercices existants a la maison mere de leur entreprise.
            //
            // La version precedente s'ecrivait `UPDATE ... JOIN ... SET`, une forme
            // propre a MySQL : la suite d'epreuves, qui tourne sur SQLite, s'arretait
            // sur `syntax error near "ec"` et toute la suite restait rouge derriere.
            // Un `UPDATE` avec sous-requete correlee dit la meme chose et se lit des
            // deux moteurs.
            DB::statement('UPDATE exercices_comptables
                SET parent_company_id = (
                    SELECT COALESCE(c.parent_company_id, c.id)
                    FROM companies c
                    WHERE c.id = exercices_comptables.company_id
                )
                WHERE company_id IS NOT NULL');
        }
    }

    public function down()
    {
        Schema::table('exercices_comptables', function (Blueprint $table) {
            $table->dropForeign(['parent_company_id']);
            $table->dropColumn('parent_company_id');
        });
    }
};
