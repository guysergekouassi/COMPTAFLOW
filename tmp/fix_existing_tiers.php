<?php

use App\Models\Company;
use App\Models\PlanTiers;
use App\Models\PlanComptable;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminConfigController;
use Illuminate\Support\Facades\Auth;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = new AdminConfigController();

$companies = Company::all();

foreach ($companies as $company) {
    echo "Processing Company: {$company->nom} (ID: {$company->id})\n";
    
    $user = \App\Models\User::where('company_id', $company->id)->first();
    if (!$user) {
        echo "  No user found for company, skipping.\n";
        continue;
    }
    Auth::login($user);
    
    $tiers = PlanTiers::where('company_id', $company->id)->get();
    echo "  Found " . count($tiers) . " tiers.\n";
    
    $digits = (int)($company->tier_digits ?? 8);
    $localMaxSeqs = []; // [prefix => current_max_seq_int]

    // Pre-calculate max sequences for this company
    foreach (['40', '41', '42', '43', '44', '45', '46', '47', '48', '49'] as $p) {
        $existing = PlanTiers::where('company_id', $company->id)
            ->where('numero_de_tiers', 'like', $p . '%')
            ->get();
        $max = 0;
        foreach ($existing as $et) {
            $suffix = substr($et->numero_de_tiers, strlen($p));
            if (is_numeric($suffix)) {
                $max = max($max, (int)$suffix);
            }
        }
        $localMaxSeqs[$p] = $max;
    }

    foreach ($tiers as $tier) {
        $currentNum = $tier->numero_de_tiers;
        $needsFix = false;
        if (strlen($currentNum) < 4) $needsFix = true;
        if (preg_match('/[a-zA-Z]/', $currentNum) && ($company->tier_id_type ?? 'numeric') === 'numeric') $needsFix = true;
        
        if ($needsFix) {
            $prefix = substr($currentNum, 0, 2);
            if (!isset($localMaxSeqs[$prefix])) {
                if ($tier->compte) {
                    $prefix = substr($tier->compte->numero_de_compte, 0, 2);
                } else {
                    $prefix = '40';
                }
            }
            
            $nextSeq = ($localMaxSeqs[$prefix] ?? 0) + 1;
            $localMaxSeqs[$prefix] = $nextSeq;
            
            $availableSpace = max(0, $digits - strlen($prefix));
            $newNum = $prefix . str_pad($nextSeq, $availableSpace, '0', STR_PAD_LEFT);
            
            try {
                $oldOriginal = $tier->numero_original;
                $newOriginal = $oldOriginal ?: $currentNum;
                
                $tier->update([
                    'numero_de_tiers' => $newNum,
                    'numero_original' => $newOriginal
                ]);
                echo "    Fixed Tier {$tier->id}: {$currentNum} -> {$newNum}\n";
            } catch (\Exception $e) {
                echo "    Error fixing Tier {$tier->id} ({$currentNum}): " . $e->getMessage() . "\n";
            }
        }
    }
}

echo "Cleanup finished.\n";
