<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\LiasseMapping;

DB::statement('SET FOREIGN_KEY_CHECKS=0;');
LiasseMapping::truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

echo "Truncated liasse_mappings.\n";

\Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'LiasseMappingSeeder']);
echo "Seeded LiasseMappingSeeder.\n";

\Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'FakeEcrituresSeeder']);
echo "Seeded FakeEcrituresSeeder.\n";

echo "Done.\n";
