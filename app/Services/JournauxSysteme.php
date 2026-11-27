<?php

namespace App\Services;

use App\Models\CodeJournal;
use App\Models\Company;
use RuntimeException;

/**
 * Journaux système utilisés par la clôture : OD (résultat) et RAN (report à nouveau).
 *
 * Le journal est retrouvé par son TYPE, jamais par un code écrit en dur. S'il n'existe pas
 * pour l'entreprise, il est créé avec la même règle de code que « Charger les journaux
 * standards » : le code suit la longueur configurée (companies.journal_code_digits, 4 par défaut),
 * par exemple OD01 et RAN1 pour 4 caractères.
 */
class JournauxSysteme
{
    private const OD = ['prefix' => 'OD', 'intitule' => 'OPERATION DIVERSE', 'type' => 'Opérations Diverses',
        'types' => ['Opérations Diverses', 'OPERATIONS DIVERSES', 'Operations Diverses']];

    private const RAN = ['prefix' => 'RAN', 'intitule' => 'REPORT A NOUVEAU', 'type' => 'REPORT A NOUVEAU',
        'types' => ['REPORT A NOUVEAU', 'REPORT À NOUVEAU']];

    public static function operationsDiverses(int $companyId, ?int $userId = null): CodeJournal
    {
        return self::retrouverOuCreer($companyId, $userId, self::OD);
    }

    public static function reportANouveau(int $companyId, ?int $userId = null): CodeJournal
    {
        return self::retrouverOuCreer($companyId, $userId, self::RAN);
    }

    /** Même formule que AdminConfigController::loadStandardJournals(). */
    public static function formaterCode(string $prefix, int $numero, int $digits): string
    {
        $numStr = (string) $numero;
        $numLen = strlen($numStr);
        if ($numLen >= $digits) {
            return substr($numStr, -$digits);
        }
        $actualPrefix = substr($prefix, 0, $digits - $numLen);
        return $actualPrefix . str_pad($numStr, $digits - strlen($actualPrefix), '0', STR_PAD_LEFT);
    }

    private static function retrouverOuCreer(int $companyId, ?int $userId, array $modele): CodeJournal
    {
        $existant = CodeJournal::where('company_id', $companyId)
            ->whereIn('type', $modele['types'])->orderBy('id')->first();
        if ($existant) {
            return $existant;
        }

        $digits = (int) (Company::find($companyId)?->journal_code_digits ?: 4);
        for ($numero = 1; $numero <= 99; $numero++) {
            $code = self::formaterCode($modele['prefix'], $numero, $digits);
            if (CodeJournal::where('company_id', $companyId)->where('code_journal', $code)->exists()) {
                continue;
            }
            return CodeJournal::create([
                'code_journal' => $code,
                'intitule' => $modele['intitule'],
                'type' => $modele['type'],
                'traitement_analytique' => 0,
                'user_id' => $userId,
                'company_id' => $companyId,
            ]);
        }
        throw new RuntimeException('Impossible de créer le journal ' . $modele['intitule'] . ' : aucun code libre.');
    }
}
