<?php

namespace App\\Http\\Controllers\\Super;

use App\\Http\\Controllers\\Controller;
use Symfony\\Component\\HttpFoundation\\BinaryFileResponse;

class SuperAdminIntegrationDocumentationController extends Controller
{
    /**
     * Documents Markdown publiés dans le dépôt et livrés avec l'application.
     * La liste blanche empêche toute lecture arbitraire de fichier.
     */
    private const DOCUMENTS = [
        'rh-flow' => [
            'title' => 'Cahier des charges RH-FLOW ↔ COMPTAFLOW',
            'description' => 'Liaison bidirectionnelle RH-FLOW, catalogue des entreprises, payloads de paie et sécurité.',
            'file' => 'CAHIER_DES_CHARGES_RH_FLOW_LIAISON_COMPTAFLOW.md',
        ],
        'applications-maison' => [
            'title' => 'Applications maison — Suite FLOW',
            'description' => 'Contrat commun destiné à RH-FLOW, SELFLOW et aux futures applications maison.',
            'file' => 'CAHIER_DES_CHARGES_APPLICATIONS_MAISON_SUITE_FLOW.md',
        ],
        'applications-tierces' => [
            'title' => 'Applications tierces — COMPTAFLOW',
            'description' => 'Enregistrement externe, demande de création de dossier, authentification et dépôts API.',
            'file' => 'CAHIER_DES_CHARGES_APPLICATIONS_TIERS_COMPTAFLOW.md',
        ],
    ];

    public function index()
    {
        $documents = [];
        foreach (self::DOCUMENTS as $slug => $document) {
            $path = $this->pathFor($document['file']);
            $documents[] = $document + [
                'slug' => $slug,
                'available' => is_file($path),
                'updated_at' => is_file($path) ? date('d/m/Y H:i', filemtime($path)) : null,
            ];
        }

        return view('superadmin.integration-documentation', compact('documents'));
    }

    public function show(string $slug)
    {
        abort_unless(isset(self::DOCUMENTS[$slug]), 404);
        $document = self::DOCUMENTS[$slug];
        $path = $this->pathFor($document['file']);
        abort_unless(is_file($path) && is_readable($path), 404);

        $content = file_get_contents($path);
        abort_if($content === false, 404);

        return view('superadmin.integration-documentation-show', [
            'document' => $document,
            'slug' => $slug,
            'content' => $content,
        ]);
    }

    public function download(string $slug): BinaryFileResponse
    {
        abort_unless(isset(self::DOCUMENTS[$slug]), 404);
        $file = self::DOCUMENTS[$slug]['file'];
        $path = $this->pathFor($file);
        abort_unless(is_file($path) && is_readable($path), 404);

        return response()->download($path, $file, [
            'Content-Type' => 'text/markdown; charset=UTF-8',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function pathFor(string $file): string
    {
        return base_path('docs/traitement recent/' . $file);
    }
}
