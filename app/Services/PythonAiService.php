<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PythonAiService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('PYTHON_AI_API_URL', 'https://dc-knowing.com/agent'), '/');
    }

    /**
     * Envoie un message de chat à un agent spécifique.
     */
    public function chat(string $message, ?int $clientId = null, string $agentType = 'superviseur', ?string $sessionId = null)
    {
        $sessionId = $sessionId ?? session()->getId();
        $clientId = $clientId ?? (Auth::check() ? Auth::user()->company_id : null);

        try {
            $response = Http::timeout(120)->post("{$this->baseUrl}/agent/chat", [
                'message' => $message,
                'client_id' => $clientId,
                'session_id' => $sessionId,
                'agent_type' => $agentType,
            ]);

            if ($response->failed()) {
                Log::error("Python AI Chat Error: " . $response->body());
                return ['success' => false, 'error' => "L'IA ne répond pas (Code: " . $response->status() . ")"];
            }

            return ['success' => true, 'data' => $response->json()];

        } catch (\Exception $e) {
            Log::error("Python AI Chat Exception: " . $e->getMessage());
            return ['success' => false, 'error' => "Erreur de connexion à l'IA : " . $e->getMessage()];
        }
    }

    /**
     * Analyse une facture (fichier uploadé).
     */
    public function analyzeInvoice($file)
    {
        try {
            $response = Http::timeout(120)
                ->attach('fichier', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post("{$this->baseUrl}/agent/facture");

            if ($response->failed()) {
                Log::error("Python AI Facture Error: " . $response->body());
                return ['success' => false, 'error' => "Échec de l'analyse (Code: " . $response->status() . ")"];
            }

            return ['success' => true, 'data' => $response->json()];

        } catch (\Exception $e) {
            Log::error("Python AI Facture Exception: " . $e->getMessage());
            return ['success' => false, 'error' => "Erreur lors de l'envoi de la facture : " . $e->getMessage()];
        }
    }

    /**
     * Calcule la paie via l'agent RH.
     */
    public function calculatePayroll(array $data)
    {
        try {
            $response = Http::timeout(60)->post("{$this->baseUrl}/agent/paie", $data);

            if ($response->failed()) {
                Log::error("Python AI Paie Error: " . $response->body());
                return ['success' => false, 'error' => "Échec du calcul (Code: " . $response->status() . ")"];
            }

            return ['success' => true, 'data' => $response->json()];

        } catch (\Exception $e) {
            Log::error("Python AI Paie Exception: " . $e->getMessage());
            return ['success' => false, 'error' => "Erreur lors du calcul de la paie : " . $e->getMessage()];
        }
    }

    /**
     * Vérifie le statut de l'API Python.
     */
    public function getStatus()
    {
        try {
            $response = Http::timeout(5)->get($this->baseUrl);
            return $response->json();
        } catch (\Exception $e) {
            return ['statut' => 'indisponible', 'error' => $e->getMessage()];
        }
    }
}
