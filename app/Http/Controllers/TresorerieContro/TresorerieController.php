<?php

namespace App\Http\Controllers\TresorerieContro;

use App\Http\Controllers\Controller;
use App\Models\tresoreries\Tresoreries;
use App\Models\PlanComptable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ManagesCompany;
use Carbon\Carbon;
use App\Models\EcritureComptable;

use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;


class TresorerieController extends Controller
{

    use ManagesCompany;
    // Affiche la liste des trésoreries

    /**
     * Méthode utilitaire pour récupérer les données de base (liste des journaux et comptes classe 5).
     */
   /**
     * Méthode utilitaire pour récupérer les données de base (liste des journaux et comptes classe 5).
     */
    private function getBaseTreasuryData()
    {
        $user = Auth::user();

        // 1. DÉTERMINATION DES IDs DE COMPAGNIE À VISUALISER
        $companyIdsToView = collect(); // Initialisation en Collection

        if ($user->role === 'admin') {
            // CORRECTION: Assurer que le résultat est une Collection, même si le trait retourne un tableau
            $managedIds = $this->getManagedCompanyIds();
            $companyIdsToView = collect($managedIds);

        } elseif ($user->role === 'super_admin') {
            $activeId = session('active_company_id');
            if ($activeId) {
                // S'assurer que c'est une Collection
                $companyIdsToView = collect([$activeId]);
            }
        } else {
            // S'assurer que c'est une Collection
            $companyIdsToView = collect([$user->company_id]);
        }

        // Si la liste des IDs est vide (maintenant nous savons que c'est une Collection)
        if ($companyIdsToView->isEmpty()) {
             $tresoreries = collect();
             $companyIdForPlanComptable = null;
        } else {
            // 2. BASE DE LA REQUÊTE : Filtrer par la ou les compagnies actives/liées
            // Utiliser toArray() est plus sûr pour whereIn, bien que non strictement nécessaire.
            $query = Tresoreries::whereIn('company_id', $companyIdsToView->toArray());

            // Nous prenons l'ID de la compagnie actuelle pour les requêtes PlanComptable
            $companyIdForPlanComptable = $user->company_id;

            // 3. FILTRAGE PAR RÔLE
            if ($user->role !== 'admin' && $user->role !== 'super_admin') {
                $query->where('user_id', $user->id);
            }

            $tresoreries = $query->get();
        }

        // Récupération des comptes de classe 5
        if (is_null($companyIdForPlanComptable)) {
             $comptesCinq = collect();
        } else {
             $comptesCinq = PlanComptable::where('company_id', $companyIdForPlanComptable)
                ->where('numero_de_compte', 'like', '5%')
                ->orderBy('numero_de_compte')
                ->get();
        }

        return compact('tresoreries', 'comptesCinq');
    }


// Mettez à jour votre méthode index() pour qu'elle utilise getBaseTreasuryData
public function index()
{
    // Utilise la méthode utilitaire pour récupérer les données de base
    $data = $this->getBaseTreasuryData();

    // Ajout des variables pour la vue Blade (pour éviter l'erreur "undefined variable")
    $data['cashFlowData'] = null;
    $data['cashFlowTotals'] = null;
    $data['reportGenerated'] = false; // Indique qu'aucun rapport n'est affiché

    return view('Tresor.journaltresorerie', $data);
}

public function generateCashFlowPlan(Request $request)
{
    $user = Auth::user();
    $companyId = ($user->role === 'super_admin') ? session('active_company_id') : $user->company_id;

    if (is_null($companyId)) {
        return back()->with('error', 'Veuillez sélectionner une compagnie active.');
    }

    // Récupérer les données calculées
    // CORRECTION APPORTÉE ICI : Ajout de l'objet $request
    list($cashFlowData, $totals) = $this->getCashFlowCalculation($companyId, $request);
    // --------------------------------------------------------------------------

    // Récupérer les données de base pour la vue (liste des journaux)
    $baseData = $this->getBaseTreasuryData();

    // Fusionner avec les données du rapport
    $reportData = [
        'cashFlowData' => $cashFlowData,
        'cashFlowTotals' => $totals,
        'reportGenerated' => true, // Indicateur pour afficher la section du rapport
        // Optionnel : ajouter les dates pour les afficher dans la vue
        'startDate' => $request->input('start_date'),
        'endDate' => $request->input('end_date'),
    ];

    // Retourner la vue unique avec toutes les données
    return view('Tresor.journaltresorerie', array_merge($baseData, $reportData));
}

//   public function index()
//     {
//         $user = Auth::user();


//         $companyIdsToView = collect();

//         if ($user->role === 'admin') {

//             $companyIdsToView = $this->getManagedCompanyIds();
//         } elseif ($user->role === 'super_admin') {

//             $activeId = session('active_company_id');
//             if ($activeId) {
//                 $companyIdsToView = [$activeId];
//             }
//         } else {

//             $companyIdsToView = [$user->company_id];
//         }

//         if (empty($companyIdsToView)) {
//              $tresoreries = collect();
//              $companyIdForPlanComptable = null;
//         } else {

//             $query = Tresoreries::whereIn('company_id', $companyIdsToView);
//             $companyIdForPlanComptable = $user->company_id;


//             if ($user->role !== 'admin' && $user->role !== 'super_admin') {
//                 $query->where('user_id', $user->id);
//             }

//             $tresoreries = $query->get();
//         }

//         if (is_null($companyIdForPlanComptable)) {
//              $comptesCinq = collect();
//         } else {
//              $comptesCinq = PlanComptable::where('company_id', $companyIdForPlanComptable)
//                 ->where('numero_de_compte', 'like', '5%')
//                 ->orderBy('numero_de_compte')
//                 ->get();
//         }

//         return view('Tresor.journaltresorerie', compact('tresoreries', 'comptesCinq'));
//     }
    // Affiche le formulaire pour créer une nouvelle trésorerie
    public function create()
    {
        $user = Auth::user();
        $companyId = ($user->role === 'super_admin') ? session('active_company_id') : $user->company_id;

        // S'assurer que le companyId est valide avant de requêter le plan comptable
        if (is_null($companyId)) {
             $comptesCinq = collect();
        } else {
             // CORRECTION : Ajouter le filtre pour les comptes de classe 5
            $comptesCinq = PlanComptable::where('company_id', $companyId)
                ->where('numero_de_compte', 'like', '5%')
                ->orderBy('numero_de_compte')
                ->get();
        }

        return view('Tresor.journaltresorerie', compact('comptesCinq'));
    }


