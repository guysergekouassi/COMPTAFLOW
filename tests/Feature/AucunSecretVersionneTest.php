<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Aucun secret ne doit dormir dans le depot.
 *
 * `.env.example` a porte une vraie cle Gemini, et `.env.example2` un vrai mot
 * de passe de base et une `APP_KEY` — celle qui chiffre les cles de liaison
 * vers Selflow. Les deux fichiers ont ete nettoyes, mais **l'historique les
 * garde** : une fuite ne se retire pas, elle se revoque.
 *
 * Cette epreuve ne repare pas le passe. Elle empeche le prochain.
 */
class AucunSecretVersionneTest extends TestCase
{
    /**
     * Les valeurs qu'un fichier d'exemple a le droit de porter.
     *
     * @var array<int, string>
     */
    private const PLACEHOLDERS = ['', 'null', 'false', 'true', '""', "''"];

    public function test_les_fichiers_denvironnement_versionnes_ne_portent_aucun_secret(): void
    {
        $fautifs = [];

        foreach (glob(base_path('.env*')) ?: [] as $chemin) {
            $nom = basename($chemin);

            // Seul `.env.example` est suivi par git ; les autres sont locaux.
            if ($nom !== '.env.example') {
                continue;
            }

            foreach (file($chemin, FILE_IGNORE_NEW_LINES) as $ligne) {
                if (!preg_match('/^([A-Z0-9_]*(KEY|SECRET|TOKEN|PASSWORD|DSN))=(.*)$/', trim($ligne), $m)) {
                    continue;
                }

                $valeur = trim($m[3]);

                if (in_array($valeur, self::PLACEHOLDERS, true)) {
                    continue;
                }

                // Un gabarit explicite se reconnait : il dit ce qu'on attend.
                if (preg_match('/^(<.*>|\$\{.*\}|votre|your|changeme|xxx|\.\.\.)/i', $valeur)) {
                    continue;
                }

                $fautifs[] = "{$nom} : {$m[1]}";
            }
        }

        $this->assertSame([], $fautifs, implode("\n", [
            'Un fichier d\'environnement versionne porte une valeur renseignee.',
            'Committer un secret le publie pour de bon : le retirer ensuite ne le',
            'reprend pas, il faut le revoquer. Laisser la valeur vide.',
        ]));
    }

    public function test_aucun_second_fichier_denvironnement_nest_versionne(): void
    {
        // `.gitignore` ignore `.env.*` sauf `.env.example`. `.env.example2` avait
        // pourtant ete ajoute de force, et portait la cle qui chiffre les cles de
        // liaison.
        $suivis = [];
        exec('git ls-files ".env*" 2>&1', $suivis);

        $inattendus = array_values(array_filter(
            array_map('trim', $suivis),
            fn ($f) => $f !== '' && $f !== '.env.example'
        ));

        $this->assertSame([], $inattendus,
            'Seul `.env.example` a sa place dans le depot. Tout autre fichier '
            . 'd\'environnement y est arrive contre le `.gitignore`, donc de force.');
    }
}
