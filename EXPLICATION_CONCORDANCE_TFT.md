# Pourquoi les montants du TFT doivent être réalignés ?

Ce document explique la différence technique entre un rapport de trésorerie simple (mouvements de banque) et le **Tableau des Flux de Trésorerie (TFT)** selon les normes SYSCOHADA, et pourquoi l'ajout des composantes (Produits, Charges, BFR) est indispensable pour obtenir les mêmes totaux.

## 1. Le Conflit des deux Méthodes

### Rapport de Trésorerie Classique (Méthode Directe Pure)
Il ne regarde que les **comptes de classe 5 (Banque/Caisse)**. 
- Si vous avez payé un fournisseur : -100.
- Si vous avez reçu un virement client : +200.
- **Problème** : Il ignore tout ce qui est "en attente" (factures non payées) et les opérations de régularisation.

### Le TFT Standard SYSCOHADA (Méthode Indirecte)
Il part du **Résultat Net** de l'entreprise pour arriver à la trésorerie.
- Il prend le bénéfice (+300)
- Il retire ce qui n'est pas du cash (ex: Dotations aux Ammortissements)
- Il ajuste selon la **Variation du BFR** (Besoin en Fonds de Roulement).

## 2. L'Obstacle : Le "Décalage"
Imaginez cette situation :
1. Vous vendez pour **1 000 FCFA** (Produit).
2. Le client ne vous a pas encore payé (**Créance**).

- **Dans le Rapport de Banque** : Vous avez **0 FCFA** (car rien n'est rentré).
- **Dans le TFT Standard** : Vous avez **0 FCFA** aussi, mais le calcul est différent : `Produit (1000) - Augmentation Créance (1000) = 0`.

**C'est là que réside l'impossibilité :** sans intégrer les notions de "Produits encaissables" et de "Variations de BFR", le rapport personnalisé ne verrait que les mouvements physiques de billets. Or, le TFT SYSCOHADA est un document de **comptabilité d'engagement**.

## 3. La Solution : L'Approche Hybride
Pour que votre **TFT Personnalisé** affiche exactement les mêmes totaux que le **TFT Standard**, nous avons dû "traduire" les éléments du standard en format "Entrées/Sorties" :

| Composante Standard | Traduction dans votre TFT Personnalisé |
| :--- | :--- |
| **CAF (Produits - Charges)** | Les ventes et achats réels décomposés. |
| **Variation BFR (Créances/Dettes)** | Les hausses ou baisses de dettes/créances traitées comme des flux. |
| **Classe 2 (Immos)** | Les investissements (Acquisitions/Cessions). |
| **Classe 10/16 (Capitaux)** | Les financements (Apports/Emprunts). |

### Conclusion
Sans cet ajout, les montants seraient différents car le standard inclut la **variation des dettes et des créances** (le BFR), alors qu'un rapport simple ne les voit pas. En ajoutant ces composantes, nous garantissons que **la réalité économique (Standard) et la réalité de gestion (Personnalisé) se rejoignent enfin sur les mêmes chiffres.**
