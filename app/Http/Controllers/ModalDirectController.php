<?php

namespace App\Http\Controllers;

use App\Models\PlanTiers;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\EcritureComptable;
use App\Models\ExerciceComptable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\PlanComptable;
use Illuminate\Support\Facades\Log;

class ModalDirectController   extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $data = $request->all();

            // Initialisation des valeurs par défaut pour éviter les erreurs "Undefined array key"
            if (!isset($data['annee'])) $data['annee'] = \Carbon\Carbon::now()->year;
            if (!isset($data['mois'])) $data['mois'] = \Carbon\Carbon::now()->month;
            if (!isset($data['n_saisie'])) $data['n_saisie'] = null;
            if (!isset($data['id_journal'])) $data['id_journal'] = null;
            if (!isset($data['code'])) $data['code'] = null;
            if (!isset($data['type'])) $data['type'] = null;
            if (!isset($data['intitule'])) $data['intitule'] = null;

            if (!isset($data['id_exercice'])) {
                $dernierExercice = ExerciceComptable::where('company_id', $user->company_id)
                    ->orderBy('date_debut', 'desc')
                    ->first();
                
                if ($dernierExercice) {
                    $data['id_exercice'] = $dernierExercice->id;
                }
            }

            $plansComptables = PlanComptable::where('company_id', $user->company_id)
                ->select('id', 'numero_de_compte', 'intitule')
                ->get();

            $plansTiers = PlanTiers::where('company_id', $user->company_id)
                ->select('id', 'numero_de_tiers', 'intitule')
                ->get();

            $query = EcritureComptable::where('company_id', $user->company_id)
                ->orderBy('created_at', 'desc');

            if (!empty($data['n_saisie'])) {
                $query->where('n_saisie', $data['n_saisie']);
            }

            if (!empty($data['id_journal'])) {
                $query->where('journaux_saisis_id', $data['id_journal']);
            }

            $queryForSum = clone $query;

            $ecritures = $query->with(['planComptable', 'planTiers', 'codeJournal'])->get();
            $totalDebit = $queryForSum->sum('debit');
            $totalCredit = $queryForSum->sum('credit');

            $lastSaisie = EcritureComptable::where('company_id', $user->company_id)
                ->max('n_saisie');

            $nextSaisieNumber = $lastSaisie ? str_pad((int) $lastSaisie + 1, 12, '0', STR_PAD_LEFT) : '000000000001';

            $exercice = ExerciceComptable::findOrFail($data['id_exercice']);

            return view('accounting_entry_real_goupes', compact(
                'plansComptables',
                'plansTiers',
                'data',
                'ecritures',
                'totalDebit',
                'totalCredit',
                'nextSaisieNumber',
                'exercice'
            ));
        } catch (\Throwable $e) {
            Log::error('Erreur dans index (EcritureComptableGroupesController) : ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement des écritures comptables.');
        }
    }




}