    // Enregistre une nouvelle trésorerie dans la base de données
    public function store(Request $request)
    {
        // Validation des données
        $validatedData = $request->validate([
            'code_journal' => 'required|string|unique:tresorerie,code_journal', // Assurez-vous que le nom du tableau est 'tresoreries' et non 'tresorerie'
            'intitule' => 'required|string',
            'traitement_analytique' => 'nullable|in:oui,non',
            'compte_de_contrepartie' => 'required|exists:plan_comptables,numero_de_compte',
            'rapprochement_sur' => 'nullable|in:automatique,manuel',
        ]);

        $user = Auth::user();

        // Déterminer le company_id à utiliser pour l'enregistrement
        $companyId = ($user->role === 'super_admin') ? session('active_company_id') : $user->company_id;

        if (is_null($companyId)) {
            // Gérer l'erreur si le Super Admin n'a pas sélectionné de compagnie active
            return back()->with('error', 'Veuillez sélectionner une compagnie active avant de créer un journal.');
        }

        // Ajout automatique de user_id et company_id
        $validatedData['user_id'] = $user->id;
        $validatedData['company_id'] = $companyId;

        // Création de la trésorerie
        Tresoreries::create($validatedData);

        return redirect()->route('indextresorerie')->with('success', 'Trésorerie créée avec succès');
    }

    // Affiche les détails d'une trésorerie spécifique
    public function show($id)
    {
        $tresorerie = Tresoreries::findOrFail($id);
        return view('Tresor.journaltresorerie', compact('tresorerie'));
    }

    // Affiche le formulaire pour modifier une trésorerie existante
    public function edit($id)
    {
        $tresorerie = Tresoreries::findOrFail($id);
        $user = Auth::user();
        $companyId = ($user->role === 'super_admin') ? session('active_company_id') : $user->company_id;

        if (is_null($companyId)) {
             $comptesClasse5 = collect();
        } else {
             // Ajouter le filtre pour les comptes de classe 5
            $comptesClasse5 = PlanComptable::where('company_id', $companyId)
                ->where('numero_de_compte', 'like', '5%')
                ->orderBy('numero_de_compte')
                ->get();
        }

        return view('editresorerie', compact('tresorerie', 'comptesClasse5'));
    }

