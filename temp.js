
        let currentFilter = 'all';

        function syncSameColInputs(input) {
            const col = input.dataset.col;
            const value = input.value;
            document.querySelectorAll(`.swal-edit-input[data-col="${col}"]`).forEach(other => {
                if (other !== input) other.value = value;
            });
        }

        function filterTable(type, event) {
            if (type) currentFilter = type;
            
            const searchText = document.getElementById('stagingSearch').value.toLowerCase();
            const rows = document.querySelectorAll('.table-staging tbody tr');
            
            rows.forEach(row => {
                const rowStatus = row.classList.contains('row-valid') ? 'valid' : 'row-error' ? 'error' : '';
                
                // Content Match (Search targets)
                let textMatch = true;
                if (searchText) {
                    const searchTargets = row.querySelectorAll('.search-target');
                    textMatch = Array.from(searchTargets).some(td => td.textContent.toLowerCase().includes(searchText));
                }

                // Status Match
                let statusMatch = true;
                if (currentFilter === 'valid') {
                    statusMatch = row.classList.contains('row-valid');
                } else if (currentFilter === 'error') {
                    statusMatch = row.classList.contains('row-error');
                }

                row.style.display = (textMatch && statusMatch) ? '' : 'none';
            });

            // Update active state of cards
            if (type) {
                document.querySelectorAll('.card-filter').forEach(card => card.classList.remove('active', 'border-primary'));
                if (currentFilter !== 'all') {
                    event.currentTarget.classList.add('active', 'border-primary');
                }
            }
        }

        function deleteStagingRow(importId, rowIndex) {
            Swal.fire({
                title: 'Supprimer cette ligne ?',
                text: "Cette action retirera définitivement la ligne de l'importation en cours.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler',
                customClass: {
                    confirmButton: 'btn btn-danger rounded-xl px-4 me-2',
                    cancelButton: 'btn btn-label-secondary rounded-xl px-4'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/admin/import/delete-row/${importId}/${rowIndex}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            Swal.fire('Erreur', data.message, 'error');
                        }
                    });
                }
            });
        }

        function editStagingRow(btn) {
            console.log("Edit Tiers - Button clicked", btn);
            try {
                const importId = btn.dataset.importId;
                const rowIndex = btn.dataset.rowIndex;
                const rawData = JSON.parse(btn.dataset.rawData);
                const mapping = JSON.parse(btn.dataset.mapping);

                console.log("Edit Tiers - Data parsed", { importId, rowIndex, rawData, mapping });

                let html = '<div class="text-start">';
                
                // On crée un champ pour chaque colonne mappée
                Object.entries(mapping).forEach(([fieldKey, colIndex]) => {
                    if (fieldKey.toLowerCase().includes('header') || colIndex === null || colIndex === "" || colIndex === "AUTO") return;
                    
                    let label = fieldKey.replace(/_/g, ' ').toUpperCase();
                    let val = rawData[colIndex] || "";
                    
                    html += `<div class="mb-3">
                                <label class="form-label text-xs font-bold text-slate-500">${label}</label>
                                <input type="text" class="form-control swal-edit-input" 
                                       data-field="${fieldKey}" 
                                       data-col="${colIndex}" 
                                       value="${val}"
                                       oninput="syncSameColInputs(this)">
                             </div>`;
                });
                html += '</div>';

                Swal.fire({
                    title: 'Modifier la ligne',
                    html: html,
                    showCancelButton: true,
                    confirmButtonText: 'Enregistrer',
                    cancelButtonText: 'Annuler',
                    customClass: {
                        confirmButton: 'btn btn-primary rounded-xl px-4 me-2',
                        cancelButton: 'btn btn-label-secondary rounded-xl px-4'
                    },
                    buttonsStyling: false,
                    preConfirm: () => {
                        let values = {};
                        let inputs = document.querySelectorAll('.swal-edit-input');
                        if (inputs.length === 0) return null;
                        inputs.forEach(input => {
                            if (input.dataset.col !== undefined) {
                                values[input.dataset.col] = input.value;
                            }
                        });
                        console.log("Staging Edit - Collected values:", values);
                        return values;
                    }
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        Swal.showLoading();
                        fetch(`/admin/import/update-row/${importId}/${rowIndex}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ values: result.value })
                        })
                        .then(async response => {
                            console.log("Staging Edit - Response status:", response.status);
                            const text = await response.text();
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                console.error("Staging Edit - Invalid JSON response:", text);
                                throw new Error(`Réponse serveur invalide (${response.status}).`);
                            }
                        })
                        .then(data => {
                            console.log("Staging Edit - Response data:", data);
                            if (data.success) {
                                window.location.reload();
                            } else {
                                Swal.fire('Erreur', data.message || 'Erreur lors de la mise à jour.', 'error');
                            }
                        })
                        .catch(err => {
                            console.error("Staging Edit - Fetch error:", err);
                            Swal.fire('Erreur', 'Détail : ' + err.message, 'error');
                        });
                    }
                });
            } catch (err) {
                console.error("Edit Tiers - Error in editStagingRow", err);
                Swal.fire('Erreur', 'Impossible d\'ouvrir le modal de modification : ' + err.message, 'error');
            }
        }

        function showRowDetails(btn) {
            const data = JSON.parse(btn.dataset.rowData);
            const errors = JSON.parse(btn.dataset.errors);
            let dataHtml = '<div class="text-start">';
            
            dataHtml += '<div class="bg-slate-50 p-4 rounded-2xl mb-4 border border-slate-100 shadow-inner">';
            dataHtml += '<h6 class="font-black text-[10px] uppercase text-slate-400 mb-3 tracking-widest">Fiche de la ligne</h6>';
            
            Object.entries(data).forEach(([key, val]) => {
                // Exclure les champs techniques
                if (val !== null && !key.toLowerCase().includes('header') && !['debit_val', 'credit_val', 'auto_num'].includes(key)) {
                    dataHtml += `<div class="d-flex justify-content-between border-bottom border-slate-100 py-2">
                                    <span class="text-xs font-bold text-slate-500">${key.replace(/_/g, ' ').toUpperCase()}</span>
                                    <span class="text-xs fw-black text-slate-800">${val}</span>
                                 </div>`;
                }
            });
            dataHtml += '</div>';

            if (errors && errors.length > 0) {
                dataHtml += '<div class="p-4 rounded-2xl bg-rose-50 border border-rose-100">';
                dataHtml += '<h6 class="font-black text-[10px] uppercase text-rose-600 mb-3 tracking-widest">Anomalies détectées</h6>';
                dataHtml += '<ul class="ps-4 mb-0">';
                errors.forEach(err => {
                    dataHtml += '<li class="text-rose-700 text-xs font-bold mb-1">' + err + '</li>';
                });
                dataHtml += '</ul></div>';
            } else {
                dataHtml += '<div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 d-flex align-items-center gap-3">';
                dataHtml += '<div class="bg-emerald-500 text-white p-2 rounded-full"><i class="fa-solid fa-check"></i></div>';
                dataHtml += '<div class="text-xs font-bold text-emerald-700">Cette ligne est prête pour l\'importation.</div></div>';
            }
            dataHtml += '</div>';

            Swal.fire({
                title: 'Détails de la ligne',
                html: dataHtml,
                icon: (errors && errors.length > 0) ? 'warning' : 'info',
                confirmButtonText: 'Fermer',
                customClass: {
                    confirmButton: 'btn btn-primary rounded-xl px-12 py-3'
                },
                buttonsStyling: false
            });
        }

        function quickCreateAccount(btn) {
            const numero = btn.dataset.compte;
            const libelle = btn.dataset.libelle;
            
            Swal.fire({
                title: 'Création du compte',
                text: `Voulez-vous créer le compte ${numero} - ${libelle} ?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Oui, créer',
                cancelButtonText: 'Annuler',
                customClass: {
                    confirmButton: 'btn btn-primary rounded-xl px-4 me-2',
                    cancelButton: 'btn btn-label-secondary rounded-xl px-4'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("{{ route('admin.import.quick_account') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            numero_compte: numero,
                            intitule: libelle,
                            type_de_compte: 'Bilan'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Succès',
                                text: data.message,
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Erreur', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Erreur', 'Une erreur est survenue lors de la création.', 'error');
                    });
                }
            });
        }

        function quickCreateGeneralAccount(baseNumero, requiredLength) {
            // Propose a padded number if shorter than required
            let proposedNumero = baseNumero.toString();
            if (proposedNumero.length < requiredLength) {
                proposedNumero = proposedNumero.padEnd(requiredLength, '0');
            } else if (proposedNumero.length > requiredLength) {
                proposedNumero = proposedNumero.substring(0, requiredLength);
            }

            Swal.fire({
                title: 'Créer le compte collectif',
                html: `
                    <div class="text-start">
                        <p class="text-sm text-slate-500 mb-3">Le système propose ce numéro de compte basé sur vos paramètres (${requiredLength} caractères max).</p>
                        <div class="mb-3">
                            <label class="form-label text-xs font-bold text-slate-500">Numéro de Compte</label>
                            <input type="text" id="swal-new-acc-num" class="form-control" value="${proposedNumero}" maxlength="${requiredLength}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-xs font-bold text-slate-500">Intitulé du Compte</label>
                            <input type="text" id="swal-new-acc-name" class="form-control" placeholder="Ex: FOURNISSEURS COLLECTIF" value="COMPTE COLLECTIF ${baseNumero}">
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Créer',
                cancelButtonText: 'Annuler',
                customClass: {
                    confirmButton: 'btn btn-primary rounded-xl px-4 me-2',
                    cancelButton: 'btn btn-label-secondary rounded-xl px-4'
                },
                buttonsStyling: false,
                preConfirm: () => {
                    const num = document.getElementById('swal-new-acc-num').value.trim();
                    const name = document.getElementById('swal-new-acc-name').value.trim();
                    if (!num || !name) {
                        Swal.showValidationMessage('Veuillez remplir tous les champs');
                        return false;
                    }
                    if (num.length !== requiredLength && requiredLength > 0) {
                        Swal.showValidationMessage(`Le numéro doit faire exactement ${requiredLength} caractères`);
                        return false;
                    }
                    return { numero: num, intitule: name };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.showLoading();
                    fetch("{{ route('admin.import.quick_account') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            numero_compte: result.value.numero,
                            intitule: result.value.intitule,
                            type_de_compte: 'Bilan'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Succès',
                                text: 'Création réussie. Actualisation en cours...',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Erreur', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Erreur', 'Une erreur est survenue lors de la création.', 'error');
                    });
                }
            });
        }

    