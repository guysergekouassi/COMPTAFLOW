<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->integer('account_digits')->default(6)->change();
        });

        // Les dossiers encore sur l'ancien défaut de huit caractères passent
        // à six. Les réglages personnalisés (autres longueurs) sont conservés.
        DB::table('companies')->where('account_digits', 8)->update(['account_digits' => 6]);
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->integer('account_digits')->default(8)->change();
        });
    }
};