    // Met à jour une trésorerie existante
    public function update(Request $request, $id)
    {
        $tresorerie = Tresoreries::findOrFail($id);
        // Validation des données
        $validatedData = $request->validate([
            'code_journal' => 'required|string|unique:tresorerie,code_journal,' . $id . ',id', // Assurez-vous que le nom du tableau est 'tresoreries'
            'intitule' => 'required|string',
            'traitement_analytique' => 'nullable|in:oui,non',
            'compte_de_contrepartie' => 'required|exists:plan_comptables,numero_de_compte',
            'rapprochement_sur' => 'nullable|in:automatique,manuel',
        ]);

        // $user = Auth::user();
        // $companyId = ($user->role === 'super_admin') ? session('active_company_id') : $user->company_id;

        // if (is_null($companyId)) {
        //     return back()->with('error', 'Veuillez sélectionner une compagnie active pour effectuer la mise à jour.');
        // }

        // Ajout automatique de user_id et company_id
        //  $validatedData['user_id'] = $user->id;
        // $validatedData['company_id'] = $companyId;

        // Mise à jour de la trésorerie

        $tresorerie->update($validatedData);

        return redirect()->route('indextresorerie')->with('success', 'Trésorerie mise à jour avec succès');
    }

    // pour charger les données par défaut dans le journal de trésorerie
    public function loadDefaultTresorerie(Request $request)
    {
        // 1. Définir les données par défaut
        $defaultJournals = [
            // Assurez-vous que les codes comptables sont cohérents avec votre PlanComptable
            ['code_journal' => 'BQ', 'intitule' => 'Banque', 'compte_de_contrepartie' => '512000'],
            ['code_journal' => 'CS', 'intitule' => 'Caisse', 'compte_de_contrepartie' => '530000'],
        ];

        $user = Auth::user();
        $companyId = ($user->role === 'super_admin') ? session('active_company_id') : $user->company_id;

        if (is_null($companyId)) {
             return back()->with('error', 'Veuillez sélectionner une compagnie active pour charger les journaux par défaut.');
        }

        foreach ($defaultJournals as $journalData) {
            // Vérifier si un journal avec le même code existe déjà pour cette compagnie.
            $exists = Tresoreries::where('company_id', $companyId)
                                 ->where('code_journal', $journalData['code_journal'])
                                 ->exists();

            if (!$exists) {
                Tresoreries::create([
                    'company_id' => $companyId,
                    'user_id' => $user->id, // Il est bon d'associer la création à l'utilisateur
                    'code_journal' => $journalData['code_journal'],
                    'intitule' => $journalData['intitule'],
                    'traitement_analytique' => 'non', // Valeur par défaut si non spécifié
                    'compte_de_contrepartie' => $journalData['compte_de_contrepartie'],
                    'rapprochement_sur' => 'manuel', // Valeur par défaut si non spécifié
                ]);
            }
        }

        return redirect()->route('indextresorerie')->with('success', 'Les journaux de trésorerie par défaut ont été chargés avec succès.');
    }

