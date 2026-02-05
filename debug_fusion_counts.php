<?php

use App\Models\Company;
use App\Models\PlanComptable;
use App\Models\CodeJournal;
use App\Models\PlanTiers;

$companies = Company::with('parent')->get();

foreach ($companies as $company) {
    echo "ID: {$company->id} - Name: {$company->company_name}\n";
    echo "  Parent ID: " . ($company->parent_company_id ?? 'None') . "\n";
    echo "  Is Active: " . ($company->is_active ? 'Yes' : 'No') . "\n";
    echo "  PlanComptable Count: " . PlanComptable::where('company_id', $company->id)->count() . "\n";
    echo "  CodeJournal Count: " . CodeJournal::where('company_id', $company->id)->count() . "\n";
    echo "  PlanTiers Count: " . PlanTiers::where('company_id', $company->id)->count() . "\n";
    echo "--------------------------------------------------\n";
}
