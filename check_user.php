<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$user = User::where('email_adresse', 'admin@admin.com')->first();
if ($user) {
    echo "ID: {$user->id}\n";
    echo "Name: {$user->name} {$user->last_name}\n";
    echo "Role: {$user->role}\n";
    echo "Company ID: {$user->company_id}\n";
    echo "Is Admin: " . ($user->isAdmin() ? "YES" : "NO") . "\n";
    echo "Is SuperAdmin: " . ($user->isSuperAdmin() ? "YES" : "NO") . "\n";
    echo "Habilitations: " . json_encode($user->habilitations) . "\n";
} else {
    echo "User not found.\n";
}
