<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Models\EcritureComptable;
use App\Models\ExerciceComptable;
use App\Models\PlanTiers;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\InternalNotification;

class DashboardController extends Controller
{
    /**
     * Point d'entrée principal du Dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // RECUPERATION DU CONTEXTE (SOCIÉTÉ)
        // 1. Priorité au header (si défini et non vide)
        // 2. Repli sur le company_id de l'utilisateur
        // 3. Dernier recours : première société pour un SuperAdmin
        $companyId = $request->header('X-Company-Id');
        if (!$companyId || $companyId === 'null' || $companyId === 'undefined') {
            $companyId = $user->company_id;
        }

        if (!$companyId && ($user->isAdmin() || $user->isSuperAdmin())) {
            $firstCompany = Company::orderBy('id')->first();
            $companyId = $firstCompany ? $firstCompany->id : null;
        }

        // RECUPERATION DU CONTEXTE (EXERCICE)
        $exerciceId = $request->header('X-Exercice-Id');
        if (!$exerciceId || $exerciceId === 'null' || $exerciceId === 'undefined') {
            if ($companyId) {
                $exerciceId = $this->getActiveExerciceId($companyId);
            }
        }

        $month = $request->query('month');
        $year = $request->query('year');

        $data = [
            'role' => $user->role,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'last_name' => $user->last_name,
            ],
            'debug' => [
                'company_id' => $companyId,
                'exercice_id' => $exerciceId,
                'role' => $user->role,
            ],
            'company' => $companyId ? Company::find($companyId) : null,
            'stats' => []
        ];

        // LOGIQUE DE STATS HARMONISÉE
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            $data['stats'] = $this->getAdminStats($companyId, $exerciceId, $month, $year);
            if ($user->isSuperAdmin()) {
                $data['stats'] = array_merge($data['stats'], $this->getSuperAdminStats());
            }
        } else {
            // Pour le collaborateur, on renvoie une structure hybride stable
            $data['stats'] = $this->getComptableStats($user->id, $companyId, $exerciceId, $month, $year);
        }

        // RECUPERATION DES COLLABORATEURS DE LA SOCIETE
        $data['collaborators'] = User::where('company_id', $companyId)
            ->where('id', '!=', $user->id)
            ->select('id', 'name', 'last_name')
            ->get()
            ->map(function($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name . ' ' . ($u->last_name ?? '')
                ];
            });

        return response()->json($data);
    }

    private function getSuperAdminStats()
    {
        return [
            'total_companies' => Company::count(),
            'active_companies' => Company::where('is_active', true)->count(),
            'total_users' => User::count(),
        ];
    }

    private function getAdminStats($companyId, $exerciceId = null, $month = null, $year = null)
    {
        // Données mensuelles
        $revenue = $this->getDetailedStats($companyId, $exerciceId, $month, $year, 'revenue');
        $expenses = $this->getDetailedStats($companyId, $exerciceId, $month, $year, 'expenses');
        $charges = $this->getDetailedStats($companyId, $exerciceId, $month, $year, 'charges');

        $approved_revenue = $revenue['approved'];
        $approved_expenses = $expenses['approved'];

        // Calcul de la croissance : 
        // Si mois présent : comparer avec mois précédent
        // Si mois absent : comparer avec année précédente
        if ($month) {
            $prev_period = $this->getPreviousPeriod($month, $year);
            $prev_revenue = $this->getDetailedStats($companyId, $exerciceId, $prev_period['month'], $prev_period['year'], 'revenue')['approved'];
        } else {
            $prev_revenue = $this->getDetailedStats($companyId, $exerciceId, null, ($year ?? Carbon::now()->year) - 1, 'revenue')['approved'];
        }

        $margin_rate = $approved_revenue != 0 ? (($approved_revenue - $approved_expenses) / abs($approved_revenue)) * 100 : 0;
        $growth_rate = $prev_revenue != 0 ? (($approved_revenue - $prev_revenue) / abs($prev_revenue)) * 100 : 0;

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'charges' => $charges,
            'honoraires' => ['approved' => 0, 'pending' => 0, 'total' => 0], // Placeholder
            'net_result' => $approved_revenue - $approved_expenses,
            'total_revenue' => $approved_revenue, 
            'total_expenses' => $approved_expenses, 
            'cash_balance' => $this->getCashBalance($companyId),
            'margin_rate' => round($margin_rate, 2),
            'growth_rate' => round($growth_rate, 2),
            'revenue_chart' => $this->getMonthlyChartData($companyId, $year),
            'expense_distribution' => $this->getExpenseDistribution($companyId, $exerciceId, $month, $year),
            'recent_entries' => EcritureComptable::withoutGlobalScopes()->where('company_id', $companyId)->latest()->limit(5)->get(),
            'unread_messages_count' => InternalNotification::where('receiver_id', auth()->id())->where('is_read', 0)->count(),
            'conversations' => $this->getConversations(auth()->id()),
        ];
    }

    private function getComptableStats($userId, $companyId, $exerciceId = null, $month = null, $year = null)
    {
        // On récupère les stats de base de l'entreprise
        $base = $this->getAdminStats($companyId, $exerciceId, $month, $year);

        $m = $month ?? Carbon::now()->month;
        $y = $year ?? Carbon::now()->year;

        // On y ajoute les stats spécifiques au collaborateur
        return array_merge($base, [
            'my_monthly_entries' => EcritureComptable::where('user_id', $userId)
                ->whereMonth('date', $m)->whereYear('date', $y)->count(),
            'alerts' => $this->getComptableAlerts($userId, $companyId, $exerciceId),
        ]);
    }

    private function getDetailedStats($companyId, $exerciceId, $month = null, $year = null, $type = 'revenue')
    {
        $query = EcritureComptable::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->when($month, fn($q) => $q->whereMonth('date', $month))
            ->when($year, fn($q) => $q->whereYear('date', $year))
            ->when(!$year && !$month && $exerciceId, fn($q) => $q->where('exercices_comptables_id', $exerciceId));

        if ($type === 'revenue') {
            $query->whereHas('planComptable', fn($q) => $q->withoutGlobalScopes()->where('numero_de_compte', 'like', '7%'));
            $field = 'credit';
        } else {
            $query->whereHas('planComptable', fn($q) => $q->withoutGlobalScopes()->where('numero_de_compte', 'like', '6%'));
            $field = 'debit';
        }

        $results = (clone $query)->selectRaw('statut, SUM(' . $field . ') as total')
            ->groupBy('statut')->pluck('total', 'statut');

        return [
            'approved' => (float) ($results['approved'] ?? 0),
            'pending' => (float) ($results['pending'] ?? 0),
            'draft' => (float) ($results['draft'] ?? 0),
            'total' => (float) (($results['approved'] ?? 0) + ($results['pending'] ?? 0) + ($results['draft'] ?? 0))
        ];
    }

    private function getCashBalance($companyId)
    {
        if (!$companyId)
            return 0;
        return EcritureComptable::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->whereHas('planComptable', fn($q) => $q->withoutGlobalScopes()->where('numero_de_compte', 'like', '5%'))
            ->selectRaw('SUM(debit) - SUM(credit) as balance')->first()->balance ?? 0;
    }

    private function getMonthlyChartData($companyId, $year)
    {
        if (!$companyId)
            return [];
        $y = $year ?? Carbon::now()->year;
        $results = EcritureComptable::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->when($year, fn($q) => $q->whereYear('date', $year))
            ->whereHas('planComptable', fn($q) => $q->withoutGlobalScopes()->where('numero_de_compte', 'like', '7%'))
            ->selectRaw('MONTH(date) as month, SUM(credit) as total')
            ->groupBy('month')->pluck('total', 'month');

        $chart = [];
        for ($i = 1; $i <= 12; $i++) {
            $chart[] = ['month' => $i, 'total' => (float) ($results[$i] ?? 0)];
        }
        return $chart;
    }

    private function getExpenseDistribution($companyId, $exerciceId, $month = null, $year = null)
    {
        if (!$companyId)
            return [];
        $results = EcritureComptable::withoutGlobalScopes()
            ->where('ecriture_comptables.company_id', $companyId)
            ->when($month, fn($q) => $q->whereMonth('ecriture_comptables.date', $month))
            ->when($year, fn($q) => $q->whereYear('ecriture_comptables.date', $year))
            ->when(!$year && !$month && $exerciceId, fn($q) => $q->where('ecriture_comptables.exercices_comptables_id', $exerciceId))
            ->join('plan_comptables', 'ecriture_comptables.plan_comptable_id', '=', 'plan_comptables.id')
            ->where('plan_comptables.numero_de_compte', 'like', '6%')
            ->select('plan_comptables.numero_de_compte', DB::raw('SUM(ecriture_comptables.debit) as total'))
            ->groupBy('plan_comptables.numero_de_compte')->get();

        $final = [];
        $grouped = [];
        foreach ($results as $res) {
            $prefix = substr($res->numero_de_compte, 0, 2);
            $label = match($prefix) {
                '60' => 'Achats',
                '61' => 'Services Ext.',
                '62' => 'Autres Serv.',
                '63' => 'Impôts & Taxes',
                '64' => 'Personnel',
                '65' => 'Autres Charges',
                '66' => 'Charges Fin.',
                '68' => 'Amortissements',
                default => 'Autres',
            };
            if (!isset($grouped[$label])) {
                $grouped[$label] = 0;
            }
            $grouped[$label] += (float) $res->total;
        }

        foreach ($grouped as $label => $total) {
            if ($total > 0) {
                $final[] = ['label' => $label, 'value' => $total];
            }
        }
        return $final;
    }

    private function getActiveExerciceId($companyId)
    {
        $ex = ExerciceComptable::where('company_id', $companyId)->where('is_active', true)->first();
        return $ex ? $ex->id : null;
    }

    private function getPreviousPeriod($month, $year)
    {
        $m = $month ?? Carbon::now()->month;
        $y = $year ?? Carbon::now()->year;
        return $m == 1 ? ['month' => 12, 'year' => $y - 1] : ['month' => $m - 1, 'year' => $y];
    }

    private function getComptableAlerts($userId, $companyId, $exerciceId)
    {
        $alerts = [];
        $count = EcritureComptable::where('user_id', $userId)->whereNull('piece_justificatif')->count();
        if ($count > 0)
            $alerts[] = ['type' => 'warning', 'message' => "$count écritures sans pièce"];
        return $alerts;
    }

    private function getConversations($userId)
    {
        $latestMessages = InternalNotification::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($item) use ($userId) {
                return $item->sender_id == $userId ? $item->receiver_id : $item->sender_id;
            });

        $conversations = [];
        foreach ($latestMessages as $contactId => $messages) {
            $lastMsg = $messages->first();
            $contact = User::find($contactId);
            if (!$contact) continue;

            $conversations[] = [
                'contact_id' => $contactId,
                'contact_name' => $contact->name . ' ' . ($contact->last_name ?? ''),
                'last_message' => $lastMsg->message,
                'last_message_time' => $lastMsg->created_at->toISOString(),
                'unread_count' => $messages->where('receiver_id', $userId)->where('is_read', 0)->count(),
                'is_last_message_from_me' => $lastMsg->sender_id == $userId,
            ];
        }

        return $conversations;
    }

    /**
     * Récupère l'historique des messages pour un contact spécifique.
     */
    public function chatHistory(Request $request, $contactId)
    {
        $userId = auth()->id();
        
        $messages = InternalNotification::where(function($q) use ($userId, $contactId) {
                $q->where('sender_id', $userId)->where('receiver_id', $contactId);
            })
            ->orWhere(function($q) use ($userId, $contactId) {
                $q->where('sender_id', $contactId)->where('receiver_id', $userId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // Marquer comme lu
        InternalNotification::where('sender_id', $contactId)
            ->where('receiver_id', $userId)
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        return response()->json([
            'contact_id' => $contactId,
            'messages' => $messages
        ]);
    }
}
