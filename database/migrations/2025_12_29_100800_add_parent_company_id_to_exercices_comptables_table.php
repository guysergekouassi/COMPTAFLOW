<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('exercices_comptables', function (Blueprint $table) {
            // Add the parent_company_id column
            $table->unsignedBigInteger('parent_company_id')->nullable()->after('company_id');
            
            // Add foreign key constraint
            $table->foreign('parent_company_id')
                  ->references('id')
                  ->on('companies')
                  ->onDelete('cascade');
        });
        
        // Update existing records to set parent_company_id based on company's parent
        \DB::statement('UPDATE exercices_comptables ec
            JOIN companies c ON ec.company_id = c.id
            SET ec.parent_company_id = COALESCE(c.parent_company_id, c.id)');
    }

    public function down()
    {
        Schema::table('exercices_comptables', function (Blueprint $table) {
            $table->dropForeign(['parent_company_id']);
            $table->dropColumn('parent_company_id');
        });
    }
};
