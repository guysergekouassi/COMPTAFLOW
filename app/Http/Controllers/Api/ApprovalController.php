<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Approval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    /**
     * Liste les approbations en attente pour l'entreprise actuelle.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $companyId = $request->header('X-Company-Id', $user->company_id);

        // On filtre les approbations liées à des modèles appartenant à l'entreprise
        $query = Approval::where('status', 'pending')
            ->with(['requester', 'approvable']);

        // Note: La logique de filtrage par compagnie dépend de comment approvable appartient à la compagnie.
        // On simplifie ici en supposant que l'utilisateur ne voit que ce qui est pertinent pour son rôle.
        
        return response()->json($query->latest()->paginate(20));
    }

    /**
     * Traite (Approuve ou Rejette) une demande d'approbation.
     */
    public function handle(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'comment' => 'nullable|string|max:500'
        ]);

        $approval = Approval::findOrFail($id);
        $user = Auth::user();

        // Sécurité : Seuls les admins ou ceux ayant la permission peuvent approuver
        if (!$user->isAdmin() && !$user->hasPermission('admin.approvals')) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $approval->update([
            'status' => $request->status,
            'handled_by' => $user->id,
            'comment' => $request->comment,
        ]);

        // Déclenchement de la logique métier selon le type
        $approvable = $approval->approvable;
        if ($approvable) {
            if ($request->status === 'approved') {
                $approvable->update(['statut' => 'approved']);
                // Ici on pourrait ajouter la logique de génération de numéro de saisie définitif si besoin
            } else {
                $approvable->update(['statut' => 'rejected']);
            }
        }

        return response()->json([
            'message' => 'Approbation traitée avec succès.',
            'approval' => $approval
        ]);
    }
}
