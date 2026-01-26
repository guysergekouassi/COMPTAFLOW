<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Approval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    public function index()
    {
        $pendingApprovals = Approval::with('requester')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

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

        DB::beginTransaction();
        try {
            // Logic to apply changes based on type
            if ($approval->type === 'accounting_entry') {
                $nSaisie = $approval->data['n_saisie'] ?? null;
                if ($nSaisie) {
                    EcritureComptable::where('n_saisie', $nSaisie)
                        ->where('company_id', auth()->user()->company_id)
                        ->update(['statut' => 'approved']);
                }
            }

            $approval->update([
                'status' => 'approved',
                'handled_by' => auth()->id()
            ]);

            DB::commit();
            return back()->with('success', 'Écriture validée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'approbation: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $approval = Approval::findOrFail($id);
        
        DB::beginTransaction();
        try {
            if ($approval->type === 'accounting_entry') {
                $nSaisie = $approval->data['n_saisie'] ?? null;
                if ($nSaisie) {
                    EcritureComptable::where('n_saisie', $nSaisie)
                        ->where('company_id', auth()->user()->company_id)
                        ->update(['statut' => 'rejected']);
                }
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
            return back()->with('error', 'Erreur lors du rejet : ' . $e->getMessage());
        }
    }
}
