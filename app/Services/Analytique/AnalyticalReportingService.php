<?php

namespace App\Services\Analytique;

use Illuminate\Support\Facades\DB;
use App\Models\EcritureComptable;
use App\Models\AxeAnalytique;
use App\Models\SectionAnalytique;
use App\Models\VentilationAnalytique;

class AnalyticalReportingService
{
    /**
     * Get Analytical Balance data.
     */
    public function getBalanceData($companyId, $axeId, $exerciceId = null, $filters = [])
    {
        $query = DB::table('ventilations_analytiques as v')
            ->join('ecriture_comptables as e', 'v.ecriture_id', '=', 'e.id')
            ->join('sections_analytiques as s', 'v.section_id', '=', 's.id')
            ->where('s.company_id', $companyId)
            ->where('e.statut', 'approved');

        if ($axeId) {
            $query->where('s.axe_id', $axeId);
        }

        if ($exerciceId) {
            $query->where('e.exercices_comptables_id', $exerciceId);
        }

        if (!empty($filters['date_debut'])) {
            $query->where('e.date', '>=', $filters['date_debut']);
        }

        if (!empty($filters['date_fin'])) {
            $query->where('e.date', '<=', $filters['date_fin']);
        }

        return $query->select(
            's.id as section_id',
            's.code',
            's.libelle',
            DB::raw('SUM(CASE WHEN e.debit > 0 THEN v.montant ELSE 0 END) as total_debit'),
            DB::raw('SUM(CASE WHEN e.credit > 0 THEN v.montant ELSE 0 END) as total_credit')
        )
        ->groupBy('s.id', 's.code', 's.libelle')
        ->orderBy('s.code')
        ->get();
    }

    /**
     * Get Analytical Grand Livre data for a specific section.
     */
    public function getGrandLivreData($companyId, $sectionId, $exerciceId = null, $filters = [])
    {
        $query = DB::table('ventilations_analytiques as v')
            ->join('ecriture_comptables as e', 'v.ecriture_id', '=', 'e.id')
            ->join('plan_comptables as pc', 'e.plan_comptables_id', '=', 'pc.id')
            ->where('v.section_id', $sectionId)
            ->where('e.company_id', $companyId)
            ->where('e.statut', 'approved');

        if ($exerciceId) {
            $query->where('e.exercices_comptables_id', $exerciceId);
        }

        if (!empty($filters['date_debut'])) $query->where('e.date', '>=', $filters['date_debut']);
        if (!empty($filters['date_fin'])) $query->where('e.date', '<=', $filters['date_fin']);

        return $query->select(
            'e.date',
            'e.n_saisie',
            'e.description_operation',
            'pc.numero_de_compte',
            'pc.intitule as compte_libelle',
            'v.pourcentage',
            'v.montant',
            DB::raw('CASE WHEN e.debit > 0 THEN "D" ELSE "C" END as sens')
        )
        ->orderBy('e.date')
        ->orderBy('e.created_at')
        ->get();
    }

    /**
     * Get Analytical Result data (Charges vs Products).
     */
    public function getResultData($companyId, $axeId, $exerciceId = null, $filters = [])
    {
        $query = DB::table('ventilations_analytiques as v')
            ->join('ecriture_comptables as e', 'v.ecriture_id', '=', 'e.id')
            ->join('sections_analytiques as s', 'v.section_id', '=', 's.id')
            ->join('plan_comptables as pc', 'e.plan_comptables_id', '=', 'pc.id')
            ->where('s.company_id', $companyId)
            ->where('e.statut', 'approved');

        if ($axeId) $query->where('s.axe_id', $axeId);
        if ($exerciceId) $query->where('e.exercices_comptables_id', $exerciceId);
        if (!empty($filters['date_debut'])) $query->where('e.date', '>=', $filters['date_debut']);
        if (!empty($filters['date_fin'])) $query->where('e.date', '<=', $filters['date_fin']);

        return $query->select(
            's.id as section_id',
            's.code',
            's.libelle',
            DB::raw('SUM(CASE WHEN pc.numero_de_compte LIKE "6%" THEN v.montant ELSE 0 END) as total_charges'),
            DB::raw('SUM(CASE WHEN pc.numero_de_compte LIKE "7%" THEN v.montant ELSE 0 END) as total_produits')
        )
        ->groupBy('s.id', 's.code', 's.libelle')
        ->orderBy('s.code')
        ->get();
    }
}
