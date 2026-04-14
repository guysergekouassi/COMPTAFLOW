<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * Service de parsing multi-format pour le module Analyse Comptable IA.
 * Supporte : .xlsx, .xls, .csv, .pdf (texte), .jpg, .png (base64 pour IA vision)
 */
class ExcelParserService
{
    /**
     * Analyse un fichier uploadé et retourne sa structure extraite.
     * @return array ['type' => string, 'contenu' => string|array, 'nom' => string, 'pages' => int]
     */
    public function parse(UploadedFile $file): array
    {
        $nom       = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension() ?: pathinfo($nom, PATHINFO_EXTENSION);
        $extension = strtolower($extension);

        Log::info("[ExcelParser] Parsing fichier: {$nom} (type: {$extension})");

        return match (true) {
            in_array($extension, ['xlsx', 'xls']) => $this->parseExcel($file, $nom),
            $extension === 'csv'                  => $this->parseCsv($file, $nom),
            $extension === 'pdf'                  => $this->parsePdfAsBase64($file, $nom),
            in_array($extension, ['jpg', 'jpeg', 'png']) => $this->parseImageAsBase64($file, $nom),
            default => ['type' => 'inconnu', 'nom' => $nom, 'contenu' => '', 'pages' => 0, 'erreur' => 'Format non supporté'],
        };
    }

    /**
     * Parse un fichier Excel (toutes les feuilles).
     */
    private function parseExcel(UploadedFile $file, string $nom): array
    {
        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $feuilles    = [];

            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $titreFeuille = $sheet->getTitle();
                $donnees      = $sheet->toArray(null, true, true, false);

                // Filtrer les lignes complètement vides
                $donneesFiltrees = array_filter($donnees, function ($ligne) {
                    return !empty(array_filter($ligne, fn($v) => $v !== null && $v !== ''));
                });

                $feuilles[] = [
                    'nom'     => $titreFeuille,
                    'lignes'  => count($donneesFiltrees),
                    'donnees' => array_values($donneesFiltrees),
                ];
            }

