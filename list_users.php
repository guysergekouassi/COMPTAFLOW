<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$users = User::all();
echo "--- LISTE DES UTILISATEURS ---\n";
foreach ($users as $u) {
    echo "ID: {$u->id}, Name: {$u->name} {$u->last_name}, Role: {$u->role}, Company: {$u->company_id}\n";
}
