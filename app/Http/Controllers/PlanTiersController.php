<?php

namespace App\Http\Controllers;

use App\Models\PlanTiers;
use App\Models\PlanComptable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class PlanTiersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        try {
            // Debug: Afficher les informations de l'utilisateur et de la société
            Log::info('User ID: ' . $user->id);
            Log::info('User Company ID: ' . $user->company_id);
            Log::info('Session Company ID: ' . session('current_company_id'));

            $comptesGeneraux = PlanComptable::where('numero_de_compte', 'LIKE', '4%')
                ->orderByRaw("LPAD(numero_de_compte, 20, '0')")
                ->get();

            // Récupère les plans tiers avec leurs comptes associés, triés par numero_de_tiers
            $currentCompanyId = session('current_company_id', $user->company_id);
            $tiers = PlanTiers::with('compte')
                ->where('company_id', $currentCompanyId)
                ->orderByDesc('created_at')
                ->get();
                
            // Debug: Afficher le nombre de tiers trouvés
            Log::info('Nombre de tiers trouvés: ' . $tiers->count());
            Log::info('Tiers: ' . $tiers->toJson());

            // Statistiques
            $totalPlanTiers = $tiers->count();
            $userCreatedTiers = $tiers->where('user_id', $user->id)->count();

            // Compter le nombre de plan tiers par type
            $tiersParType = $tiers->groupBy('type_de_tiers')->map(fn($group) => $group->count());

            $correspondances = [];

            $typesPrefixes = [
                'Fournisseur' => '40',
                'Client' => '41',
                'Personnel' => '42',
                'CNPS' => '43',
                'Impots' => '44',
                'Associé' => '45',
            ];

            $autres = []; // Pour Divers Tiers

            foreach ($typesPrefixes as $type => $prefix) {
                $correspondances[$type] = $comptesGeneraux->filter(function ($compte) use ($prefix) {
                    return str_starts_with($compte->numero_de_compte, $prefix);
                })->map(function ($compte) {
                    return [
                        'id' => $compte->id,
                        'numero' => $compte->numero_de_compte,
                        'intitule' => $compte->intitule,
                    ];
                })->values()->toArray();
            }

            // Gérer "Divers Tiers" = tous les comptes ne correspondant à aucun des types ci-dessus
            $correspondances['Divers Tiers'] = $comptesGeneraux->filter(function ($compte) use ($typesPrefixes) {
                foreach ($typesPrefixes as $prefix) {
                    if (str_starts_with($compte->numero_de_compte, $prefix)) {
                        return false;
                    }
                }
                return true;
            })->map(function ($compte) {
                return [
                    'id' => $compte->id,
                    'numero' => $compte->numero_de_compte,
                    'intitule' => $compte->intitule,
                ];
            })->values()->toArray();



            return view('plan_tiers', compact(
                'comptesGeneraux',
                'tiers',
                'totalPlanTiers',
                'userCreatedTiers',
                'correspondances',
                'tiersParType'
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors du chargement des plans tiers : ' . $e->getMessage());
        }
    }


    public function getDernierNumero($racine)
{
    try {
        $longueurTotal = 8;

        // Récupérer le dernier tiers existant (filtré auto)
        $dernierTiers = PlanTiers::where('numero_de_tiers', 'like', $racine . '%')
            ->orderBy('numero_de_tiers', 'desc')
            ->first();

        if ($dernierTiers) {
            // Calculer le suffixe à incrémenter
            $suffixe = (int) substr($dernierTiers->numero_de_tiers, strlen($racine));
            $suffixe++;
            $nouveauNumero = $racine . str_pad($suffixe, $longueurTotal - strlen($racine), '0', STR_PAD_LEFT);
        } else {
            // Aucun tiers existant, premier suffixe = 1
            $nouveauNumero = $racine . str_pad(1, $longueurTotal - strlen($racine), '0', STR_PAD_LEFT);
        }

        return response()->json(['numero' => $nouveauNumero]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erreur lors de la récupération du dernier numéro : ' . $e->getMessage()], 500);
    }
}


    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            $currentCompanyId = session('current_company_id', $user->company_id);
            
            Log::info('Création d\'un nouveau plan tiers - User ID: ' . $user->id . ', Company ID: ' . $currentCompanyId);
            
            $request->validate([
                'numero_de_tiers' => 'required|string',
                'compte_general' => 'required|exists:plan_comptables,id',
                'intitule' => 'required|string',
                'type_de_tiers' => 'required',
            ]);

            // Vérifier si le numéro de tiers existe déjà pour cette société
            $numeroExiste = PlanTiers::where('company_id', $currentCompanyId)
                ->where('numero_de_tiers', $request->numero_de_tiers)
                ->exists();

            if ($numeroExiste) {
                Log::warning('Tentative de création d\'un numéro de tiers existant: ' . $request->numero_de_tiers . ' pour la société: ' . $currentCompanyId);
                return redirect()->back()->with('error', 'Ce numéro de tiers existe déjà pour cette société.');
            }

            $intitule_formate = ucfirst(strtolower($request->intitule));

            Log::info('Création du plan tiers avec les données:', [
                'numero_de_tiers' => $request->numero_de_tiers,
                'compte_general' => $request->compte_general,
                'intitule' => $intitule_formate,
                'type_de_tiers' => $request->type_de_tiers,
                'user_id' => $user->id,
                'company_id' => $currentCompanyId
            ]);

            $planTiers = PlanTiers::create([
                'numero_de_tiers' => $request->numero_de_tiers,
                'compte_general' => $request->compte_general,
                'intitule' => $intitule_formate,
                'type_de_tiers' => $request->type_de_tiers,
                'user_id' => $user->id,
                'company_id' => $currentCompanyId
            ]);

            Log::info('Plan tiers créé avec succès - ID: ' . $planTiers->id);

            // Vérifier si c'est une requête AJAX
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Plan Tiers créé avec succès',
                    'id' => $planTiers->id,
                    'numero_de_tiers' => $planTiers->numero_de_tiers,
                    'intitule' => $planTiers->intitule
                ]);
            }

            return redirect()->back()->with('success', 'Plan Tiers créé avec succès')->with('reload', true);
        } catch (\Exception $e) {
            logger()->error('Erreur dans PlanTiersController@store: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            // Vérifier si c'est une requête AJAX
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Erreur lors de la création du plan tiers: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Erreur lors de la création du plan tiers: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'numero_de_tiers' => 'required|string|max:255',
                'intitule' => 'required|string|max:255',
                'type_de_tiers' => 'required|string',
                'compte_general' => 'required|exists:plan_comptables,id'
            ]);

            $tiers = PlanTiers::findOrFail($id);
            $tiers->numero_de_tiers = $request->numero_de_tiers;
            $tiers->intitule = $request->intitule;
            $tiers->type_de_tiers = $request->type_de_tiers;
            $tiers->compte_general = $request->compte_general;
            $tiers->save();

            return redirect()->back()->with('success', 'Plan de tiers mis à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la mise à jour du plan tiers : ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $user = Auth::user();
            $currentCompanyId = session('current_company_id', $user->company_id);
            
            $tier = PlanTiers::with(['compte', 'ecritures' => function($query) use ($currentCompanyId) {
                $query->where('company_id', $currentCompanyId)
                      ->orderBy('date', 'desc')
                      ->take(10);
            }])
            ->where('company_id', $currentCompanyId)
            ->findOrFail($id);
            
            // Statistiques pour ce tiers
            $stats = [
                'total_ecritures' => \App\Models\EcritureComptable::where('plan_tiers_id', $id)
                    ->where('company_id', $currentCompanyId)
                    ->count(),
                'total_debit' => \App\Models\EcritureComptable::where('plan_tiers_id', $id)
                    ->where('company_id', $currentCompanyId)
                    ->sum('debit'),
                'total_credit' => \App\Models\EcritureComptable::where('plan_tiers_id', $id)
                    ->where('company_id', $currentCompanyId)
                    ->sum('credit'),
                'solde' => 0
            ];
            
            $stats['solde'] = $stats['total_debit'] - $stats['total_credit'];
            
            return view('plan_tiers_show', compact('tier', 'stats'));
            
        } catch (\Exception $e) {
            return redirect()->route('plan_tiers')->with('error', 'Tiers non trouvé : ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $tiers = PlanTiers::findOrFail($id);

            $utilise = \App\Models\EcritureComptable::where('plan_tiers_id', $id)->exists();

            if ($utilise) {
                return redirect()->back()->with('error', 'Ce plan tiers est utilisé dans des écritures comptables et ne peut pas être supprimé.');
            }

            $tiers->delete();

            return redirect()->back()->with('success', 'Plan de tiers supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression du plan tiers : ' . $e->getMessage());
        }
    }
}
