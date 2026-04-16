<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_honoraires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            // Services : COMPTABILITE, FISCALITE, SOCIAL_RH, JURIDIQUE, DROIT, AUDIT, CONSEIL
            $table->string('service_name');
            $table->text('description')->nullable();
            $table->decimal('prix_mensuel', 15, 2)->nullable(); // null = prix à définir
            $table->json('declarations')->nullable(); // ["CNPS","FNE","CMU","TE"]
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->enum('statut_paiement', ['paid', 'pending', 'overdue'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_honoraires');
    }
};
