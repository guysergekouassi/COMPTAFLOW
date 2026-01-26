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
        Schema::create('import_stagings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('source')->nullable(); // sage, sap, excel, etc.
            $table->string('type')->default('courant'); // initial, courant
            $table->string('file_name')->nullable();
            $table->longText('raw_data')->nullable(); // Données brutes JSON
            $table->text('mapping')->nullable(); // Mapping JSON
            $table->string('status')->default('pending'); // pending, staging, committed, error
            $table->text('error_log')->nullable();
            $table->timestamps();

            // $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_stagings');
    }
};
