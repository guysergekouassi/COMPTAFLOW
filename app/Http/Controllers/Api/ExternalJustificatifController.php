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
                $lignes = EcritureComptable::query()
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
                    if (is_file(public_path('justificatifs/' . $existant))) {
                        $hashExistant = hash_file('sha256', public_path('justificatifs/' . $existant));
                        if (hash_equals($hashExistant, $empreinte)) {
                            return [
                                'status' => 200,
                                'body' => [
                                    'success' => true,
                                    'message' => 'Cette pièce est déjà rattachée à l’opération.',
                                    'operation_source' => $donnees['operation_source'],
                                    'sha256' => $empreinte,
                                    'piece_justificatif' => $existant,
                                    'url' => asset('justificatifs/' . $existant),
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
                $chemin = $nomTechnique;
                $repertoire = public_path('justificatifs');

                if (!is_dir($repertoire) && !mkdir($repertoire, 0750, true) && !is_dir($repertoire)) {
                    throw new \RuntimeException('Impossible de créer le dossier des justificatifs.');
                }

                if (file_put_contents($repertoire . DIRECTORY_SEPARATOR . $chemin, $contenu, LOCK_EX) === false) {
                    throw new \RuntimeException('Impossible d’enregistrer le fichier PDF.');
                }

                EcritureComptable::query()
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
                        'url' => asset('justificatifs/' . $chemin),
                        'nombre_lignes' => $lignes->count(),
                        'idempotent' => false,
                    ],
                ];
            }, 5);

            return response()->json($resultat['body'], $resultat['status']);
        } catch (\Throwable $e) {
            if ($chemin && is_file(public_path('justificatifs/' . $chemin))) {
                @unlink(public_path('justificatifs/' . $chemin));
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
}
