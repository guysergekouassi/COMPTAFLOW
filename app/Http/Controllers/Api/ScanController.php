<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlanComptable;
use App\Models\PlanTiers;
use App\Models\CodeJournal;
use App\Models\EcritureComptable;
use App\Models\ExerciceComptable;
use App\Models\AxeAnalytique;
use App\Models\IaLog;
use App\Services\ScanService;
use App\Services\VertexAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ScanController extends Controller
{
    protected $scanService;
    protected $vertexAiService;

    public function __construct(ScanService $scanService, VertexAiService $vertexAiService)
    {
        $this->scanService = $scanService;
        $this->vertexAiService = $vertexAiService;
    }

    /**
     * Retourne le contexte nécessaire pour l'interface de scan par lot mobile.
     */
    public function getContext(Request $request)
    {
        $user = $request->user();
        $companyId = $request->header('X-Company-Id', $user->company_id);

        $journals = CodeJournal::where('company_id', $companyId)->get();
        $planComptable = PlanComptable::where('company_id', $companyId)
            ->select('id', 'numero_de_compte', 'intitule')
            ->orderBy('numero_de_compte')
            ->get();
        $planTiers = PlanTiers::where('company_id', $companyId)
            ->select('id', 'numero_de_tiers', 'intitule', 'compte_general')
            ->get();
        
        $exerciceActif = ExerciceComptable::where('company_id', $companyId)
            ->where('is_active', 1)
            ->first() ?? ExerciceComptable::where('company_id', $companyId)
            ->where('cloturer', 0)
            ->orderBy('date_debut', 'desc')
            ->first();

        $axes = AxeAnalytique::where('company_id', $companyId)->with('sections')->get();

        // Calcul du prochain numéro de saisie utilisateur
        $initials = $user->initiales;
        $prefix = "CPT-" . $initials . "_";
        $nextSequence = EcritureComptable::where('company_id', $companyId)
            ->where('n_saisie_user', 'like', $prefix . '%')
            ->distinct('n_saisie_user')
            ->count('n_saisie_user') + 1;
        
        $nextSaisieNumber = $prefix . str_pad($nextSequence, 12, '0', STR_PAD_LEFT);

        return response()->json([
            'journals' => $journals,
            'plan_comptable' => $planComptable,
            'plan_tiers' => $planTiers,
            'exercice_actif' => $exerciceActif,
            'axes' => $axes,
            'next_saisie_number' => $nextSaisieNumber,
        ]);
    }

    /**
     * Upload et analyse d'une facture isolée.
     */
    public function upload(Request $request)
    {
        try {
            $user = $request->user();
            $companyId = $request->header('X-Company-Id', $user->company_id);

            $request->validate([
                'facture' => 'required|file|mimes:jpeg,jpg,png,pdf|max:10240',
            ]);

            $file = $request->file('facture');
            $extension = strtolower($file->getClientOriginalExtension());
            $image_hash = md5_file($file->getPathname()) . '_' . $file->getSize();

            if ($extension === 'pdf') {
                $image_data = base64_encode(file_get_contents($file->getPathname()));
                $mime_type = "application/pdf";
            } else {
                $compressed = $this->scanService->compressImage($file->getPathname());
                $image_data = base64_encode($compressed);
                $mime_type = "image/jpeg";
            }

            $prompt = $this->scanService->buildPrompt($companyId, $request->input('journal_code', 'AC'));
            $result = $this->vertexAiService->analyzeInvoice($image_data, $mime_type, $prompt);

            if (isset($result['has_error']) && $result['has_error']) {
                $errorMsg = $result['error_message'] ?? 'Erreur inconnue Vertex AI';
                IaLog::create([
                    'company_id' => $companyId,
                    'user_id' => $user->id,
                    'image_hash' => $image_hash,
                    'image_nom' => $file->getClientOriginalName(),
                    'status' => 'error',
                    'erreur_message' => substr($errorMsg, 0, 250),
                    'json_brut' => json_encode($result),
                ]);
                return response()->json(['success' => false, 'error' => $errorMsg], 500);
            }

            $data = $result['data'] ?? null;
            
            if (!$data) {
                return response()->json(['success' => false, 'error' => 'Aucune donnée extraite par l\'IA'], 500);
            }
            
            IaLog::create([
                'company_id' => $companyId,
                'user_id' => $user->id,
                'image_hash' => $image_hash,
                'image_nom' => $file->getClientOriginalName(),
                'status' => 'success',
                'json_brut' => json_encode($data),
            ]);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Enregistre un lot d'écritures validées (Scan par lot).
     */
    public function storeBatch(Request $request)
    {
        $user = $request->user();
        $companyId = $request->header('X-Company-Id', $user->company_id);

        $request->validate([
            'entries' => 'required|array|min:1',
            'entries.*.date' => 'required|date',
            'entries.*.code_journal_id' => 'required|exists:code_journals,id',
            'entries.*.n_saisie_user' => 'required|string',
            'entries.*.lines' => 'required|array|min:2',
            'entries.*.lines.*.plan_comptable_id' => 'required|exists:plan_comptables,id',
            'entries.*.lines.*.debit' => 'nullable|numeric|min:0',
            'entries.*.lines.*.credit' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $results = [];
            foreach ($request->entries as $entryData) {
                // Pour chaque pièce (saisie groupée)
                $status = ($user->isAdmin() || $user->hasPermission('admin.approvals')) ? 'approved' : 'pending';
                
                // Génération numéro si approuvé, sinon numéro utilisateur
                $nSaisie = ($status === 'approved') 
                    ? $this->generateGlobalSaisieNumber($companyId) 
                    : $entryData['n_saisie_user'];

                foreach ($entryData['lines'] as $line) {
                    $ecriture = EcritureComptable::create([
                        'company_id' => $companyId,
                        'user_id' => $user->id,
                        'n_saisie' => $nSaisie,
                        'n_saisie_user' => $entryData['n_saisie_user'],
                        'code_journal_id' => $entryData['code_journal_id'],
                        'exercices_comptables_id' => $this->getExerciceId($companyId, $entryData['date']),
                        'date' => $entryData['date'],
                        'description_operation' => $line['description'] ?? $entryData['description'] ?? '',
                        'reference_piece' => $entryData['reference'] ?? null,
                        'plan_comptable_id' => $line['plan_comptable_id'],
                        'plan_tiers_id' => $line['plan_tiers_id'] ?? null,
                        'debit' => $line['debit'] ?? 0,
                        'credit' => $line['credit'] ?? 0,
                        'statut' => $status,
                    ]);

                    // Gestion analytique simplifiée pour l'instant
                    if (!empty($line['ventilations'])) {
                        foreach ($line['ventilations'] as $v) {
                            $ecriture->ventilations()->create([
                                'section_id' => $v['section_id'],
                                'montant' => $v['montant'],
                                'pourcentage' => $v['pourcentage'],
                            ]);
                        }
                    }
                }
                $results[] = $nSaisie;
            }

            DB::commit();
            return response()->json(['success' => true, 'n_saisies' => $results]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function getExerciceId($companyId, $date)
    {
        $d = Carbon::parse($date);
        $ex = ExerciceComptable::where('company_id', $companyId)
            ->where('date_debut', '<=', $d)
            ->where('date_fin', '>=', $d)
            ->first();
        
        return $ex ? $ex->id : null;
    }

    private function generateGlobalSaisieNumber($companyId)
    {
        $last = EcritureComptable::where('company_id', $companyId)
            ->where('n_saisie', 'like', 'ECR_%')
            ->latest('id')
            ->first();

        $nextNum = $last ? ((int)str_replace('ECR_', '', $last->n_saisie) + 1) : 1;
        return 'ECR_' . str_pad($nextNum, 12, '0', STR_PAD_LEFT);
    }
}
