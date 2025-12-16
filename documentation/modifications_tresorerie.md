# Récapitulatif des Modifications

Ce document présente les modifications apportées pour deux fonctionnalités distinctes.

---

## 📌 Modification 1 : Filtrage des Comptes Généraux par Flux de Trésorerie

### Objectif
Lorsqu'un **Poste de trésorerie** est sélectionné, le champ **Compte Général** doit afficher uniquement les comptes liés au type de flux sélectionné.

### Fichiers Modifiés

#### 1️⃣ Backend : [EcritureComptableController.php](file:///c:/laragon/www/COMPTAFLOW/app/Http/Controllers/EcritureComptableController.php#L237-L276)

**Méthode modifiée :** `getComptesParFlux()` (lignes 237-276)

**Changement :** Ajout des classes 4 (Tiers) et 5 (Trésorerie) à tous les flux

```php
public function getComptesParFlux(Request $request) {
    $user = Auth::user();
    $typeFlux = $request->query('type');
    
    Log::info("AJAX getComptesParFlux called. TypeFlux received: '" . $typeFlux . "'");
    
    $query = PlanComptable::where('company_id', $user->company_id)
        ->select('id', 'numero_de_compte', 'intitule');

    // Filtrage selon la logique comptable des flux de trésorerie
    if ($typeFlux && stripos($typeFlux, 'Operationnelles') !== false) {
         Log::info("Matched: Operationnelles - Classes 4, 5, 6, 7");
        $query->where(function($q) {
            // ✅ AJOUT : Classes 4 et 5
            $q->where('numero_de_compte', 'like', '4%')
              ->orWhere('numero_de_compte', 'like', '5%')
              ->orWhere('numero_de_compte', 'like', '6%')
              ->orWhere('numero_de_compte', 'like', '7%');
        });
    } elseif ($typeFlux && stripos($typeFlux, 'Investissement') !== false) {
         Log::info("Matched: Investissement - Classes 2, 4, 5");
        $query->where(function($q) {
            // ✅ AJOUT : Classes 4 et 5
            $q->where('numero_de_compte', 'like', '2%')
              ->orWhere('numero_de_compte', 'like', '4%')
              ->orWhere('numero_de_compte', 'like', '5%');
        });
    } elseif ($typeFlux && stripos($typeFlux, 'Financement') !== false) {
         Log::info("Matched: Financement - Classes 1, 4, 5");
        $query->where(function($q) {
            // ✅ AJOUT : Classes 4 et 5
            $q->where('numero_de_compte', 'like', '1%')
              ->orWhere('numero_de_compte', 'like', '4%')
              ->orWhere('numero_de_compte', 'like', '5%');
        });
    }
    else {
         Log::info("No match found. Returning default limit 500.");
         $query->limit(500); 
    }

    $comptes = $query->orderBy('numero_de_compte', 'asc')->get();
    Log::info("Returning " . $comptes->count() . " accounts.");

    return response()->json($comptes);
}
```

**Résumé des changements :**
- ✅ Flux Opérationnelles : Ajout classes **4** et **5** (avant : seulement 6 et 7)
- ✅ Flux Investissement : Ajout classes **4** et **5** (avant : seulement 2)
- ✅ Flux Financement : Ajout classes **4** et **5** (avant : seulement 1 et 16)

---

#### 2️⃣ Frontend : [accounting_entry_real.blade.php](file:///c:/laragon/www/COMPTAFLOW/resources/views/accounting_entry_real.blade.php#L607-L731)

**Section modifiée :** Script JavaScript de filtrage (lignes 607-731)

**Changement principal :** Initialisation après ouverture du modal + réinitialisation du selectpicker

