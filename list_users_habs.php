<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

foreach (User::all() as $u) {
    echo "ID: {$u->id} | Email: {$u->email_adresse} | Role: {$u->role} | Habilitations: " . json_encode($u->habilitations) . "\n";
}
