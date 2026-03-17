# Documentation du Master Prompt IA - COMPTAFLOW

Cette documentation détaille le fonctionnement de l'IA (Gemini) au sein de COMPTAFLOW, spécifiquement pour les pages **Saisie par Scan (Simple)** et **Centre de Scan (Batch)**.

## 1. Flux de Données (Architecture)

Le système utilise une architecture client-serveur classique pour l'IA :

1.  **Frontend (Blade/JS)** : L'utilisateur importe une image ou un PDF. Le fichier est compressé localement (Canvas API) pour optimiser l'envoi.
2.  **Transmission** : Le fichier est envoyé via POST à la route `/ia/traiter` (`IaController@traiter`).
3.  **Backend (Laravel)** : 
    -   Récupère le **Plan Comptable** (comptes 6, 7, 4, 2, 5) de l'entreprise.
    -   Récupère la liste des **Tiers** existants.
    -   Récupère les **Mappings appris** (corrections passées).
    -   Construit un **Master Prompt** dynamique incluant ce contexte.
4.  **IA (Gemini)** : Reçoit l'image/PDF + le Prompt. Analyse le document selon les règles SYSCOHADA.
5.  **Retour** : L'IA renvoie un JSON strict que le contrôleur valide et transmet au frontend pour affichage dans le tableau d'écritures.

---

## 2. Le Master Prompt (Instructions Système)

C'est le "cerveau" de l'IA. Voici les instructions fondamentales configurées dans `IaController.php` :

### Rôles et Compétences
- Expert-Comptable SYSCOHADA Senior.
- Spécialiste de la zone OHADA (Afrique de l'Ouest).
- Capacité de déchiffrement de documents manuscrits ou de faible qualité.

### Principes de Classification
- **Biais d'acceptation élevé** : Par défaut, tout document est considéré comme une facture (`est_facture: true`).
- **Cas de rejets stricts** : Uniquement photos non comptables (paysages, personnes) ou fichiers totalement corrompus.
- **Tolérance** : Même lisible à 30%, l'IA doit tenter l'extraction.

### Règles Métier (Extraction)
- **TVA** : Si absente mais TTC présent, déduction automatique de 18% (Standard Côte d'Ivoire).
- **Date** : Si illisible, utilise la date du jour.
- **SYSCOHADA** : 
    - Charges → Classe 6 (Débit).
    - Fournisseurs → 401xxx (Crédit).
    - Immobilisations → Classe 2.
    - Produits → Classe 7 (Crédit) | Clients → 411xxx (Débit).

---

## 3. Spécification du Format JSON

L'IA doit obligatoirement répondre sous ce format JSON strict :

```json
{
  "est_facture": true,
  "statut_lecture": "lisible|partiel|illisible",
  "type_rejet": "none",
  "explication_rejet": "Note sur la qualité du document",
  "type_document": "Facture|Reçu|Note de frais|Relevé",
  "tiers": "Nom du commerce déduit",
  "date": "AAAA-MM-JJ",
  "reference": "Numéro de pièce ou vide",
  "montant_ht": 1000,
  "montant_tva": 180,
  "montant_ttc": 1180,
  "devise": "XOF",
  "ecriture": [
    {
      "compte": "601100",
      "intitule": "Achat de marchandises",
      "debit": 1000,
      "credit": 0,
      "apply_tva": true
    },
    {
      "compte": "401100",
      "intitule": "NOM FOURNISSEUR",
      "debit": 0,
      "credit": 1000
    }
  ],
  "analyse": "Raisonnement de l'IA"
}
```

---

## 4. Intégration dans les Pages

### Scan Simple (`scan.blade.php`)
- **Usage** : Un seul document à la fois.
- **Interface** : Affichage d'un tableau interactif immédiat.

### Centre de Scan (`bulk_scan.blade.php`)
- **Usage** : Analyse de masse (multi-upload).
- **Interface** : File d'attente circulaire. Chaque document devient une "carte" interactive.

---

## 5. Résilience (Modèles de secours)
Le système teste automatiquement plusieurs modèles si le premier échoue :
1. `gemini-2.0-flash`
2. `gemini-1.5-pro`
3. `gemini-1.5-pro-latest`
4. `gemini-pro-vision`
---
*Document généré le 17 Mars 2026 pour le projet COMPTAFLOW.*
