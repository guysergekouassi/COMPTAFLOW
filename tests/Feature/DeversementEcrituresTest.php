<?php

namespace Tests\Feature;

use App\Services\NumerotationSaisie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Concerns\SchemaDeLaPasserelle;
use Tests\TestCase;

/**
 * Le déversement des écritures de Selflow : POST /api/external/ecritures/deverser.
 *
 * `n_saisie` recevait la `cle_selflow` de chaque ligne, unique par ligne : une
 * facture de vente arrivait en quatre saisies d'une ligne, aucune équilibrée,
 * et l'écran des saisies comme la copie — qui regroupent par `n_saisie` — y
 * voyaient quatre pièces. Constaté côté Selflow et rangé au chantier 7.1 de
 * son plan de correction : « une ligne unique par numéro de saisie, et la
 * balance qui ne tombe pas ».
 */
class DeversementEcrituresTest extends TestCase
{
    use SchemaDeLaPasserelle;

    private const SECRET = 'secret-serveur-de-test';
    private const DOSSIER = 42;
    private const SELFLOW = 7;

    private string $cle;

    protected function setUp(): void
    {
        parent::setUp();

        config(['external_sync.external_sync_secret' => self::SECRET]);
        Mail::fake();
        NumerotationSaisie::oublierReservations();

        $this->monterLeSchema();
        DB::table('users')->insert(['id' => 1, 'name' => 'Comptable']);
        $this->cle = $this->creerUnDossier(self::DOSSIER, self::SELFLOW, 'ELIKET MARKET');
    }

    /** Une facture de vente : client au débit, vente et TVA au crédit. */
    private function facture(string $operation = 'OP-000042'): array
    {
        $ligne = fn (int $n, array $champs) => $champs + [
            'cle_selflow'        => 'SELFLOW-' . self::SELFLOW . '-' . $operation . '-' . $n,
            'date_ecriture'      => '2026-06-15',
            'code_journal'       => 'VTE',
            'libelle'            => 'Facture FA-042',
            'reference_document' => 'FA-042',
        ];

        return [
            $ligne(1, ['compte_debit' => '411000', 'debit' => 118000, 'credit' => 0]),
            $ligne(2, ['compte_credit' => '701000', 'debit' => 0, 'credit' => 100000]),
            $ligne(3, ['compte_credit' => '443100', 'debit' => 0, 'credit' => 18000]),
        ];
    }

    private function deverser(array $ecritures, ?string $operation = 'OP-000042')
    {
        return $this->postJson('/api/external/ecritures/deverser', array_filter([
            'secret'             => self::SECRET,
            'selflow_company_id' => self::SELFLOW,
            'atomique'           => true,
            'operation'          => $operation,
            'ecritures'          => $ecritures,
        ], fn ($v) => $v !== null), ['X-Company-Key' => $this->cle]);
    }

    private function numeros(): array
    {
        return DB::table('ecriture_comptables')->orderBy('id')->pluck('n_saisie')->all();
    }

    public function test_les_lignes_d_une_operation_partagent_un_numero_de_saisie(): void
    {
        $this->deverser($this->facture())->assertOk()->assertJson(['success' => true, 'count' => 3]);

        $numeros = $this->numeros();
        $this->assertCount(3, $numeros);
        $this->assertCount(1, array_unique($numeros), 'Une opération, une saisie.');
        $this->assertStringStartsWith('ECR-', $numeros[0], 'Le numéro est le nôtre, pas la clé de Selflow.');
    }

    public function test_deux_operations_font_deux_saisies(): void
    {
        $this->deverser($this->facture('OP-1'), 'OP-1')->assertOk();
        $this->deverser($this->facture('OP-2'), 'OP-2')->assertOk();

        $this->assertCount(2, array_unique($this->numeros()));
    }

    public function test_un_renvoi_n_ajoute_rien_et_garde_le_numero(): void
    {
        $this->deverser($this->facture())->assertOk();
        $avant = $this->numeros();

        $this->deverser($this->facture())->assertOk()->assertJson(['count' => 0, 'ignorees' => 3]);

        $this->assertSame($avant, $this->numeros());
    }

    /**
     * Le rejeu du chantier 7.4 : des lignes reçues sous l'ancienne forme —
     * une saisie par ligne — se regroupent quand Selflow renvoie leur
     * opération, sans qu'aucune ne soit dupliquée.
     */
    public function test_le_rejeu_regroupe_ce_qui_etait_arrive_ligne_par_ligne(): void
    {
        $this->deverser($this->facture(), null)->assertOk();
        $this->assertCount(3, array_unique($this->numeros()), 'Sans opération annoncée : l\'ancienne forme.');

        $this->deverser($this->facture())->assertOk()->assertJson(['count' => 0]);

        $this->assertSame(3, DB::table('ecriture_comptables')->count());
        $this->assertCount(1, array_unique($this->numeros()));
    }

    public function test_une_operation_desequilibree_est_refusee_en_entier(): void
    {
        $lignes = $this->facture();
        $lignes[2]['credit'] = 17000;

        $this->deverser($lignes)->assertStatus(422)->assertJson(['success' => false]);

        $this->assertSame(0, DB::table('ecriture_comptables')->count());
    }

    public function test_une_ligne_a_deux_comptes_fait_refuser_l_operation(): void
    {
        $lignes = $this->facture();
        $lignes[0]['compte_credit'] = '701000';

        $this->deverser($lignes)->assertStatus(422);

        $this->assertSame(0, DB::table('ecriture_comptables')->count());
    }
}
