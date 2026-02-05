<?php

namespace App\Services\Import;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;

class UniversalParser
{
    /**
     * Parses any supported file format into a standardized array.
     * Returns: ['headers' => ['Col1', 'Col2'], 'rows' => [['Val1', 'Val2'], ...]]
     */
    public function parse(string $filePath, ?string $clientExtension = null): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("Le fichier n'existe pas : $filePath");
        }

        // Determine extension (trust client extension if provided, else file path)
        $extension = $clientExtension 
            ? strtolower($clientExtension) 
            : strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'xlsx':
            case 'xls':
            case 'csv':
                return $this->parseSpreadsheet($filePath);
            
            case 'txt':
                return $this->parseTxt($filePath);
            
            case 'html':
            case 'htm':
                return $this->parseHtml($filePath);
                
            case 'xml':
                return $this->parseXml($filePath);

            default:
                throw new \Exception("Format de fichier non supporté : $extension");
        }
    }

    protected function parseSpreadsheet(string $filePath): array
    {
        try {
            // Identify format even if extension is wrong (e.g. CSV named .xls)
            $inputFileType = IOFactory::identify($filePath);
            $reader = IOFactory::createReader($inputFileType);
            
            // Read-only for speed - set to false to allow reading formats (dates)
            $reader->setReadDataOnly(false);
            $spreadsheet = $reader->load($filePath);
            
            $sheet = $spreadsheet->getActiveSheet();
            // On active formatData (3ème param) pour obtenir les valeurs formatées (ex: Dates lisibles)
            $rows = $sheet->toArray(null, true, true, false);

            if (empty($rows)) {
                throw new \Exception("Le fichier est vide.");
            }

            // Assume first row is headers
            $headers = array_shift($rows);
            $headers = array_map('trim', $headers);
            
            // Verify headers are not empty
            if (empty(array_filter($headers))) {
                 throw new \Exception("Les en-têtes de colonnes sont vides ou introuvables.");
            }

            return [
                'headers' => $headers,
                'rows' => $rows
            ];

        } catch (\Exception $e) {
            Log::error("UniversalParser Excel Error: " . $e->getMessage());
            throw new \Exception("Erreur lecture Excel/CSV : " . $e->getMessage());
        }
    }

    protected function parseTxt(string $filePath): array
    {
        // Handle encoding (convert to UTF-8 if needed)
        $content = file_get_contents($filePath);
        if (!$content) throw new \Exception("Fichier vide ou illisible.");
        
        // Simple encoding fix
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1'); 
        }

        $lines = explode(PHP_EOL, $content);
        $lines = array_filter($lines, fn($l) => !empty(trim($l))); // Remove empty lines
        $lines = array_values($lines); // Re-index

        if (empty($lines)) throw new \Exception("Le fichier texte ne contient aucune ligne de données.");

        // Detect Delimiter on first line
        $firstLine = $lines[0];
        $delimiters = ["\t" => 0, ";" => 0, "|" => 0, "," => 0];
        foreach ($delimiters as $d => $c) {
            $delimiters[$d] = substr_count($firstLine, $d);
        }
        arsort($delimiters);
        $bestDelimiter = array_key_first($delimiters);
        
        // If no frequent delimiter, assume fixed width or single column? 
        // For now, default to single column if count is 0, but usually tabs or semi-colons exist.
        if ($delimiters[$bestDelimiter] == 0) {
             // Fallback: Check if it looks like fixed width? 
             // Without config, we assume it might be a raw dump. Return as single column.
             $rows = array_map(fn($l) => [$l], $lines);
             $headers = ["Colonne A"];
        } else {
            $rows = [];
            foreach ($lines as $line) {
                // str_getcsv is robust
                $rows[] = str_getcsv($line, $bestDelimiter);
            }

            // Heuristic: only treat first row as headers if it clearly looks like headers.
            // Otherwise, keep ALL rows as data and generate synthetic headers Colonne A/B/C...
            $firstRow = $rows[0] ?? [];
            $headerTokens = [
                'date', 'jour', 'journal', 'codejournal', 'compte', 'tiers', 'libelle', 'debit', 'credit',
                'reference', 'piece', 'saisie', 'nsaisie', 'typesaisie', 'typeecriture'
            ];

            $looksLikeHeader = false;
            if (is_array($firstRow) && count($firstRow) > 1) {
                $hits = 0;
                foreach ($firstRow as $cell) {
                    $cell = trim((string)$cell);
                    if ($cell === '') continue;
                    $clean = strtolower(preg_replace('/[^a-z0-9]/', '', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $cell)));
                    if ($clean === '') continue;
                    foreach ($headerTokens as $t) {
                        if (str_contains($clean, $t)) { $hits++; break; }
                    }
                }
                // Need multiple strong hits to be confident it's a header row
                if ($hits >= 2) {
                    $looksLikeHeader = true;
                }
            }

            if ($looksLikeHeader) {
                $headers = array_shift($rows);
                $headers = array_map('trim', $headers);
            } else {
                $maxCols = 0;
                foreach ($rows as $r) {
                    if (is_array($r)) $maxCols = max($maxCols, count($r));
                }
                $headers = [];
                for ($i = 0; $i < $maxCols; $i++) {
                    $headers[] = "Colonne " . chr(65 + ($i % 26)) . ($i > 25 ? floor($i / 26) : '');
                }
                // Pad rows to maxCols so no column is lost in preview/mapping
                $rows = array_map(fn($r) => array_pad((array)$r, $maxCols, null), $rows);
            }
        }

        return [
            'headers' => $headers,
            'rows' => $rows
        ];
    }

    protected function parseHtml(string $filePath): array
    {
        $content = file_get_contents($filePath);
        // Suppress HTML parsing warnings
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($content);
        libxml_clear_errors();

        $rows = [];
        $headers = [];

        // Find the "biggest" table (heuristic: table with most rows usually contains data)
        $tables = $dom->getElementsByTagName('table');
        if ($tables->length === 0) throw new \Exception("Aucune table HTML trouvée.");

        $bestTable = null;
        $maxRows = 0;

        foreach ($tables as $table) {
            $trCount = $table->getElementsByTagName('tr')->length;
            if ($trCount > $maxRows) {
                $maxRows = $trCount;
                $bestTable = $table;
            }
        }

        if (!$bestTable) throw new \Exception("Table vide.");

        $trs = $bestTable->getElementsByTagName('tr');
        foreach ($trs as $rowIndex => $tr) {
            $cells = [];
            
            // Look for th
            $ths = $tr->getElementsByTagName('th');
            if ($ths->length > 0) {
                foreach ($ths as $th) $cells[] = trim($th->textContent);
                if (empty($headers)) {
                    $headers = $cells;
                    continue; 
                }
            }
            
            // Look for td
            $tds = $tr->getElementsByTagName('td');
            if ($tds->length > 0) {
                foreach ($tds as $td) $cells[] = trim($td->textContent);
            }

            // Fallback: If no th found, first row with checking is headers
            if (!empty($cells)) {
                if (empty($headers) && $rowIndex == 0) {
                    $headers = $cells;
                } else {
                    $rows[] = $cells;
                }
            }
        }

        return ['headers' => $headers, 'rows' => $rows];
    }
    
    protected function parseXml(string $filePath): array
    {
        // XML is tricky because structure varies.
        // We assume a flat list like <row><col1>...</col1></row>
        $xml = simplexml_load_file($filePath);
        if ($xml === false) throw new \Exception("Fichier XML invalide.");

        $json = json_encode($xml);
        $array = json_decode($json, true);

        // Heuristic: Find the array of rows. 
        // It could be root -> row[], or root -> sheet -> row[]
        // We flatten the structure to find the first list of arrays.
        
        $rows = [];
        // Walk specifically: usually XML exports have a wrapper
        // Let's try to detect a repeating element
        foreach ($array as $key => $value) {
            if (is_array($value) && isset($value[0])) {
                // Found a list!
                $rows = $value;
                break;
            }
        }
        
        // Fallback for direct root list
        if (empty($rows) && isset($array[0])) {
            $rows = $array;
        }

        if (empty($rows)) throw new \Exception("Structure XML non reconnue (liste plate attendue).");

        // Normalize rows (sometimes XML attributes vs values)
        // We take keys of the first row as headers
        $firstRow = $rows[0];
        if (!is_array($firstRow)) throw new \Exception("Les lignes XML doivent être des éléments structurés.");

        $headers = array_keys($firstRow);
        
        // Clean rows to simple values (handle attributes/CDATA if SimpleXML didn't)
        $cleanRows = [];
        foreach ($rows as $r) {
            $cleanRow = [];
            foreach ($headers as $h) {
                $val = $r[$h] ?? '';
                $cleanRow[] = is_array($val) ? json_encode($val) : trim((string)$val);
            }
            $cleanRows[] = $cleanRow;
        }

        return ['headers' => $headers, 'rows' => $cleanRows];
    }
}
