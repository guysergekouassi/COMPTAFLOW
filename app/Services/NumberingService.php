<?php

namespace App\Services;

use App\Models\PlanComptable;
use App\Models\PlanTiers;
use Illuminate\Support\Facades\DB;

class NumberingService
{
    /**
     * Find next available sequential number for an account or tier.
     * 
     * @param string $type 'account' or 'tier'
     * @param int $companyId
     * @param string $originalNumber
     * @param int $digits Total length required
     * @param array $excludeNumbers Numbers already assigned in the current batch
     * @return string
     */
    public static function findNextAvailable($type, $companyId, $originalNumber, $digits, $excludeNumbers = [])
    {
        $table = $type === 'account' ? 'plan_comptables' : 'plan_tiers';
        $column = $type === 'account' ? 'numero_de_compte' : 'numero_de_tiers';
        
        // Use first 3 digits as root for accounts, or full original for tiers if specified
        $root = substr($originalNumber, 0, 3);
        if ($type === 'tier') {
            // For tiers, we often use the first 2-4 digits as root (e.g. 401, 411)
            $root = substr($originalNumber, 0, 3);
        }

        $current = $originalNumber;
        
        // Recursive check: increment until we find a hole
        while (true) {
            $existsInDb = DB::table($table)
                ->where('company_id', $companyId)
                ->where($column, $current)
                ->exists();
                
            $existsInBatch = in_array($current, $excludeNumbers);
            
            if (!$existsInDb && !$existsInBatch) {
                return $current;
            }
            
            // Increment logic
            $current = self::increment($current, $digits);
            
            // Safety break to prevent infinite loop
            if (strlen($current) > $digits + 2) break;
        }
        
        return $originalNumber; // Fallback
    }

    /**
     * Increment a string number by 1, preserving length and padding.
     */
    public static function increment($number, $digits)
    {
        $val = (int)$number;
        $next = $val + 1;
        
        // Pad to ensure it doesn't lose leading zeros if any (unlikely for accounts but possible)
        return str_pad((string)$next, $digits, '0', STR_PAD_RIGHT);
    }
}