    // Supprime une trésorerie
    public function destroy($id)
    {
        $user = Auth::user();
        // 🛑 AJOUT DE LA VÉRIFICATION D'AUTORISATION 🛑
    // Seuls les utilisateurs 'admin' ou 'super_admin' sont autorisés à supprimer.
    if ($user->role !== 'admin' && $user->role !== 'super_admin') {
        // Option 1 : Rediriger avec un message d'erreur clair
        return redirect()->route('indextresorerie')->with('error', 'Vous n\'êtes pas autorisé à supprimer une trésorerie.');
    }
        $tresorerie = Tresoreries::findOrFail($id);
        // Vous pourriez également ajouter une vérification si la trésorerie est liée à des écritures (comme dans votre CodeJournalController)
    // if ($tresorerieEstUtilisee) {
    //     return redirect()->route('indextresorerie')->with('error', 'Cette trésorerie est utilisée dans des transactions et ne peut pas être supprimée.');
    // }
        $tresorerie->delete();

        return redirect()->route('indextresorerie')->with('success', 'Trésorerie supprimée avec succès');
    }


public function exportCashFlowCsv(Request $request)
{
    // 1. Récupérer la logique de calcul des données (similaire à generateCashFlowPlan)
    $user = Auth::user();
    $companyId = ($user->role === 'super_admin') ? session('active_company_id') : $user->company_id;

    if (is_null($companyId)) {
        return back()->with('error', 'Veuillez sélectionner une compagnie active avant d\'exporter.');
    }

    // --- Répétez ou Extrayez la Logique de Calcul ici ---

    // Pour l'exemple, supposons que vous avez extrait la logique de calcul dans une méthode
    // pour obtenir les données formatées et les totaux
    list($cashFlowData, $totals) = $this->getCashFlowCalculation($companyId, $request);


    // ----------------------------------------------------

    // 2. Préparation du contenu CSV
    $headers = [
        'Compte Trésorerie', 'Solde Initial', 'Encaissements (Débit)', 'Décaissements (Crédit)', 'Solde Final'
    ];

    $rows = [];
    foreach ($cashFlowData as $data) {
        $rows[] = [
            $data['compte'],
            $data['solde_initial'], // Attention: ces données sont formatées avec ' F' et les espaces
            $data['encaissements'],
            $data['decaissements'],
            $data['solde_final'],
        ];
    }

    // Ajout de la ligne Total
    $rows[] = [
        'TOTAL',
        $totals['total_solde_initial'],
        $totals['total_encaissements'],
        $totals['total_decaissements'],
        $totals['total_solde_final'],
    ];

    // 3. Création du fichier CSV
    $filename = 'Plan_Tresorerie_' . now()->format('Ymd_His') . '.csv';

    $handle = fopen('php://temp', 'r+');
    fputcsv($handle, $headers, ';'); // Utilisation du point-virgule (convention française)

    foreach ($rows as $row) {
        // Enlèvement du ' F' pour garder un fichier propre
        $row = array_map(function($value) {
            return str_replace([' F', ' '], '', $value); // Nettoyer les données formatées
        }, $row);
        fputcsv($handle, $row, ';');
    }

    rewind($handle);
    $csv_content = stream_get_contents($handle);
    fclose($handle);

    // 4. Retourner la réponse téléchargeable
    return response($csv_content, 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ]);
}

private function getCashFlowCalculation($companyId, Request $request)
{


    // 1. Définition des Dates (Utilise Carbon)

    $startDate = $request->input('start_date')
    ? Carbon::parse($request->input('start_date'))->startOfDay()
    : Carbon::now()->startOfMonth()->startOfDay();

    // Date de fin du rapport : Dernier jour du mois en cours
    // Utilise la date fournie, ou la fin du mois courant si vide.
    $endDate = $request->input('end_date')
    ? Carbon::parse($request->input('end_date'))->endOfDay()
    : Carbon::now()->endOfMonth()->endOfDay();

    // debug (à retirer plus tard)
    //  dd($startDate->toDateString(), $endDate->toDateString());


    // 2. Récupérer TOUS les Comptes de Trésorerie (Classe 5) pour cette compagnie.
    $comptesTresorerie = PlanComptable::where('company_id', $companyId)
        ->where('numero_de_compte', 'like', '5%')
        ->orderBy('numero_de_compte')
        ->pluck('numero_de_compte','id')
        ->toArray();

    $cashFlowData = [];
    $totalSoldeInitial = 0;
    $totalEncaissements = 0;
    $totalDecaissements = 0;
    $totalSoldeFinal = 0;

    // Si des comptes sont trouvés, exécute la logique réelle
    if (!empty($comptesTresorerie)) {

        // 3. Calculer les flux pour chaque compte de trésorerie
        foreach ($comptesTresorerie as $compteId => $compteNumero) {

            // Calcul du Solde Initial (Débit - Crédit AVANT la date de début)
            $soldeInitialDebit = EcritureComptable::where('plan_comptable_id', $compteId)
                ->where('date', '<', $startDate)
                ->sum('debit');

            $soldeInitialCredit = EcritureComptable::where('plan_comptable_id', $compteId)
                ->where('date', '<', $startDate)
                ->sum('credit');

            $soldeInitial = $soldeInitialDebit - $soldeInitialCredit;


            // Calcul des Encaissements (Débits PENDANT la période de rapport)
            $encaissements = EcritureComptable::where('plan_comptable_id', $compteId)
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('debit');

            // Calcul des Décaissements (Crédits PENDANT la période de rapport)
            $decaissements = EcritureComptable::where('plan_comptable_id', $compteId)
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('credit');

            // Calcul du Solde Final
            $soldeFinal = $soldeInitial + $encaissements - $decaissements;

            $cashFlowData[] = [
                'compte' => $compteNumero,
                // Assurez-vous d'utiliser number_format pour le formatage
                'solde_initial' => number_format($soldeInitial, 2, ',', ' ') . ' F',
                'encaissements' => number_format($encaissements, 2, ',', ' ') . ' F',
                'decaissements' => number_format($decaissements, 2, ',', ' ') . ' F',
                'solde_final' => number_format($soldeFinal, 2, ',', ' ') . ' F',
            ];

            // Agrégation pour le Total
            $totalSoldeInitial += $soldeInitial;
            $totalEncaissements += $encaissements;
            $totalDecaissements += $decaissements;
            $totalSoldeFinal += $soldeFinal;
        }

    } else {
        // Gestion du cas où aucun compte Trésorerie n'est trouvé
        $cashFlowData[] = [
            'compte' => 'Aucun compte Trésorerie (Classe 5) trouvé',
            'solde_initial' => '0,00 F',
            'encaissements' => '0,00 F',
            'decaissements' => '0,00 F',
            'solde_final' => '0,00 F',
        ];
    }

    // Initialisation et formatage des totaux
    $totals = [
        'total_solde_initial' => number_format($totalSoldeInitial, 2, ',', ' ') . ' F',
        'total_encaissements' => number_format($totalEncaissements, 2, ',', ' ') . ' F',
        'total_decaissements' => number_format($totalDecaissements, 2, ',', ' ') . ' F',
        'total_solde_final' => number_format($totalSoldeFinal, 2, ',', ' ') . ' F',
    ];

    // Retourne les données de flux et les totaux
    return [$cashFlowData, $totals];
}





