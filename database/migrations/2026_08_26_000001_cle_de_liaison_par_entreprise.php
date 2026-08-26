<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Une clé de liaison par dossier, et non plus un seul secret pour tous.
 *
 * Selflow et Comptaflow s'authentifiaient par `EXTERNAL_SYNC_SECRET`, la même
 * valeur des deux côtés. Ce secret ne dit pas **quelle** entreprise appelle :
 * c'était le corps de la requête qui l'annonçait, et Comptaflow le croyait sur
 * parole. Quiconque détenait le secret pouvait donc déverser des écritures dans
 * les livres de n'importe quelle entreprise.
 *
 * Chaque dossier porte désormais sa propre clé, présentée en en-tête
 * `X-Company-Key`. Les colonnes ajoutées ici sont ce qui permet de la
 * reconnaître, de la refuser en la nommant, et de dater la liaison.
 *
 * ── Pourquoi un haché *et* une copie chiffrée ────────────────────────────────
 *
 * `selflow_sync_key` stockait la clé **en clair** : une lecture de la base — un
 * export, une sauvegarde, un compte de lecture seule — livrait de quoi écrire
 * dans les livres. La recherche se fait maintenant sur `selflow_sync_key_hash`,
 * un SHA-256 indexé et unique : la valeur d'origine n'est déductible d'aucune
 * colonne, et chaque déversement retrouve son dossier par un index plutôt que
 * par un balayage de table.
 *
 * La copie chiffrée existe pour une seule raison, et elle est nécessaire :
 * `companies/provision` doit être **idempotent**. Rappelé pour une entreprise
 * déjà provisionnée — validation cliquée deux fois, appel rejoué après un délai
 * réseau — il doit rendre *la même* clé plutôt qu'ouvrir un second dossier. Un
 * haché seul ne se retourne pas ; sans cette copie, chaque rappel devrait tirer
 * une nouvelle clé et invalider celle que Selflow venait de ranger.
 * Le chiffrement dépend de `APP_KEY`, qui ne vit pas dans la base : une lecture
 * de la base seule ne rend toujours rien.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'selflow_sync_key_hash')) {
                // 64 caractères : la longueur d'un SHA-256 en hexadécimal.
                $table->string('selflow_sync_key_hash', 64)->nullable()->unique()
                    ->after('selflow_sync_key');
            }
            if (!Schema::hasColumn('companies', 'selflow_sync_key_chiffree')) {
                $table->text('selflow_sync_key_chiffree')->nullable()
                    ->after('selflow_sync_key_hash');
            }
            if (!Schema::hasColumn('companies', 'selflow_sync_key_revoked_at')) {
                // Révoquée, pas effacée. Un appel refusé doit pouvoir dire
                // « clé révoquée le 12/03 » plutôt que « clé inconnue » : une
                // entreprise déliée par erreur passerait sinon la journée à
                // chercher une panne de réseau qui n'existe pas.
                $table->timestamp('selflow_sync_key_revoked_at')->nullable()
                    ->after('selflow_sync_key_chiffree');
            }
            if (!Schema::hasColumn('companies', 'selflow_linked_at')) {
                $table->timestamp('selflow_linked_at')->nullable()
                    ->after('selflow_sync_key_revoked_at');
            }
            if (!Schema::hasColumn('companies', 'selflow_last_deposit_at')) {
                // La date de **réception**. Selflow affiche aujourd'hui à
                // l'entreprise une date écrite au moment de l'*envoi* : elle
                // date une réception qui n'a pas forcément eu lieu.
                $table->timestamp('selflow_last_deposit_at')->nullable()
                    ->after('selflow_linked_at');
            }
        });

        $this->basculerLesClesEnClair();
        $this->unifierSelflowCompanyId();
    }

    /**
     * Les clés déjà posées en clair passent au haché + copie chiffrée.
     *
     * Sans ce passage, les entreprises liées avant ce lot n'auraient plus de
     * clé reconnaissable : leur premier déversement serait refusé alors que
     * rien n'a changé de leur côté.
     */
    private function basculerLesClesEnClair(): void
    {
        DB::table('companies')
            ->whereNotNull('selflow_sync_key')
            ->where('selflow_sync_key', '!=', '')
            ->orderBy('id')
            ->chunkById(200, function ($lignes) {
                foreach ($lignes as $ligne) {
                    DB::table('companies')->where('id', $ligne->id)->update([
                        'selflow_sync_key_hash'     => hash('sha256', $ligne->selflow_sync_key),
                        'selflow_sync_key_chiffree' => Crypt::encryptString($ligne->selflow_sync_key),
                        // La colonne en clair est vidée : c'est tout l'objet du
                        // changement. La valeur reste récupérable par la copie
                        // chiffrée, donc rien n'est perdu.
                        'selflow_sync_key'          => null,
                    ]);
                }
            });
    }

    /**
     * `selflow_company_id` devient unique.
     *
     * Deux dossiers Comptaflow rattachés à la même entreprise Selflow rendent
     * le déversement indéterminé : `where('selflow_company_id', …)->first()`
     * choisit alors le plus ancien, et la moitié des écritures d'une entreprise
     * peut partir dans des livres qui ne sont pas les siens.
     *
     * On refuse d'installer l'index en silence si des doublons existent déjà :
     * les taire reviendrait à laisser ouverte exactement la porte que cet index
     * ferme. La migration s'arrête en nommant les dossiers à trancher.
     */
    private function unifierSelflowCompanyId(): void
    {
        $doublons = DB::table('companies')
            ->select('selflow_company_id')
            ->whereNotNull('selflow_company_id')
            ->groupBy('selflow_company_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('selflow_company_id');

        if ($doublons->isNotEmpty()) {
            $detail = DB::table('companies')
                ->whereIn('selflow_company_id', $doublons)
                ->orderBy('selflow_company_id')
                ->get(['id', 'selflow_company_id'])
                ->map(fn ($c) => "dossier Comptaflow n° {$c->id} → entreprise Selflow n° {$c->selflow_company_id}")
                ->implode(' ; ');

            throw new RuntimeException(
                'Plusieurs dossiers Comptaflow sont rattachés à la même entreprise Selflow : '
                . $detail . '. Déliez les dossiers en trop (selflow_company_id à NULL) '
                . 'avant de rejouer cette migration : tant qu\'ils coexistent, un déversement '
                . 'peut atterrir dans les mauvais livres.'
            );
        }

        Schema::table('companies', function (Blueprint $table) {
            $table->unique('selflow_company_id', 'companies_selflow_company_id_unique');
        });
    }

    public function down(): void
    {
        // Les clés reviennent en clair, sans quoi les entreprises liées
        // perdraient leur liaison en revenant en arrière.
        DB::table('companies')
            ->whereNotNull('selflow_sync_key_chiffree')
            ->orderBy('id')
            ->chunkById(200, function ($lignes) {
                foreach ($lignes as $ligne) {
                    try {
                        $clair = Crypt::decryptString($ligne->selflow_sync_key_chiffree);
                    } catch (\Throwable $e) {
                        continue;
                    }
                    DB::table('companies')->where('id', $ligne->id)
                        ->update(['selflow_sync_key' => $clair]);
                }
            });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropUnique('companies_selflow_company_id_unique');
            $table->dropColumn([
                'selflow_sync_key_hash',
                'selflow_sync_key_chiffree',
                'selflow_sync_key_revoked_at',
                'selflow_linked_at',
                'selflow_last_deposit_at',
            ]);
        });
    }
};
