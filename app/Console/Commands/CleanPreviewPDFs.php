<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use File;

class CleanPreviewPDFs extends Command
{
    // Nom de la commande artisan
    protected $signature = 'previews:clean';

    // Description
    protected $description = 'Supprime les fichiers PDF de prévisualisation de plus de 24 heures';

    public function handle()
    {
        $directory = public_path('previews');
        $filesDeleted = 0;

        if (!file_exists($directory)) {
            $this->info('Le dossier previews n’existe pas.');
            return 0;
        }

        $files = File::files($directory);

        foreach ($files as $file) {
            // Vérifie si le fichier a été créé il y a plus de 24h
            if ($file->getCTime() < now()->subDay()->timestamp) {
                File::delete($file->getPathname());
                $filesDeleted++;
            }
        }

        $this->info("Suppression terminée. Fichiers supprimés : $filesDeleted");
        return 0;
    }
}
