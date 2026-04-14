<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ExcelIaProjet;
use App\Models\ExcelIaProjetFichier;
use Illuminate\Support\Facades\Storage;

class ExcelIaProjetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $projets = ExcelIaProjet::where('company_id', $companyId)
            ->withCount('fichiers', 'analyses')
            ->orderByDesc('updated_at')
            ->get();

        return view('excel_ia_projets', compact('projets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'instructions' => 'nullable|string',
            'couleur' => 'nullable|string|max:20'
        ]);

        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $projet = ExcelIaProjet::create([
            'company_id' => $companyId,
            'user_id' => $user->id,
            'titre' => $request->titre,
            'instructions' => $request->instructions,
            'couleur' => $request->couleur ?? '#6366f1'
        ]);

        return redirect()->route('excel_ia.projets.show', $projet->id)
            ->with('success', 'Projet créé avec succès.');
    }

    public function show($id)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $projet = ExcelIaProjet::where('company_id', $companyId)
            ->with(['fichiers', 'analyses'])
            ->findOrFail($id);

        return view('excel_ia_projet_show', compact('projet'));
    }

    public function uploadFichier(Request $request, $id)
    {
        $request->validate([
            'fichiers' => 'required|array|max:20',
            'fichiers.*' => 'file|max:10240' // 10MB max par fichier
        ]);

        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $projet = ExcelIaProjet::where('company_id', $companyId)->findOrFail($id);

        if ($request->hasFile('fichiers')) {
            $count = 0;
            foreach ($request->file('fichiers') as $file) {
                $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                
                $path = $file->storeAs('public/excel_ia_depots', $filename);

                // Optionnel : Pré-parser le contenu pour plus de rapidité dans le chat
                $parser = new \App\Services\ExcelParserService();
                $absolutePath = \Illuminate\Support\Facades\Storage::disk('local')->path($path);
                $contenuExtrait = "";
                
                try {
                    if (file_exists($absolutePath)) {
                        $fileObj = new \Illuminate\Http\UploadedFile($absolutePath, $file->getClientOriginalName(), $file->getMimeType(), null, true);
                        $parsed = $parser->parse($fileObj);
                        $contenuExtrait = $parsed['contenu'] ?? '';
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("Prégénération cache IA échouée: " . $e->getMessage());
                }

                ExcelIaProjetFichier::create([
                    'projet_id' => $projet->id,
                    'nom' => $file->getClientOriginalName(),
                    'chemin' => $path,
                    'mime' => $file->getMimeType(),
                    'taille' => $file->getSize(),
                    'contenu_extrait' => $contenuExtrait
                ]);
                $count++;
            }

            return back()->with('success', "{$count} fichier(s) ajouté(s) au dépôt de données.");
        }

        return back()->with('error', 'Aucun fichier reçu.');
    }

    public function deleteFichier($id)
    {
        $fichier = ExcelIaProjetFichier::findOrFail($id);
        
        // Vérifier les droits du projet
        $projet = ExcelIaProjet::findOrFail($fichier->projet_id);
        $user = Auth::user();
        if ($projet->company_id != session('current_company_id', $user->company_id)) {
            abort(403);
        }

        if (Storage::exists($fichier->chemin)) {
            Storage::delete($fichier->chemin);
        }

        $fichier->delete();

        return back()->with('success', 'Fichier supprimé.');
    }

    public function updateInstructions(Request $request, $id)
    {
        $request->validate(['instructions' => 'nullable|string']);

        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $projet = ExcelIaProjet::where('company_id', $companyId)->findOrFail($id);
        $projet->update(['instructions' => $request->instructions]);

        return back()->with('success', 'Instructions mises à jour.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'couleur' => 'nullable|string|max:20'
        ]);

        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $projet = ExcelIaProjet::where('company_id', $companyId)->findOrFail($id);
        $projet->update($request->only(['titre', 'couleur']));

        return back()->with('success', 'Projet mis à jour avec succès.');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $projet = ExcelIaProjet::where('company_id', $companyId)->with('fichiers')->findOrFail($id);

        // Supprimer les fichiers physiques
        foreach ($projet->fichiers as $fichier) {
            if (Storage::exists($fichier->chemin)) {
                Storage::delete($fichier->chemin);
            }
        }

        // La suppression du projet supprimera les fichiers en cascade si la foreign key est configurée en cascade, 
        // sinon on le fait manuellement ici pour être sûr.
        $projet->fichiers()->delete();
        $projet->analyses()->delete();
        $projet->delete();

        return redirect()->route('excel_ia.projets.index')->with('success', 'Projet et données associés supprimés.');
    }
}
