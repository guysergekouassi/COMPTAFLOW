<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MergeManagerAccountsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'compta:merge-accounts {main_email} {old_email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fusionne un ancien compte de gérant avec le compte principal et lui réaffecte toutes ses entreprises.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mainEmail = $this->argument('main_email');
        $oldEmail = $this->argument('old_email');

        $mainUser = User::where('email_adresse', $mainEmail)->first();
        $oldUser = User::where('email_adresse', $oldEmail)->first();

        if (!$mainUser) {
            $this->error("Compte principal introuvable pour l'email: {$mainEmail}");
            return Command::FAILURE;
        }

        if (!$oldUser) {
            $this->error("Ancien compte introuvable pour l'email: {$oldEmail}");
            return Command::FAILURE;
        }

        $this->info("Début de la fusion...");
        $this->info("Compte Principal: {$mainUser->name} {$mainUser->last_name} (ID: {$mainUser->id})");
        $this->info("Ancien Compte: {$oldUser->name} {$oldUser->last_name} (ID: {$oldUser->id})");

        DB::beginTransaction();
        try {
            // 1. Rattacher toutes les entreprises de l'ancien gérant
            $companies = Company::where('user_id', $oldUser->id)->get();
            $this->info("Nombre d'entreprises trouvées à rattacher : " . $companies->count());

            foreach ($companies as $company) {
                // Mettre à jour le propriétaire
                $company->update(['user_id' => $mainUser->id]);

                // Insérer dans la table pivot de liaison
                DB::table('company_user')->updateOrInsert(
                    ['company_id' => $company->id, 'user_id' => $mainUser->id],
                    ['role' => 'admin', 'created_at' => now(), 'updated_at' => now()]
                );
                
                $this->line("- Entreprise '{$company->company_name}' (ID: {$company->id}) rattachée.");
            }

            // 2. Mettre à jour les clés étrangères dans toutes les tables pertinentes de manière dynamique
            $tables = [
                'companies' => 'user_id',
                'ecriture_comptables' => 'user_id',
                'plan_comptables' => 'user_id',
                'plan_tiers' => 'user_id',
                'code_journals' => 'user_id',
                'exercices_comptables' => 'user_id',
                'journal_audits' => 'user_id',
                'tresoreries' => 'user_id',
            ];

            foreach ($tables as $table => $column) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                    $count = DB::table($table)->where($column, $oldUser->id)->count();
                    if ($count > 0) {
                        DB::table($table)->where($column, $oldUser->id)->update([$column => $mainUser->id]);
                        $this->line("- Table '{$table}' : {$count} lignes mises à jour pour user_id.");
                    }
                }
            }

            // 3. Supprimer le compte temporaire/ancien
            $oldUser->delete();
            $this->info("Ancien compte de gérant supprimé avec succès.");

            DB::commit();
            $this->info("Fusion terminée et enregistrée avec succès !");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Erreur lors de la fusion : " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
