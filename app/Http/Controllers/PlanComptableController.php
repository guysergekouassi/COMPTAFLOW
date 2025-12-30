<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlanComptable;
use App\Models\EcritureComptable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Traits\ManagesCompany;
use Yajra\DataTables\Facades\DataTables;

class PlanComptableController extends Controller
{
    use ManagesCompany;
  public function index()
{
    try {
        $user = Auth::user();
        
        // 1. Récupérer l'ID de la société active (gestion du switch admin)
        $companyId = session('current_company_id', $user->company_id);

        // 2. Récupérer TOUS les plans de cette société (auto + manuel)
        // On utilise withoutGlobalScopes() si vous avez un scope qui bloque l'admin
        $query = PlanComptable::where('company_id', $companyId);

        // $plansComptables = (clone $query)
        //     ->orderByRaw("LPAD(numero_de_compte, 20, '0')")
        //     ->get();

        $plansComptables = PlanComptable::where('company_id', $companyId)
    ->orderByRaw("LPAD(numero_de_compte, 20, '0')")
    ->get();

        // 3. CALCUL DES STATISTIQUES RÉELLES
        // Nombre total
        $totalPlans = $plansComptables->count();
        
        // Nombre de plans créés MANUELLEMENT (votre indicateur vert)
        $plansByUser = $plansComptables->where('adding_strategy', 'manuel')->count();
        
        // Nombre de plans créés AUTOMATIQUEMENT (Système)
        $plansSys = $plansComptables->where('adding_strategy', 'auto')->count();
        
        $hasAutoStrategy = $plansSys > 0;

        return view('plan_comptable', [
            'plansComptables' => $plansComptables,
            'totalPlans' => $totalPlans,
            'plansByUser' => $plansByUser, // Sera maintenant dynamique (ex: 2)
            'plansSys' => $plansSys,
            'hasAutoStrategy' => $hasAutoStrategy,
            'isDefaultView' => true 
        ]);

    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Erreur : ' . $e->getMessage());
    }
}

    /**
     * Détermine le type de compte en fonction du numéro
     */
    private function determinerTypeCompte($numero) {
        $premierChiffre = substr($numero, 0, 1);
        
        switch($premierChiffre) {
            case '1':
            case '2':
            case '3':
            case '4':
                return 'actif';
            case '5':
            case '6':
                return 'passif';
            case '7':
                return 'produit';
            case '8':
                return 'charge';
            default:
                return 'divers';
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

        // 1. RÉCUPÉRER L'ID DE LA SOCIÉTÉ EN SESSION (Switch)
        $companyId = session('current_company_id', Auth::user()->company_id);

        // 2. Vérifier l'existence au sein de CETTE société uniquement
        $exists = PlanComptable::where('company_id', $companyId)
            ->where(function ($query) use ($numero_formate, $intitule_formate) {
                $query->where('numero_de_compte', $numero_formate)
                      ->orWhere('intitule', $intitule_formate);
            })
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Ce numéro de compte ou cet intitulé existe déjà dans cette comptabilité.');
        }

        $user = Auth::user();
        
        // 3. ENREGISTRER AVEC LE BON company_id
        PlanComptable::create([
            'numero_de_compte' => $numero_formate,
            'intitule' => $intitule_formate,
            'adding_strategy' => 'manuel',
            'user_id' => $user->id,
            'company_id' => $companyId, // Utilise l'ID switché
            'type_de_compte' => $this->determinerTypeCompte($numero_formate), // Optionnel mais conseillé
        ]);

        return redirect()->back()->with('success', 'Plan comptable ajouté avec succès à la comptabilité actuelle.');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Erreur lors de l\'ajout : ' . $e->getMessage());
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

    /**
     * Retourne les données pour DataTables
     */
    public function datatable()
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        
        $query = PlanComptable::where('company_id', $companyId)
            ->select([
                'id',
                'numero_de_compte',
                'intitule',
                'type_de_compte',
                'created_at',
                'adding_strategy'
            ]);
        
        // Gestion du filtrage
        $filterType = request()->get('filter_type');
        if ($filterType === 'user') {
            $query->where('adding_strategy', 'manuel');
        } elseif ($filterType === 'system') {
            $query->where('adding_strategy', 'auto');
        }
            
        return DataTables::of($query)
            ->addColumn('actions', function($plan) {
                return view('components.actions-plan-comptable', compact('plan'))->render();
            })
            ->editColumn('created_at', function($plan) {
                return $plan->created_at ? $plan->created_at->format('Y-m-d H:i:s') : null;
            })
            ->rawColumns(['actions'])
            ->make(true);
    }
}
