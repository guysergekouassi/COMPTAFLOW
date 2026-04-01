# Documentation API Mobile - Scan par Lot

Cette documentation est destinée au collaborateur en charge du développement mobile. Elle détaille l'intégration du scan par lot et de l'IA OCR.

## 0. Authentification
Avant d'utiliser les endpoints de scan, l'utilisateur doit être connecté.

**Endpoint :** `POST /api/v1/login`
**Params :**
- `email`: (string)
- `password`: (string)
- `device_name`: (string, ex: "iPhone 15")

**Réponse :** Retourne un `token` à utiliser dans les headers suivants.

---

## Headers Communs
Tous les appels suivants nécessitent :
- **Authorization**: `Bearer {token}`
- **Accept**: `application/json`
- **X-Company-Id**: `{ID}` (L'ID de l'entreprise sélectionnée par l'utilisateur mobile)

---

## 1. Initialisation (Contexte)
Récupère les listes pour remplir les sélecteurs du formulaire de scan.

**Endpoint :** `GET /api/v1/scan/context`

**Données retournées :**
- `journals`: Liste des codes journaux (AC, VT, BQ, etc.)
- `plan_comptable`: Liste des comptes généraux (601100, 401100, etc.)
- `plan_tiers`: Liste des tiers (Fournisseurs, Clients)
- `axes`: Axes analytiques et leurs sections (si activés)
- `next_saisie_number`: Le numéro de saisie suggéré (ex: `CPT-AS_000000000042`)

---

## 2. Analyse OCR IA (Unitaire)
Appelé chaque fois que l'utilisateur prend une photo ou sélectionne un fichier.

**Endpoint :** `POST /api/v1/scan/upload`
**Body (form-data) :**
- `facture`: Fichier image ou PDF
- `journal_code`: (optionnel) ex: "AC" pour affiner la détection IA.

**Réponse JSON :**
- Contient les données extraites : `tiers`, `date`, `reference`, `montant_ttc`.
- **`ecriture`**: Propose déjà un équilibrage Débit/Crédit basé sur le plan comptable de l'entreprise.

---

## 3. Enregistrement Final (Batch Store)
Envoie toutes les factures validées par l'utilisateur.

**Endpoint :** `POST /api/v1/scan/batch-store`
**Body (JSON) :**
```json
{
  "entries": [
    {
      "date": "2024-03-15",
      "code_journal_id": 1,
      "n_saisie_user": "CPT-AS_000001",
      "reference": "FACT N° 45892",
      "description": "Achat fournitures",
      "lines": [
        {
          "plan_comptable_id": 10,
          "debit": 15000,
          "credit": 0,
          "plan_tiers_id": null
        },
        {
          "plan_comptable_id": 55,
          "debit": 0,
          "credit": 15000,
          "plan_tiers_id": 5,
          "ventilations": [
            { "section_id": 2, "montant": 15000, "pourcentage": 100 }
          ]
        }
      ]
    }
  ]
}
```

---

## Notes Importantes
1. **Validation**: Si le total Débit != Crédit dans une entrée, l'API renverra une erreur 500/422.
2. **Statut**: 
   - Si l'utilisateur est Admin : Les écritures sont créées en statut `approved` (écriture réelle).
   - Sinon : Statut `pending` (apparaît dans **Approbations** sur le Web).
3. **Optimisation**: Bien que le serveur compresse les images, il est recommandé de compresser les photos sur le téléphone avant l'upload pour économiser la bande passante.
4. **Erreurs**: Les erreurs retournent un JSON standard :
   - Erreur 422 (Validation) : `{ "message": "...", "errors": { "field": ["detail"] } }`
   - Erreur 500 (Serveur) : `{ "error": "Message d'erreur" }`

