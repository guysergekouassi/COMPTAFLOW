<?php

use App\Models\Cabinet;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Donner un cabinet aux titulaires qui en tenaient un sans le savoir.
 *
 * Avant l'entité cabinet, un gérant n'était relié à ses dossiers que par la
 * colonne créateur. On crée son cabinet, on lui donne un code, et l'on y
 * rattache ce qu'il porte déjà : ses comptabilités et les personnes qui y
 * travaillent. Rien n'est retiré à personne.
 */
return new class extends Migration
{
    public function up(): void
    {
        $titulaires = User::where('role', '!=', 'super_admin')
            ->where(function ($q) {
                $q->where('pack', 'cabinet')->orWhereNull('pack');
            })
            ->whereNull('created_by_id')
            ->get();

        $crees = 0;

        foreach ($titulaires as $titulaire) {
            if (Cabinet::where('user_id', $titulaire->id)->exists()) {
                continue;
            }

            $societes = Company::where('user_id', $titulaire->id)->get();

            // Un compte rattaché à une seule comptabilité qu'il n'a pas créée
            // n'est pas un cabinet : il tient son dossier. On ne lui en invente pas.
            if ($societes->isEmpty() && $titulaire->company_id) {
                continue;
            }
            if ($titulaire->pack === 'entreprise') {
                continue;
            }

            $nom = trim('Cabinet ' . trim($titulaire->name . ' ' . $titulaire->last_name));
            $cabinet = Cabinet::create([
                'nom'     => $nom,
                'code'    => Cabinet::genererCode($nom),
                'user_id' => $titulaire->id,
            ]);
            $crees++;

            DB::table('cabinet_user')->updateOrInsert(
                ['cabinet_id' => $cabinet->id, 'user_id' => $titulaire->id],
                ['role' => 'gerant', 'created_at' => now(), 'updated_at' => now()]
            );

            Company::where('user_id', $titulaire->id)->update(['cabinet_id' => $cabinet->id]);

            // Les personnes qui travaillent déjà sur ces dossiers en font partie
            $membres = DB::table('company_user')
                ->whereIn('company_id', $societes->pluck('id')->all() ?: [0])
                ->pluck('user_id')
                ->merge(User::where('created_by_id', $titulaire->id)->pluck('id'))
                ->unique()
                ->reject(fn ($id) => (int) $id === (int) $titulaire->id);

            foreach ($membres as $membreId) {
                DB::table('cabinet_user')->updateOrInsert(
                    ['cabinet_id' => $cabinet->id, 'user_id' => $membreId],
                    ['role' => 'collaborateur', 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        if ($crees) {
            echo '  Cabinets constitues : ' . $crees . PHP_EOL;
        }
    }

    public function down(): void
    {
        // Les cabinets créés ici portent désormais des rattachements réels :
        // les défaire ferait perdre le lien entre un gérant et ses dossiers.
    }
};
