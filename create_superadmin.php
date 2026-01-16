<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

// User data
$userData = [
    'id' => 94,
    'name' => 'agnimel',
    'last_name' => 'abraham',
    'email_adresse' => 'abraham@gmail.com',
    'role' => 'super_admin',
    'company_id' => null,
];

try {
    // Check if user exists
    $user = User::find(94);
    if ($user) {
        echo "User with ID 94 already exists. Updating...\n";
        $user->name = 'agnimel';
        $user->last_name = 'abraham';
        $user->email_adresse = 'abraham@gmail.com';
        $user->password = Hash::make('12345');
        $user->role = 'super_admin';
        $user->save();
    } else {
        echo "Creating new user...\n";
        $user = new User();
        $user->id = 94;
        $user->name = 'agnimel';
        $user->last_name = 'abraham';
        $user->email_adresse = 'abraham@gmail.com';
        $user->password = Hash::make('12345');
        $user->role = 'super_admin';
        $user->company_id = null;
        $user->save();
    }
    
    echo "User created/updated successfully.\n";
    echo "ID: " . $user->id . "\n";
    echo "Name: " . $user->name . " " . $user->last_name . "\n";
    echo "Email: " . $user->email_adresse . "\n";
    echo "Role: " . $user->role . "\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
