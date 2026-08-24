<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Le déversement du référentiel de Selflow : POST /api/external/referentiel/deverser.
 *
 * Les épreuves montent leur propre schéma — les seules tables que le
 * déversement touche — plutôt que de rejouer les migrations de l'application :
 * plusieurs d'entre elles portent du SQL MySQL (`ALTER TABLE … MODIFY COLUMN
 * ENUM`) que SQLite ne sait pas lire.
 */
class DeversementReferentielTest extends TestCase
{
    private const SECRET = 'secret-de-test-partage';

    /** L'entreprise Comptaflow, et l'entreprise Selflow qui lui est liée. */
    private const COMPTAFLOW = 42;
    private const SELFLOW    = 7;

    protected function setUp(): void
    {
        parent::setUp();

        config(['external_sync.external_sync_secret' => self::SECRET]);

        $this->monterLeSchema();

        DB::table('users')->insert(['id' => 1, 'name' => 'Comptable']);
        DB::table('companies')->insert([
            'id'                 => self::COMPTAFLOW,
            'name'               => 'ELIKET MARKET',
            'user_id'            => 1,
            'selflow_company_id' => self::SELFLOW,
            'tier_digits'        => 6,
        ]);
    }

    // ─── Le secret, et la liaison ────────────────────────────────────────────

    public function test_un_secret_faux_est_refuse(): void
    {
        $this->deverser(['plan_comptable' => []], 'mauvais')
            ->assertStatus(401)
            ->assertJson(['success' => false]);
    }

    public function test_sans_secret_configure_rien_ne_passe(): void
    {
        // Un secret non configuré ne vaut pas « pas de contrôle ».
        config(['external_sync.external_sync_secret' => null]);

        $this->deverser(['plan_comptable' => []])->assertStatus(401);
    }

    public function test_une_liaison_absente_est_refusee_et_nest_pas_creee(): void
    {
        $this->postJson('/api/external/referentiel/deverser', [
            'selflow_company_id'    => 999,
            'comptaflow_company_id' => self::COMPTAFLOW,
            'plan_comptable'        => [['numero_de_compte' => '411000', 'intitule' => 'Clients']],
        ], ['X-Sync-Secret' => self::SECRET])->assertStatus(404);

        $this->assertSame(0, DB::table('plan_comptables')->count());
    }

    // ─── L'amorçage : Comptaflow est vide ────────────────────────────────────

    public function test_amorce_le_referentiel_quand_comptaflow_est_vide(): void
    {
        $reponse = $this->deverser($this->referentiel())->assertOk();

        $reponse->assertJson([
            'success'  => true,
            'comptes'  => 3,
            'journaux' => 2,
            'tiers'    => 2,
        ]);

        $this->assertSame(3, DB::table('plan_comptables')->count());
        $this->assertSame(2, DB::table('code_journals')->count());
        $this->assertSame(2, DB::table('plan_tiers')->count());

        // Le type suit la classe SYSCOHADA : un compte de vente n'arrive pas
        // à l'actif du bilan.
        $this->assertSame('produit', DB::table('plan_comptables')
            ->where('numero_de_compte', '701000')->value('type_de_compte'));

        // Le journal de trésorerie porte son compte.
        $mtn = DB::table('code_journals')->where('code_journal', 'MTN')->first();
        $this->assertSame(
            DB::table('plan_comptables')->where('numero_de_compte', '521500')->value('id'),
            $mtn->compte_de_tresorerie
        );

        // L'ordre compte : le tiers est rattaché à son compte général, qui
        // vient d'être créé au-dessus.
        $tiers = DB::table('plan_tiers')->where('numero_de_tiers', '410007')->first();
        $this->assertSame(
            DB::table('plan_comptables')->where('numero_de_compte', '411000')->value('id'),
            $tiers->compte_general
        );
        $this->assertSame('+225 07 00 00 00', $tiers->telephone);
    }

    public function test_le_compte_general_absent_est_deduit_du_prefixe(): void
    {
        $this->deverser([
            'plan_comptable' => [['numero_de_compte' => '401000', 'intitule' => 'Fournisseurs']],
            'tiers'          => [[
                'numero_de_tiers' => '401001',
                'intitule'        => 'CDCI Distribution',
                'type_de_tiers'   => 'fournisseur',
            ]],
        ])->assertOk();

        $this->assertSame(
            DB::table('plan_comptables')->where('numero_de_compte', '401000')->value('id'),
            DB::table('plan_tiers')->where('numero_de_tiers', '401001')->value('compte_general')
        );
    }

    // ─── Comptaflow n'est pas vide : rien n'est écrasé, rien n'est supprimé ──

    public function test_rejoue_ne_double_rien(): void
    {
        $this->deverser($this->referentiel())->assertOk();
        $this->deverser($this->referentiel())->assertOk();

        $this->assertSame(3, DB::table('plan_comptables')->count());
        $this->assertSame(2, DB::table('code_journals')->count());
        $this->assertSame(2, DB::table('plan_tiers')->count());
    }

    public function test_ce_que_le_comptable_a_saisi_nest_jamais_reecrit(): void
    {
        $this->deverser($this->referentiel())->assertOk();

        DB::table('plan_comptables')->where('numero_de_compte', '411000')
            ->update(['intitule' => 'CLIENTS — LIBELLÉ DU COMPTABLE']);
        DB::table('code_journals')->where('code_journal', 'VTE')
            ->update(['intitule' => 'VENTES BOUTIQUE', 'type' => 'Ventes détail']);
        DB::table('plan_tiers')->where('numero_de_tiers', '410007')
            ->update(['intitule' => 'KONAN YAO (ABIDJAN)', 'telephone' => '+225 01 02 03 04']);

        $this->deverser($this->referentiel())->assertOk();

        $this->assertSame('CLIENTS — LIBELLÉ DU COMPTABLE', DB::table('plan_comptables')
            ->where('numero_de_compte', '411000')->value('intitule'));
        $this->assertSame('VENTES BOUTIQUE', DB::table('code_journals')
            ->where('code_journal', 'VTE')->value('intitule'));
        $this->assertSame('Ventes détail', DB::table('code_journals')
            ->where('code_journal', 'VTE')->value('type'));

        $tiers = DB::table('plan_tiers')->where('numero_de_tiers', '410007')->first();
        $this->assertSame('KONAN YAO (ABIDJAN)', $tiers->intitule);
        $this->assertSame('+225 01 02 03 04', $tiers->telephone);
    }

    public function test_un_champ_reste_vide_est_complete(): void
    {
        $this->deverser([
            'tiers' => [[
                'numero_de_tiers' => '410007',
                'intitule'        => 'Konan Yao',
                'type_de_tiers'   => 'client',
            ]],
        ])->assertOk();

        $this->assertNull(DB::table('plan_tiers')->where('numero_de_tiers', '410007')->value('telephone'));

        $this->deverser([
            'tiers' => [[
                'numero_de_tiers' => '410007',
                'intitule'        => 'Konan Yao',
                'type_de_tiers'   => 'client',
                'informations'    => ['telephone' => '+225 07 00 00 00', 'adresse' => ''],
            ]],
        ])->assertOk()->assertJson(['detail' => ['tiers' => ['completes' => 1]]]);

        $tiers = DB::table('plan_tiers')->where('numero_de_tiers', '410007')->first();
        $this->assertSame('+225 07 00 00 00', $tiers->telephone);

        // Un champ vide n'est pas transmis : il écraserait ce que Comptaflow
        // détient peut-être déjà.
        $this->assertNull($tiers->adresse);
    }

    public function test_rien_nest_supprime(): void
    {
        // Un compte, un journal et un tiers propres à Comptaflow, absents du
        // déversement : ils peuvent avoir été créés par le comptable, ou
        // porter des écritures.
        DB::table('plan_comptables')->insert([
            'numero_de_compte' => '622000', 'intitule' => 'LOCATIONS',
            'user_id' => 1, 'company_id' => self::COMPTAFLOW, 'adding_strategy' => 'manuel',
        ]);
        DB::table('code_journals')->insert([
            'code_journal' => 'AN', 'intitule' => 'A NOUVEAU', 'type' => 'Opérations Diverses',
            'traitement_analytique' => false, 'user_id' => 1, 'company_id' => self::COMPTAFLOW,
        ]);

        $this->deverser($this->referentiel())->assertOk();

        $this->assertNotNull(DB::table('plan_comptables')->where('numero_de_compte', '622000')->first());
        $this->assertNotNull(DB::table('code_journals')->where('code_journal', 'AN')->first());
    }

    public function test_une_ligne_sans_numero_est_ecartee_et_signalee(): void
    {
        $this->deverser([
            'plan_comptable' => [['numero_de_compte' => '', 'intitule' => 'Compte sans numéro']],
            'tiers'          => [['numero_de_tiers' => '410009', 'intitule' => '']],
        ])->assertOk()->assertJson(['comptes' => 0, 'tiers' => 0]);

        $this->assertSame(0, DB::table('plan_comptables')->count());
        $this->assertSame(0, DB::table('plan_tiers')->count());
        $this->assertCount(2, $this->deverser([
            'plan_comptable' => [['numero_de_compte' => '', 'intitule' => 'Compte sans numéro']],
            'tiers'          => [['numero_de_tiers' => '410009', 'intitule' => '']],
        ])->json('refus'));
    }

    // ─── Utilitaires ─────────────────────────────────────────────────────────

    private function deverser(array $charge, ?string $secret = self::SECRET)
    {
        return $this->postJson('/api/external/referentiel/deverser', array_merge([
            'selflow_company_id'    => self::SELFLOW,
            'comptaflow_company_id' => self::COMPTAFLOW,
        ], $charge), ['X-Sync-Secret' => $secret]);
    }

    /** Le référentiel type, tel que Selflow le transmet. */
    private function referentiel(): array
    {
        return [
            'plan_comptable' => [
                ['numero_de_compte' => '411000', 'intitule' => 'Clients',      'numero_original' => null],
                ['numero_de_compte' => '701000', 'intitule' => 'Ventes',       'numero_original' => null],
                ['numero_de_compte' => '521500', 'intitule' => 'MTN Money',    'numero_original' => null],
            ],
            'codes_journaux' => [
                ['code_journal' => 'VTE', 'intitule' => 'Ventes', 'type' => 'Ventes',
                 'compte_numero' => null, 'numero_original' => null],
                ['code_journal' => 'MTN', 'intitule' => 'MTN Mobile Money', 'type' => 'Trésorerie',
                 'compte_numero' => '521500', 'numero_original' => null],
            ],
            'tiers' => [
                ['numero_de_tiers' => '410000', 'intitule' => 'Client divers', 'type_de_tiers' => 'client',
                 'compte_general' => '411000', 'informations' => [], 'numero_original' => '3'],
                ['numero_de_tiers' => '410007', 'intitule' => 'Konan Yao', 'type_de_tiers' => 'client',
                 'compte_general' => '411000',
                 'informations' => ['telephone' => '+225 07 00 00 00'], 'numero_original' => '11'],
            ],
        ];
    }

    private function monterLeSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('selflow_company_id')->nullable();
            $table->integer('tier_digits')->default(6);
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

        // `CodeJournal::created` répercute le journal sur les exercices : la
        // table doit exister, même vide.
        Schema::create('exercices_comptables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->boolean('is_active')->default(true);
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
        });
    }
}
