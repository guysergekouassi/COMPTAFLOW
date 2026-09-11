<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Un dossier provisionné doit pouvoir recevoir une écriture.
 *
 * ─────────────────────────────────────────────────────────────────────────
 * Ce qui s'est passé
 * ─────────────────────────────────────────────────────────────────────────
 *
 * Le provisionnement créait le dossier, l'administrateur, les catégories de
 * trésorerie et la clé de liaison — **mais aucun exercice comptable**. Or
 * `deverserEcritures()` refuse en 422 (Unprocessable Content — contenu non
 * traitable) tant qu'il n'y en a pas.
 *
 * La liaison s'affichait donc active des deux côtés, le référentiel arrivait,
 * et **pas une seule écriture ne pouvait se poser**. Cinq des huit dossiers
 * étaient dans ce cas. Personne ne l'avait vu : côté Selflow, la tâche de
 * déversement marquait l'écriture en échec **sans écrire nulle part
 * pourquoi**, et se déclarait terminée.
 *
 * Ces épreuves tiennent les deux moitiés du correctif : l'exercice s'ouvre à
 * la création, et il s'ouvre aussi au rejeu — sans quoi les dossiers déjà liés
 * seraient restés inguérissables.
 */
class ExerciceALOuvertureTest extends TestCase
{
    private const SECRET = 'secret-serveur-de-test';
    private const SELFLOW_ID = 77;

    protected function setUp(): void
    {
        parent::setUp();

        config(['external_sync.external_sync_secret' => self::SECRET]);
        Mail::fake();

        $this->monterLeSchema();
    }

    /**
     * La charge utile d'un provisionnement, avec ou sans exercice annoncé.
     *
     * @param array{debut: string, fin: string, libelle?: string}|null $exercice
     * @return array<string, mixed>
     */
    private function dossier(?array $exercice, int $selflowId = self::SELFLOW_ID): array
    {
        return array_filter([
            'secret'             => self::SECRET,
            'selflow_company_id' => $selflowId,
            'entreprise' => [
                'nom'         => 'QUINCAILLERIE DU PLATEAU',
                'admin_email' => 'gerant@quincaillerie.ci',
                'admin_nom'   => 'Kouadio Lewis',
            ],
            'longueur_tiers' => 6,
            'exercice'       => $exercice,
        ], fn ($v) => $v !== null);
    }

    // ── L'ouverture ──────────────────────────────────────────────────

    public function test_le_dossier_naissant_recoit_lexercice_annonce_par_selflow(): void
    {
        $this->postJson('/api/external/companies/provision', $this->dossier([
            'debut'   => '2026-01-01',
            'fin'     => '2026-12-31',
            'libelle' => 'Exercice 2026',
        ]))->assertOk();

        $dossier = DB::table('companies')->where('selflow_company_id', self::SELFLOW_ID)->first();
        $exercice = DB::table('exercices_comptables')->where('company_id', $dossier->id)->first();

        $this->assertNotNull($exercice, "Le dossier est né sans exercice : il ne pourra recevoir aucune écriture.");
        $this->assertSame('2026-01-01', substr((string) $exercice->date_debut, 0, 10));
        $this->assertSame('2026-12-31', substr((string) $exercice->date_fin, 0, 10));
        $this->assertSame('Exercice 2026', $exercice->intitule);
        $this->assertTrue((bool) $exercice->is_active);
    }

    public function test_sans_libelle_annonce_lexercice_porte_son_annee(): void
    {
        // `??` ne rattraperait que `null` : un libellé vide passerait tel quel
        // et l'exercice s'afficherait sans nom.
        $this->postJson('/api/external/companies/provision', $this->dossier([
            'debut'   => '2026-01-01',
            'fin'     => '2026-12-31',
            'libelle' => '',
        ]))->assertOk();

        $dossier = DB::table('companies')->where('selflow_company_id', self::SELFLOW_ID)->first();

        $this->assertSame('Exercice 2026',
            DB::table('exercices_comptables')->where('company_id', $dossier->id)->value('intitule'));
    }

    // ── Le rejeu, qui répare l'existant ──────────────────────────────

    public function test_le_rejeu_ouvre_lexercice_dun_dossier_deja_lie_qui_nen_avait_pas(): void
    {
        // Un dossier né avant le correctif : lié, actif, et sans exercice.
        $this->postJson('/api/external/companies/provision', $this->dossier(null))->assertOk();

        $dossier = DB::table('companies')->where('selflow_company_id', self::SELFLOW_ID)->first();
        $this->assertSame(0,
            DB::table('exercices_comptables')->where('company_id', $dossier->id)->count());

        // Selflow réannonce son exercice, sans délier.
        $this->postJson('/api/external/companies/provision', $this->dossier([
            'debut' => '2026-01-01',
            'fin'   => '2026-12-31',
        ]))->assertOk();

        $this->assertSame(1,
            DB::table('exercices_comptables')->where('company_id', $dossier->id)->count(),
            "Un dossier déjà lié sans exercice doit pouvoir être réparé sans être délié.");
    }

