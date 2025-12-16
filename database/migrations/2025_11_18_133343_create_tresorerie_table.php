     <?php

     use Illuminate\Database\Migrations\Migration;
     use Illuminate\Database\Schema\Blueprint;
     use Illuminate\Support\Facades\Schema;

     class CreateTresorerieTable extends Migration
     {
         public function up()
         {
            if (!Schema::hasTable('tresorerie')) {
             Schema::create('tresorerie', function ( $table) {
                 $table->id();
                 $table->string('code_journal')->unique();
                 $table->string('intitule');
                 $table->enum('traitement_analytique', ['oui', 'non'])->nullable();
                 $table->foreignId('compte_de_contrepartie')->constrained('plan_comptables')->onDelete('cascade');
                 $table->enum('rapprochement_sur', ['automatique', 'manuel'])->nullable();
                 $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                 $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
                 $table->timestamps();
             });
         }
        }
         public function down()
         {
             Schema::dropIfExists('tresorerie');
         }
     }
