<?php
// Script de diagnostic pour le parser TXT

$files = [
    "c:/laragon/www/COMPTAFLOW/dossier .txt/EXP1_Codes journaux.txt",
    "c:/laragon/www/COMPTAFLOW/dossier .txt/EXP2_CG.txt",
    "c:/laragon/www/COMPTAFLOW/dossier .txt/EXP3_Tiers.txt",
    "c:/laragon/www/COMPTAFLOW/dossier .txt/EXP4_Ecritures.txt"
];

foreach ($files as $filePath) {
    echo "--- ANALYSE DE : " . basename($filePath) . " ---\n";
    if (!file_exists($filePath)) {
        echo "Fichier introuvable.\n\n";
        continue;
    }

    $rawContent = file_get_contents($filePath);
    
    // Détection d'encodage sommaire
    $encoding = mb_detect_encoding($rawContent, ['UTF-8', 'ISO-8859-1', 'Windows-1252']);
    echo "Encodage détecté : $encoding\n";
    
    if ($encoding && $encoding !== 'UTF-8') {
        $rawContent = mb_convert_encoding($rawContent, 'UTF-8', $encoding);
    }

    $content = explode("\n", str_replace("\r", "", $rawContent));
    $sample = array_filter(array_slice($content, 0, 30), 'trim');
    
    $delimiters = [';', ',', "\t", '|'];
    $bestDelim = null;
    $maxCols = 0;
    foreach($delimiters as $d) {
        $cols = count(explode($d, $sample[0] ?? ''));
        if ($cols > $maxCols && $cols > 1) { $maxCols = $cols; $bestDelim = $d; }
    }

    $sheetData = [];
    if ($bestDelim) {
        echo "Mode : DÉLIMITÉ (Séparateur: '$bestDelim')\n";
        foreach(array_slice($content, 0, 5) as $l) {
            print_r(str_getcsv($l, $bestDelim));
        }
    } else {
        echo "Mode : LARGEUR FIXE\n";
        $lineLengths = array_map('mb_strlen', $sample);
        $maxL = max($lineLengths ?: [0]);
        $density = array_fill(0, $maxL, 0);
        foreach($sample as $l) {
            for($i=0; $i<mb_strlen($l); $i++) {
                if (mb_substr($l, $i, 1) !== ' ') $density[$i]++;
            }
        }
        
        $offsets = [0];
        $inSilence = false;
        for($i=0; $i<$maxL; $i++) {
            $isVoid = ($density[$i] == 0);
            if ($isVoid && !$inSilence) { $inSilence = true; }
            elseif (!$isVoid && $inSilence) { $offsets[] = $i; $inSilence = false; }
        }
        
        echo "Offsets détectés : " . implode(', ', $offsets) . "\n";
        foreach(array_slice($content, 0, 5) as $l) {
            $row = [];
            for($i=0; $i<count($offsets); $i++) {
                $start = $offsets[$i];
                $len = isset($offsets[$i+1]) ? ($offsets[$i+1] - $start) : null;
                $row[] = trim($len ? mb_substr($l, $start, $len) : mb_substr($l, $start));
            }
            print_r($row);
        }
    }
    echo "\n\n";
}
