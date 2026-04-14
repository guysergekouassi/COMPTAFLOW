<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\FactureProduite;

class FactureProduiteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user      = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $factures = FactureProduite::where('company_id', $companyId)
            ->orderByDesc('date_facture')
            ->paginate(20);

        return view('factures_produites', compact('factures'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fichier'      => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'date_facture' => 'required|date',
            'client_nom'   => 'nullable|string|max:255',
            'montant'      => 'nullable|numeric|min:0',
            'notes'        => 'nullable|string|max:1000',
            'client_tiers_code' => 'nullable|string|max:20',
        ]);

        $user      = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $annee     = date('Y', strtotime($request->date_facture));
        $mois      = date('n', strtotime($request->date_facture));
        $fichier   = $request->file('fichier');
        $ext       = strtolower($fichier->getClientOriginalExtension());

        // Numéro de référence automatique
        $lastRef = FactureProduite::where('company_id', $companyId)->max('id') ?? 0;
        $reference = "FAC-{$annee}-" . str_pad($lastRef + 1, 4, '0', STR_PAD_LEFT);

        // Stockage du fichier
        $dossier = "factures_produites/{$companyId}/{$annee}/{$mois}";
        $nomFichier = $reference . '_' . now()->format('His') . '.' . $ext;
        $chemin  = $fichier->storeAs($dossier, $nomFichier);

        FactureProduite::create([
            'company_id'         => $companyId,
            'user_id'            => $user->id,
            'exercice_id'        => session('exercice_id'),
            'reference'          => $reference,
            'client_nom'         => $request->client_nom,
            'client_tiers_code'  => $request->client_tiers_code,
            'montant'            => $request->montant ?? 0,
            'date_facture'       => $request->date_facture,
            'mois'               => $mois,
            'annee'              => $annee,
            'nom_fichier_original' => $fichier->getClientOriginalName(),
            'chemin_fichier'     => $chemin,
            'type_fichier'       => $ext,
            'taille_fichier'     => $fichier->getSize(),
            'notes'              => $request->notes,
            'statut'             => 'valide',
        ]);

        return response()->json(['success' => true, 'reference' => $reference]);
    }

    public function show(int $id)
    {
        $user      = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $facture   = FactureProduite::where('id', $id)->where('company_id', $companyId)->firstOrFail();
        return view('facture_produite_show', compact('facture'));
    }

    public function download(int $id)
    {
        $user      = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $facture   = FactureProduite::where('id', $id)->where('company_id', $companyId)->firstOrFail();

        if (!Storage::exists($facture->chemin_fichier)) {
            abort(404, 'Fichier introuvable');
        }

        return Storage::download($facture->chemin_fichier, $facture->nom_fichier_original);
    }

    public function destroy(int $id)
    {
        $user      = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $facture   = FactureProduite::where('id', $id)->where('company_id', $companyId)->firstOrFail();

        Storage::delete($facture->chemin_fichier);
        $facture->delete();

        return response()->json(['success' => true]);
    }
}
