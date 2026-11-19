<?php

namespace Tests\Feature;

use App\Models\CodeJournal;
use App\Models\Company;
use App\Models\EcritureComptable;
use App\Models\ExerciceComptable;
use App\Models\PlanComptable;
use App\Models\User;
use App\Services\ComptesDeResultat;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Passage indépendant de l'écriture de résultat et report à nouveau.
 *
 * Le résultat est enregistré une seule fois dans l'exercice d'origine, sur
 * 1301 au crédit ou 1309 au débit. La clôture reporte ensuite ces soldes avec
 * les autres comptes de bilan, sans recréer l'écriture de résultat.
 */
class ClotureReportANouveauTest extends TestCase
{
    use DatabaseTransactions;

    private Company $company;
    private User $user;
    private ExerciceComptable $exercice;
    private CodeJournal $journal;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');

        $this->company = Company::create([
            'company_name' => 'Cloture SARL',
            'activity' => 'Test',
            'juridique_form' => 'SARL',
            'email_adresse' => 'cloture@test.ci',
            'account_digits' => 8,
        ]);

        $this->user = User::factory()->create(['company_id' => $this->company->id]);

        // La clôture peut être anticipée ; la date théorique ne bloque plus l'opération.
        $this->exercice = ExerciceComptable::create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'intitule' => 'EXERCICE 2024',
            'date_debut' => '2024-01-01',
            'date_fin' => '2024-12-31',
            'is_active' => true,
            'cloturer' => 0,
        ]);

        $this->journal = CodeJournal::create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'code_journal' => 'VTE',
            'intitule' => 'Ventes',
            'traitement_analytique' => 0,
        ]);

        foreach ([
            '57100000' => 'CAISSE',
            '70100000' => 'VENTES',
            '60100000' => 'ACHATS',
            '13010000' => "RESULTAT EN INSTANCE D'AFFECTATION - BENEFICE",
            '13090000' => "RESULTAT EN INSTANCE D'AFFECTATION - PERTE",
        ] as $numero => $intitule) {
            PlanComptable::create([
                'company_id' => $this->company->id,
                'user_id' => $this->user->id,
                'numero_de_compte' => $numero,
                'intitule' => $intitule,
            ]);
        }

        $this->actingAs($this->user);
        session(['current_company_id' => $this->company->id]);
    }

    private function compte(string $numero): PlanComptable
    {
        return PlanComptable::where('company_id', $this->company->id)
            ->where('numero_de_compte', $numero)->firstOrFail();
    }

    /**
     * @param  array<int, array{0:string,1:float,2:float}>  $lignes
     */
    private function piece(string $numero, array $lignes): void
    {
        foreach ($lignes as [$compte, $debit, $credit]) {
            EcritureComptable::create([
                'company_id' => $this->company->id,
                'code_journal_id' => $this->journal->id,
                'user_id' => $this->user->id,
                'exercices_comptables_id' => $this->exercice->id,
                'date' => '2024-06-15',
                'n_saisie' => $numero,
                'description_operation' => 'Test',
                'plan_comptable_id' => $this->compte($compte)->id,
                'debit' => $debit,
                'credit' => $credit,
                'statut' => 'approved',
            ]);
        }
    }

    private function passerResultat(): void
    {
        $reponse = $this->patch(route('exercice_comptable.resultat', $this->exercice->id));
        $erreur = $reponse->baseResponse->getSession()?->get('error');
        $this->assertNull($erreur, "Le passage du résultat a été refusé : " . (string) $erreur);
        $this->assertFalse($this->exercice->fresh()->cloturer, "Passer le résultat ne doit pas clôturer l'exercice.");
    }

    private function cloturer(): void
    {
        $reponse = $this->patch(route('exercice_comptable.cloturer', $this->exercice->id));

        // La clôture rapporte ses refus par la session : sans cela, un test
        // qui échoue ne dit pas pourquoi.
        $erreur = $reponse->baseResponse->getSession()?->get('error');
        $this->assertNull($erreur, 'La clôture a été refusée : ' . (string) $erreur);
    }

    /** @return \Illuminate\Support\Collection<int, EcritureComptable> */
    private function lignesDuReport()
    {
        $suivant = ExerciceComptable::where('company_id', $this->company->id)
            ->where('date_debut', '>', $this->exercice->date_fin)->firstOrFail();

        return EcritureComptable::where('exercices_comptables_id', $suivant->id)
            ->where('is_ran', true)->with('planComptable')->get();
    }

    public function test_un_benefice_part_au_credit_du_compte_1301(): void
    {
        // Vente de 5 000 000 encaissée : bénéfice de 5 000 000.
        $this->piece('ECR-000001', [
            ['57100000', 5000000, 0],
            ['70100000', 0, 5000000],
        ]);

        $this->passerResultat();
        $this->cloturer();

        $resultat = $this->lignesDuReport()
            ->first(fn ($l) => ComptesDeResultat::estCompteDeResultat($l->planComptable->numero_de_compte));

        $this->assertNotNull($resultat, "Le solde 1301/1309 issu du résultat doit être repris au report.");
        $this->assertSame('13010000', $resultat->planComptable->numero_de_compte);
        $this->assertEqualsWithDelta(5000000, (float) $resultat->credit, 0.01, 'Un bénéfice va au crédit.');
        $this->assertEqualsWithDelta(0, (float) $resultat->debit, 0.01);
    }

    public function test_une_perte_part_au_debit_du_compte_1309(): void
    {
        // Achat de 2 000 000 payé : perte de 2 000 000.
        $this->piece('ECR-000001', [
            ['60100000', 2000000, 0],
            ['57100000', 0, 2000000],
        ]);

        $this->passerResultat();
        $this->cloturer();

        $resultat = $this->lignesDuReport()
            ->first(fn ($l) => ComptesDeResultat::estCompteDeResultat($l->planComptable->numero_de_compte));

        $this->assertNotNull($resultat);
        $this->assertSame('13090000', $resultat->planComptable->numero_de_compte);
        $this->assertEqualsWithDelta(2000000, (float) $resultat->debit, 0.01, 'Une perte va au débit.');
        $this->assertEqualsWithDelta(0, (float) $resultat->credit, 0.01);
    }

    public function test_le_report_a_nouveau_est_equilibre(): void
    {
        $this->piece('ECR-000001', [
            ['57100000', 5000000, 0],
            ['70100000', 0, 5000000],
        ]);
        $this->piece('ECR-000002', [
            ['60100000', 1200000, 0],
            ['57100000', 0, 1200000],
        ]);

        $this->passerResultat();
        $this->cloturer();

        $lignes = $this->lignesDuReport();

        $this->assertGreaterThan(0, $lignes->count(), 'Le report doit produire des lignes.');
        $this->assertEqualsWithDelta(
            $lignes->sum(fn ($l) => (float) $l->debit),
            $lignes->sum(fn ($l) => (float) $l->credit),
            0.01,
            'Le report à nouveau doit être équilibré.'
        );
    }

    public function test_le_passage_du_resultat_ne_cloture_pas_et_est_unique(): void
    {
        $this->piece('ECR-000001', [
            ['57100000', 5000000, 0],
            ['70100000', 0, 5000000],
        ]);

        $this->passerResultat();

        $this->assertTrue($this->exercice->fresh()->resultat_passe);
        $this->assertFalse($this->exercice->fresh()->cloturer);

        $second = $this->patch(route('exercice_comptable.resultat', $this->exercice->id));
        $this->assertNotNull($second->baseResponse->getSession()?->get('error'));

        $lignesResultat = EcritureComptable::where('exercices_comptables_id', $this->exercice->id)
            ->whereHas('planComptable', fn ($q) => $q->where('numero_de_compte', 'like', '1301%')
                ->orWhere('numero_de_compte', 'like', '1309%'))
            ->count();

        $this->assertSame(1, $lignesResultat);
    }

    public function test_la_cloture_peut_etre_anticipee_avant_la_date_de_fin(): void
    {
        $this->exercice->update(['date_fin' => now()->addDays(30)->toDateString()]);

        $this->piece('ECR-000001', [
            ['57100000', 5000000, 0],
            ['70100000', 0, 5000000],
        ]);

        $this->passerResultat();
        $this->cloturer();

        $exerciceCloture = $this->exercice->fresh();
        $this->assertTrue((bool) $exerciceCloture->cloturer);
        $this->assertSame(now()->toDateString(), $exerciceCloture->date_fin->toDateString());

        $suivant = ExerciceComptable::where('company_id', $this->exercice->company_id)
            ->whereDate('date_debut', now()->addDay()->toDateString())
            ->first();
        $this->assertNotNull($suivant, 'Le nouvel exercice doit commencer le lendemain de la clôture anticipée.');
    }

    public function test_le_sens_du_resultat_est_annonce_en_clair(): void
    {
        $this->assertStringContainsString('CRÉDIT', ComptesDeResultat::annonce(5000000));
        $this->assertStringContainsString('1301', ComptesDeResultat::annonce(5000000));
        $this->assertStringContainsString('DÉBIT', ComptesDeResultat::annonce(-2000000));
        $this->assertStringContainsString('1309', ComptesDeResultat::annonce(-2000000));
    }
}
