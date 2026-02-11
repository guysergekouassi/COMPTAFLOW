# Fonctionnement du Mapping des Postes de Trésorerie (Import & TFT)

Cette notice explique comment COMPTAFLOW gère automatiquement le lien entre vos écritures (même par centaines) et votre Tableau des Flux de Trésorerie (TFT).

## 1. Le Pivot : Le Plan Comptable Général
Le système se base sur une règle simple : **Un compte général (Classe 5) = Un Poste de Trésorerie**.

Lors de l'importation de 300+ écritures :
1. **Lecture** : Le moteur d'importation lit chaque ligne de votre fichier Excel.
2. **Identification** : Il repère le compte de trésorerie utilisé (ex: `52110000` pour la banque BOA).
3. **Traduction Automatique** : Le système consulte votre configuration des "Postes de Trésorerie". S'il voit que le compte `52110000` est lié au poste "BOA Agence Principale", il "étiquette" instantanément l'écriture avec ce poste.
   - *Avantage* : Vous n'avez pas besoin de remplir une colonne "Poste" dans votre Excel, le système le déduit de votre numéro de compte.

## 2. La Liaison avec le TFT (Flux)
Chaque **Poste de Trésorerie** possède une propriété appelée `syscohada_line_id`. C'est le "code secret" qui indique au rapport où ranger l'argent.

Exemple de configuration :
- Postes **Caisse** ou **Banque Courante** -> Activités Opérationnelles (par défaut).
- Poste **Vente Matériel** -> Activité d'Investissement (`INV_CESSION_IMMO`).
- Poste **Emprunt Bancaire** -> Activité de Financement (`FIN_EMPRUNT`).

## 3. Méthode de Calcul des Rapports
Quand vous ouvrez le **TFT Mensuel** :
- **Pour le Flux Opérationnel** : Le système utilise la "méthode indirecte". Il regarde toutes les charges (6) et produits (7) pour calculer la capacité d'autofinancement.
- **Pour l'Investissement et le Financement** : Le système utilise la **méthode directe**. Il scanne toutes les écritures portant une étiquette de poste de trésorerie configurée pour ces flux. 

## 4. Que se passe-t-il si je change le mapping ?
C'est la force du système : **Tout est dynamique**.
Si vous aviez importé 500 écritures sur un poste "Divers" et que vous décidez plus tard de lier ce poste à "Remboursement de dettes" :
1. Vous modifiez juste la fiche du **Poste de Trésorerie**.
2. Le rapport TFT se met à jour **immédiatement** au prochain chargement. 
3. Les 500 écritures n'ont pas besoin d'être modifiées une par une, car elles pointent vers le poste, et c'est le poste qui porte la destination du flux.

---
**En résumé :** Le système se base sur vos **numéros de comptes de classe 5** pour identifier les postes, et sur la **configuration des postes** pour déterminer le flux (Investissement, Financement ou Opérationnel).

## 5. Focus : Le "TFT Mensuel" et les imports massifs
Pour votre rapport **TFT Mensuel**, le système va plus loin que le simple mapping :
- **Répartition Temporelle** : Même si vous importez 300 écritures d'un coup, le système regarde la `date` de chaque ligne.
- **Ventilation par Colonne** : Il place automatiquement chaque montant dans la colonne du mois correspondant (Janvier, Février, etc.).
- **Calcul des Soldes** : Pour chaque mois, il fait la somme des encaissements et décaissements selon le mapping des postes.

**Pourquoi c'est puissant pour vous ?**
Si vous importez toute votre année en une seule fois via Excel, le **TFT Mensuel** se construira tout seul, mois par mois, en classant chaque opération dans la bonne catégorie (Opérationnelle, Investissement ou Financement) grâce aux postes de trésorerie.
