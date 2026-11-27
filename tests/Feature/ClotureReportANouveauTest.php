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
 * Le bouton Résultat ne crée aucun mouvement. À la clôture, le résultat SIG
 * est porté par une seule ligne 1301/1309 dans le RAN du prochain exercice.
 * Aucune contrepartie n'est créée sur les comptes de gestion 6, 7 ou 8.
 */
class ClotureReportANouveauTest extends TestCase
{
    use DatabaseTransactions;

    private Company $company;
    private User $user;
    private ExerciceComptable $exercice;
    private ExerciceComptable $exerciceSuivant;
    private CodeJournal $journal;
    private CodeJournal $journalRan;
    private CodeJournal $journalOd;

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

        // L'exercice source est terminé ; son successeur immédiat est créé dans le jeu de test.
        $this->exercice = ExerciceComptable::create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'intitule' => 'EXERCICE 2024',
            'date_debut' => '2024-01-01',
            'date_fin' => '2024-12-31',
            'is_active' => true,
            'cloturer' => 0,
        ]);

        $this->exerciceSuivant = ExerciceComptable::create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'intitule' => 'EXERCICE 2025',
            'date_debut' => '2025-01-01',
            'date_fin' => '2025-12-31',
            'is_active' => false,
            'cloturer' => 0,
        ]);

        $this->journal = CodeJournal::create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'code_journal' => 'VTE',
            'intitule' => 'Ventes',
            'traitement_analytique' => 0,
        ]);

        // Journal système RAN, paramétré par l'entreprise : code sur 4 caractères,
        // retrouvé par son TYPE et non par son code.
        $this->journalRan = CodeJournal::create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'code_journal' => 'RAN1',
            'intitule' => 'REPORT A NOUVEAU',
            'type' => 'REPORT A NOUVEAU',
            'traitement_analytique' => 0,
        ]);

        // Journal des opérations diverses (OD), distinct du RAN : il porte la ligne de résultat.
        $this->journalOd = CodeJournal::create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'code_journal' => 'OD',
            'intitule' => 'OPERATION DIVERSE',
            'type' => 'Opérations Diverses',
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
            ->where(function ($q) {
                $q->where('is_ran', true)
                    ->orWhere('reference_piece', 'RES-' . $this->exercice->id);
            })->with('planComptable')->get();
    }

    public function test_la_cloture_passe_automatiquement_le_resultat_si_absent(): void
    {
        $this->piece('ECR-000001', [
            ['57100000', 5000000, 0],
            ['70100000', 0, 5000000],
        ]);

        // Pas d'appel préalable au bouton « Passer le résultat ».
        $this->cloturer();

        $this->assertTrue((bool) $this->exercice->fresh()->cloturer);
        $resultatSource = EcritureComptable::where('exercices_comptables_id', $this->exercice->id)
            ->whereHas('planComptable', fn ($q) => $q->where('numero_de_compte', 'like', '1301%')
                ->orWhere('numero_de_compte', 'like', '1309%'))
            ->count();
        $this->assertSame(0, $resultatSource, 'Aucune ligne de résultat ne doit être créée dans l’exercice source.');
        $this->assertSame(1, $this->lignesDuReport()->filter(
            fn ($l) => ComptesDeResultat::estCompteDeResultat($l->planComptable->numero_de_compte)
        )->count(), 'Le RAN doit porter une seule ligne de résultat.');
    }

    public function test_les_ecritures_du_dossier_sont_visibles_a_un_autre_utilisateur_autorise(): void
    {
        $this->piece('ECR-000001', [
            ['57100000', 5000000, 0],
            ['70100000', 0, 5000000],
        ]);

        $secondUser = User::factory()->create(['company_id' => $this->company->id]);
        $this->actingAs($secondUser);
        session(['current_company_id' => $this->company->id]);

        $visible = EcritureComptable::where('company_id', $this->company->id)->count();
        $this->assertSame(2, $visible, 'Le filtre de dossier ne doit pas masquer les écritures selon leur auteur.');
    }

    public function test_un_benefice_part_au_credit_du_compte_1301(): void
    {
        // Vente de 5 000 000 encaissée : bénéfice de 5 000 000.
        $this->piece('ECR-000001', [
            ['57100000', 5000000, 0],
            ['70100000', 0, 5000000],
        ]);

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

    public function test_la_cloture_est_refusee_si_la_date_de_fin_n_est_pas_atteinte(): void
    {
        $dateFinFuture = now()->addDays(30)->startOfDay();
        $this->exercice->update(['date_fin' => $dateFinFuture->toDateString()]);
        $this->exerciceSuivant->update([
            'date_debut' => $dateFinFuture->copy()->addDay()->toDateString(),
            'date_fin' => $dateFinFuture->copy()->addYear()->toDateString(),
        ]);
        $this->piece('ECR-000001', [
            ['57100000', 5000000, 0],
            ['70100000', 0, 5000000],
        ]);

        $reponse = $this->patch(route('exercice_comptable.cloturer', $this->exercice->id));
        $this->assertNotNull($reponse->baseResponse->getSession()?->get('error'));
        $this->assertFalse((bool) $this->exercice->fresh()->cloturer);
        $this->assertSame($dateFinFuture->toDateString(), $this->exercice->fresh()->date_fin->toDateString());
        $this->assertSame(2, EcritureComptable::where('exercices_comptables_id', $this->exercice->id)->count());
    }

    public function test_cloture_sans_exercice_suivant_ne_modifie_aucune_donnee(): void
    {
        $this->exerciceSuivant->delete();
        $this->piece('ECR-000001', [
            ['57100000', 5000000, 0],
            ['70100000', 0, 5000000],
        ]);
        $avant = EcritureComptable::where('company_id', $this->company->id)->count();

        $reponse = $this->patch(route('exercice_comptable.cloturer', $this->exercice->id));
        $message = $reponse->baseResponse->getSession()?->get('error');

        $this->assertSame(
            "L'exercice 2025 (du 01/01/2025 au 31/12/2025) n'existe pas encore. Veuillez le créer avant de clôturer l'exercice 2024.",
            $message
        );
        $this->assertFalse((bool) $this->exercice->fresh()->cloturer);
        $this->assertFalse((bool) $this->exercice->fresh()->resultat_passe);
        $this->assertSame($avant, EcritureComptable::where('company_id', $this->company->id)->count());
        $this->assertSame(0, EcritureComptable::where('reference_piece', 'RAN-' . $this->exercice->id)->count());
    }

    public function test_apres_creation_de_l_exercice_suivant_la_cloture_fonctionne(): void
    {
        $this->exerciceSuivant->delete();
        $this->piece('ECR-000001', [
            ['57100000', 5000000, 0],
            ['70100000', 0, 5000000],
        ]);
        $this->exerciceSuivant = ExerciceComptable::create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'intitule' => 'EXERCICE 2025',
            'date_debut' => '2025-01-01',
            'date_fin' => '2025-12-31',
            'is_active' => false,
            'cloturer' => 0,
        ]);

        $this->cloturer();
        $this->assertTrue((bool) $this->exercice->fresh()->cloturer);
        $this->assertSame(1, $this->lignesDuReport()->filter(
            fn ($l) => ComptesDeResultat::estCompteDeResultat($l->planComptable->numero_de_compte)
        )->count());
    }

    public function test_les_anciennes_lignes_res_systeme_sont_exclues_du_sig_et_nettoyees(): void
    {
        $this->piece('ECR-000001', [
            ['57100000', 5000000, 0],
            ['70100000', 0, 5000000],
        ]);

        // Ancienne pièce erronée : contrepartie de gestion + ligne 1301.
        foreach ([
            ['70100000', 5000000, 0],
            ['13010000', 0, 5000000],
        ] as [$numero, $debit, $credit]) {
            EcritureComptable::create([
                'company_id' => $this->company->id,
                'code_journal_id' => $this->journal->id,
                'user_id' => $this->user->id,
                'exercices_comptables_id' => $this->exercice->id,
                'date' => '2024-12-31',
                'n_saisie' => 'RES-2024',
                'description_operation' => 'Ancienne affectation système',
                'reference_piece' => 'RES-' . $this->exercice->id,
                'plan_comptable_id' => $this->compte($numero)->id,
                'debit' => $debit,
                'credit' => $credit,
                'is_resultat_systeme' => true,
                'is_ran' => false,
                'statut' => 'approved',
            ]);
        }

        $this->cloturer();

        $this->assertSame(0, EcritureComptable::where('exercices_comptables_id', $this->exercice->id)
            ->where('reference_piece', 'RES-' . $this->exercice->id)->count());
        $resultat = $this->lignesDuReport()->first(
            fn ($line) => str_starts_with($line->planComptable->numero_de_compte, '1301')
        );
        $this->assertNotNull($resultat);
        $this->assertEqualsWithDelta(5000000, (float) $resultat->credit, 0.01);
    }

    public function test_une_ligne_resultat_deja_presente_n_est_pas_dupliquee(): void
    {
        $this->piece('ECR-000001', [
            ['57100000', 5000000, 0],
            ['70100000', 0, 5000000],
        ]);
        // Report déjà présent (caisse) et ligne de résultat déjà passée en OD.
        foreach ([
            ['57100000', 5000000, 0, $this->journalRan->id, 'RAN-', true, false],
            ['13010000', 0, 5000000, $this->journalOd->id, 'RES-', false, true],
        ] as [$numero, $debit, $credit, $journalId, $prefixe, $estRan, $estSysteme]) {
            EcritureComptable::create([
                'company_id' => $this->company->id,
                'code_journal_id' => $journalId,
                'user_id' => $this->user->id,
                'exercices_comptables_id' => $this->exerciceSuivant->id,
                'date' => '2025-01-01',
                'n_saisie' => $prefixe . '2024',
                'description_operation' => 'Existant',
                'reference_piece' => $prefixe . $this->exercice->id,
                'plan_comptable_id' => $this->compte($numero)->id,
                'debit' => $debit,
                'credit' => $credit,
                'is_ran' => $estRan,
                'is_resultat_systeme' => $estSysteme,
                'statut' => 'approved',
            ]);
        }

        $this->cloturer();

        $this->assertSame(1, $this->lignesDuReport()->filter(
            fn ($line) => ComptesDeResultat::estCompteDeResultat($line->planComptable->numero_de_compte)
        )->count());
        $this->assertSame(2, $this->lignesDuReport()->count(), 'Aucune ligne existante ne doit être dupliquée.');
    }

    public function test_le_resultat_est_dans_le_journal_od_et_le_report_dans_le_journal_ran(): void
    {
        $this->piece('ECR-000001', [
            ['57100000', 5000000, 0],
            ['70100000', 0, 5000000],
        ]);

        $this->cloturer();

        $lignes = $this->lignesDuReport();
        $resultat = $lignes->first(fn ($l) => ComptesDeResultat::estCompteDeResultat($l->planComptable->numero_de_compte));
        $this->assertNotNull($resultat);
        $this->assertSame($this->journalOd->id, (int) $resultat->code_journal_id, 'Le résultat va dans le journal OD.');
        $this->assertFalse((bool) $resultat->is_ran);
        $this->assertTrue((bool) $resultat->is_resultat_systeme);
        $this->assertSame('2025-01-01', \Carbon\Carbon::parse($resultat->date)->toDateString());

        $report = $lignes->filter(fn ($l) => (bool) $l->is_ran);
        $this->assertTrue($report->isNotEmpty());
        $this->assertSame([$this->journalRan->id], $report->pluck('code_journal_id')->unique()->values()->all(),
            'Le report à nouveau va dans le journal RAN.');
        $this->assertEqualsWithDelta($lignes->sum(fn ($l) => (float) $l->debit), $lignes->sum(fn ($l) => (float) $l->credit), 0.01);
    }

    public function test_la_cloture_est_refusee_si_le_journal_od_n_est_pas_parametre(): void
    {
        $this->journalOd->delete();
        $this->piece('ECR-000001', [
            ['57100000', 5000000, 0],
            ['70100000', 0, 5000000],
        ]);
        $avant = EcritureComptable::where('company_id', $this->company->id)->count();
        $journauxAvant = CodeJournal::where('company_id', $this->company->id)->count();

        $reponse = $this->patch(route('exercice_comptable.cloturer', $this->exercice->id));
        $message = (string) $reponse->baseResponse->getSession()?->get('error');

        $this->assertStringContainsString('Journal des opérations diverses non paramétré', $message);
        $this->assertFalse((bool) $this->exercice->fresh()->cloturer);
        $this->assertSame($avant, EcritureComptable::where('company_id', $this->company->id)->count());
        $this->assertSame($journauxAvant, CodeJournal::where('company_id', $this->company->id)->count());
    }

    public function test_la_reouverture_supprime_le_report_et_la_ligne_de_resultat(): void
    {
        $this->piece('ECR-000001', [
            ['57100000', 5000000, 0],
            ['70100000', 0, 5000000],
        ]);
        $this->cloturer();
        $this->assertTrue($this->lignesDuReport()->isNotEmpty());

        $reponse = $this->patch(route('exercice_comptable.reouvrir', $this->exercice->id));
        $this->assertNull($reponse->baseResponse->getSession()?->get('error'),
            'Réouverture refusée : ' . (string) $reponse->baseResponse->getSession()?->get('error'));

        $this->assertFalse((bool) $this->exercice->fresh()->cloturer);
        $this->assertSame(0, EcritureComptable::where('exercices_comptables_id', $this->exerciceSuivant->id)->count(),
            'Le report à nouveau et la ligne de résultat doivent être supprimés.');
    }

    public function test_la_cloture_est_refusee_si_le_journal_ran_n_est_pas_parametre(): void
    {
        $this->journalRan->delete();
        $this->piece('ECR-000001', [
            ['57100000', 5000000, 0],
            ['70100000', 0, 5000000],
        ]);
        $avant = EcritureComptable::where('company_id', $this->company->id)->count();
        $journauxAvant = CodeJournal::where('company_id', $this->company->id)->count();

        $reponse = $this->patch(route('exercice_comptable.cloturer', $this->exercice->id));
        $message = (string) $reponse->baseResponse->getSession()?->get('error');

        $this->assertStringContainsString('Journal de report à nouveau non paramétré', $message);
        $this->assertFalse((bool) $this->exercice->fresh()->cloturer);
        $this->assertSame($avant, EcritureComptable::where('company_id', $this->company->id)->count());
        $this->assertSame($journauxAvant, CodeJournal::where('company_id', $this->company->id)->count(),
            'Le journal ne doit jamais être créé automatiquement.');
    }

    public function test_le_report_utilise_le_journal_ran_parametre_quel_que_soit_son_code(): void
    {
        $this->piece('ECR-000001', [
            ['57100000', 5000000, 0],
            ['70100000', 0, 5000000],
        ]);

        $this->cloturer();

        $this->assertTrue($this->lignesDuReport()->isNotEmpty());
        $this->assertSame(
            [$this->journalRan->id],
            $this->lignesDuReport()->filter(fn ($l) => (bool) $l->is_ran)
                ->pluck('code_journal_id')->unique()->values()->all()
        );
        $this->assertSame('RAN1', $this->journalRan->fresh()->code_journal);
    }

    public function test_un_resultat_anterieur_non_affecte_est_reporte_puis_s_ajoute_au_nouveau_resultat(): void
    {
        // 2024 : bénéfice de 5 000 000, non affecté.
        $this->piece('ECR-000001', [
            ['57100000', 5000000, 0],
            ['70100000', 0, 5000000],
        ]);
        $this->cloturer();

        // 2025 : perte de 2 000 000.
        EcritureComptable::create([
            'company_id' => $this->company->id, 'code_journal_id' => $this->journal->id,
            'user_id' => $this->user->id, 'exercices_comptables_id' => $this->exerciceSuivant->id,
            'date' => '2025-06-15', 'n_saisie' => 'ECR-000002', 'description_operation' => 'Test',
            'plan_comptable_id' => $this->compte('60100000')->id,
            'debit' => 2000000, 'credit' => 0, 'statut' => 'approved',
        ]);
        EcritureComptable::create([
            'company_id' => $this->company->id, 'code_journal_id' => $this->journal->id,
            'user_id' => $this->user->id, 'exercices_comptables_id' => $this->exerciceSuivant->id,
            'date' => '2025-06-15', 'n_saisie' => 'ECR-000002', 'description_operation' => 'Test',
            'plan_comptable_id' => $this->compte('57100000')->id,
            'debit' => 0, 'credit' => 2000000, 'statut' => 'approved',
        ]);
        $exercice2026 = ExerciceComptable::create([
            'company_id' => $this->company->id, 'user_id' => $this->user->id,
            'intitule' => 'EXERCICE 2026', 'date_debut' => '2026-01-01', 'date_fin' => '2026-12-31',
            'is_active' => false, 'cloturer' => 0,
        ]);

        $reponse = $this->patch(route('exercice_comptable.cloturer', $this->exerciceSuivant->id));
        $this->assertNull(
            $reponse->baseResponse->getSession()?->get('error'),
            'La clôture enchaînée a été refusée : ' . (string) $reponse->baseResponse->getSession()?->get('error')
        );

        $lignes = EcritureComptable::where('exercices_comptables_id', $exercice2026->id)
            ->where(function ($q) {
                $q->where('is_ran', true)->orWhere('reference_piece', 'RES-' . $this->exerciceSuivant->id);
            })->with('planComptable')->get();
        $parCompte = fn (string $prefixe) => $lignes->filter(
            fn ($l) => str_starts_with($l->planComptable->numero_de_compte, $prefixe)
        );

        $this->assertEqualsWithDelta(5000000, (float) $parCompte('1301')->sum('credit'), 0.01,
            'Le bénéfice 2024 non affecté reste porté sur 1301.');
        $this->assertEqualsWithDelta(2000000, (float) $parCompte('1309')->sum('debit'), 0.01,
            'La perte 2025 est portée sur 1309.');
        $this->assertSame(1, $lignes->filter(fn ($l) => (bool) $l->is_resultat_systeme)->count(),
            'Une seule ligne de résultat de l’exercice clôturé.');
        $this->assertEqualsWithDelta(
            round((float) $lignes->sum('debit'), 2), round((float) $lignes->sum('credit'), 2), 0.01
        );
        $this->assertSame(0, $parCompte('6')->count() + $parCompte('7')->count() + $parCompte('8')->count());
    }

    public function test_le_sens_du_resultat_est_annonce_en_clair(): void
    {
        $this->assertStringContainsString('CRÉDIT', ComptesDeResultat::annonce(5000000));
        $this->assertStringContainsString('1301', ComptesDeResultat::annonce(5000000));
        $this->assertStringContainsString('DÉBIT', ComptesDeResultat::annonce(-2000000));
        $this->assertStringContainsString('1309', ComptesDeResultat::annonce(-2000000));
    }
}