            return [
                'type'    => 'excel',
                'nom'     => $nom,
                'feuilles'=> $feuilles,
                'pages'   => count($feuilles),
                'contenu' => $this->sanitizeUtf8($this->excelToTexte($feuilles)),
            ];
        } catch (\Throwable $e) {
            Log::error("[ExcelParser] Erreur Excel: " . $e->getMessage());
            return ['type' => 'excel', 'nom' => $nom, 'contenu' => '', 'pages' => 0, 'erreur' => $e->getMessage()];
        }
    }

    /**
     * Convertit les feuilles Excel en texte structuré pour le prompt IA.
     */
    private function excelToTexte(array $feuilles): string
    {
        $texte = "";
        foreach ($feuilles as $feuille) {
            $texte .= "\n\n=== FEUILLE : {$feuille['nom']} ===\n";
            foreach ($feuille['donnees'] as $idx => $ligne) {
                // Ignorer les lignes vides
                $valeursNonVides = array_filter($ligne, fn($v) => $v !== null && $v !== '');
                if (empty($valeursNonVides)) continue;

                // Première ligne = en-têtes
                if ($idx === 0) {
                    $texte .= "EN-TÊTES: " . implode(' | ', array_map('strval', $ligne)) . "\n";
                    $texte .= str_repeat('-', 60) . "\n";
                } else {
                    $texte .= implode(' | ', array_map(fn($v) => $v !== null ? strval($v) : '', $ligne)) . "\n";
                }
            }
        }
        return $texte;
    }

    /**
     * Parse un fichier CSV.
     */
    private function parseCsv(UploadedFile $file, string $nom): array
    {
        try {
            $contenu    = file_get_contents($file->getPathname());
            $lignes     = explode("\n", $contenu);
            $separateur = str_contains($lignes[0] ?? '', ';') ? ';' : ',';
            $donnees    = [];

            foreach ($lignes as $ligne) {
                $ligne = trim($ligne);
                if (empty($ligne)) continue;
                $donnees[] = str_getcsv($ligne, $separateur);
            }

            $texte = "=== FICHIER CSV : {$nom} ===\n";
            foreach ($donnees as $row) {
                $texte .= implode(' | ', $row) . "\n";
            }

            return [
                'type'    => 'csv',
                'nom'     => $nom,
                'contenu' => $this->sanitizeUtf8($texte),
                'pages'   => 1,
                'lignes'  => count($donnees),
            ];
        } catch (\Throwable $e) {
            return ['type' => 'csv', 'nom' => $nom, 'contenu' => '', 'pages' => 0, 'erreur' => $e->getMessage()];
        }
    }

    /**
     * PDF → Base64 pour envoi direct à l'IA vision.
     */
    private function parsePdfAsBase64(UploadedFile $file, string $nom): array
    {
        try {
            $data = file_get_contents($file->getPathname());
            return [
                'type'      => 'pdf',
                'nom'       => $nom,
                'contenu'   => '',
                'base64'    => base64_encode($data),
                'mime'      => 'application/pdf',
                'pages'     => 1,
                'ia_vision' => true,
            ];
        } catch (\Throwable $e) {
            return ['type' => 'pdf', 'nom' => $nom, 'contenu' => '', 'pages' => 0, 'erreur' => $e->getMessage()];
        }
    }

    /**
     * Image → Base64 pour envoi à l'IA vision.
     */
    private function parseImageAsBase64(UploadedFile $file, string $nom): array
    {
        try {
            $extension = strtolower($file->getClientOriginalExtension());
            $mime      = in_array($extension, ['jpg', 'jpeg']) ? 'image/jpeg' : 'image/png';
            $data      = file_get_contents($file->getPathname());

            return [
                'type'      => 'image',
                'nom'       => $nom,
                'contenu'   => '',
                'base64'    => base64_encode($data),
                'mime'      => $mime,
                'pages'     => 1,
                'ia_vision' => true,
            ];
        } catch (\Throwable $e) {
            return ['type' => 'image', 'nom' => $nom, 'contenu' => '', 'pages' => 0, 'erreur' => $e->getMessage()];
        }
    }

    /**
     * Construit le contexte textuel combiné de tous les fichiers pour le prompt IA.
     */
    public function construireContexteGlobal(array $fichiersParsed): string
    {
        $contexte = "=== DONNÉES COMPTABLES FOURNIES ===\n";
        $contexte .= "Nombre de fichiers : " . count($fichiersParsed) . "\n\n";

        foreach ($fichiersParsed as $fichier) {
            if (!empty($fichier['erreur'])) {
                $contexte .= "\n⚠️ FICHIER EN ERREUR : {$fichier['nom']} — {$fichier['erreur']}\n";
                continue;
            }

            if ($fichier['ia_vision'] ?? false) {
                // Les fichiers image/PDF seront envoyés directement comme données vision
                $contexte .= "\n📎 FICHIER VISUEL : {$fichier['nom']} (type: {$fichier['type']}) — sera analysé en mode vision IA.\n";
            } else {
                $contexte .= "\n" . ($fichier['contenu'] ?? '') . "\n";
            }
        }
        return $contexte;
    }

    /**
     * Nettoie une chaîne pour s'assurer qu'elle est en UTF-8 valide.
     */
    private function sanitizeUtf8(string $text): string
    {
        if (empty($text)) return '';
        
        // Détecter l'encodage et convertir si nécessaire (souvent ISO-8859-1 pour les CSV Excel)
        $encoding = mb_detect_encoding($text, 'UTF-8, ISO-8859-1, Windows-1252', true);
        if ($encoding && $encoding !== 'UTF-8') {
            $text = mb_convert_encoding($text, 'UTF-8', $encoding);
        }

        // Nettoyer les caractères UTF-8 malformés restants
        return mb_convert_encoding($text, 'UTF-8', 'UTF-8');
    }
}
