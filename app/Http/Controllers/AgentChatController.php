<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PythonAiService;
use Illuminate\Support\Facades\Auth;

class AgentChatController extends Controller
{
    private PythonAiService $pythonAi;

    public function __construct(PythonAiService $pythonAi)
    {
        $this->middleware('auth');
        $this->pythonAi = $pythonAi;
    }

    /**
     * Affiche l'interface de chat premium.
     */
    public function index()
    {
        $agents = [
            'superviseur' => ['name' => 'Superviseur IA', 'desc' => 'Coordinateur central du cabinet', 'icon' => 'fas fa-user-shield', 'color' => '#4f46e5'],
            'comptabilite' => ['name' => 'Expert Comptable', 'desc' => 'Spécialiste OHADA et saisie', 'icon' => 'fas fa-calculator', 'color' => '#0891b2'],
            'fiscalite' => ['name' => 'Expert Fiscal', 'desc' => 'TVA, BIC, et déclarations DGI', 'icon' => 'fas fa-file-invoice-dollar', 'color' => '#dc2626'],
            'rh' => ['name' => 'Gestionnaire RH', 'desc' => 'Paie et droit du travail CI', 'icon' => 'fas fa-users-cog', 'color' => '#16a34a'],
            'droit' => ['name' => 'Juriste Affaires', 'desc' => 'Droit OHADA et contrats', 'icon' => 'fas fa-gavel', 'color' => '#ca8a04'],
            'finance' => ['name' => 'Analyste Financier', 'desc' => 'Trésorerie et ratios', 'icon' => 'fas fa-chart-line', 'color' => '#9333ea'],
        ];

        return view('admin.ia.agent_chat', compact('agents'));
    }

    /**
     * Traite un message envoyé via le chat.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'agent' => 'required|string',
        ]);

        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        
        $result = $this->pythonAi->chat(
            $request->message,
            $companyId,
            $request->agent
        );

        if (!$result['success']) {
            return response()->json(['success' => false, 'error' => $result['error']], 500);
        }

        return response()->json([
            'success' => true,
            'reponse' => $result['data']['reponse'],
            'agent' => $result['data']['agent'] ?? $request->agent
        ]);
    }
}
