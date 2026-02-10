<?php

namespace App\Http\Controllers\Compte;
use App\Http\Controllers\Controller;
use App\Models\tresoreries\Tresoreries;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use App\Models\PlanComptable;
use App\Models\ExerciceComptable;
// Assurez-vous que ces modèles existent pour les KPI
use App\Models\CompteTresorerie;
use App\Models\MouvementTresorerie;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\EcritureComptable;

use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Traits\HandlesTreasuryPosts;

class PosteTresorController extends Controller
{
    use HandlesTreasuryPosts;
// Fichier : PosteTresorController.php (Méthode index() mise à jour)

    public function index(){
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        // 1. Démarrez la requête de base pour les comptes de classe 5
        $query = PlanComptable::whereRaw("CAST(numero_de_compte AS CHAR) LIKE '5%'")
            ->where('company_id', $companyId)
            ->orderBy('numero_de_compte');

        // 2. Exécuter la requête pour obtenir les comptes de classe 5 de la compagnie
        $comptes5 = $query
            ->groupBy('numero_de_compte', 'intitule')
            ->selectRaw('MIN(id) as id, numero_de_compte, intitule')
            ->orderBy('numero_de_compte')
            ->get();

        // 3. Récupération des postes de trésorerie de la compagnie
        $comptes = CompteTresorerie::where('company_id', $companyId)
            ->with('category')
            ->get();
        $postesTresorerie = $comptes;

        // 4. Récupération des catégories prédéfinies
        $categories = \App\Models\TreasuryCategory::where('company_id', $companyId)
            ->whereIn('name', [
                'I. Flux de trésorerie des activités opérationnelles',
                'II. Flux de trésorerie des activités d\'investissement',
                'III. Flux de trésorerie des activités de financement',
            ])
            ->orderBy('name')
            ->get();

        // 5. Passer toutes les listes à la vue
        return view('Poste.posteTresor' , compact('comptes', 'postesTresorerie', 'comptes5', 'categories'));
    }









   public function create()
    {
        $user = Auth::user();
        $companyId = ($user->role === 'super_admin') ? session('active_company_id') : $user->company_id;

        // Logique corrigée
        $query = PlanComptable::where('numero_de_compte', 'like', '5%')
            ->orderBy('numero_de_compte');

        if (!is_null($companyId)) {
            $query->where('company_id', $companyId);
        }

        $comptesComptablesClasse5 = $query->get(['id', 'intitule', 'numero_de_compte']);

        // Cette route n'est plus utilisée pour afficher le formulaire si tout est géré par la modal index.
        return view('Poste.createPoste', compact('comptesComptablesClasse5'));
    }




   public function show(CompteTresorerie $compte)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $comptes = CompteTresorerie::where('company_id', $companyId)
            ->with('category')
            ->get();
        $postesTresorerie = $comptes;
        
        // S'assurer que le compte appartient à la compagnie
        if ($compte->company_id != $companyId) {
            return redirect()->route('postetresorerie.index')->with('error', 'Accès non autorisé.');
        }

        $mouvements = $compte->mouvements()->orderBy('date_mouvement', 'desc')->paginate(20);

        // Récupération des catégories prédéfinies
        $categories = \App\Models\TreasuryCategory::where('company_id', $companyId)
            ->whereIn('name', [
                'I. Flux de trésorerie des activités opérationnelles',
                'II. Flux de trésorerie des activités d\'investissement',
                'III. Flux de trésorerie des activités de financement',
            ])
            ->orderBy('name')
            ->get();

