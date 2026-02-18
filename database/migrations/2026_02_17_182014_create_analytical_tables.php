<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Axes Analytiques
        Schema::create('axes_analytiques', function (Blueprint $table) {
            $table->id();
            $table->string('code')->index();
            $table->string('libelle');
            $table->string('type')->default('divers'); // Ex: 'projet', 'departement'
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Sections Analytiques
        Schema::create('sections_analytiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('axe_id')->constrained('axes_analytiques')->onDelete('cascade');
            $table->string('code')->index();
            $table->string('libelle');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Ventilations Analytiques
        Schema::create('ventilations_analytiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecriture_id')->constrained('ecriture_comptables')->onDelete('cascade');
            $table->foreignId('section_id')->constrained('sections_analytiques')->onDelete('cascade');
            $table->decimal('montant', 15, 2)->default(0);
            $table->decimal('pourcentage', 5, 2)->default(0);
            $table->timestamps();
        });

        // 4. Règles de Ventilation par compte
        Schema::create('regles_ventilation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_comptable_id')->constrained('plan_comptables')->onDelete('cascade');
            $table->foreignId('section_id')->constrained('sections_analytiques')->onDelete('cascade');
            $table->decimal('pourcentage_defaut', 5, 2)->default(100.00);
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regles_ventilation');
        Schema::dropIfExists('ventilations_analytiques');
        Schema::dropIfExists('sections_analytiques');
        Schema::dropIfExists('axes_analytiques');
    }
};