// Fichier : TresorerieController.php

public function previewCashFlowPdf(Request $request)
{
    ini_set('max_execution_time', 300);
    ini_set('memory_limit', '512M');

    $user = Auth::user();
    $companyId = ($user->role === 'super_admin') ? session('active_company_id') : $user->company_id;

    if (is_null($companyId)) {
        return response('Veuillez sélectionner une compagnie active.', 400);
    }

    list($cashFlowData, $totals) = $this->getCashFlowCalculation($companyId, $request);

    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');

    // 3. Charger la vue Blade pour le PDF
    $data = compact('cashFlowData', 'totals', 'startDate', 'endDate');

    // Décommenter et utiliser la logique PDF ci-dessous :
    try {
        // Assurez-vous d'utiliser la bonne vue (Tresor.plan_tresorerie_pdf semble correct)
        $pdf = Pdf::loadView('Tresor.cashflow_pdf', $data);


        $fileName = 'preview_tresorerie_' . time() . '.pdf';
        $directory = public_path('previews');
        $filePath = $directory . '/' . $fileName;

        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        $pdf->save($filePath);


        $url = asset('previews/' . $fileName);

        return response()->json([
            'success' => true,
            'url' => $url
        ]);

    } catch (\Exception $e) {

        // Loggez l'erreur pour la déboguer si elle survient après ce changement
        Log::error("Erreur lors de la génération du PDF (Trésorerie): " . $e->getMessage());


        return response()->json(['error' => 'Erreur serveur lors de la génération du PDF: ' . $e->getMessage()], 500);
    }
}
public function generatePdf(Request $request)
{
    ini_set('max_execution_time', 300);
    ini_set('memory_limit', '512M');

    $user = Auth::user();
    $companyId = ($user->role === 'super_admin') ? session('active_company_id') : $user->company_id;

    if (is_null($companyId)) {
        // Retourne à la page précédente avec une erreur si pas de compagnie
        return back()->with('error', 'Veuillez sélectionner une compagnie active.');
    }

    try {
        // 1. Récupérer les données de calcul via la méthode utilitaire
        // Elle utilise la Request pour les dates
        list($cashFlowData, $totals) = $this->getCashFlowCalculation($companyId, $request);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // 2. Charger la vue Blade avec les données
        $data = compact('cashFlowData', 'totals', 'startDate', 'endDate');
        $pdf = Pdf::loadView('Tresor.cashflow_pdf', $data); // Assurez-vous que la vue 'cashflow_pdf' existe

        // 3. Retourner la réponse en streaming
        $fileName = 'plan_tresorerie_' . Carbon::now()->format('Ymd') . '.pdf';

        // La méthode stream() force le navigateur à afficher le contenu PDF dans l'onglet actuel
        return $pdf->stream($fileName);

    } catch (\Exception $e) {
        Log::error("Erreur lors du streaming du PDF (Trésorerie): " . $e->getMessage());
        return back()->with('error', 'Erreur lors de la génération du PDF. Consultez les logs pour plus de détails.');
    }
}

// FIN de la méthode - suppression du bloc try/catch avec dd()
}
