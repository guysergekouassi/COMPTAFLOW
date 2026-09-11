<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Le cabinet devient une entité à part entière.
 *
 * Jusqu'ici, « cabinet » ne désignait qu'un compte isolé : ses comptabilités
 * et ses collaborateurs n'étaient reliés que par leur créateur. Un cabinet a
 * pourtant un nom, un code, un gérant, des collaborateurs et des sociétés —
 * y compris celles que ses collaborateurs créent de leur côté.
 *
 * Les habilitations arrivent aussi sur le rattachement à une comptabilité :
 * les droits accordés à quelqu'un sur un dossier ne concernent que ce dossier,
 * sans toucher à ceux qu'il tient d'ailleurs.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cabinets')) {
            Schema::create('cabinets', function (Blueprint $table) {
                $table->id();
                $table->string('nom', 191);
                $table->string('code', 30)->unique();
                $table->unsignedBigInteger('user_id')->comment('Gérant du cabinet');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('user_id');
            });
        }

        if (!Schema::hasTable('cabinet_user')) {
            Schema::create('cabinet_user', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cabinet_id');
                $table->unsignedBigInteger('user_id');
                $table->string('role', 20)->default('collaborateur')->comment('gerant ou collaborateur');
                $table->timestamps();

                $table->unique(['cabinet_id', 'user_id']);
                $table->index('user_id');
            });
        }

        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'cabinet_id')) {
                $table->unsignedBigInteger('cabinet_id')->nullable()->after('parent_company_id');
                $table->index('cabinet_id');
            }
        });

        Schema::table('company_user', function (Blueprint $table) {
            if (!Schema::hasColumn('company_user', 'habilitations')) {
                // Droits accordés sur cette comptabilité seulement.
                $table->json('habilitations')->nullable()->after('role');
            }
        });
    }

    public function down(): void
    {
        Schema::table('company_user', function (Blueprint $table) {
            if (Schema::hasColumn('company_user', 'habilitations')) {
                $table->dropColumn('habilitations');
            }
        });

        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'cabinet_id')) {
                $table->dropIndex(['cabinet_id']);
                $table->dropColumn('cabinet_id');
            }
        });

        Schema::dropIfExists('cabinet_user');
        Schema::dropIfExists('cabinets');
    }
};