```javascript
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('=== Script de filtrage des comptes chargé ===');
        
        let eventsAttached = false;

        // ✅ NOUVEAU : Attendre l'ouverture du modal
        $('#modalCenterCreate').on('shown.bs.modal', function () {
            console.log('🔔 Modal ouvert - Initialisation du filtrage des comptes');
            
            if (eventsAttached) {
                return;
            }
            
            const compteTresorerieField = document.getElementById('compteTresorerieField');
            const compteGeneralSelect = document.getElementById('compte_general');
            const $compteGeneralSelect = $(compteGeneralSelect);
            const $compteTresorerieField = $(compteTresorerieField);
            const labelCompteGeneral = document.querySelector('label[for="compte_general"]');

            const apiAccountsUrl = "{{ route('api.comptes_par_flux') }}";

            function loadAccountsByFlow() {
                const selectedOption = compteTresorerieField.options[compteTresorerieField.selectedIndex];
                let flowType = '';
                if (selectedOption) {
                    flowType = selectedOption.getAttribute('data-type') || '';
                }
                
                console.log('=== loadAccountsByFlow appelée ===');
                console.log('Flow type:', flowType);
                
                if(labelCompteGeneral) {
                    labelCompteGeneral.textContent = "Compte Général (Chargement...)";
                    labelCompteGeneral.style.color = "red";
                }
                
                $compteGeneralSelect.prop('disabled', true);
                $compteGeneralSelect.selectpicker('refresh');

                fetch(`${apiAccountsUrl}?type=${encodeURIComponent(flowType)}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log(`✅ ${data.length} comptes reçus`);
                        
                        $compteGeneralSelect.empty();
                        
                        const fragment = document.createDocumentFragment();
                        data.forEach(account => {
                            const option = document.createElement('option');
                            option.value = account.id;
                            option.text = `${account.numero_de_compte} - ${account.intitule}`;
                            option.setAttribute('data-intitule_compte_general', account.numero_de_compte);
                            fragment.appendChild(option);
                        });
                        compteGeneralSelect.appendChild(fragment);

                        // ✅ NOUVEAU : Réinitialisation complète du selectpicker
                        $compteGeneralSelect.prop('disabled', false);
                        $compteGeneralSelect.selectpicker('destroy');
                        $compteGeneralSelect.selectpicker();
                        $compteGeneralSelect.selectpicker('refresh');
                        
                        if(labelCompteGeneral) {
                            labelCompteGeneral.textContent = "Compte Général";
                            labelCompteGeneral.style.color = "";
                        }
                        
                        console.log('✅ Comptes chargés et affichés avec succès');
                    })
                    .catch(error => {
                        console.error('❌ Erreur:', error);
                    });
            }

            if (compteTresorerieField) {
                // ✅ NOUVEAU : Un seul événement Bootstrap Select
                $compteTresorerieField.on('changed.bs.select', function(e) {
                    console.log('🔔 Événement CHANGED.BS.SELECT déclenché');
                    loadAccountsByFlow();
                });
                
                eventsAttached = true;
            }
        });
    });
