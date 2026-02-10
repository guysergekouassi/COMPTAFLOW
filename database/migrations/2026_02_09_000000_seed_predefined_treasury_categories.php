<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Company;
use App\Models\TreasuryCategory;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $categories = [
            'I. Flux de trésorerie des activités opérationnelles',
            'II. Flux de trésorerie des activités d\'investissement',
            'III. Flux de trésorerie des activités de financement',
        ];

        $companies = Company::all();

        foreach ($companies as $company) {
            foreach ($categories as $categoryName) {
                TreasuryCategory::firstOrCreate([
                    'name' => $categoryName,
                    'company_id' => $company->id,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionnel: On pourrait supprimer ces catégories, mais attention aux dépendances
        // DB::table('treasury_categories')->whereIn('name', [...])->delete();
    }
};
