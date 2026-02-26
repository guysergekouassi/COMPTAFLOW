<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$companies = \App\Models\Company::all();
foreach ($companies as $c) {
    echo "ID: " . $c->id . " | Name: " . $c->company_name . "\n";
}
