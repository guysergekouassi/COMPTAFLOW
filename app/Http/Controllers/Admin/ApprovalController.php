<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Approval;
use Illuminate\Http\Request;
use App\Models\EcritureComptable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalController extends Controller
{
    public function index()
    {
        $query = Approval::with('requester')
            ->where('status', 'pending');

        // FILTRE CONTEXTUEL PAR EXERCICE (SESSION)
        $sessionExerciseId = session('current_exercice_id');
        if ($sessionExerciseId) {
            $query->whereHasMorph('approvable', [\App\Models\EcritureComptable::class], function ($q) use ($sessionExerciseId) {
                $q->where('exercices_comptables_id', $sessionExerciseId);
            });
        }

        $pendingApprovals = $query->orderBy('created_at', 'desc')->get();

        $history = Approval::with(['requester', 'handler'])
            ->where('status', '!=', 'pending')
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get();

        return view('admin.approvals.index', compact('pendingApprovals', 'history'));
    }

    public function approve($id)
    {
        $approval = Approval::findOrFail($id);

        Log::info('Approval attempt', [
            'approval_id' => $id,
            'type' => $approval->type,
            'data' => $approval->data,
            'user_id' => auth()->id()
        ]);

        DB::beginTransaction();
        try {
            // Logic to apply changes based on type
            if ($approval->type === 'accounting_entry') {
                $nSaisieUser = $approval->data['n_saisie'] ?? null; // C'est le n_saisie original (user)
                
                if (!$nSaisieUser) {
                    throw new \Exception('Numéro de saisie introuvable dans les données d\'approbation');
                }
                
                $companyId = auth()->user()->company_id;
                
                // Trouver les écritures avec le numéro utilisateur
                $ecritures = EcritureComptable::where(function($query) use ($nSaisieUser) {
                        $query->where('n_saisie_user', $nSaisieUser)
                              ->orWhere('n_saisie', $nSaisieUser);
                    })
                    ->where('company_id', $companyId)
                    ->get();
                
                if ($ecritures->isEmpty()) {
                    Log::warning('No entries found for approval', [
                        'n_saisie_user' => $nSaisieUser,
                        'company_id' => $companyId
                    ]);
                    throw new \Exception('Aucune écriture trouvée pour ce numéro de saisie');
                }
                
                // Générer le nouveau numéro global au format ECR_000000000001
                $lastRealSaisie = EcritureComptable::where('company_id', $companyId)
                    ->where('n_saisie', 'like', 'ECR_%')
                    ->orderBy('n_saisie', 'desc')
                    ->first();
                
                $nextNumber = 1;
                if ($lastRealSaisie) {
                    $lastNSaisie = $lastRealSaisie->n_saisie;
                    $numberPart = str_replace('ECR_', '', $lastNSaisie);
                    $nextNumber = (int)$numberPart + 1;
                }
                
                $newNSaisie = 'ECR_' . str_pad($nextNumber, 12, '0', STR_PAD_LEFT);

                // Mettre à jour toutes les lignes associées
                foreach ($ecritures as $ecriture) {
                    $ecriture->update([
                        'statut' => 'approved',
                        'n_saisie' => $newNSaisie,
                        // n_saisie_user reste inchangé pour garder la trace de l'origine
                    ]);
                }
                
                Log::info('Entries approved successfully', [
                    'count' => $ecritures->count(),
                    'old_number' => $nSaisieUser,
                    'new_number' => $newNSaisie
                ]);
            }

            $approval->update([
                'status' => 'approved',
                'handled_by' => auth()->id()
            ]);

            DB::commit();
            return back()->with('success', 'Écriture validée avec succès. Nouveau numéro : ' . ($newNSaisie ?? 'N/A'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Approval failed', [
                'approval_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Erreur lors de l\'approbation: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $approval = Approval::findOrFail($id);
        
        Log::info('Rejection attempt', [
            'approval_id' => $id,
            'type' => $approval->type,
            'data' => $approval->data,
            'user_id' => auth()->id()
        ]);
        
        DB::beginTransaction();
        try {
            if ($approval->type === 'accounting_entry') {
                // Utiliser n_saisie_user pour identifier les écritures en attente
                $nSaisieUser = $approval->data['n_saisie'] ?? null;
                
                if (!$nSaisieUser) {
                    throw new \Exception('Numéro de saisie introuvable dans les données d\'approbation');
                }
                
                // Trouver toutes les écritures avec ce numéro utilisateur
                $ecritures = EcritureComptable::where(function($query) use ($nSaisieUser) {
                        $query->where('n_saisie_user', $nSaisieUser)
                              ->orWhere('n_saisie', $nSaisieUser);
                    })
                    ->where('company_id', auth()->user()->company_id)
                    ->get();
                
                if ($ecritures->isEmpty()) {
                    Log::warning('No entries found for rejection', [
                        'n_saisie_user' => $nSaisieUser,
                        'company_id' => auth()->user()->company_id
                    ]);
                    throw new \Exception('Aucune écriture trouvée pour ce numéro de saisie');
                }
                
                // Mettre à jour le statut
                foreach ($ecritures as $ecriture) {
                    $ecriture->update(['statut' => 'rejected']);
                }
                
                Log::info('Entries rejected successfully', [
                    'count' => $ecritures->count(),
                    'n_saisie_user' => $nSaisieUser
                ]);
            }

            $approval->update([
                'status' => 'rejected',
                'handled_by' => auth()->id(),
                'comment' => $request->comment
            ]);

            DB::commit();
            return back()->with('success', 'Écriture rejetée avec motif : ' . $request->comment);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Rejection failed', [
                'approval_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Erreur lors du rejet : ' . $e->getMessage());
        }
    }
    public function getDetails($id)
    {
        try {
            $approval = Approval::findOrFail($id);
            $nSaisie = $approval->data['n_saisie'] ?? null;
            
            if (!$nSaisie) {
                return response()->json(['success' => false, 'message' => 'N° Saisie non trouvé']);
            }

            $ecritures = EcritureComptable::with(['planComptable', 'planTiers', 'codeJournal', 'compteTresorerie'])
                ->where('n_saisie', $nSaisie)
                ->where('company_id', auth()->user()->company_id)
                ->get();

            return response()->json([
                'success' => true,
                'ecritures' => $ecritures,
                'type' => $approval->type,
                'requester' => $approval->requester->name ?? 'Système'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