</script>
```

**Résumé des changements :**
- ✅ Initialisation déplacée dans l'événement `shown.bs.modal`
- ✅ Ajout de `selectpicker('destroy')` puis `selectpicker()` pour réinitialiser
- ✅ Simplification : un seul événement `changed.bs.select`
- ✅ Logs de débogage détaillés

---

## 📌 Modification 2 : Exclusion Mutuelle Débit/Crédit

### Objectif
Rendre les champs **Débit** et **Crédit** mutuellement exclusifs selon le **Type de Flux** sélectionné.

### Fichier Modifié

#### Frontend : [accounting_entry_real.blade.php](file:///c:/laragon/www/COMPTAFLOW/resources/views/accounting_entry_real.blade.php#L799-L867)

**Section ajoutée :** Nouveau script JavaScript (lignes 799-867)

**Emplacement :** Après le script de filtrage des comptes

```javascript
<!-- Script pour gérer l'exclusion mutuelle Débit/Crédit selon le Type de Flux -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('=== Script d\'exclusion mutuelle Débit/Crédit chargé ===');
        
        let fluxEventsAttached = false;

        // ✅ Attendre l'ouverture du modal
        $('#modalCenterCreate').on('shown.bs.modal', function () {
            console.log('🔔 Modal ouvert - Initialisation de l\'exclusion mutuelle Débit/Crédit');
            
            if (fluxEventsAttached) {
                return;
            }
            
            const typeFluxSelect = document.getElementById('typeFlux');
            const debitInput = document.getElementById('debit');
            const creditInput = document.getElementById('credit');
            
            // ✅ Fonction pour gérer l'exclusion mutuelle
            function handleFluxTypeChange() {
                const selectedType = typeFluxSelect.value;
                console.log('🔄 Type de flux sélectionné:', selectedType);
                
                if (selectedType === 'debit') {
                    // ✅ Décaissement : activer Débit, désactiver Crédit
                    debitInput.disabled = false;
                    creditInput.disabled = true;
                    creditInput.value = ''; // Vider le champ Crédit
                    console.log('✅ Débit activé, Crédit désactivé');
                } else if (selectedType === 'credit') {
                    // ✅ Encaissement : activer Crédit, désactiver Débit
                    creditInput.disabled = false;
                    debitInput.disabled = true;
                    debitInput.value = ''; // Vider le champ Débit
                    console.log('✅ Crédit activé, Débit désactivé');
                }
            }
            
            if (typeFluxSelect) {
                // ✅ Événement Bootstrap Select
                $(typeFluxSelect).on('changed.bs.select', function(e) {
                    console.log('🔔 Événement changed.bs.select déclenché');
                    handleFluxTypeChange();
                });
                
                // ✅ Initialiser au chargement du modal
                handleFluxTypeChange();
                
                fluxEventsAttached = true;
            }
        });
    });
</script>
```

**Résumé des changements :**
- ✅ Nouveau script ajouté après le script de filtrage
- ✅ Écoute l'événement `changed.bs.select` sur le champ `typeFlux`
- ✅ Désactive et vide automatiquement le champ opposé
- ✅ S'initialise au chargement du modal

---

## 📊 Tableau Récapitulatif

| Modification | Fichier | Lignes | Type | Description |
|--------------|---------|--------|------|-------------|
| **1. Filtrage Backend** | `EcritureComptableController.php` | 237-276 | Backend | Ajout classes 4 et 5 à tous les flux |
| **1. Filtrage Frontend** | `accounting_entry_real.blade.php` | 607-731 | Frontend | Initialisation après modal + réinit selectpicker |
| **2. Exclusion Débit/Crédit** | `accounting_entry_real.blade.php` | 799-867 | Frontend | Nouveau script pour exclusion mutuelle |

---

## 🎯 Comportements Implémentés

### Modification 1 : Filtrage des Comptes

| Flux Sélectionné | Classes de Comptes Affichées |
|------------------|------------------------------|
| **Opérationnelles** | 4 (Tiers), 5 (Trésorerie), 6 (Charges), 7 (Produits) |
| **Investissement** | 2 (Immobilisations), 4 (Tiers), 5 (Trésorerie) |
| **Financement** | 1 (Capitaux), 4 (Tiers), 5 (Trésorerie) |

### Modification 2 : Exclusion Débit/Crédit

| Type de Flux Sélectionné | Champ Actif | Champ Désactivé |
|--------------------------|-------------|-----------------|
| **Décaissement (Débit)** | Débit ✅ | Crédit ❌ (vidé) |
| **Encaissement (Crédit)** | Crédit ✅ | Débit ❌ (vidé) |

---

## ✅ Points Clés Techniques

### Modification 1
1. **Backend** : Utilisation de `stripos()` pour détecter le type de flux
2. **Frontend** : Événement `shown.bs.modal` pour garantir l'initialisation
3. **Frontend** : Séquence `destroy()` → `selectpicker()` → `refresh()` pour réinitialiser

### Modification 2
1. **Timing** : Initialisation après ouverture du modal
2. **Événement** : `changed.bs.select` pour Bootstrap Select
3. **Logique** : Désactivation + vidage du champ opposé

---

**Document créé le :** 2025-12-16  
**Version :** 1.0  
**Statut :** ✅ Implémenté et testé
