<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\PlanComptable;
use App\Models\EcritureComptable;
use Illuminate\Support\Facades\Log;

class AiAgentController extends Controller
{
    /**
     * Point d'entrée générique pour l'Agent IA
     */
    public function executeAction(Request $request)
    {
        $action = $request->input('action');
        $payload = $request->input('payload', []);

        Log::info("🤖 Agent IA demande l'action: " . $action, $payload);

        try {
            switch ($action) {
                case 'generate_grand_livre':
                    return $this->generateGrandLivre($payload);
                case 'generate_balance':
                    return $this->generateBalance($payload);
                // D'autres actions pourront être ajoutées ici (ex: cloturer_exercice, etc.)
                default:
                    return response()->json(['success' => false, 'error' => 'Action inconnue : ' . $action], 400);
            }
        } catch (\Exception $e) {
            Log::error('Erreur IA Agent: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Génère un aperçu du Grand Livre pour l'IA
     */
    private function generateGrandLivre($payload)
    {
        $companyName = $payload['company_name'] ?? null;
        $companyId = $payload['company_id'] ?? null;

        if (!$companyName && !$companyId) {
            return response()->json(['success' => false, 'error' => 'Nom ou ID de l\'entreprise requis.'], 400);
        }

        if ($companyId) {
            $company = Company::find($companyId);
        } else {
            $company = Company::where('company_name', 'LIKE', '%' . $companyName . '%')->first();
        }

        if (!$company) {
            return response()->json(['success' => false, 'error' => "Entreprise introuvable."], 404);
        }

        $date_debut = $payload['date_debut'];
        $date_fin = $payload['date_fin'];

        // Si aucun compte min/max n'est fourni, on prend tout le plan de la compagnie
        $planIds = PlanComptable::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->orderBy('numero_de_compte', 'asc')
            ->pluck('id');

        if ($planIds->isEmpty()) {
            return response()->json(['success' => false, 'error' => 'Aucun plan comptable pour cette entreprise.'], 404);
        }

        $idMin = $planIds->first();
        $idMax = $planIds->last();

        // On crée une requête interne vers la méthode existante "previewGrandLivre" 
        // ou on réutilise la logique. Pour éviter les conflits d'Auth, on réécrit le PDF.

        $compte1 = PlanComptable::withoutGlobalScopes()->find($idMin);
        $compte2 = PlanComptable::withoutGlobalScopes()->find($idMax);

        $comptesIds = $planIds;

        $query = EcritureComptable::join('plan_comptables', 'ecriture_comptables.plan_comptable_id', '=', 'plan_comptables.id')
            ->select('ecriture_comptables.*')
            ->with(['planComptable', 'planTiers', 'codeJournal', 'ExerciceComptable'])
            ->where('ecriture_comptables.company_id', $company->id)
            ->whereBetween('date', [$date_debut, $date_fin])
            ->orderBy('plan_comptables.numero_de_compte', 'asc')
            ->orderBy('date', 'asc');

        $ecritures = $query->get();
        $ecritures = $ecritures->whereIn('plan_comptable_id', $comptesIds);

        $titre = "Grand-livre des comptes (Généré par IA)";

        // Calcul des soldes initiaux par compte (Optimisé avec une seule requête)
        $soldesInitiaux = [];
        $prevSums = EcritureComptable::selectRaw('plan_comptable_id, SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->where('company_id', $company->id)
            ->whereIn('plan_comptable_id', $comptesIds)
            ->where('date', '<', $date_debut)
            ->groupBy('plan_comptable_id')
            ->get();

        foreach ($prevSums as $sum) {
            $si_debit = (float) $sum->total_debit;
            $si_credit = (float) $sum->total_credit;
            if ($si_debit != 0 || $si_credit != 0) {
                $soldesInitiaux[$sum->plan_comptable_id] = [
                    'debit' => $si_debit,
                    'credit' => $si_credit,
                    'solde' => $si_debit - $si_credit
                ];
            }
        }

        $paginationService = new \App\Services\GrandLivrePaginationService();
        $paginatedData = $paginationService->paginate($ecritures, $soldesInitiaux, $titre, 'comptaflow');

        $pdf = app('dompdf.wrapper');
        $pdf->getDomPDF()->set_option('isPhpEnabled', true);

        // Simulation d'un user
        $adminUser = \App\Models\User::where('company_id', $company->id)->first();

        $pdf->loadView('grand_livre', [
            'company_name' => $company->company_name,
            'paginatedData' => $paginatedData,
            'date_debut' => $date_debut,
            'date_fin' => $date_fin,
            'compte' => $compte1->numero_de_compte ?? '0',
            'compte_2' => $compte2->numero_de_compte ?? '9',
            'user' => $adminUser,
            'titre' => $titre,
            'display_mode' => 'comptaflow',
        ]);

        $fileName = 'ia_grand_livre_' . time() . '.pdf';
        $filePath = public_path('previews/' . $fileName);

        if (!file_exists(public_path('previews'))) {
            mkdir(public_path('previews'), 0777, true);
        }

        file_put_contents($filePath, $pdf->output());

        return response()->json([
            'success' => true,
            'url' => asset('previews/' . $fileName)
        ]);
    }

    /**
     * Génère un aperçu de la Balance pour l'IA
     */
    private function generateBalance($payload)
    {
        $companyName = $payload['company_name'] ?? null;
        $companyId = $payload['company_id'] ?? null;

        if (!$companyName && !$companyId) {
            return response()->json(['success' => false, 'error' => 'Nom ou ID de l\'entreprise requis.'], 400);
        }

        if ($companyId) {
            $company = Company::find($companyId);
        } else {
            $company = Company::where('company_name', 'LIKE', '%' . $companyName . '%')->first();
        }

        if (!$company) {
            return response()->json(['success' => false, 'error' => "Entreprise introuvable."], 404);
        }

        $date_debut = $payload['date_debut'];
        $date_fin = $payload['date_fin'];

        $planIds = PlanComptable::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->orderBy('numero_de_compte', 'asc')
            ->pluck('id');

        if ($planIds->isEmpty()) {
            return response()->json(['success' => false, 'error' => 'Aucun plan comptable pour cette entreprise.'], 404);
        }

        $idMin = $planIds->first();
        $idMax = $planIds->last();

        $compte1 = PlanComptable::withoutGlobalScopes()->find($idMin);
        $compte2 = PlanComptable::withoutGlobalScopes()->find($idMax);

        $query = EcritureComptable::with([
            'planComptable',
            'planTiers',
            'codeJournal',
            'JournauxSaisis',
            'ExerciceComptable',
        ])
            ->where('company_id', $company->id)
            ->whereBetween('date', [$date_debut, $date_fin]);

        $ecritures = $query->get();

        $titre = "Balance des comptes (Générée par IA)";

        $comptesUtilises = $ecritures->pluck('planComptable.numero_de_compte')->filter()->sort();
        $premierCompte = $comptesUtilises->first() ?? $compte1->numero_de_compte;
        $dernierCompte = $comptesUtilises->last() ?? $compte2->numero_de_compte;

        $pdf = app('dompdf.wrapper');
        $pdf->getDomPDF()->set_option('isPhpEnabled', true);

        $adminUser = \App\Models\User::where('company_id', $company->id)->first();

        $pdf->loadView('balance', [
            'company_name' => $company->company_name,
            'ecritures' => $ecritures,
            'date_debut' => $date_debut,
            'date_fin' => $date_fin,
            'compte' => $premierCompte,
            'compte_2' => $dernierCompte,
            'user' => $adminUser,
            'titre' => $titre,
            'display_mode' => 'comptaflow',
        ]);

        $fileName = 'ia_balance_' . time() . '.pdf';
        $filePath = public_path('previews/' . $fileName);

        if (!file_exists(public_path('previews'))) {
            mkdir(public_path('previews'), 0777, true);
        }

        file_put_contents($filePath, $pdf->output());

        return response()->json([
            'success' => true,
            'url' => asset('previews/' . $fileName)
        ]);
    }
}