    public function test_le_rejeu_nouvre_jamais_un_second_exercice(): void
    {
        $charge = $this->dossier(['debut' => '2026-01-01', 'fin' => '2026-12-31']);

        $this->postJson('/api/external/companies/provision', $charge)->assertOk();
        $this->postJson('/api/external/companies/provision', $charge)->assertOk();
        $this->postJson('/api/external/companies/provision', $charge)->assertOk();

        $dossier = DB::table('companies')->where('selflow_company_id', self::SELFLOW_ID)->first();

        $this->assertSame(1,
            DB::table('exercices_comptables')->where('company_id', $dossier->id)->count(),
            'Trois provisionnements ne doivent ouvrir qu\'un exercice.');
    }

    // ── Ce qu'on n'invente pas ───────────────────────────────────────

    public function test_sans_exercice_annonce_le_dossier_nait_sans_exercice(): void
    {
        // Un exercice comptable est une décision, pas un réglage : on ne le
        // devine pas. Le dossier naît sans, et le déversement le dira.
        $this->postJson('/api/external/companies/provision', $this->dossier(null))->assertOk();

        $dossier = DB::table('companies')->where('selflow_company_id', self::SELFLOW_ID)->first();

        $this->assertSame(0,
            DB::table('exercices_comptables')->where('company_id', $dossier->id)->count());
    }

    public function test_un_exercice_a_lenvers_est_refuse(): void
    {
        $this->postJson('/api/external/companies/provision', $this->dossier([
            'debut' => '2026-12-31',
            'fin'   => '2026-01-01',
        ]))->assertStatus(422);

        $this->assertSame(0,
            DB::table('companies')->where('selflow_company_id', self::SELFLOW_ID)->count(),
            "Une charge utile refusée ne doit pas laisser un dossier à moitié créé.");
    }

    // ── La configuration du dossier ──────────────────────────────────

    public function test_le_dossier_se_configure_sur_la_convention_de_selflow(): void
    {
        $charge = $this->dossier(['debut' => '2026-01-01', 'fin' => '2026-12-31']);
        $charge['longueur_comptes'] = 6;

        $this->postJson('/api/external/companies/provision', $charge)->assertOk();

        $dossier = DB::table('companies')->where('selflow_company_id', self::SELFLOW_ID)->first();

        // Le défaut de Comptaflow est 8. Selflow numérote sur six chiffres, et
        // c'est le format « COMPTES SAGE (6) » de son propre référentiel.
        $this->assertSame(6, (int) $dossier->account_digits);
        $this->assertSame(6, (int) $dossier->tier_digits);
    }

    public function test_le_rejeu_aligne_un_dossier_reste_sur_le_defaut(): void
    {
        // Un dossier né avant que Selflow n'annonce ses conventions.
        $this->postJson('/api/external/companies/provision', $this->dossier(null))->assertOk();

        $dossier = DB::table('companies')->where('selflow_company_id', self::SELFLOW_ID)->first();
        DB::table('companies')->where('id', $dossier->id)->update(['account_digits' => 8]);

        $charge = $this->dossier(['debut' => '2026-01-01', 'fin' => '2026-12-31']);
        $charge['longueur_comptes'] = 6;

        $this->postJson('/api/external/companies/provision', $charge)->assertOk();

        $this->assertSame(6,
            (int) DB::table('companies')->where('id', $dossier->id)->value('account_digits'),
            "Un dossier resté sur `account_digits = 8` recevrait des comptes à six "
            . 'chiffres de Selflow, et en produirait à huit dès le premier import : '
            . 'deux conventions dans un même dossier.');
    }

    // ── Le schéma minimal ────────────────────────────────────────────

    private function monterLeSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email_adresse')->nullable();
            $table->string('password')->nullable();
            $table->string('role')->nullable();
            $table->string('pack')->nullable();
            $table->boolean('is_online')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_blocked')->default(false);
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('activation_token')->nullable();
            $table->timestamp('activation_token_expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('activity')->nullable();
            $table->string('juridique_form')->nullable();
            $table->decimal('social_capital', 15, 2)->nullable();
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
            $table->unsignedBigInteger('parent_company_id')->nullable();
            $table->unsignedBigInteger('selflow_company_id')->nullable();
            $table->integer('account_digits')->default(8);
            $table->integer('journal_code_digits')->default(4);
            $table->string('journal_code_type')->default('alphabetical');
            $table->integer('tier_digits')->default(6);
            $table->string('tier_id_type')->default('numeric');
            // La colonne d'avant le chiffrement : `poserUneCleDeLiaison()` la
            // vide encore, pour qu'aucune clé ne reste en clair quelque part.
            $table->text('selflow_sync_key')->nullable();
            $table->text('selflow_sync_key_hash')->nullable();
            $table->text('selflow_sync_key_chiffree')->nullable();
            $table->timestamp('selflow_sync_key_revoked_at')->nullable();
            $table->timestamp('selflow_linked_at')->nullable();
            $table->string('selflow_sync_status')->nullable();
            $table->text('selflow_sync_key_hash_precedente')->nullable();
            $table->timestamp('selflow_sync_key_precedente_expire_at')->nullable();
            $table->timestamp('selflow_sync_key_rotated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('treasury_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
        });

        Schema::create('exercices_comptables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('parent_company_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('intitule')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->timestamps();
        });
    }
}
