<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * La migration qui pose la clé par dossier, jouée seule.
 *
 * La chaîne complète des migrations de l'application ne passe pas sous SQLite —
 * plusieurs portent du SQL MySQL (`ALTER TABLE … MODIFY COLUMN ENUM`) — donc on
 * monte le strict nécessaire et on joue celle du lot par-dessus. C'est aussi ce
 * qui permet de vérifier la **bascule des clés déjà posées en clair** : sans
 * elle, les entreprises liées avant ce lot verraient leur premier déversement
 * refusé alors que rien n'a changé de leur côté.
 */
class MigrationCleDeLiaisonTest extends TestCase
{
    private const MIGRATION = __DIR__ . '/../../database/migrations/2026_08_26_000001_cle_de_liaison_par_entreprise.php';
    private const MIGRATION_GRACE = __DIR__ . '/../../database/migrations/2026_08_26_000003_grace_de_rotation_de_cle.php';

    private function monterCompanies(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->unsignedBigInteger('selflow_company_id')->nullable();
            $table->string('selflow_sync_key', 100)->nullable();
            $table->timestamps();
        });
    }

    /** `require` — et non `require_once` : chaque appel doit rendre une instance neuve. */
    private function jouerLaMigration(): void
    {
        (require self::MIGRATION)->up();
    }

    public function test_la_grace_de_rotation_sinstalle_et_se_defait(): void
    {
        // La migration de la grâce se joue **après** celle de la clé, et ses
        // colonnes se posent `after` les siennes : jouée dans le désordre, ou
        // rejouée, elle ne doit pas s'effondrer — une migration qui échoue à
        // mi-chemin laisse une base que personne ne sait plus décrire.
        $this->monterCompanies();
        $this->jouerLaMigration();

        (require self::MIGRATION_GRACE)->up();

        $this->assertTrue(Schema::hasColumn('companies', 'selflow_sync_key_hash_precedente'));
        $this->assertTrue(Schema::hasColumn('companies', 'selflow_sync_key_precedente_expire_at'));
        $this->assertTrue(Schema::hasColumn('companies', 'selflow_sync_key_rotated_at'));

        // Rejouée, elle ne fait rien de plus.
        (require self::MIGRATION_GRACE)->up();
        $this->assertTrue(Schema::hasColumn('companies', 'selflow_sync_key_rotated_at'));

        (require self::MIGRATION_GRACE)->down();
        $this->assertFalse(Schema::hasColumn('companies', 'selflow_sync_key_hash_precedente'));
    }

    public function test_une_cle_deja_posee_en_clair_bascule_en_hache_et_copie_chiffree(): void
    {
        $this->monterCompanies();
        DB::table('companies')->insert([
            'id' => 1, 'company_name' => 'ELIKET MARKET',
            'selflow_company_id' => 7, 'selflow_sync_key' => 'ancienne-cle-en-clair',
        ]);

        $this->jouerLaMigration();

        $dossier = DB::table('companies')->find(1);

        // La colonne en clair est vidée : c'est tout l'objet du changement.
        $this->assertNull($dossier->selflow_sync_key);
        $this->assertSame(hash('sha256', 'ancienne-cle-en-clair'), $dossier->selflow_sync_key_hash);
        // Rien n'est perdu pour autant : `provision` doit pouvoir rendre la même
        // clé si Selflow rejoue son appel.
        $this->assertSame('ancienne-cle-en-clair', Crypt::decryptString($dossier->selflow_sync_key_chiffree));
    }

    public function test_deux_dossiers_ne_peuvent_plus_viser_la_meme_entreprise_selflow(): void
    {
        // Deux dossiers rattachés à la même entreprise Selflow rendent le
        // déversement indéterminé : la moitié des écritures d'une entreprise
        // peut partir dans des livres qui ne sont pas les siens.
        $this->monterCompanies();
        DB::table('companies')->insert(['id' => 1, 'selflow_company_id' => 7]);

        $this->jouerLaMigration();

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        DB::table('companies')->insert(['id' => 2, 'selflow_company_id' => 7]);
    }

    public function test_plusieurs_dossiers_non_lies_cohabitent(): void
    {
        // L'unicité ne doit pas empêcher d'avoir plusieurs dossiers que Selflow
        // ne connaît pas : ce sont les entreprises gérées uniquement ici.
        $this->monterCompanies();
        $this->jouerLaMigration();

        DB::table('companies')->insert([
            ['id' => 1, 'selflow_company_id' => null],
            ['id' => 2, 'selflow_company_id' => null],
        ]);

        $this->assertSame(2, DB::table('companies')->count());
    }

    public function test_des_doublons_preexistants_arretent_la_migration_en_les_nommant(): void
    {
        // Installer l'index en silence — ou le sauter — reviendrait à laisser
        // ouverte exactement la porte qu'il ferme. L'opérateur doit trancher.
        $this->monterCompanies();
        DB::table('companies')->insert([
            ['id' => 1, 'selflow_company_id' => 7],
            ['id' => 2, 'selflow_company_id' => 7],
        ]);

        try {
            $this->jouerLaMigration();
            $this->fail('La migration a installé l\'index malgré les doublons.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('dossier Comptaflow n° 1', $e->getMessage());
            $this->assertStringContainsString('dossier Comptaflow n° 2', $e->getMessage());
        }
    }
}
