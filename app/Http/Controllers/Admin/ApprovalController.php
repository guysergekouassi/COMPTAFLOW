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
            switch ($approval->type) {
                case 'new_user':
                    // Logic to activate user
                    break;
                case 'accounting_entry':
                    // Logic to validate entry
                    break;
            }

            $approval->update([
                'status' => 'approved',
                'handled_by' => auth()->id()
            ]);

            DB::commit();
            return back()->with('success', 'Approbation réussie.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'approbation: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $approval = Approval::findOrFail($id);
        
        $approval->update([
            'status' => 'rejected',
            'handled_by' => auth()->id(),
            'comment' => $request->comment
        ]);

        return back()->with('success', 'Demande rejetée.');
    }
}