        return view('Poste.posteTresor', compact('comptes','compte', 'mouvements', 'postesTresorerie', 'categories'));
    }










    public function storeMouvement(Request $request)
    {
        // 1. Validation des données
        $request->validate([
            'compte_id' => 'required|exists:compte_tresoreries,id',
            'date_mouvement' => 'required|date',
            'libelle' => 'required|string|max:255',
            'type_mouvement' => 'required|in:encaissement,decaissement',
            'montant' => 'required|numeric|min:0.01',
            'reference_piece' => 'nullable|string|max:100',
        ]);

        // 2. Préparation des montants Débit/Crédit
        $montant = $request->input('montant');
        $isEncaissement = $request->input('type_mouvement') === 'encaissement';

        $debit = $isEncaissement ? null : $montant;
        $credit = $isEncaissement ? $montant : null;

        // 3. Création du Mouvement de Trésorerie
        $mouvement = MouvementTresorerie::create([
            'compte_tresorerie_id' => $request->input('compte_id'),
            'date_mouvement' => $request->input('date_mouvement'),
            'libelle' => $request->input('libelle'),
            'reference_piece' => $request->input('reference_piece'),
            'montant_debit' => $debit,
            'montant_credit' => $credit,
        ]);

        // 4. Mise à jour du Solde Actuel du Compte
        $compte = CompteTresorerie::find($request->input('compte_id'));

        if ($isEncaissement) {
            $compte->solde_actuel += $montant;
        } else {
            $compte->solde_actuel -= $montant;
        }

        $compte->save();

        return redirect()->route('postetresorerie.index')
                         ->with('success', 'Mouvement de trésorerie enregistré avec succès.');
    }










    // Méthode pour l'enregistrement d'un NOUVEAU POSTE de trésorerie (Route: postetresorerie.store_poste)
    public function storeCompteTresorerie(Request $request)
    {
        if (!auth()->user()->hasPermission('admin.config.tresorerie_posts')) {
            abort(403);
        }

        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:treasury_categories,id',
            'syscohada_line_id' => 'nullable|string|max:50',
        ]);

        // Vérification d'unicité par compagnie
        $exists = CompteTresorerie::where('company_id', $companyId)
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', "Le poste '{$validated['name']}' existe déjà pour cette entreprise.");
        }

        $compte = CompteTresorerie::create([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'syscohada_line_id' => $validated['syscohada_line_id'] ?? null,
            'company_id' => $companyId,
            'solde_initial' => 0,
            'solde_actuel' => 0,
        ]);

        return redirect()->route('postetresorerie.index')
                         ->with('success', 'Poste de trésorerie créé avec succès.');
    }

    // Méthode rapide pour l'enregistrement via AJAX (In-Place)
    public function storeQuickAJAX(Request $request)
    {
        if (!auth()->user()->hasPermission('admin.config.tresorerie_posts')) {
            return response()->json(['success' => false, 'error' => 'Permission refusée'], 403);
        }

        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:treasury_categories,id',
            'ecriture_id' => 'nullable|exists:ecriture_comptables,id',
            'syscohada_line_id' => 'nullable|string|max:50',
        ]);

        // Vérification d'unicité par compagnie
        $existingPost = CompteTresorerie::where('company_id', $companyId)
            ->where('name', $validated['name'])
            ->first();

        if ($existingPost) {
            $compte = $existingPost;
            // Optionnel : Mettre à jour le syscohada_line_id si fourni et différent ? 
            // Pour l'instant, on suppose que le quick edit sert aussi à mettre à jour.
            if (isset($validated['syscohada_line_id'])) {
                $compte->update(['syscohada_line_id' => $validated['syscohada_line_id']]);
            }
        } else {
            $compte = CompteTresorerie::create([
                'name' => $validated['name'],
                'category_id' => $validated['category_id'],
                'syscohada_line_id' => $validated['syscohada_line_id'] ?? null,
                'company_id' => $companyId,
                'solde_initial' => 0,
                'solde_actuel' => 0,
            ]);
        }

        // Si ecriture_id est fourni, on lie l'écriture au poste
        if (!empty($validated['ecriture_id'])) {
            $ecriture = EcritureComptable::find($validated['ecriture_id']);
            if ($ecriture && $ecriture->company_id == $companyId) {
                $ecriture->update(['poste_tresorerie_id' => $compte->id]);
            }
        }

        return response()->json([
            'success' => true,
            'id' => $compte->id,
            'name' => $compte->id == ($existingPost->id ?? null) ? $compte->name : $compte->name,
            'category_name' => $compte->category->name ?? '',
            'category_id' => $compte->category_id,
            'syscohada_line_id' => $compte->syscohada_line_id,
            'message' => 'Poste de trésorerie enregistré avec succès'
        ]);
    }

    public function update(Request $request, CompteTresorerie $compte)
    {
        if (!auth()->user()->hasPermission('admin.config.tresorerie_posts')) {
            abort(403);
        }

        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        if ($compte->company_id != $companyId) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:treasury_categories,id',
            'syscohada_line_id' => 'nullable|string|max:50',
        ]);

        // Vérification d'unicité par compagnie
        $exists = CompteTresorerie::where('company_id', $companyId)
            ->where('name', $validated['name'])
            ->where('id', '!=', $compte->id)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', "Un autre poste de trésorerie porte déjà le nom '{$validated['name']}'.");
        }

        $compte->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'syscohada_line_id' => $validated['syscohada_line_id'] ?? null,
        ]);

        return redirect()->route('postetresorerie.index')
                         ->with('success', 'Poste de trésorerie "' . $compte->name . '" mis à jour avec succès.');
    }

    /**
     * Répare rétroactivement les liens manquants pour les écritures de classe 5
     */
    public function repairLinks(Request $request)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        
        $ecritures = EcritureComptable::where('company_id', $companyId)
            ->whereHas('planComptable', function($q) {
                $q->where('numero_de_compte', 'like', '5%');
            })
            ->whereNull('poste_tresorerie_id')
            ->get();
            
        $fixedCount = 0;
        foreach ($ecritures as $ecriture) {
            $posteId = $this->resolveTreasuryPost($companyId, $ecriture->plan_comptable_id);
            if ($posteId) {
                $ecriture->update(['poste_tresorerie_id' => $posteId]);
                $fixedCount++;
            }
        }
        
        return redirect()->back()->with('success', "$fixedCount écritures ont été liées à un poste de trésorerie.");
    }

    public function deleteCompteTresorerie($id)
    {
        try {
            $user = Auth::user();
            $companyId = session('current_company_id', $user->company_id);
            $poste = CompteTresorerie::where('company_id', $companyId)->findOrFail($id);

            // Vérifier s'il y a des mouvements ou des écritures liés
            if ($poste->mouvements()->count() > 0 || $poste->ecritures()->count() > 0) {
                return redirect()->back()->with('error', 'Impossible de supprimer ce poste car il contient des mouvements ou des écritures comptables.');
            }

            $poste->delete();
            return redirect()->route('postetresorerie.index')->with('success', 'Poste de trésorerie supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }



//generation de fichier
public function exportCashFlowCsv(Request $request)
{
    // 1. Récupérer les informations de la compagnie et de l'utilisateur
    $user = Auth::user();
    $companyId = ($user->role === 'super_admin') ? session('active_company_id') : $user->company_id;

    if (is_null($companyId)) {
        return back()->with('error', 'Veuillez sélectionner une compagnie active avant d\'exporter.');
    }

    // Récupérer les dates de la requête ou définir une période par défaut
    // Assurez-vous que ces paramètres correspondent à ceux utilisés pour générer le PDF/HTML
    $startDate = $request->input('start_date', Carbon::now()->startOfYear()->format('Y-m-d'));
    $endDate = $request->input('end_date', Carbon::now()->endOfYear()->format('Y-m-d'));

    // 2. Appel de la logique de calcul des données de flux de trésorerie mensuel
    // (Cette méthode est supposée retourner les données structurées: periods, incomes, etc.)
    $data = $this->getMonthlyCashFlowData($companyId, $startDate, $endDate);

    $periods = $data['periods'];

    // 3. Préparation des En-têtes du CSV
    // Correspond à l'en-tête du <thead> du modèle Blade
    $headers = array_merge(
        ['Flux de Trésorerie'],
        $periods,
        ['Total Global']
    );

    $rows = [];

    // Fonction utilitaire pour ajouter une ligne de données (totaux)
    $formatRow = function($name, $dataArray, $total) use (&$rows) {
        $row = [$name];
        foreach ($dataArray as $value) {
            $row[] = $value;
        }
        $row[] = $total;
        $rows[] = $row;
    };

    // Fonction utilitaire pour extraire les lignes de détail des flux
    $extractFlowRows = function($flowArray) use (&$rows) {
        foreach ($flowArray as $flow) {
            $row = [$flow['name']];
            foreach ($flow['data'] as $value) {
                $row[] = $value;
            }
            $row[] = $flow['total'];
            $rows[] = $row;
        }
    };

    // Fonction utilitaire pour ajouter une ligne de titre/séparation (simule le <td> colspan du HTML)
    $addTitleRow = function($title) use (&$rows, $periods) {
        // La colonne 'Flux de Trésorerie' contient le titre, les autres sont vides
        $row = [$title];
        // Ajouter une cellule vide pour chaque période + la colonne Total Global
        for($i=0; $i <= count($periods); $i++) {
            $row[] = '';
        }
        $rows[] = $row;
    };

    // 4. Construction des Lignes du CSV (calquée exactement sur la structure du Blade)

    // SECTION 1 (Titre H2 - .group-header)
    $addTitleRow('1. Flux de trésorerie des activités opérationnelles');

    // Lignes de DÉTAIL des Encaissements (@foreach($incomes))
    $extractFlowRows($data['incomes']);

    // Ligne Total des Encaissements (@tr.total-line)
    $formatRow(
        'Total des encaissements',
        $data['totalEncaissementsByPeriod'],
        $data['grandTotalEncaissements']
    );

    // Décaissements - Ligne de Titre (Simule la ligne <tr class="group-header"> Décaissements)
    $addTitleRow('Décaissements');

    // GROUPE : Dépenses de production (Simule la ligne <tr class="sub-group-header">)
    $rows[] = ['Dépenses de production'];
    $extractFlowRows($data['productionExpenses']);

    // GROUPE : Autres achats (Simule la ligne <tr class="sub-group-header">)
    $rows[] = ['Autres achats'];
    $extractFlowRows($data['otherExpenses']);

    // Ligne Total des Décaissements (@tr.total-line)
    $formatRow(
        'Total des décaissements',
        $data['totalDecaissementsByPeriod'],
        $data['grandTotalDecaissements']
    );

    // SOLDE NET DE TRÉSORERIE D'EXPLOITATION (@tr.grand-total-line)
    $formatRow(
        'Solde Net des Opérations (Encaissements - Décaissements)',
        $data['netCashFlowByPeriod'],
        $data['grandNetCashFlow']
    );

    // SECTION 2 (Titre H2 - .group-header)
    $addTitleRow('2. Flux de trésorerie des activités D\'INVESTISSEMENT');
    $rows[] = ['Données à implémenter pour l\'investissement...'];

    // SECTION 3 (Titre H2 - .group-header)
    $addTitleRow('3. Flux de trésorerie des activités FINANCEMENT');
    $rows[] = ['Données à implémenter pour le financement...'];


    // 5. Création du fichier CSV
    $filename = 'Flux_Tresorerie_Mensuel_' . now()->format('Ymd_His') . '.csv';

    $handle = fopen('php://temp', 'r+');
    fputcsv($handle, $headers, ';'); // Utilisation du point-virgule (convention française)

    foreach ($rows as $row) {
        // Nettoyage des données formatées par getMonthlyCashFlowData (ex: "1 000,00" -> "1000.00")
        $cleanedRow = array_map(function($value) {
            // Remplacer les espaces (séparateurs de milliers) et le tiret '—' par rien
            $cleaned = str_replace([' ', '—'], ['', ''], $value);

            // Si la valeur est un nombre décimal en format FR (avec virgule), la convertir en format US (avec point)
            if (strpos($cleaned, ',') !== false && is_numeric(str_replace(',', '.', $cleaned))) {
                return str_replace(',', '.', $cleaned);
            }

            // Retourner les autres valeurs (descriptions, titres vides)
            return $cleaned;
        }, $row);

        fputcsv($handle, $cleanedRow, ';');
    }

    rewind($handle);
    $csv_content = stream_get_contents($handle);
    fclose($handle);

    // 6. Retourner la réponse téléchargeable
    return response($csv_content, 200, [
        'Content-Type' => 'text/csv; charset=UTF-8',
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
            return back()->with('error', 'Veuillez sélectionner une compagnie active.');
        }

        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            // 1. Appel de la nouvelle fonction pour obtenir les données au format mensuel détaillé
            $data = $this->getMonthlyCashFlowData($companyId, $startDate, $endDate);

            // 2. Charger la vue Blade avec toutes les données
            // La variable $data contient maintenant TOUTES les variables nécessaires.
            $pdf = Pdf::loadView('Tresor.cashflow_pdf', $data);

            // 3. Retourner la réponse en streaming
            $fileName = 'plan_tresorerie_' . Carbon::now()->format('Ymd') . '.pdf';

            return $pdf->stream($fileName);

        } catch (\Exception $e) {
            Log::error("Erreur lors du streaming du PDF (Trésorerie): " . $e->getMessage());
            return back()->with('error', 'Erreur lors de la génération du PDF: ' . $e->getMessage());
        }
    }



private function getMonthlyCashFlowData($companyId, $startDate, $endDate)
{
    // ... (Étape 1 : Génération des Périodes, PériodMap, NumPeriods)

    $start = Carbon::parse($startDate)->startOfDay();
    $end = Carbon::parse($endDate)->endOfDay();
    $periods = [];
    $periodMap = [];
    $current = $start->clone()->startOfMonth();
    $index = 0;
    while ($current->lte($end)) {
        $periodName = $current->isoFormat('MMM-YY');
        $periods[] = $periodName;
        $periodMap[$periodName] = $index++;
        $current->addMonth();
    }
    $numPeriods = count($periods);

    // 2. INTERROGER LES DONNÉES BRUTES ET LES POSTES

    // Récupérer les noms et types des postes de trésorerie
    $tresoreriePostes = CompteTresorerie::select('id', 'name', 'type')
        ->where('company_id', $companyId)
        ->get()
        ->keyBy('id');

    // Récupérer TOUTES les écritures concernées (pas d'agrégation SQL ici)
    $rawEntries = EcritureComptable::where('company_id', $companyId)
        ->whereNotNull('compte_tresorerie_id')
        ->whereBetween('date', [$start, $end])
        ->get();

    // 3. TRAITER ET STRUCTURER LES DONNÉES

    // ... (Initialisation des groupes $operationalFlow, $investmentFlow, $financingFlow)
    $operationalFlow = ['incomes' => [], 'expenses' => []];
    $investmentFlow = ['incomes' => [], 'expenses' => []];
    $financingFlow = ['incomes' => [], 'expenses' => []];

    // ... (Initialisation des totaux généraux)
    $totalEncaissementsByPeriod = array_fill(0, $numPeriods, 0.0);
    $totalDecaissementsByPeriod = array_fill(0, $numPeriods, 0.0);
    $grandTotalEncaissements = 0.0;
    $grandTotalDecaissements = 0.0;

    // MAPPING pour diriger les postes vers les variables correctes
    $flowMap = [
        'Flux Des Activités Operationnelles' => &$operationalFlow,
        'Flux Des Activités Investissement'  => &$investmentFlow,
        'Flux Des Activités de Financement'  => &$financingFlow,
    ];

    $groupedByFluxAndCompte = $rawEntries->groupBy(['type_flux', 'compte_tresorerie_id']);

    foreach (['encaissement', 'decaissement'] as $fluxType) {
        $isIncome = ($fluxType === 'encaissement');
        $entriesByPoste = $groupedByFluxAndCompte->get($fluxType) ?? collect();

        foreach ($entriesByPoste as $compteId => $entries) {
            $poste = $tresoreriePostes->get($compteId);
            if (!$poste) continue;

            $flowType = $poste->type;

            $monthlyTotals = array_fill(0, $numPeriods, 0.0);
            $detailsList = []; // Liste pour stocker les transactions détaillées
            $totalCompte = 0.0;
foreach ($entries as $entry) {
    $periodName = Carbon::parse($entry->date)->isoFormat('MMM-YY');
    $periodKey = $periodMap[$periodName] ?? null;

    if (is_int($periodKey)) {
        // RÉPARATION : On extrait le montant positif peu importe la colonne (débit ou crédit)
        $debitVal = (float) $entry->debit;
        $creditVal = (float) $entry->credit;

        // On prend la valeur qui n'est pas nulle
        $value = ($debitVal > 0) ? $debitVal : $creditVal;

        $monthlyTotals[$periodKey] += $value;
        $totalCompte += $value;

        // Structure pour le tableau de détails
        $detailsList[] = [
            'date' => Carbon::parse($entry->date)->format('d/m'),
            'description' => $entry->description_operation,
            'reference' => $entry->reference_piece,
            // On affiche fidèlement ce qui est en base pour le débit/crédit
            'debit' => $debitVal > 0 ? number_format($debitVal, 2, ',', ' ') : '0,00',
            'credit' => $creditVal > 0 ? number_format($creditVal, 2, ',', ' ') : '0,00',
        ];

                    // Mise à jour des totaux généraux
                    if ($isIncome) {
                        $totalEncaissementsByPeriod[$periodKey] += $value;
                        $grandTotalEncaissements += $value;
                    } else {
                        $totalDecaissementsByPeriod[$periodKey] += $value;
                        $grandTotalDecaissements += $value;
                    }
                }
            }

            $dataRow = [
                'name' => $poste->name,
                'type' => $flowType,
                'data' => $this->formatMonthlyData($monthlyTotals),
                'total' => number_format($totalCompte, 2, ',', ' '),
                'raw_totals' => $monthlyTotals,
                'details' => $detailsList, // NOUVEAU: Le tableau de transactions
                'raw_total' => $totalCompte, // NOUVEAU: Total global BRUT
            ];

            if (isset($flowMap[$flowType])) {
                if ($isIncome) {
                    $flowMap[$flowType]['incomes'][] = $dataRow;
                } else {
                    $flowMap[$flowType]['expenses'][] = $dataRow;
                }
            }
        }
    }

        // 5. CLASSIFICATION DÉTAILLÉE DES DÉCAISSEMENTS OPÉRATIONNELS (pour la vue Blade)
        // La vue Blade attend les variables 'incomes', 'productionExpenses', 'otherExpenses'.
        $incomes = $operationalFlow['incomes']; // Encaissements Opérationnels
        $tempExpenses = $operationalFlow['expenses']; // Décaissements Opérationnels bruts

        // LOGIQUE À PERSONNALISER : Ici, simple répartition des décaissements opérationnels
        if (count($tempExpenses) > 2) {
            // Séparation en 70% pour 'Production' et 30% pour 'Autres'
            $splitPoint = (int) ceil(count($tempExpenses) * 0.7);
            $productionExpenses = array_slice($tempExpenses, 0, $splitPoint);
            $otherExpenses = array_slice($tempExpenses, $splitPoint);
        } else {
            $productionExpenses = $tempExpenses;
            $otherExpenses = [];
        }

        // 6. CALCULER ET FORMATER LES SOLDES NETS OPÉRATIONNELS
        $netCashFlowByPeriod = $this->calculateAndFormatNet($totalEncaissementsByPeriod, $totalDecaissementsByPeriod, $numPeriods);
        $grandNetCashFlow = number_format($grandTotalEncaissements - $grandTotalDecaissements, 2, ',', ' ');

        // 7. CALCULER LES SOLDES NETS ET LA VARIATION TOTALE
     $variationGlobaleByPeriod = array_fill(0, $numPeriods, 0.0);

        for ($i = 0; $i < $numPeriods; $i++) {
    // Récupération des valeurs brutes (raw) pour les calculs mathématiques
    $opNet = ($operationalFlow['totalEncaissementsRaw'][$i] ?? 0) - ($operationalFlow['totalDecaissementsRaw'][$i] ?? 0);
    $invNet = $investmentTotals['raw_net_by_period'][$i] ?? 0;
    $finNet = $financingTotals['raw_net_by_period'][$i] ?? 0;

    // Variation mensuelle globale
    $variationGlobaleByPeriod[$i] = $opNet + $invNet + $finNet;
}

// 7. FORMATAGE POUR LA VUE PDF
$grandVariationGlobale = array_sum($variationGlobaleByPeriod);
$formattedVariationGlobale = $this->formatTotals($variationGlobaleByPeriod);




        // 8. FORMATAGE DES TOTAUX RESTANTS (Encaissements et Décaissements Totaux)
        $totalEncaissementsByPeriod = $this->formatTotals($totalEncaissementsByPeriod);
        $totalDecaissementsByPeriod = $this->formatTotals($totalDecaissementsByPeriod);
        $grandTotalEncaissements = number_format($grandTotalEncaissements, 2, ',', ' ');
        $grandTotalDecaissements = number_format($grandTotalDecaissements, 2, ',', ' ');

    // Calcul des totaux spécifiques pour l'Investissement et le Financement
    $investmentTotals = $this->calculateFlowTotals($investmentFlow, $numPeriods);
    $financingTotals = $this->calculateFlowTotals($financingFlow, $numPeriods);

    return compact(
        'startDate',
        'endDate',
        'periods',
        // Flux Opérationnels (détaillés)
        'incomes',
        'productionExpenses',
        'otherExpenses',
        'totalEncaissementsByPeriod', // Totaux Opérationnels
        'totalDecaissementsByPeriod', // Totaux Opérationnels
        'grandTotalEncaissements',    // Total Global Opérationnel
        'grandTotalDecaissements',    // Total Global Opérationnel
        'netCashFlowByPeriod',
        'grandNetCashFlow',
        // Flux d'Investissement
        'investmentFlow',
        'investmentTotals', // NOUVEAU
        // Flux de Financement
        'financingFlow',
        'financingTotals', // NOUVEAU
        'formattedVariationGlobale', // La variation totale de la période
        'grandVariationGlobale'      // Total global de la variation
    );
}

    protected function formatMonthlyData($monthlyData)
    {
        $formatted = [];
        foreach ($monthlyData as $value) {
            $formatted[] = ($value === 0.0) ? '—' : number_format($value, 2, ',', ' ');
        }
        return $formatted;
    }

    /**
     * Méthode utilitaire pour formater les totaux.
     */
    protected function formatTotals($totals)
    {
        $formatted = [];
        foreach ($totals as $total) {
            $formatted[] = number_format($total, 2, ',', ' ');
        }
        return $formatted;
    }

    /**
     * Méthode utilitaire pour calculer et formater le flux net.
     */
    protected function calculateAndFormatNet($incomes, $expenses, $numPeriods)
    {
        $netCashFlowByPeriod = [];
        for ($index = 0; $index < $numPeriods; $index++) {
            $net = $incomes[$index] - $expenses[$index];
            $netCashFlowByPeriod[$index] = number_format($net, 2, ',', ' ');
        }
        return $netCashFlowByPeriod;
    }

    protected function calculateFlowTotals($flowData, $numPeriods)
{
    $incomesByPeriod = array_fill(0, $numPeriods, 0.0);
    $expensesByPeriod = array_fill(0, $numPeriods, 0.0);
    $totalIncome = 0.0;
    $totalExpense = 0.0;

    // Calcul des totaux des Encaissements
    foreach ($flowData['incomes'] as $item) {
        // Déformater les données mensuelles pour le calcul (retirer le formatage '—' ou ' ')
        foreach ($item['data'] as $index => $value) {
            // Utilisation des totaux bruts 'raw_totals' si disponibles, sinon déformatage
            $floatValue = $item['raw_totals'][$index] ?? (float) str_replace([' ', ','], ['', '.'], $value);
            $incomesByPeriod[$index] += $floatValue;
        }
        $totalIncome += $item['raw_total'] ?? (float) str_replace([' ', ','], ['', '.'], $item['total']);
    }

    // Calcul des totaux des Décaissements
    foreach ($flowData['expenses'] as $item) {
        // Déformater les données mensuelles pour le calcul
        foreach ($item['data'] as $index => $value) {
            // Utilisation des totaux bruts 'raw_totals' si disponibles, sinon déformatage
            $floatValue = $item['raw_totals'][$index] ?? (float) str_replace([' ', ','], ['', '.'], $value);
            $expensesByPeriod[$index] += $floatValue;
        }
        $totalExpense += $item['raw_total'] ?? (float) str_replace([' ', ','], ['', '.'], $item['total']);
    }

    // Calcul du Solde Net
    $netByPeriod = [];
    for ($i = 0; $i < $numPeriods; $i++) {
        $net = $incomesByPeriod[$i] - $expensesByPeriod[$i];
        $netByPeriod[$i] = number_format($net, 2, ',', ' ');
    }

    // Formatage des totaux
    return [
        'totalIncomeByPeriod' => $this->formatTotals($incomesByPeriod),
        'totalExpenseByPeriod' => $this->formatTotals($expensesByPeriod),
        'totalIncome' => number_format($totalIncome, 2, ',', ' '),
        'totalExpense' => number_format($totalExpense, 2, ',', ' '),
        'netByPeriod' => $netByPeriod,
        'grandNet' => number_format($totalIncome - $totalExpense, 2, ',', ' '),
    ];
}
}
