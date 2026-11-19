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
    /** La clé du dossier : les tolérances de transition sont tombées. */
    private const CLE = 'cptf_live_deversement_referentiel_de_test_000000';

    /** L'entreprise Comptaflow, et l'entreprise Selflow qui lui est liée. */
    private const COMPTAFLOW = 42;
    private const SELFLOW    = 7;

    protected function setUp(): void
    {
        parent::setUp();

        $this->monterLeSchema();

        DB::table('users')->insert(['id' => 1, 'name' => 'Comptable']);
        // La convention du dossier est écrite en clair : c'est elle, et non
        // celle de Selflow, que le déversement doit suivre.
        DB::table('companies')->insert([
            'id'                    => self::COMPTAFLOW,
            'name'                  => 'ELIKET MARKET',
            'user_id'               => 1,
            'selflow_company_id'    => self::SELFLOW,
            'account_digits'        => 8,
            'journal_code_digits'   => 4,
            'tier_digits'           => 6,
            'selflow_sync_key_hash' => hash('sha256', self::CLE),
        ]);
    }

    // ─── La clé de liaison suffit ────────────────────────────────────────────

    public function test_la_cle_authentifie_le_dossier_sans_secret_ni_identifiants_dans_le_json(): void
    {
        $this->deverser($this->referentiel())->assertOk()->assertJson(['success' => true]);
    }

    public function test_une_liaison_absente_est_refusee_et_nest_pas_creee(): void
    {
        $this->postJson('/api/external/referentiel/deverser', [
            'plan_comptable' => [['numero_de_compte' => '411000', 'intitule' => 'Clients']],
        ], ['X-Company-Key' => 'cle-inconnue'])->assertStatus(401);

        $this->assertSame(0, DB::table('plan_comptables')->count());
    }

    // ─── L'amorçage : Comptaflow est vide ────────────────────────────────────

    public function test_amorce_le_referentiel_quand_comptaflow_est_vide(): void
    {
        $reponse = $this->deverser($this->referentiel());
        $reponse->assertOk();

        $reponse->assertJson([
            'success'  => true,
            'comptes'  => 3,
            'journaux' => 2,
            'tiers'    => 2,
        ]);

        $this->assertSame(3, DB::table('plan_comptables')->count());
        $this->assertSame(2, DB::table('code_journals')->count());
        $this->assertSame(2, DB::table('plan_tiers')->count());

        // Le type est celui que l'import de Comptaflow donne : un compte de
        // classe 7 va au compte de résultat, pas au bilan.
        $this->assertSame('Compte de résultat', DB::table('plan_comptables')
            ->where('numero_de_compte', '70100000')->value('type_de_compte'));

        // Le journal de trésorerie porte son compte.
        $mtn = DB::table('code_journals')->where('code_journal', 'MTN1')->first();
        $this->assertSame(
            DB::table('plan_comptables')->where('numero_de_compte', '52150000')->value('id'),
            $mtn->compte_de_tresorerie
        );

        // L'ordre compte : le tiers est rattaché à son compte général, qui
        // vient d'être créé au-dessus.
        $tiers = DB::table('plan_tiers')->where('numero_original', '410007')->first();
        $this->assertSame(
            DB::table('plan_comptables')->where('numero_de_compte', '41100000')->value('id'),
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
            DB::table('plan_comptables')->where('numero_de_compte', '40100000')->value('id'),
            DB::table('plan_tiers')->where('numero_original', '401001')->value('compte_general')
        );
    }

    // ─── La machine d'uniformisation : un déversement est un import ─────────

    public function test_le_referentiel_prend_la_convention_du_dossier_et_garde_l_original_dessous(): void
    {
        $this->deverser($this->referentiel())->assertOk();

        // Le compte : huit chiffres, complétés à droite, et le numéro de
        // Selflow rangé dessous.
        $compte = DB::table('plan_comptables')->where('numero_de_compte', '41100000')->first();
        $this->assertNotNull($compte, 'Le compte 411000 de Selflow devait devenir 41100000.');
        $this->assertSame('411000', $compte->numero_original);
        $this->assertSame(0, DB::table('plan_comptables')->where('numero_de_compte', '411000')->count(),
            'Aucun compte ne doit rester à la convention de Selflow.');

        // Le journal : la règle de l'import, et non une copie. `VTE` sur quatre
        // caractères devient `VTE1` — la copie du lot 23 donnait `VTE0`.
        $journal = DB::table('code_journals')->where('code_journal', 'VTE1')->first();
        $this->assertNotNull($journal);
        $this->assertSame('VTE', $journal->numero_original);

        // Le tiers : déjà conforme — six caractères, préfixe 41, numérique —,
        // l'import le garde tel quel, et en déduit la catégorie.
        $tiers = DB::table('plan_tiers')->where('numero_original', '410007')->first();
        $this->assertSame('410007', $tiers->numero_de_tiers);
        $this->assertSame('Client', $tiers->type_de_tiers);
    }

    public function test_un_dossier_lie_avant_la_regle_retrouve_ses_lignes_brutes(): void
    {
        // Le déversement rangeait jusqu'ici les numéros tels quels, sans
        // numéro d'origine. Ne chercher que sur la convention du dossier et
        // sur `numero_original` ne les retrouvait plus : chaque compte se
        // serait créé une seconde fois à côté du premier.
        DB::table('plan_comptables')->insert([
            'numero_de_compte' => '411000', 'intitule' => 'CLIENTS',
            'user_id' => 1, 'company_id' => self::COMPTAFLOW, 'adding_strategy' => 'imported',
        ]);
        DB::table('code_journals')->insert([
            'code_journal' => 'VTE', 'intitule' => 'VENTES', 'type' => 'Ventes',
            'traitement_analytique' => false, 'user_id' => 1, 'company_id' => self::COMPTAFLOW,
        ]);

        $this->deverser($this->referentiel())->assertOk();

        $this->assertSame(0, DB::table('plan_comptables')->where('numero_de_compte', '41100000')->count(),
            'Le compte brut existait : il ne devait pas être créé une seconde fois.');
        $this->assertSame(0, DB::table('code_journals')->where('code_journal', 'VTE1')->count(),
            'Le journal brut existait : il ne devait pas être créé une seconde fois.');
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

        DB::table('plan_comptables')->where('numero_de_compte', '41100000')
            ->update(['intitule' => 'CLIENTS — LIBELLÉ DU COMPTABLE']);
        DB::table('code_journals')->where('code_journal', 'VTE1')
            ->update(['intitule' => 'VENTES BOUTIQUE', 'type' => 'Ventes détail']);
        DB::table('plan_tiers')->where('numero_original', '410007')
            ->update(['intitule' => 'KONAN YAO (ABIDJAN)', 'telephone' => '+225 01 02 03 04']);

        $this->deverser($this->referentiel())->assertOk();

        $this->assertSame('CLIENTS — LIBELLÉ DU COMPTABLE', DB::table('plan_comptables')
            ->where('numero_de_compte', '41100000')->value('intitule'));
        $this->assertSame('VENTES BOUTIQUE', DB::table('code_journals')
            ->where('code_journal', 'VTE1')->value('intitule'));
        $this->assertSame('Ventes détail', DB::table('code_journals')
            ->where('code_journal', 'VTE1')->value('type'));

        $tiers = DB::table('plan_tiers')->where('numero_original', '410007')->first();
        $this->assertSame('KONAN YAO (ABIDJAN)', $tiers->intitule);
        $this->assertSame('+225 01 02 03 04', $tiers->telephone);
    }

    public function test_un_champ_reste_vide_est_complete(): void
    {
        // L'import refuse un tiers qu'il ne peut rattacher à aucun compte de
        // classe 4 : le compte collectif part avec lui, comme Selflow l'envoie.
        $this->deverser([
            'plan_comptable' => [['numero_de_compte' => '411000', 'intitule' => 'Clients']],
            'tiers' => [[
                'numero_de_tiers' => '410007',
                'intitule'        => 'Konan Yao',
                'type_de_tiers'   => 'client',
            ]],
        ])->assertOk();

        $this->assertNull(DB::table('plan_tiers')->where('numero_original', '410007')->value('telephone'));

        $this->deverser([
            'plan_comptable' => [['numero_de_compte' => '411000', 'intitule' => 'Clients']],
            'tiers' => [[
                'numero_de_tiers' => '410007',
                'intitule'        => 'Konan Yao',
                'type_de_tiers'   => 'client',
                'informations'    => ['telephone' => '+225 07 00 00 00', 'adresse' => ''],
            ]],
        ])->assertOk();

        $tiers = DB::table('plan_tiers')->where('numero_original', '410007')->first();
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
        ])->assertOk()->assertJson(['detail' => [
            'plan_comptable' => ['creees' => 0, 'ecartees' => 1],
            'tiers'          => ['creees' => 0, 'ecartees' => 1],
        ]]);

        $this->assertSame(0, DB::table('plan_comptables')->count());
        $this->assertSame(0, DB::table('plan_tiers')->count());
        $this->assertCount(2, $this->deverser([
            'plan_comptable' => [['numero_de_compte' => '', 'intitule' => 'Compte sans numéro']],
            'tiers'          => [['numero_de_tiers' => '410009', 'intitule' => '']],
        ])->json('refus'));
    }


    public function test_depot_pdf_rattache_l_operation_et_refuse_un_remplacement_silencieux(): void
    {
        $operation = 'SELFLOW-7-OP000123';
        foreach ([1, 2] as $numero) {
            DB::table('ecriture_comptables')->insert([
                'date' => '2026-11-19',
                'n_saisie' => (string) $numero,
                'description_operation' => 'Vente test',
                'reference_piece' => 'VTE-123',
                'plan_comptable_id' => 1,
                'plan_tiers_id' => 1,
                'plan_analytique' => false,
                'code_journal_id' => 1,
                'debit' => 100,
                'credit' => 0,
                'user_id' => 1,
                'company_id' => self::COMPTAFLOW,
                'cle_selflow' => 'SELFLOW-7-' . $numero,
                'operation_selflow' => $operation,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $pdf = "%PDF-1.4\n% pièce de test\n%%EOF";
        $charge = [
            'operation_source' => $operation,
            'nom_fichier' => 'facture-123.pdf',
            'sha256' => hash('sha256', $pdf),
            'contenu_base64' => base64_encode($pdf),
        ];

        $reponse = $this->postJson('/api/external/pieces-justificatives/deverser', $charge, [
            'X-Company-Key' => self::CLE,
        ]);
        $reponse->assertCreated()->assertJson(['success' => true, 'idempotent' => false]);

        $chemin = DB::table('ecriture_comptables')
            ->where('company_id', self::COMPTAFLOW)
            ->where('operation_selflow', $operation)
            ->value('piece_justificatif');

        $this->assertStringStartsWith('private/justificatifs/42/', $chemin);
        $this->assertSame(1, DB::table('ecriture_comptables')
            ->where('operation_selflow', $operation)
            ->where('piece_justificatif', $chemin)->count());

        $meme = $this->postJson('/api/external/pieces-justificatives/deverser', $charge, [
            'X-Company-Key' => self::CLE,
        ]);
        $meme->assertOk()->assertJson(['success' => true, 'idempotent' => true]);

        $autrePdf = "%PDF-1.4\n% autre pièce\n%%EOF";
        $autre = $charge;
        $autre['sha256'] = hash('sha256', $autrePdf);
        $autre['contenu_base64'] = base64_encode($autrePdf);
        $this->postJson('/api/external/pieces-justificatives/deverser', $autre, [
            'X-Company-Key' => self::CLE,
        ])->assertStatus(409);

        $ecritureId = DB::table('ecriture_comptables')
            ->where('operation_selflow', $operation)->value('id');
        $this->get('/api/external/pieces-justificatives/' . $ecritureId, [
            'X-Company-Key' => self::CLE,
        ])->assertOk()->assertHeader('Content-Type', 'application/pdf');

        @unlink(storage_path('app/' . $chemin));
    }

    // ─── Utilitaires ─────────────────────────────────────────────────────────

    private function deverser(array $charge)
    {
        return $this->postJson('/api/external/referentiel/deverser', $charge, [
            'X-Company-Key' => self::CLE,
        ]);
    }

    /** Le référentiel type, tel que Selflow le transmet. */
    private function referentiel(): array
    {
        return [
            'plan_comptable' => [
                ['numero_de_compte' => '411000', 'intitule' => 'Clients'],
                ['numero_de_compte' => '701000', 'intitule' => 'Ventes'],
                ['numero_de_compte' => '521500', 'intitule' => 'MTN Money'],
            ],
            'codes_journaux' => [
                ['code_journal' => 'VTE', 'intitule' => 'Ventes', 'type' => 'Ventes',
                 'compte_numero' => null],
                ['code_journal' => 'MTN', 'intitule' => 'MTN Mobile Money', 'type' => 'Trésorerie',
                 'compte_numero' => '521500'],
            ],
            'tiers' => [
                ['numero_de_tiers' => '410000', 'intitule' => 'Client divers', 'type_de_tiers' => 'client',
                 'compte_general' => '411000', 'informations' => []],
                ['numero_de_tiers' => '410007', 'intitule' => 'Konan Yao', 'type_de_tiers' => 'client',
                 'compte_general' => '411000',
                 'informations' => ['telephone' => '+225 07 00 00 00']],
            ],
        ];
    }

    private function monterLeSchema(): void
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
            $table->unsignedBigInteger('company_id')->nullable();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('selflow_company_id')->nullable();
            // La configuration que le provisionnement aligne sur celle de
            // Selflow : sans ces colonnes, l'insertion tombe en « no such
            // column » et la reponse part en 500.
            $table->integer('account_digits')->default(8);
            $table->integer('journal_code_digits')->default(4);
            $table->string('journal_code_type')->default('alphabetical');
            $table->integer('tier_digits')->default(6);
            // Un déversement accepté date sa **réception** : Selflow affichait
            // cette date à l'entreprise en l'écrivant au moment de l'*envoi*,
            // ce qui datait une réception qui n'avait pas forcément eu lieu.
            $table->timestamp('selflow_last_deposit_at')->nullable();
            // Ce que lit le filtre `cle.entreprise`, maintenant qu'aucun
            // appel n'entre plus sans clé.
            $table->string('selflow_sync_key_hash', 64)->nullable()->unique();
            $table->string('selflow_sync_key_hash_precedente', 64)->nullable();
            $table->timestamp('selflow_sync_key_precedente_expire_at')->nullable();
            $table->timestamp('selflow_sync_key_revoked_at')->nullable();
            $table->timestamps();
                    // La corbeille : une entreprise supprimee reste recuperable
            // trente jours. Le modele porte SoftDeletes, la table doit
            // donc offrir la colonne.
            $table->softDeletes();
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
            // L'import ne range rien dans un exercice clos.
            $table->boolean('cloturer')->default(false);
            $table->boolean('is_active')->default(true);
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
        });

        Schema::create('ecriture_comptables', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->string('n_saisie')->nullable();
            $table->string('description_operation')->nullable();
            $table->string('reference_piece')->nullable();
            $table->unsignedBigInteger('plan_comptable_id')->nullable();
            $table->unsignedBigInteger('plan_tiers_id')->nullable();
            $table->boolean('plan_analytique')->default(false);
            $table->unsignedBigInteger('code_journal_id')->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->string('piece_justificatif')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->string('cle_selflow')->nullable();
            $table->string('operation_selflow')->nullable();
            $table->timestamps();
        });
    }
}
