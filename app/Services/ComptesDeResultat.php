<?php

namespace App\Services;

use App\Models\PlanComptable;

/**
 * Les comptes qui portent le résultat de l'exercice.
 *
 * SYSCOHADA révisé, classe 13 « Résultat net de l'exercice » :
 *   1301  Résultat en instance d'affectation — Bénéfice   (soldé au CRÉDIT)
 *   1309  Résultat en instance d'affectation — Perte      (soldé au DÉBIT)
 *
 * Un bénéfice enrichit les capitaux propres : il se porte au crédit. Une perte
 * les diminue : elle se porte au débit. C'est l'inverse de l'intuition qu'on a
 * en regardant un relevé bancaire, et c'est la source d'erreur la plus
 * fréquente sur cette écriture.
 *
 * Les numéros sont complétés à la longueur du dossier (1301 devient 13010000
 * sur huit chiffres), on cherche donc par préfixe.
 */
class ComptesDeResultat
{
    public const BENEFICE = '1301';
    public const PERTE = '1309';

    /** Toute la classe 13 : résultat net de l'exercice. */
    public static function estCompteDeResultat(?string $numero): bool
    {
        return $numero !== null
            && (str_starts_with($numero, self::BENEFICE) || str_starts_with($numero, self::PERTE));
    }

    /**
     * Le compte à mouvementer pour un résultat donné.
     *
     * @param  float  $resultat  positif = bénéfice, négatif = perte
     */
    public static function pour(int $companyId, float $resultat): ?PlanComptable
    {
        $prefixe = $resultat >= 0 ? self::BENEFICE : self::PERTE;

        $compte = PlanComptable::where('company_id', $companyId)
            ->where('numero_de_compte', 'like', $prefixe . '%')
            ->orderBy('numero_de_compte')
            ->first();

        if ($compte) {
            return $compte;
        }

        // Seuls les comptes 1301 (bénéfice) et 1309 (perte) sont admis.
        return null;
    }

    /**
     * Le compte à mouvementer, créé au besoin.
     *
     * Tous les plans ne portent pas de compte 1301 ni 1309. La clôture se
     * contentait alors de NE PAS ÉCRIRE la ligne de résultat — silencieusement.
     * Le report à nouveau partait donc déséquilibré du montant exact du
     * résultat, sans que rien ne le signale.
     *
     * Le compte est désormais créé, à la longueur du dossier.
     */
    public static function pourOuCreer(int $companyId, float $resultat, ?int $userId = null): PlanComptable
    {
        $existant = self::pour($companyId, $resultat);

        if ($existant) {
            return $existant;
        }

        $digits = (int) (\App\Models\Company::find($companyId)?->account_digits ?? 8);
        $prefixe = $resultat >= 0 ? self::BENEFICE : self::PERTE;

        return PlanComptable::create([
            'company_id' => $companyId,
            'user_id' => $userId,
            'numero_de_compte' => str_pad($prefixe, max($digits, strlen($prefixe)), '0', STR_PAD_RIGHT),
            'intitule' => $resultat >= 0
                ? "Resultat en instance d'affectation - Benefice"
                : "Resultat en instance d'affectation - Perte",
            'adding_strategy' => 'auto',
            'classe' => '1',
        ]);
    }

    /**
     * Le sens et le montant à porter sur ce compte.
     *
     * @return array{debit: float, credit: float}
     */
    public static function sens(float $resultat): array
    {
        return $resultat >= 0
            ? ['debit' => 0.0, 'credit' => round($resultat, 2)]
            : ['debit' => round(abs($resultat), 2), 'credit' => 0.0];
    }

    /**
     * Une phrase lisible pour l'écran de clôture.
     */
    public static function annonce(float $resultat): string
    {
        $montant = number_format(abs($resultat), 0, ',', ' ');

        return $resultat >= 0
            ? "Bénéfice de $montant : il sera porté au CRÉDIT du compte de résultat (1301)."
            : "Perte de $montant : elle sera portée au DÉBIT du compte de résultat (1309).";
    }
}
