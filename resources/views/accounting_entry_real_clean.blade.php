<script>
                                                    // Message de succès qui disparaît après 2s
                                                    function showSuccessMessage(message) {
                                                        const successDiv = document.createElement('div');
                                                        successDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
                                                        successDiv.style.top = '20px';
                                                        successDiv.style.right = '20px';
                                                        successDiv.style.zIndex = '9999';
                                                        successDiv.style.minWidth = '300px';
                                                        successDiv.innerHTML = `
                                                            <i class="bx bx-check-circle me-2"></i>${message}
                                                        `;
                                                        document.body.appendChild(successDiv);
                                                        
                                                        setTimeout(() => {
                                                            successDiv.remove();
                                                        }, 2000);
                                                    }
                                                    
                                                    // Logique simplifiée pour l'affichage des comptes
                                                    document.addEventListener('DOMContentLoaded', function() {
                                                        const posteTresorerie = document.getElementById('compteTresorerieField');
                                                        const typeFlux = document.getElementById('typeFluxField');
                                                        const compteGeneral = document.getElementById('compte_general');
                                                        const debit = document.getElementById('debit');
                                                        const credit = document.getElementById('credit');
                                                        
                                                        // Attendre l'initialisation complète
                                                        setTimeout(function() {
                                                            const allOptions = Array.from(compteGeneral.options);
                                                            
                                                            // Sauvegarder l'option par défaut
                                                            const defaultOption = compteGeneral.querySelector('option[value=""]');
                                                            
                                                            // Filtrage des comptes selon le poste de trésorerie
                                                            posteTresorerie.addEventListener('change', function() {
                                                                const selectedPoste = this.value;
                                                                const selectedOption = this.options[this.selectedIndex];
                                                                const posteName = selectedOption ? selectedOption.text.toLowerCase() : '';
                                                                
                                                                // Vider le select sauf l'option par défaut
                                                                compteGeneral.innerHTML = '';
                                                                if (defaultOption) {
                                                                    compteGeneral.appendChild(defaultOption.cloneNode(true));
                                                                }
                                                                
                                                                if (!selectedPoste) {
                                                                    // Si aucun poste sélectionné, afficher TOUS les comptes
                                                                    allOptions.forEach(option => {
                                                                        if (option.value !== '') { // Ne pas dupliquer l'option par défaut
                                                                            compteGeneral.appendChild(option.cloneNode(true));
                                                                        }
                                                                    });
                                                                } else {
                                                                    // Filtrer selon la logique comptable basée sur les noms
                                                                    let filteredCount = 0;
                                                                    allOptions.forEach(option => {
                                                                        if (option.value === '') return; // Ignorer l'option par défaut
                                                                        
                                                                        const numeroCompte = option.getAttribute('data-intitule_compte_general');
                                                                        
                                                                        // Logique de filtrage selon le poste de trésorerie
                                                                        let shouldInclude = false;
                                                                        
                                                                        if (posteName.includes('banque') || posteName.includes('caisse')) {
                                                                            // Comptes de trésorerie (51xxx, 53xxx)
                                                                            shouldInclude = numeroCompte && (numeroCompte.startsWith('51') || numeroCompte.startsWith('53'));
                                                                        } else if (posteName.includes('achat') || posteName.includes('fournisseur')) {
                                                                            // Comptes fournisseurs (401xxx) et achats (60xxx)
                                                                            shouldInclude = numeroCompte && (numeroCompte.startsWith('401') || numeroCompte.startsWith('60'));
                                                                        } else if (posteName.includes('vente') || posteName.includes('client')) {
                                                                            // Comptes clients (411xxx) et ventes (70xxx)
                                                                            shouldInclude = numeroCompte && (numeroCompte.startsWith('411') || numeroCompte.startsWith('70'));
                                                                        } else if (posteName.includes('immobilisation')) {
                                                                            // Comptes d'immobilisations (2xxxx)
                                                                            shouldInclude = numeroCompte && numeroCompte.startsWith('2');
                                                                        } else if (posteName.includes('taxe') || posteName.includes('tva')) {
                                                                            // Comptes de TVA (445xxx)
                                                                            shouldInclude = numeroCompte && numeroCompte.startsWith('445');
                                                                        } else if (posteName.includes('capital')) {
                                                                            // Comptes de capital (1xxxx)
                                                                            shouldInclude = numeroCompte && numeroCompte.startsWith('1');
                                                                        } else {
                                                                            // Par défaut, inclure tous les comptes
                                                                            shouldInclude = true;
                                                                        }
                                                                        
                                                                        if (shouldInclude) {
                                                                            compteGeneral.appendChild(option.cloneNode(true));
                                                                            filteredCount++;
                                                                        }
                                                                    });
                                                                }
                                                            });
                                                            
                                                            // Gérer le crédit/débit selon le type de flux
                                                            typeFlux.addEventListener('change', function() {
                                                                const fluxValue = this.value;
                                                                
                                                                if (fluxValue === 'decaissement') {
                                                                    debit.disabled = false;
                                                                    credit.disabled = true;
                                                                    credit.value = '';
                                                                    credit.style.backgroundColor = '#f8f9fa';
                                                                    credit.style.boxShadow = '0 0 8px rgba(108, 117, 125, 0.4), inset 0 0 4px rgba(108, 117, 125, 0.2)';
                                                                    credit.style.border = '1px solid #ced4da';
                                                                    credit.style.cursor = 'not-allowed';
                                                                    debit.style.backgroundColor = '';
                                                                    debit.style.boxShadow = '';
                                                                    debit.style.border = '';
                                                                    debit.style.cursor = '';
                                                                } else if (fluxValue === 'encaissement') {
                                                                    debit.disabled = true;
                                                                    credit.disabled = false;
                                                                    debit.value = '';
                                                                    debit.style.backgroundColor = '#f8f9fa';
                                                                    debit.style.boxShadow = '0 0 8px rgba(108, 117, 125, 0.4), inset 0 0 4px rgba(108, 117, 125, 0.2)';
                                                                    debit.style.border = '1px solid #ced4da';
                                                                    debit.style.cursor = 'not-allowed';
                                                                    credit.style.backgroundColor = '';
                                                                    credit.style.boxShadow = '';
                                                                    credit.style.border = '';
                                                                    credit.style.cursor = '';
                                                                } else {
                                                                    debit.disabled = false;
                                                                    credit.disabled = false;
                                                                    debit.style.backgroundColor = '';
                                                                    credit.style.backgroundColor = '';
                                                                    debit.style.boxShadow = '';
                                                                    credit.style.boxShadow = '';
                                                                    debit.style.border = '';
                                                                    credit.style.border = '';
                                                                    debit.style.cursor = '';
                                                                    credit.style.cursor = '';
                                                                }
                                                            });
                                                            
                                                            // Logique existante pour les comptes tiers
                                                            const compteGeneral2 = document.getElementById('compte_general');
                                                            const compteTiersWrapper = document.getElementById('compte_tiers_wrapper');
                                                            const compteTiers = $('#compte_tiers');

                                                            compteGeneral2.addEventListener('change', function() {
                                                                const selectedOption = compteGeneral2.options[compteGeneral2.selectedIndex];
                                                                const numeroCompte = selectedOption.getAttribute('data-intitule_compte_general');

                                                                if (numeroCompte && numeroCompte.startsWith('4')) {
                                                                    compteTiersWrapper.style.display = 'block';
                                                                    $('#compte_tiers').selectpicker('val', '');
                                                                } else {
                                                                    compteTiersWrapper.style.display = 'none';
                                                                    $('#compte_tiers').selectpicker('val', '');
                                                                }
                                                            });
                                                        }, 1000);
                                                    });
                                                
                                                function ajouterEcriture() {
                                                    try {
                                                        // Récupérer les valeurs du formulaire
                                                        const date = document.getElementById('date');
                                                        const nSaisie = document.getElementById('n_saisie');
                                                        const libelle = document.getElementById('description_operation');
                                                        const debit = document.getElementById('debit');
                                                        const credit = document.getElementById('credit');
                                                        const posteTresorerie = document.getElementById('compteTresorerieField');
                                                        const typeFlux = document.getElementById('typeFluxField');
                                                        const compteGeneral = document.getElementById('compte_general');
                                                        const referencePiece = document.getElementById('reference_piece');
                                                        const compteTiers = document.getElementById('compte_tiers');
                                                        const pieceFile = document.getElementById('piece_justificatif');
                                                        const compteTresorerieId = posteTresorerie ? posteTresorerie.value : null;
                                                        const debit = document.getElementById('debit').value;
                                                        const credit = document.getElementById('credit').value;

                                                        // Logique demandée :
                                                        // Si Crédit est rempli -> Décaissement
                                                        // Si Débit est rempli -> Encaissement
                                                        if (parseFloat(credit.value) > 0) {
                                                            typeFluxAutomatique = 'decaissement';
                                                        } else if (parseFloat(debit) > 0) {
                                                            typeFluxAutomatique = 'encaissement';
                                                        }
                                                        // Validation des champs obligatoires
                                                        if (!date.value || !libelle.value || !compteGeneral.value) {
                                                            alert('Veuillez remplir tous les champs obligatoires (Date, Description, Compte Général).');
                                                            return;
                                                        }
                                                        
                                                        if (!debit.value && !credit.value) {
                                                            alert('Veuillez saisir un montant au débit ou au crédit.');
                                                            return;
                                                        }
                                                        
                                                        // Récupérer les valeurs affichées
                                                        const posteTresorerieValue = posteTresorerie ? posteTresorerie.options[posteTresorerie.selectedIndex].text : '';
                                                        const typeFluxValue = typeFlux ? typeFlux.options[typeFlux.selectedIndex].text : '';
                                                        const compteGeneralValue = compteGeneral.options[compteGeneral.selectedIndex].text;
                                                        const compteTiersValue = compteTiers && compteTiers.value ? compteTiers.options[compteTiers.selectedIndex].text : '';
                                                        
                                                        // Créer la nouvelle ligne
                                                        const tbody = document.querySelector('#ecrituresTable tbody');
                                                        const newRow = tbody.insertRow();
                                                        newRow.setAttribute('data-treso-id', compteTresorerieId);
                                                        // Ajouter les cellules
                                                        newRow.innerHTML = `
                                                            <td>${date.value}</td>
                                                            <td>${nSaisie.value}</td>
                                                            <td>${referencePiece.value || ''}</td>
                                                            <td>${libelle.value}</td>
                                                            <td>${compteGeneralValue}</td>
                                                            <td>${compteTiersValue}</td>
                                                            <td></td>
                                                            <td>${debit.value || ''}</td>
                                                            <td>${credit.value || ''}</td>
                                                            <td>${posteTresorerieValue}</td>
                                                            <td>${typeFluxValue}</td>
                                                            <td>${pieceFile.files[0] ? pieceFile.files[0].name : ''}</td>
                                                        `;
                                                        
                                                        // Ajouter un bouton de suppression
                                                        const actionCell = document.createElement('td');
                                                        const deleteBtn = document.createElement('button');
                                                        deleteBtn.className = 'btn btn-sm btn-danger';
                                                        deleteBtn.innerHTML = '<i class="bx bx-trash"></i>';
                                                        deleteBtn.onclick = function() {
                                                            if (confirm('Supprimer cette écriture ?')) {
                                                                newRow.remove();
                                                            }
                                                        };
                                                        actionCell.appendChild(deleteBtn);
                                                        newRow.appendChild(actionCell);
                                                        
                                                        // Réinitialiser le formulaire
                                                        libelle.value = '';
                                                        debit.value = '';
                                                        credit.value = '';
                                                        referencePiece.value = '';
                                                        pieceFile.value = '';
                                                        
                                                        // Réinitialiser les champs débit/crédit selon le type de flux
                                                        const fluxValue = typeFlux.value;
                                                        if (fluxValue === 'decaissement') {
                                                            credit.value = '';
                                                            credit.style.backgroundColor = '#f8f9fa';
                                                            credit.style.boxShadow = '0 0 8px rgba(108, 117, 125, 0.4), inset 0 0 4px rgba(108, 117, 125, 0.2)';
                                                            credit.style.border = '1px solid #ced4da';
                                                            credit.style.cursor = 'not-allowed';
                                                        } else if (fluxValue === 'encaissement') {
                                                            debit.value = '';
                                                            debit.style.backgroundColor = '#f8f9fa';
                                                            debit.style.boxShadow = '0 0 8px rgba(108, 117, 125, 0.4), inset 0 0 4px rgba(108, 117, 125, 0.2)';
                                                            debit.style.border = '1px solid #ced4da';
                                                            debit.style.cursor = 'not-allowed';
                                                        }
                                                        
                                                        // Afficher le message de succès
                                                        showSuccessMessage('Écriture ajoutée avec succès !');
                                                        
                                                    } catch (error) {
                                                        console.error('Erreur lors de l\'ajout de l\'écriture:', error);
                                                        alert('Une erreur est survenue lors de l\'ajout de l\'écriture.');
                                                    }
                                                }
                                            </script>
