<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "TIERS >= 430:\n";
print_r(DB::table('plan_tiers')->where('id', '>=', 430)->get()->toArray());
