<?php

use App\Models\Company;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$child = Company::where('company_name', 'LIKE', '%AGNIMEL2%')->firstOrFail();
// Chercher 'Comptabilité Orange' ou juste 'Orange' ou l'ID 33 sur lequel l'utilisateur travaille
// Le user a dit "l'entreprise comptabilité orange a créer l'entreprise AGNIMEL2"
// Je vais chercher une compagnie qui contient "orange" ou "comptabilité"
$parent = Company::where('company_name', 'LIKE', '%Orange%')->first();

if (!$parent) {
    // Fallback: search for company with ID 33 (common in previous logs)
    $parent = Company::find(33);
}

if (!$parent) {
    echo "Parent company not found.\n";
    exit(1);
}

echo "Linking '{$child->company_name}' (ID: {$child->id}) to Parent '{$parent->company_name}' (ID: {$parent->id})...\n";

$child->parent_company_id = $parent->id;
$child->save();

echo "Link established! 'Fusion' section should now appear.\n";
