<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EcritureComptable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExternalJustificatifController extends Controller
{
    private const TAILLE_MAX_OCTETS = 10 * 1024 * 1024;

    /**
     * Dépose un PDF et le rattache à toutes les lignes d'une opération Selflow.
     *
     * Le dossier est exclusivement celui que le middleware cle.entreprise a
     * résolu depuis X-Company-Key. operation_source est une clé métier
     * d'idempotence ; ce n'est jamais un identifiant de dossier.
     */
    public function deverser(Request $request): JsonResponse
    {
        $donnees = $request->validate([
            'operation_source' => ['required', 'string', 'max:190'],
            'nom_fichier' => ['required', 'string', 'max:255'],
            'sha256' => ['required', 'string', 'regex:/^[a-fA-F0-9]{64}$/'],
            'contenu_base64' => ['required', 'string', 'max:14000000'],
        ]);

        $company = $request->attributes->get('entreprise_liee');
        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Dossier non résolu par la clé de liaison.',
            ], 401);
        }

        $contenu = base64_decode($donnees['contenu_base64'], true);
        if ($contenu === false || $contenu === '') {
            return response()->json(['success' => false, 'message' => 'Le contenu PDF encodé est invalide.'], 422);
        }

        if (strlen($contenu) > self::TAILLE_MAX_OCTETS) {
            return response()->json(['success' => false, 'message' => 'Le PDF dépasse la limite de 10 Mo.'], 413);
        }

        if (!str_starts_with($contenu, '%PDF-')) {
            return response()->json(['success' => false, 'message' => 'Le fichier transmis n’est pas un PDF valide.'], 422);
        }

        $empreinte = hash('sha256', $contenu);
        if (!hash_equals(strtolower($donnees['sha256']), $empreinte)) {
            return response()->json(['success' => false, 'message' => 'L’empreinte SHA-256 ne correspond pas au fichier reçu.'], 422);
        }

        $chemin = null;

        try {
            $resultat = DB::transaction(function () use ($company, $donnees, $contenu, $empreinte, &$chemin) {
                $lignes = EcritureComptable::withoutGlobalScopes()
                    ->where('company_id', $company->id)
                    ->where('operation_selflow', $donnees['operation_source'])
                    ->lockForUpdate()
                    ->get();

                if ($lignes->isEmpty()) {
                    return [
                        'status' => 404,
                        'body' => ['success' => false, 'message' => 'Aucune écriture ne correspond à cette opération dans ce dossier.'],
                    ];
                }

                $cheminsExistants = $lignes->pluck('piece_justificatif')->filter()->unique()->values();
                if ($cheminsExistants->count() > 1) {
                    return [
                        'status' => 409,
                        'body' => ['success' => false, 'message' => 'Les lignes de cette opération portent déjà des pièces différentes.'],
                    ];
                }

                if ($cheminsExistants->isNotEmpty()) {
                    $existant = $cheminsExistants->first();
                    if (is_file(storage_path('app/' . $existant))) {
                        $hashExistant = hash_file('sha256', storage_path('app/' . $existant));
                        if (hash_equals($hashExistant, $empreinte)) {
                            // Réparer aussi les lignes qui n'auraient pas reçu
                            // le chemin lors d'une ancienne tentative partielle.
                            EcritureComptable::withoutGlobalScopes()
                                ->where('company_id', $company->id)
                                ->where('operation_selflow', $donnees['operation_source'])
                                ->whereNull('piece_justificatif')
                                ->update(['piece_justificatif' => $existant]);

                            return [
                                'status' => 200,
                                'body' => [
                                    'success' => true,
                                    'message' => 'Cette pièce est déjà rattachée à l’opération.',
                                    'operation_source' => $donnees['operation_source'],
                                    'sha256' => $empreinte,
                                    'piece_justificatif' => $existant,
                                    'url' => route('api.external.pieces-justificatives.show', $lignes->first()->id),
                                    'idempotent' => true,
                                ],
                            ];
                        }
                    }

                    return [
                        'status' => 409,
                        'body' => ['success' => false, 'message' => 'Une autre pièce est déjà rattachée à cette opération. Utilisez la procédure de remplacement contrôlé.'],
                    ];
                }

                $nomTechnique = $company->id . '_' . hash('sha256', $donnees['operation_source']) . '_' . $empreinte . '.pdf';
                $chemin = 'private/justificatifs/' . $company->id . '/' . $nomTechnique;
                $repertoire = storage_path('app/private/justificatifs/' . $company->id);

                if (!is_dir($repertoire) && !mkdir($repertoire, 0750, true) && !is_dir($repertoire)) {
                    throw new \RuntimeException('Impossible de créer le dossier des justificatifs.');
                }

                if (file_put_contents($repertoire . DIRECTORY_SEPARATOR . $nomTechnique, $contenu, LOCK_EX) === false) {
                    throw new \RuntimeException('Impossible d’enregistrer le fichier PDF.');
                }

                EcritureComptable::withoutGlobalScopes()
                    ->where('company_id', $company->id)
                    ->where('operation_selflow', $donnees['operation_source'])
                    ->update(['piece_justificatif' => $chemin]);

                Log::info('Pièce justificative externe rattachée', [
                    'company_id' => $company->id,
                    'operation_source' => $donnees['operation_source'],
                    'sha256' => $empreinte,
                    'taille' => strlen($contenu),
                    'nom_fichier_source' => basename($donnees['nom_fichier']),
                    'nombre_lignes' => $lignes->count(),
                ]);

                return [
                    'status' => 201,
                    'body' => [
                        'success' => true,
                        'message' => 'Pièce justificative enregistrée et rattachée à l’opération.',
                        'operation_source' => $donnees['operation_source'],
                        'sha256' => $empreinte,
                        'piece_justificatif' => $chemin,
                        'url' => route('api.external.pieces-justificatives.show', $lignes->first()->id),
                        'nombre_lignes' => $lignes->count(),
                        'idempotent' => false,
                    ],
                ];
            }, 5);

            return response()->json($resultat['body'], $resultat['status']);
        } catch (\Throwable $e) {
            if ($chemin && is_file(storage_path('app/' . $chemin))) {
                @unlink(storage_path('app/' . $chemin));
            }

            Log::error('Échec du dépôt de pièce justificative externe', [
                'company_id' => $company->id,
                'operation_source' => $donnees['operation_source'],
                'erreur' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Le dépôt de la pièce justificative a échoué.',
            ], 500);
        }
    }

    /**
     * Téléchargement côté utilisateur connecté : le dossier actif de session
     * doit posséder l'écriture. Les anciens fichiers publics restent servis
     * par leur URL historique ; cette route ne sert que les chemins privés.
     */
    public function telechargerPourUtilisateur(int $id)
    {
        $companyId = (int) session('current_company_id');
        abort_unless($companyId > 0, 403);

        $ecriture = EcritureComptable::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->findOrFail($id);

        return $this->reponseFichierPrive($ecriture->piece_justificatif);
    }

    /**
     * Téléchargement côté application externe, sous la même clé de dossier.
     */
    public function telechargerPourIntegration(Request $request, int $id)
    {
        $company = $request->attributes->get('entreprise_liee');
        abort_unless($company, 401);

        $ecriture = EcritureComptable::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->findOrFail($id);

        return $this->reponseFichierPrive($ecriture->piece_justificatif);
    }

    private function reponseFichierPrive(?string $chemin)
    {
        abort_unless(
            is_string($chemin) && str_starts_with($chemin, 'private/justificatifs/'),
            404
        );

        $absolu = storage_path('app/' . $chemin);
        abort_unless(is_file($absolu), 404);

        return response()->file($absolu, [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

}
