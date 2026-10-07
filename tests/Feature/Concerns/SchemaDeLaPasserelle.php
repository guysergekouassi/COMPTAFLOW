<?php

namespace Tests\Feature\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Le schéma que la passerelle Selflow touche, et un dossier lié prêt à
 * recevoir.
 *
 * Sorti de `LiaisonCleParEntrepriseTest` pour servir aussi à
 * `DeversementEcrituresTest`. Comme ailleurs, les épreuves montent leur propre
 * schéma plutôt que de rejouer les migrations : plusieurs portent du SQL
 * MySQL que SQLite ne sait pas lire.
 */
trait SchemaDeLaPasserelle
{
    /**
     * Un dossier lié, sa clé rendue en clair — comme `provision` la rendrait.
     */
    protected function creerUnDossier(int $id, int $selflowId, string $nom): string
    {
        $cle = 'cptf_live_' . str_pad((string) $id, 40, 'k');

        DB::table('companies')->insert([
            'id'                    => $id,
            'company_name'          => $nom,
            'user_id'               => 1,
            'selflow_company_id'    => $selflowId,
            'selflow_sync_key_hash' => hash('sha256', $cle),
            'selflow_sync_key_chiffree' => Crypt::encryptString($cle),
            'selflow_linked_at'     => now(),
            'tier_digits'           => 6,
            'tier_id_type'          => 'numeric',
        ]);

        // De quoi recevoir des écritures : un exercice ouvert et un journal.
        DB::table('exercices_comptables')->insert([
            'company_id' => $id, 'user_id' => 1, 'is_active' => true,
            'date_debut' => '2026-01-01', 'date_fin' => '2026-12-31',
        ]);
        DB::table('code_journals')->insert([
            'code_journal' => 'VTE', 'intitule' => 'Ventes', 'type' => 'Ventes',
            'user_id' => 1, 'company_id' => $id,
        ]);

        return $cle;
    }

    protected function monterLeSchema(): void
    {
        // L'import de Comptaflow, par lequel le référentiel passe désormais :
        // ses lignes déposées, les sections qu'il consulte, et la trésorerie
        // où il cherche la séquence d'un code journal.
        // Comptaflow journalise toute modification faite au nom d'un
        // utilisateur, et l'import travaille au nom de l'administrateur.
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('action')->nullable();
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->text('description')->nullable();
            $table->longText('payload')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::create('import_stagings', function (Blueprint $table) {
            $table->id();
            $table->string('batch_id')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('exercice_id')->nullable();
            $table->string('source')->nullable();
            $table->string('type')->default('courant');
            $table->string('file_name')->nullable();
            $table->longText('raw_data')->nullable();
            $table->text('mapping')->nullable();
            $table->text('metadata')->nullable();
            $table->string('status')->default('pending');
            $table->text('error_log')->nullable();
            $table->timestamps();
        });

        Schema::create('sections_analytiques', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tresorerie', function (Blueprint $table) {
            $table->id();
            $table->string('code_journal');
            $table->string('intitule')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email_adresse')->nullable();
            $table->string('password')->nullable();
            $table->string('role')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('activation_token', 64)->nullable();
            $table->timestamp('activation_token_expires_at')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->timestamps();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->string('activity')->nullable();
            $table->string('juridique_form')->nullable();
            $table->decimal('social_capital', 20, 2)->nullable();
            $table->string('adresse')->nullable();
            $table->string('code_postal')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email_adresse')->nullable();
            $table->string('ncc')->nullable();
            $table->string('rccm')->nullable();
            $table->string('regime')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('user_id')->nullable();
            // La configuration que le provisionnement aligne sur celle de
            // Selflow : sans ces colonnes, l'insertion tombe en « no such
            // column » et la reponse part en 500.
            $table->integer('account_digits')->default(8);
            $table->integer('journal_code_digits')->default(4);
            $table->string('journal_code_type')->default('alphabetical');
            $table->integer('tier_digits')->default(6);
            $table->string('tier_id_type')->default('numeric');
            $table->unsignedBigInteger('selflow_company_id')->nullable()->unique();
            $table->string('selflow_sync_key', 100)->nullable();
            $table->string('selflow_sync_key_hash', 64)->nullable()->unique();
            $table->string('selflow_sync_key_hash_precedente', 64)->nullable();
            $table->timestamp('selflow_sync_key_precedente_expire_at')->nullable();
            $table->timestamp('selflow_sync_key_rotated_at')->nullable();
            $table->text('selflow_sync_key_chiffree')->nullable();
            $table->timestamp('selflow_sync_key_revoked_at')->nullable();
            $table->timestamp('selflow_linked_at')->nullable();
            $table->timestamp('selflow_last_deposit_at')->nullable();
            $table->string('selflow_sync_status')->nullable();
            $table->timestamps();
                    // La corbeille : une entreprise supprimee reste recuperable
            // trente jours. Le modele porte SoftDeletes, la table doit
            // donc offrir la colonne.
            $table->softDeletes();
});

        Schema::create('treasury_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
        });

        Schema::create('plan_comptables', function (Blueprint $table) {
            $table->id();
            $table->string('numero_de_compte');
            $table->string('numero_original')->nullable();
            $table->string('intitule');
            $table->string('type_de_compte')->nullable();
            $table->string('classe')->nullable();
            $table->string('adding_strategy')->default('manuel');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
        });

        Schema::create('code_journals', function (Blueprint $table) {
            $table->id();
            $table->string('code_journal');
            $table->string('numero_original')->nullable();
            $table->string('intitule');
            $table->string('type')->nullable();
            $table->unsignedBigInteger('compte_de_tresorerie')->nullable();
            $table->string('compte_de_contrepartie')->nullable();
            $table->boolean('traitement_analytique')->default(false);
            $table->string('rapprochement_sur')->nullable();
            $table->string('poste_tresorerie')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
        });

        Schema::create('plan_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('numero_de_tiers');
            $table->string('numero_original')->nullable();
            $table->unsignedBigInteger('compte_general')->nullable();
            $table->string('intitule');
            $table->string('type_de_tiers');
            $table->string('ncc')->nullable();
            $table->string('rccm')->nullable();
            $table->string('compte_contribuable')->nullable();
            $table->string('regime')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->string('adresse')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
            $table->unique(['numero_de_tiers', 'company_id']);
        });

        Schema::create('ecriture_comptables', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->string('n_saisie')->nullable();
            $table->string('description_operation')->nullable();
            $table->string('reference_piece')->nullable();
            $table->string('cle_selflow', 64)->nullable();
            $table->unsignedBigInteger('plan_comptable_id')->nullable();
            $table->unsignedBigInteger('plan_tiers_id')->nullable();
            $table->unsignedBigInteger('code_journal_id')->nullable();
            $table->unsignedBigInteger('exercices_comptables_id')->nullable();
            $table->decimal('debit', 20, 2)->default(0);
            $table->decimal('credit', 20, 2)->default(0);
            $table->string('statut')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
        });

        Schema::create('exercices_comptables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('parent_company_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            // La colonne s'appelle `intitule`, non `libelle` : le provisionnement
            // y écrivait sous le mauvais nom et l'exercice naissait sans titre.
            $table->string('intitule')->nullable();
            // L'import ne range rien dans un exercice clos.
            $table->boolean('cloturer')->default(false);
            $table->boolean('is_active')->default(true);
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
        });

        // `CodeJournal::created` répercute le journal sur les exercices.
        Schema::create('journaux_saisis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exercices_comptables_id');
            $table->unsignedBigInteger('code_journals_id');
            $table->integer('annee')->nullable();
            $table->integer('mois')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->timestamps();
        });
    }
}
