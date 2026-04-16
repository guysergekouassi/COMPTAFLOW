<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->enum('app_name', ['RHFLOW', 'COMPTAFLOW', 'TASKFLOW', 'SELFLOW', 'LEGALFLOW']);
            $table->string('pack_name'); // Basic, Pro, Basic Edge, Pro Edge, Pro Max, Pro Master, Pro Day
            $table->decimal('prix_mensuel', 15, 2)->default(0); // en XOF
            $table->date('date_debut');
            $table->date('date_fin')->nullable(); // null = abonnement actif
            $table->enum('statut_paiement', ['paid', 'pending', 'overdue'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_subscriptions');
    }
};
