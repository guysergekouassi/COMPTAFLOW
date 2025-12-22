# Liste des Commandes Git et Leurs Rôles

## Commandes de Base

### `git init`
**Rôle** : Initialise un nouveau dépôt Git dans le répertoire courant.

### `git clone <url>`
**Rôle** : Clone un dépôt distant existant sur la machine locale.

### `git status`
**Rôle** : Affiche l'état des fichiers dans le répertoire de travail et l'index.

### `git add <fichier>`
**Rôle** : Ajoute des fichiers à l'index pour le prochain commit.

### `git add .`
**Rôle** : Ajoute tous les fichiers modifiés à l'index.

### `git commit -m "message"`
**Rôle** : Enregistre les changements dans le dépôt avec un message descriptif.

### `git log`
**Rôle** : Affiche l'historique des commits.

### `git log --oneline`
**Rôle** : Affiche l'historique des commits de manière concise.

## Commandes de Branches

### `git branch`
**Rôle** : Liste toutes les branches locales.

### `git branch <nom>`
**Rôle** : Crée une nouvelle branche.

### `git branch -d <nom>`
**Rôle** : Supprime une branche locale.

### `git checkout <branche>`
**Rôle** : Bascule vers une branche existante.

### `git checkout -b <nom>`
**Rôle** : Crée et bascule vers une nouvelle branche.

### `git merge <branche>`
**Rôle** : Fusionne une branche dans la branche actuelle.

## Commandes de Synchronisation

### `git remote`
**Rôle** : Affiche les dépôts distants configurés.

### `git remote add <nom> <url>`
**Rôle** : Ajoute un nouveau dépôt distant.

### `git fetch`
**Rôle** : Récupère les changements depuis le dépôt distant sans les fusionner.

### `git pull`
**Rôle** : Récupère et fusionne les changements depuis le dépôt distant.

### `git push`
**Rôle** : Envoie les commits locaux vers le dépôt distant.

### `git push origin <branche>`
**Rôle** : Envoie une branche spécifique vers le dépôt distant.

## Commandes d'Historique

### `git diff`
**Rôle** : Affiche les différences entre le répertoire de travail et l'index.

### `git diff --staged`
**Rôle** : Affiche les différences entre l'index et le dernier commit.

### `git diff <branche1> <branche2>`
**Rôle** : Affiche les différences entre deux branches.

### `git show <commit>`
**Rôle** : Affiche les détails d'un commit spécifique.

### `git blame <fichier>`
**Rôle** : Affiche qui a modifié chaque ligne d'un fichier et quand.

## Commandes d'Annulation

### `git reset <fichier>`
**Rôle** : Retire un fichier de l'index.

### `git reset --hard`
**Rôle** : Annule tous les changements dans le répertoire de travail et l'index.

### `git reset --soft HEAD~1`
**Rôle** : Annule le dernier commit mais garde les changements dans l'index.

### `git revert <commit>`
**Rôle** : Crée un nouveau commit qui annule les changements d'un commit précédent.

### `git checkout -- <fichier>`
**Rôle** : Annule les modifications locales d'un fichier.

## Commandes de Stash

### `git stash`
**Rôle** : Sauvegarde temporairement les changements non commités.

### `git stash list`
**Rôle** : Affiche la liste des stashes.

### `git stash apply`
**Rôle** : Applique le dernier stash sans le supprimer.

### `git stash pop`
**Rôle** : Applique le dernier stash et le supprime.

### `git stash drop`
**Rôle** : Supprime le dernier stash.

## Commandes de Configuration

### `git config --global user.name "nom"`
**Rôle** : Définit le nom d'utilisateur global.

### `git config --global user.email "email"`
**Rôle** : Définit l'email utilisateur global.

### `git config --list`
**Rôle** : Affiche toutes les configurations Git.

## Commandes de Tag

### `git tag`
**Rôle** : Liste tous les tags.

### `git tag <nom>`
**Rôle** : Crée un nouveau tag sur le commit actuel.

### `git tag -a <nom> -m "message"`
**Rôle** : Crée un tag annoté avec un message.

### `git push --tags`
**Rôle** : Envoie tous les tags vers le dépôt distant.

## Commandes Avancées

### `git rebase <branche>`
**Rôle** : Réapplique les commits de la branche actuelle sur une autre branche.

### `git cherry-pick <commit>`
**Rôle** : Applique un commit spécifique sur la branche actuelle.

### `git bisect start`
**Rôle** : Démarre une recherche binaire pour trouver un commit problématique.

### `git clean -fd`
**Rôle** : Supprime les fichiers non suivis et les répertoires.

### `git reflog`
**Rôle** : Affiche l'historique complet des références (incluant les commits supprimés).

## Commandes de Sous-modules

### `git submodule add <url> <chemin>`
**Rôle** : Ajoute un sous-module au dépôt.

### `git submodule update --init`
**Rôle** : Initialise et met à jour les sous-modules.

### `git submodule foreach <commande>`
**Rôle** : Exécute une commande dans chaque sous-module.

## Commandes d'Archivage

### `git archive --format=zip HEAD > projet.zip`
**Rôle** : Crée une archive ZIP du projet à l'état actuel.

### `git bundle create <fichier> <branche>`
**Rôle** : Crée un bundle d'une branche pour le transfert.

## Commandes de Dépannage

### `git fsck`
**Rôle** : Vérifie l'intégrité du dépôt Git.

### `git gc`
**Rôle** : Nettoie et optimise le dépôt (garbage collection).

### `git prune`
**Rôle** : Supprime les objets inaccessibles du dépôt.

## Commandes de Patch

### `git format-patch <commit>`
**Rôle** : Crée des fichiers patch pour les commits.

### `git am <fichier.patch>`
**Rôle** : Applique un fichier patch créé par format-patch.

### `git apply <fichier.patch>`
**Rôle** : Applique un patch sans créer de commit.
