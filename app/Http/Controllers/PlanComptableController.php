<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlanComptable;
use App\Models\EcritureComptable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Traits\ManagesCompany;

class PlanComptableController extends Controller
{

   use ManagesCompany;
    public function index()
    {
        try {
            // Récupérer l'utilisateur connecté
            $user = Auth::user();
            
            // Récupérer toutes les compagnies
            $companies = \App\Models\Company::with(['plansComptables' => function($query) {
                $query->orderByRaw("LPAD(numero_de_compte, 20, '0')");
            }])->get();

            // Récupérer les plans de la compagnie actuelle de l'utilisateur
            $currentCompanyPlans = $user->company->plansComptables()
                ->orderByRaw("LPAD(numero_de_compte, 20, '0')")
                ->get();

            // Statistiques globales
            $totalPlans = PlanComptable::count();
            $plansByUser = PlanComptable::where('adding_strategy', 'manuel')->count();
            $plansSys = PlanComptable::where('adding_strategy', 'auto')->count();
            $hasAutoStrategy = $plansSys > 0;

            // Charger les plans par défaut depuis le fichier JSON
            $jsonPath = storage_path('app/plan_comptable.json');
            $plansComptablesDefauts = file_exists($jsonPath) ? 
                json_decode(file_get_contents($jsonPath), true) : [];

            return view('plan_comptable', [
                'companies' => $companies,
                'currentCompanyPlans' => $currentCompanyPlans,
                'totalPlans' => $totalPlans,
                'plansByUser' => $plansByUser,
                'plansSys' => $plansSys,
                'hasAutoStrategy' => $hasAutoStrategy,
                'plansComptablesDefauts' => $plansComptablesDefauts,
                'currentCompanyId' => $user->company_id
            ]);
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Erreur lors du chargement des plans comptables : ' . $e->getMessage());
    }
}

    public function verifierNumeroCompte(Request $request)
    {
        try {
            $existe = PlanComptable::where('numero_de_compte', $request->numero_de_compte)->exists();

            return response()->json([
                'exists' => $existe,
                'numero_formatte' => $request->numero_de_compte
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la vérification du numéro de compte : ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'numero_de_compte' => 'required',
                'intitule' => 'required',
            ]);

            $numero_formate = str_pad($request->numero_de_compte, 8, '0', STR_PAD_RIGHT);
            $intitule_formate = ucfirst(strtolower($request->intitule));

            $exists = PlanComptable::where(function ($query) use ($numero_formate, $intitule_formate) {
                    $query->where('numero_de_compte', $numero_formate)
                        ->orWhere('intitule', $intitule_formate);
                })
                ->exists();

            if ($exists) {
                return redirect()->back()->with('error', 'Ce numéro de compte ou cet intitulé existe déjà.');
            }

            $user = Auth::user();
            
            PlanComptable::create([
                'numero_de_compte' => $numero_formate,
                'intitule' => $intitule_formate,
                'adding_strategy' => 'manuel',
                'user_id' => $user->id,
                'company_id' => $user->company_id,
            ]);

            return redirect()->back()->with('success', 'Plan comptable ajouté avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de l\'ajout du plan comptable : ' . $e->getMessage());
        }
    }

    public function useDefault(Request $request)
    {
        if ($request->input('use_default') === 'true') {
            try {
                $user = Auth::user();

                $jsonPath = storage_path('app/sous_compte.json');

                if (!File::exists($jsonPath)) {
                    return redirect()->back()->with('error', 'Fichier de plan comptable introuvable.');
                }

                $data = json_decode(File::get($jsonPath), true);

                if (!is_array($data)) {
                    return redirect()->back()->with('error', 'Format du fichier JSON invalide.');
                }

                foreach ($data as $numero => $intitule) {
                    $existe = PlanComptable::where('numero_de_compte', $numero)
                        ->where('company_id', $user->company_id)
                        ->exists();

                    if (!$existe) {
                        PlanComptable::create([
                            'numero_de_compte' => $numero,
                            'intitule' => $intitule,
                            'adding_strategy' => 'auto',
                            'user_id' => $user->id,
                            'company_id' => $user->company_id,
                        ]);
                    }
                }

                return redirect()->back()->with('success', 'Plan comptable par défaut chargé avec succès.');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Erreur lors du chargement du plan par défaut : ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('error', 'Action non autorisée.');
    }

    public function update(Request $request, $id)
    {

        if (Auth::check() && Auth::user()->role !== 'admin') {
        // Renvoie une erreur 403 (Accès interdit)
        abort(403, 'Seul un administrateur est autorisé à modifier le plan comptable.');
    }
        try {
            $request->validate([
                'numero_de_compte' => 'required|string',
                'intitule' => 'required|string',
                'type_de_compte' => 'nullable|string|in:Bilan,Compte resultat',
                'poste' => 'nullable|string',
                'extrait_du_compte' => 'nullable|in:oui,non',
                'traitement_analytique' => 'nullable|in:oui,non',
            ]);

            $plan = PlanComptable::findOrFail($id);

            $numero = $request->input('numero_de_compte');
            if (strlen($numero) < 8) {
                $numero = str_pad($numero, 8, "0");
            }

            $intitule_formate = ucfirst(strtolower($request->intitule));

            $plan->update([
                'numero_de_compte' => $numero,
                'intitule' => $intitule_formate,
                'type_de_compte' => $request->type_de_compte,
                'poste' => $request->poste,
                'extrait_du_compte' => $request->extrait_du_compte === 'oui',
                'traitement_analytique' => $request->traitement_analytique === 'oui',
            ]);

            return redirect()->back()->with('success', 'Plan comptable mis à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la mise à jour du plan comptable : ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        // dd($id);
        try {
            $plan = PlanComptable::findOrFail($id);

            $utilise = EcritureComptable::where('plan_comptable_id', $id)->exists();

            if ($utilise) {
                return redirect()->back()->with('error', 'Ce plan comptable est utilisé dans des écritures comptables et ne peut pas être supprimé.');
            }

            $plan->delete();

            return redirect()->back()->with('success', 'Le plan comptable a été supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression du plan comptable : ' . $e->getMessage());
        }
    }
}
