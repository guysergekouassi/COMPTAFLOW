<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Config;

$userOrange = User::where('email_adresse', 'userorange@gmail.com')->first();
if ($userOrange) {
    $permissions = Config::get('accounting_permissions.role_permissions_map.comptable', []);
    $habs = [];
    foreach ($permissions as $p) {
        $habs[$p] = "1";
    }
    $userOrange->habilitations = $habs;
    $userOrange->save();
    echo "Permissions updated for userorange@gmail.com\n";
}
