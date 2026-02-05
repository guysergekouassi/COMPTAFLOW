<?php

namespace App\Services\Import;

use Illuminate\Support\Str;

class HeuristicAnalyzer
{
    /**
     * Dictionary of Synonyms (French/English/Abbr)
     * Key = DB Column, Value = Array of regex patterns
     */
    protected $dictionary = [
        'numero_de_compte' => ['/compte/i', '/account/i', '/^cpt\.?$/i', '/^n°?.*cp?t/i', '/client/i', '/fournisseur/i', '/général/i'],
        'intitule' => ['/intitulé/i', '/libellé/i', '/nom/i', '/label/i', '/description/i', '/raison.*soc/i'],
        'code_journal' => ['/^journal$/i', '/^jnl$/i', '/^code.?j/i', '/^cod.?jo/i'],
        'numero_de_tiers' => ['/^tiers$/i', '/^aux/i', '/^compte.*aux/i', '/^num.*tier/i', '/^client/i', '/^fournisseur/i'],
        
        // Entries specifics
        'date_ecriture' => ['/^date$/i', '/^jour$/i', '/^date.*ecr/i', '/^dt/i'],
        'debit' => ['/^debit$/i', '/^deb$/i', '/^d$/i', '/^montant.*deb/i'],
        'credit' => ['/^credit$/i', '/^cred$/i', '/^c$/i', '/^montant.*cred/i'],
        'piece_ref' => ['/^ref/i', '/^piece/i', '/^n°?.*piece/i', '/^facture/i', '/^doc/i'],
        'libelle' => ['/^libellé/i', '/^lib/i', '/^label/i', '/^ecriture/i', '/^description/i']
    ];

    /**
     * Analyze headers and sample data to propose a mapping.
     * @param array $headers List of file headers
     * @param array $sampleRows Sample data (used for type checking if needed)
     * @return array ['db_field' => 'header_name']
     */
    public function analyze(array $headers, array $sampleRows = []): array
    {
        $proposal = [];
        $usedHeaders = [];

        // Normalize Headers Logic
        // We iterate through our dictionary and try to find the BEST match in headers
        foreach ($this->dictionary as $dbField => $patterns) {
            
            foreach ($headers as $header) {
                if (in_array($header, $usedHeaders)) continue;

                // 1. Exact Match Check (Highest Priority)
                if (Str::slug($header, '_') === $dbField) {
                    $proposal[$dbField] = $header;
                    $usedHeaders[] = $header;
                    break; 
                }

                // 2. Regex Match
                foreach ($patterns as $pattern) {
                    // Remove accents for comparison
                    $normalizedHeader = Str::ascii($header);
                    
                    if (preg_match($pattern, $normalizedHeader)) {
                        $proposal[$dbField] = $header;
                        $usedHeaders[] = $header;
                        break 2; // Move to next dbField
                    }
                }
            }
        }

        // 3. Data Type Analysis (Heuristic refinement)
        // If we missed 'date_ecriture' but have a column that looks like dates, use it.
        // If we have 'debit' vs 'credit', distinguish by assumptions if ambiguous? 
        // For "Hyper Correct", we prefer NOT to guess if regex failed, to avoid false positives. 
        // We let the user decide in mapping step.

        return $proposal;
    }
}
