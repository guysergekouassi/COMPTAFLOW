// saisie-grille.js
// Grille de saisie unique (Style SAGE) + conteneur Sage + liste des écritures.
// Support complet du switch de vue, assistant TVA, ventilation analytique, brouillons et modèles.

const saisieGrille = (() => {
  const {
    plansComptables, plansTiers, comptesTresorerie, idExercice, csrfToken,
    storeMultipleUrl, miseAJourMassiveUrl, ecritureModelesStoreUrl,
    ecritures, modelesSaisie,
  } = window.SAISIE_DATA;

  const body = document.getElementById('grilleBody'); // conservé par compatibilité mais non utilisé
  const inputRow = document.getElementById('inputRow');
  const addedLinesBody = document.getElementById('addedLinesBody');
  const listeBody = document.getElementById('listeEcrituresBody');
  const consultationBody = document.getElementById('listeConsultationBody');
  const hint = document.getElementById('contrepartieHint');
  const carte = document.getElementById('carteDesequilibre');
  const panel = document.getElementById('panelSaisie');

  let addedLines = [];
  let currentBatchId = null; // Stocke le batch_id si on charge un brouillon
  let filtreDesequilibre = false;
  let modeEdition = false;
  let nSaisieEnEdition = null;
  let viewMode = 'sage'; // 'sage' ou 'consultation'

  // Liste locale des modèles, alimentée par le serveur
  window.MODELES_SAISIE = Array.isArray(modelesSaisie) ? modelesSaisie.slice() : [];

  // ---------- Switch de Vue (Sage / Consultation) ----------
  function switchViewMode(mode) {
    viewMode = mode;
    const btnSage = document.getElementById('btnTabModeSage');
    const btnListe = document.getElementById('btnTabModeListe');
    const containerSage = document.getElementById('viewSageContainer');
    const containerConsultation = document.getElementById('viewConsultationContainer');

    if (mode === 'sage') {
      if (btnSage) { btnSage.className = 'btn btn-primary btn-sm px-3 fw-bold active'; }
      if (btnListe) { btnListe.className = 'btn btn-outline-primary btn-sm px-3 fw-bold bg-white'; }
      if (containerSage) containerSage.style.display = 'block';
      if (containerConsultation) containerConsultation.style.display = 'none';
      rafraichirListe();
    } else {
      if (btnSage) { btnSage.className = 'btn btn-outline-primary btn-sm px-3 fw-bold bg-white'; }
      if (btnListe) { btnListe.className = 'btn btn-primary btn-sm px-3 fw-bold active'; }
      if (containerSage) containerSage.style.display = 'none';
      if (containerConsultation) containerConsultation.style.display = 'block';
      appliquerFiltresConsultation();
    }
  }

  // ---------- Toggle Header Card (Ouvrir / Fermer En-tête) ----------
  function toggleHeaderCard() {
    const content = document.getElementById('headerCardContent');
    const icon = document.getElementById('iconToggleHeader');
    if (!content) return;

    if (content.style.display === 'none') {
      content.style.display = 'flex';
      if (icon) icon.className = 'bx bx-chevron-up';
    } else {
      content.style.display = 'none';
      if (icon) icon.className = 'bx bx-chevron-down';
    }
  }

  // ---------- Helpers options ----------
  function optionsComptes(selectedId) {
    return '<option value="">— Choisir —</option>' + plansComptables.map(p =>
      `<option value="${p.id}" data-numero="${p.numero_de_compte}" ${p.id == selectedId ? 'selected' : ''}>
        ${p.numero_de_compte} - ${p.intitule}
      </option>`
    ).join('');
  }

  function optionsTiers(compteGeneralId, selectedId) {
    let tiers = [];
    if (compteGeneralId) {
      tiers = plansTiers.filter(t => t.compte_general == compteGeneralId);
    }
    if (!tiers.length) {
      tiers = plansTiers;
    }
    if (!tiers.length) return '<option value="">— Aucun tiers —</option>';

    return '<option value="">— Sélectionner un tiers —</option>' + tiers.map(t =>
      `<option value="${t.id}" ${t.id == selectedId ? 'selected' : ''}>${t.numero_de_tiers} - ${t.intitule}</option>`
    ).join('');
  }

  function optionsPostes(selectedId) {
    return '<option value="">— Sélectionner poste —</option>' + comptesTresorerie.map(c =>
      `<option value="${c.id}" ${c.id == selectedId ? 'selected' : ''}>${c.name}</option>`
    ).join('');
  }

  function brancherBoutonPlus(btn, modalId, cible) {
    if (!btn) return;
    btn.addEventListener('click', () => {
      window._fcCibleCreation = { rowId: 'inputRow', cible };
      const modalEl = document.getElementById(modalId);
      if (modalEl && window.bootstrap) {
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
      }
    });
  }

  // Injection directe depuis les modales
  window.fcInjecterElementCree = function (type, item) {
    const compSelect = document.getElementById('input_compte_general');
    const tiersSelect = document.getElementById('input_compte_tiers');
    const posteSelect = document.getElementById('input_poste_treso');

    if (type === 'compte') {
      plansComptables.push(item);
      if (compSelect) {
        compSelect.innerHTML = optionsComptes(item.id);
        onCompteChange();
        if (window.jQuery && typeof jQuery.fn.select2 === 'function') {
          $(compSelect).trigger('change.select2');
        }
      }
    } else if (type === 'tiers') {
      plansTiers.push(item);
      if (tiersSelect) {
        tiersSelect.innerHTML = optionsTiers(compSelect ? compSelect.value : '', item.id);
        if (window.jQuery && typeof jQuery.fn.select2 === 'function') {
          $(tiersSelect).trigger('change.select2');
        }
      }
    } else if (type === 'poste') {
      comptesTresorerie.push(item);
      if (posteSelect) {
        posteSelect.innerHTML = optionsPostes(item.id);
        if (window.jQuery && typeof jQuery.fn.select2 === 'function') {
          $(posteSelect).trigger('change.select2');
        }
      }
    }
    window._fcCibleCreation = null;
  };

  // ---------- Gestion Compte Général -> Tiers et Poste de Trésorerie ----------
  function onCompteChange() {
    const compSelect = document.getElementById('input_compte_general');
    const tiersSelect = document.getElementById('input_input_compte_tiers') || document.getElementById('input_compte_tiers');
    const plusTiersBtn = document.querySelector('.btn-plus-tiers');
    const posteSelect = document.getElementById('input_poste_treso');
    const plusPosteBtn = document.getElementById('input_btn_plus_poste');

    if (!compSelect) return;

    const compId = compSelect.value;
    const compObj = plansComptables.find(p => p.id == compId);
    const numero = compObj ? String(compObj.numero_de_compte).trim() : '';

    // Classe 4 -> Tiers dégrisé
    const isClasse4 = numero.startsWith('4');
    if (tiersSelect) {
      if (!isClasse4) {
        tiersSelect.value = '';
      }
      tiersSelect.innerHTML = optionsTiers(isClasse4 ? compId : '', tiersSelect.value);
      if (window.jQuery && typeof jQuery.fn.select2 === 'function') {
        $(tiersSelect).val(isClasse4 ? tiersSelect.value : '').prop('disabled', !isClasse4).trigger('change.select2');
      } else {
        tiersSelect.disabled = !isClasse4;
      }
    }
    if (plusTiersBtn) plusTiersBtn.disabled = !isClasse4;

    // Classe 5 -> Poste Trésorerie dégrisé
    const isClasse5 = numero.startsWith('5');
    if (posteSelect) {
      posteSelect.disabled = !isClasse5;
      if (!isClasse5) posteSelect.value = '';
    }
    if (plusPosteBtn) plusPosteBtn.disabled = !isClasse5;
  }

  // ---------- Exclusion Mutuelle Débit / Crédit ----------
  function handleDebitCreditExclusion(e) {
    const debitInput = document.getElementById('input_debit');
    const creditInput = document.getElementById('input_credit');
    if (!debitInput || !creditInput) return;

    const debitVal = parseFloat(debitInput.value) || 0;
    const creditVal = parseFloat(creditInput.value) || 0;

    if (debitVal > 0) {
      creditInput.value = '';
      creditInput.disabled = true;
      creditInput.style.backgroundColor = '#f1f5f9';
    } else {
      creditInput.disabled = false;
      creditInput.style.backgroundColor = '';
    }

    if (creditVal > 0) {
      debitInput.value = '';
      debitInput.disabled = true;
      debitInput.style.backgroundColor = '#f1f5f9';
    } else {
      debitInput.disabled = false;
      debitInput.style.backgroundColor = '';
    }

    calculerTotaux();
  }

  // ---------- Rendu des lignes ajoutées (addedLines) ----------
  function rafraichirAddedLines() {
    if (!addedLinesBody) return;
    addedLinesBody.innerHTML = '';

    addedLines.forEach((line, index) => {
      const compObj = plansComptables.find(p => p.id == line.plan_comptable_id);
      const tiersObj = plansTiers.find(t => t.id == line.plan_tiers_id);
      const posteObj = comptesTresorerie.find(c => c.id == line.poste_tresorerie_id);

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="fw-bold">${compObj ? compObj.numero_de_compte : '—'}</td>
        <td>${tiersObj ? tiersObj.numero_de_tiers : '—'}</td>
        <td class="text-end fw-semibold text-primary">${line.debit > 0 ? line.debit.toLocaleString('fr-FR') : '—'}</td>
        <td class="text-end fw-semibold text-primary">${line.credit > 0 ? line.credit.toLocaleString('fr-FR') : '—'}</td>
        <td>${posteObj ? posteObj.name : '—'}</td>
        <td class="text-center">—</td>
        <td class="text-center">${line.plan_analytique ? '<i class="bx bx-check text-success"></i>' : '—'}</td>
        <td class="text-center">
          <i class="bx bx-edit-alt text-warning me-2" style="cursor:pointer;font-size:16px" title="Modifier" onclick="saisieGrille.editerLigneEnCours(${index})"></i>
          <i class="bx bx-trash text-danger" style="cursor:pointer;font-size:16px" title="Retirer" onclick="saisieGrille.supprimerLigneEnCours(${index})"></i>
        </td>
      `;
      addedLinesBody.appendChild(tr);
    });

    calculerTotaux();
  }

  // ---------- Ajouter une ligne au groupe en cours ----------
  function ajouterLigneEnCours(clearFields = true) {
    const compSelect = document.getElementById('input_compte_general');
    const tiersSelect = document.getElementById('input_compte_tiers');
    const debitInput = document.getElementById('input_debit');
    const creditInput = document.getElementById('input_credit');
    const posteSelect = document.getElementById('input_poste_treso');

    // Validation complète avec liste des champs manquants
    if (clearFields !== false) { // Ne pas re-valider lors d'un auto-ajout interne
      const journalId = document.getElementById('code_journal_id')?.value || '';
      const libelle = document.getElementById('description_operation')?.value?.trim() || '';
      const manquants = [];
      if (!journalId) manquants.push('Journal');
      if (!libelle) manquants.push('Libellé de l\'opération');
      if (!compSelect || !compSelect.value) manquants.push('Compte général');
      const debitVal = parseFloat(debitInput.value) || 0;
      const creditVal = parseFloat(creditInput.value) || 0;
      if (debitVal <= 0 && creditVal <= 0) manquants.push('Débit ou Crédit');

      if (manquants.length > 0) {
        if (window.Swal) Swal.fire({
          icon: 'warning',
          title: 'Champs manquants',
          html: `<p>Veuillez renseigner les champs suivants avant d'ajouter la ligne :</p><ul class="text-start mt-2">${manquants.map(m => `<li><strong>${m}</strong></li>`).join('')}</ul>`,
        });
        return false;
      }
    }

    if (!compSelect || !compSelect.value) {
      if (window.Swal) Swal.fire('Compte général manquant', 'Veuillez sélectionner un compte général.', 'warning');
      return false;
    }

    const debit = parseFloat(debitInput.value) || 0;
    const credit = parseFloat(creditInput.value) || 0;

    if (debit <= 0 && credit <= 0) {
      if (window.Swal) Swal.fire('Montant manquant', 'Veuillez saisir un montant au débit ou au crédit.', 'warning');
      return false;
    }

    const vnts = inputRow.dataset.ventilations ? JSON.parse(inputRow.dataset.ventilations) : [];

    // Push line to temporary array
    addedLines.push({
      id: inputRow.dataset.ecritureId || null,
      plan_comptable_id: compSelect.value,
      plan_tiers_id: tiersSelect ? tiersSelect.value : null,
      debit: debit,
      credit: credit,
      poste_tresorerie_id: posteSelect ? posteSelect.value : null,
      plan_analytique: vnts.length > 0 ? 1 : 0,
      ventilations: vnts
    });

    if (clearFields) {
      // Reset input fields
      compSelect.value = '';
      if (window.jQuery && typeof jQuery.fn.select2 === 'function') {
        $(compSelect).trigger('change.select2');
      }
      if (tiersSelect) {
        tiersSelect.value = '';
        if (window.jQuery && typeof jQuery.fn.select2 === 'function') {
          $(tiersSelect).trigger('change.select2');
        }
      }
      debitInput.value = '';
      creditInput.value = '';
      debitInput.disabled = false;
      debitInput.style.backgroundColor = '';
      creditInput.disabled = false;
      creditInput.style.backgroundColor = '';
      if (posteSelect) posteSelect.value = '';
      inputRow.dataset.ventilations = '[]';
      inputRow.dataset.ecritureId = '';
    }

    rafraichirAddedLines();
    return true;
  }

  function editerLigneEnCours(index) {
    const line = addedLines[index];
    if (!line) return;

    const compSelect = document.getElementById('input_compte_general');
    const tiersSelect = document.getElementById('input_compte_tiers');
    const debitInput = document.getElementById('input_debit');
    const creditInput = document.getElementById('input_credit');
    const posteSelect = document.getElementById('input_poste_treso');

    if (compSelect) {
      compSelect.value = line.plan_comptable_id;
      onCompteChange();
      if (window.jQuery && typeof jQuery.fn.select2 === 'function') {
        $(compSelect).trigger('change.select2');
      }
    }

    if (tiersSelect && line.plan_tiers_id) {
      tiersSelect.value = line.plan_tiers_id;
      if (window.jQuery && typeof jQuery.fn.select2 === 'function') {
        $(tiersSelect).trigger('change.select2');
      }
    }

    if (debitInput) {
      debitInput.value = line.debit || '';
      debitInput.disabled = !!line.credit;
      debitInput.style.backgroundColor = line.credit ? '#f1f5f9' : '';
    }

    if (creditInput) {
      creditInput.value = line.credit || '';
      creditInput.disabled = !!line.debit;
      creditInput.style.backgroundColor = line.debit ? '#f1f5f9' : '';
    }

    if (posteSelect && line.poste_tresorerie_id) {
      posteSelect.value = line.poste_tresorerie_id;
    }

    inputRow.dataset.ventilations = JSON.stringify(line.ventilations || []);
    inputRow.dataset.ecritureId = line.id || '';

    // Remove from array and refresh
    addedLines.splice(index, 1);
    rafraichirAddedLines();
  }

  function supprimerLigneEnCours(index) {
    addedLines.splice(index, 1);
    rafraichirAddedLines();
  }

  // ---------- Calculs globaux Débit / Crédit ----------
  function calculerTotaux() {
    let debit = 0;
    let credit = 0;

    // 1. Somme des lignes déjà ajoutées
    addedLines.forEach(l => {
      debit += l.debit;
      credit += l.credit;
    });

    // 2. Plus le montant de l'input courant en cours de frappe
    const curDebit = parseFloat(document.getElementById('input_debit')?.value || 0);
    const curCredit = parseFloat(document.getElementById('input_credit')?.value || 0);

    const totalDebitVal = debit + curDebit;
    const totalCreditVal = credit + curCredit;

    document.getElementById('totalDebit').textContent = totalDebitVal.toLocaleString('fr-FR');
    document.getElementById('totalCredit').textContent = totalCreditVal.toLocaleString('fr-FR');

    const badge = document.getElementById('balanceBadge');
    const btnValider = document.getElementById('btnValiderGrille');
    const ecart = Math.abs(totalDebitVal - totalCreditVal);

    const equilibre = ecart < 0.01 && totalDebitVal > 0 && (addedLines.length >= 1 || (curDebit > 0 || curCredit > 0));

    if (equilibre) {
      badge.textContent = 'Équilibré';
      badge.className = 'badge bg-success rounded-pill px-3 py-2';
      if (btnValider) {
        btnValider.disabled = false;
        btnValider.classList.add('btn-balanced-active');
      }
      masquerCarteDesequilibre();
    } else {
      badge.textContent = totalDebitVal === totalCreditVal ? 'Non équilibré' : `Écart ${ecart.toLocaleString('fr-FR')}`;
      badge.className = 'badge bg-danger rounded-pill px-3 py-2';
      if (btnValider) {
        btnValider.disabled = true;
        btnValider.classList.remove('btn-balanced-active');
      }
      if (totalDebitVal > 0 || totalCreditVal > 0) {
        afficherCarteDesequilibre(totalDebitVal, totalCreditVal);
      }
    }
  }

  function afficherCarteDesequilibre(debit, credit) {
    const journalOpt = document.getElementById('code_journal_id').selectedOptions[0];
    const journalLabel = journalOpt ? journalOpt.textContent.trim() : '—';
    const ns = document.getElementById('n_saisie_user').value;
    carte.style.setProperty('display', 'flex', 'important');
    document.getElementById('carteDesequilibreTitre').textContent =
      `L'écriture ${ns} du journal ${journalLabel} n'est pas équilibrée`;
    document.getElementById('carteDesequilibreTexte').textContent =
      `Écart de ${Math.abs(debit - credit).toLocaleString('fr-FR')} FCFA. Corrigez les montants avant de pouvoir enregistrer.`;
  }

  function masquerCarteDesequilibre() {
    carte.style.setProperty('display', 'none', 'important');
  }

  // ---------- Assistant TVA interactif ----------
  async function appliquerAssistantTVA() {
    const debitInput = document.getElementById('input_debit');
    const creditInput = document.getElementById('input_credit');
    const compSelect = document.getElementById('input_compte_general');

    if (!compSelect || !compSelect.value) {
      Swal.fire('Compte général manquant', 'Veuillez d\'abord choisir un compte général.', 'warning');
      return;
    }

    const debit = parseFloat(debitInput.value) || 0;
    const credit = parseFloat(creditInput.value) || 0;
    const montantHT = debit || credit;

    if (!montantHT) {
      Swal.fire('Montant manquant', 'Veuillez d\'abord saisir un montant au débit ou au crédit.', 'warning');
      return;
    }

    const { value: formValues } = await Swal.fire({
      title: 'Assistant TVA',
      html: `
        <div class="text-start mb-3">
          <label class="form-label fw-bold small text-muted">Montant HT de référence:</label>
          <div class="fs-5 fw-bold text-primary">${montantHT.toLocaleString('fr-FR')} FCFA</div>
        </div>
        <div class="mb-3 text-start">
          <label class="form-label fw-bold small">Choix du taux de TVA :</label>
          <select id="swal_tva_taux" class="form-select">
            <option value="18" selected>18% (Taux normal SYSCOHADA)</option>
            <option value="9">9% (Taux réduit)</option>
            <option value="0">0% (Exonéré)</option>
            <option value="custom">Saisir un montant ou pourcentage spécifique</option>
          </select>
        </div>
        <div id="swal_custom_container" class="mb-3 text-start" style="display:none">
          <label class="form-label fw-bold small">Montant de la TVA (FCFA) :</label>
          <input type="number" step="0.01" id="swal_tva_custom_montant" class="form-control" placeholder="Entrez le montant de TVA">
        </div>
      `,
      focusConfirm: false,
      showCancelButton: true,
      confirmButtonText: 'Calculer & Ajouter',
      cancelButtonText: 'Annuler',
      didOpen: () => {
        const select = document.getElementById('swal_tva_taux');
        const customContainer = document.getElementById('swal_custom_container');
        select.addEventListener('change', () => {
          customContainer.style.display = select.value === 'custom' ? 'block' : 'none';
        });
      },
      preConfirm: () => {
        const selectVal = document.getElementById('swal_tva_taux').value;
        if (selectVal === 'custom') {
          const customAmt = parseFloat(document.getElementById('swal_tva_custom_montant').value || 0);
          return { montantTVA: customAmt };
        } else {
          const taux = parseFloat(selectVal) / 100;
          return { montantTVA: Math.round(montantHT * taux * 100) / 100 };
        }
      }
    });

    if (!formValues || formValues.montantTVA === undefined) return;
    const montantTVA = formValues.montantTVA;
    if (montantTVA <= 0) return;

    // 1. Ajouter d'abord la ligne HT courante
    ajouterLigneEnCours(true);

    // 2. Déterminer le compte de TVA et l'ajouter
    const prefixeTVA = debit ? '4452' : '4431';
    const compteTVA = plansComptables.find(p => p.numero_de_compte.startsWith(prefixeTVA))
      || plansComptables.find(p => p.numero_de_compte.startsWith('445') || p.numero_de_compte.startsWith('443'));

    addedLines.push({
      id: null,
      plan_comptable_id: compteTVA ? compteTVA.id : '',
      plan_tiers_id: null,
      debit: debit ? montantTVA : 0,
      credit: credit ? montantTVA : 0,
      poste_tresorerie_id: null,
      plan_analytique: 0,
      ventilations: []
    });

    rafraichirAddedLines();

    Swal.fire({
      icon: 'success',
      title: 'TVA Ajoutée',
      text: `Ligne de TVA de ${montantTVA.toLocaleString('fr-FR')} FCFA ajoutée avec succès.`,
      timer: 2000,
      showConfirmButton: false
    });
  }

  // ---------- Ventilation Analytique (Bouton 📊) ----------
  function ouvrirModalVentilationRow() {
    const compSelect = document.getElementById('input_compte_general');
    const compVal = compSelect ? compSelect.value : '';
    const debit = parseFloat(document.getElementById('input_debit').value || 0);
    const credit = parseFloat(document.getElementById('input_credit').value || 0);
    const montant = debit || credit;

    if (!compVal || !montant) {
      Swal.fire('Ventilation Analytique', 'Veuillez choisir un compte général et saisir un montant au débit ou au crédit avant de ventiler cette ligne.', 'warning');
      return;
    }

    window.currentRowForVentilation = inputRow;
    const displayEl = document.getElementById('montant_a_ventiler_display');
    if (displayEl) displayEl.innerText = montant.toLocaleString('fr-FR', { minimumFractionDigits: 2 });

    const modalEl = document.getElementById('modalVentilationAnalytique');
    if (modalEl && window.bootstrap) {
      if (typeof window.initialiserTableauVentilation === 'function') {
        window.initialiserTableauVentilation(montant);
      }
      const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      modal.show();
    }
  }

  // Ventilation sur une écriture existante du tableau
  function ouvrirVentilationExisting(ecritureId) {
    const e = ecritures.find(x => x.id == ecritureId);
    if (!e) return;
    const montant = Number(e.debit || e.credit || 0);

    const dummyTr = document.createElement('tr');
    dummyTr.dataset.ecritureId = e.id;
    window.currentRowForVentilation = dummyTr;

    const displayEl = document.getElementById('montant_a_ventiler_display');
    if (displayEl) displayEl.innerText = montant.toLocaleString('fr-FR', { minimumFractionDigits: 2 });

    const modalEl = document.getElementById('modalVentilationAnalytique');
    if (modalEl && window.bootstrap) {
      if (typeof window.initialiserTableauVentilation === 'function') {
        window.initialiserTableauVentilation(montant);
      }
      const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      modal.show();
    }
  }

  // ---------- Ouverture / fermeture du panneau ----------
  function toggle() {
    if (panel.style.display === 'none') {
      modeEdition = false;
      nSaisieEnEdition = null;
      currentBatchId = null;
      panel.style.display = 'block';
      document.getElementById('saisieTitre').textContent = 'Nouvelle saisie d\'écriture';
      const btnValider = document.getElementById('btnValiderGrille');
      if (btnValider) {
        btnValider.textContent = 'Valider & enregistrer';
        btnValider.classList.remove('btn-balanced-active');
      }
      addedLines = [];
      rafraichirAddedLines();
      
      // Clear inputs
      const compSelect = document.getElementById('input_compte_general');
      if (compSelect) {
        compSelect.value = '';
        if (window.jQuery && typeof jQuery.fn.select2 === 'function') $(compSelect).trigger('change.select2');
      }
      document.getElementById('input_debit').value = '';
      document.getElementById('input_credit').value = '';
      onCompteChange();

      panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
      fermer();
    }
  }

  function editerGroupe(nSaisie) {
    const lignesDuGroupe = ecritures.filter(e => e.n_saisie === nSaisie);
    if (!lignesDuGroupe.length) return;

    modeEdition = true;
    nSaisieEnEdition = nSaisie;
    currentBatchId = null;

    panel.style.display = 'block';
    document.getElementById('saisieTitre').textContent = `Modifier l'écriture ${nSaisie}`;
    const btnValider = document.getElementById('btnValiderGrille');
    if (btnValider) btnValider.textContent = 'Enregistrer les modifications';

    const premiereLigne = lignesDuGroupe[0];

    // Restaurer le journal
    const journalSelect = document.getElementById('code_journal_id');
    if (journalSelect && premiereLigne.code_journal_id) {
      journalSelect.value = premiereLigne.code_journal_id;
    }

    // Restaurer la date (mois + jour) depuis la date de la 1ère ligne
    if (premiereLigne.date) {
      const d = new Date(premiereLigne.date);
      if (!isNaN(d.getTime())) {
        const moisSelect = document.getElementById('mois_ecriture');
        const jourSelect  = document.getElementById('jour_ecriture');
        if (moisSelect) {
          moisSelect.value = d.getMonth() + 1;
          // Régénérer les jours pour ce mois avant d'affecter le jour
          if (typeof updateDaysForMonth === 'function') updateDaysForMonth();
          else {
            // Déclencher l'événement change pour que updateDaysForMonth se charge
            moisSelect.dispatchEvent(new Event('change'));
          }
        }
        if (jourSelect)  jourSelect.value = d.getDate();
      }
    }

    document.getElementById('description_operation').value = premiereLigne.description_operation || '';
    const refInput = document.getElementById('reference_piece');
    if (refInput) refInput.value = premiereLigne.reference_piece || '';
    document.getElementById('n_saisie_user').value = nSaisie;

    // Charger les lignes dans addedLines
    addedLines = lignesDuGroupe.map(e => {
      const compte = plansComptables.find(p => p.numero_de_compte === e.compte_general);
      const tiers  = plansTiers.find(t => t.numero_de_tiers === e.compte_tiers);
      const poste  = comptesTresorerie.find(c => c.name === e.poste_tresorerie);
      return {
        id: e.id,
        date: e.date,
        plan_comptable_id: compte ? compte.id : null,
        plan_tiers_id: tiers ? tiers.id : null,
        debit: parseFloat(e.debit || 0),
        credit: parseFloat(e.credit || 0),
        poste_tresorerie_id: e.poste_tresorerie_id || (poste ? poste.id : null),
        plan_analytique: e.analytique ? 1 : 0,
        ventilations: e.ventilations || []
      };
    });

    rafraichirAddedLines();
    panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  // ---------- Supprimer un groupe d’écritures ----------
  async function supprimerGroupe(nSaisie) {
    const result = await Swal.fire({
      title: 'Supprimer l\'écriture ?',
      text: `Voulez-vous vraiment supprimer toutes les lignes du groupe ${nSaisie} ? Cette action est irréversible.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#64748b',
      confirmButtonText: 'Oui, supprimer',
      cancelButtonText: 'Annuler'
    });
    if (!result.isConfirmed) return;

    try {
      const res = await fetch(`/ecritures/supprimer-groupe/${encodeURIComponent(nSaisie)}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
      });
      const json = await res.json();
      if (json.success) {
        // Retirer du tableau local
        const idx = ecritures.findIndex(e => e.n_saisie === nSaisie);
        if (idx !== -1) ecritures.splice(idx, 1);
        // Retirer toutes les lignes du groupe
        for (let i = ecritures.length - 1; i >= 0; i--) {
          if (ecritures[i].n_saisie === nSaisie) ecritures.splice(i, 1);
        }
        rafraichirListe();
        appliquerFiltresConsultation();
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Écriture supprimée.', timer: 2000, showConfirmButton: false });
      } else {
        Swal.fire({ icon: 'error', title: 'Erreur', text: json.message || 'La suppression a échoué.' });
      }
    } catch (e) {
      Swal.fire({ icon: 'error', title: 'Erreur réseau', text: e.message });
    }
  }

  function fermer() {
    panel.style.display = 'none';
    masquerCarteDesequilibre();
    modeEdition = false;
    nSaisieEnEdition = null;
    currentBatchId = null;
    addedLines = [];
    rafraichirAddedLines();
  }

  function onFichierChoisi(input) {
    document.getElementById('pieceLabel').textContent = input.files[0] ? input.files[0].name : 'Pièce jointe (facultatif)';
  }

  // ---------- Collecte pour storeMultiple / update ----------
  function collecterLignes() {
    const description = document.getElementById('description_operation')?.value || '';
    const reference = document.getElementById('reference_piece')?.value || '';
    const rawMois = document.getElementById('mois_ecriture')?.value || '';
    const rawJour = document.getElementById('jour_ecriture')?.value || '';
    const annee = document.getElementById('annee_exercice')?.value || new Date().getFullYear();
    const codeJournalId = document.getElementById('code_journal_id')?.value || null;

    const currentNow = new Date();
    const moisNum = rawMois ? parseInt(rawMois) : (currentNow.getMonth() + 1);
    const jourNum = rawJour ? parseInt(rawJour) : currentNow.getDate();

    const moisStr = String(moisNum).padStart(2, '0');
    const jourStr = String(jourNum).padStart(2, '0');
    const date = `${annee}-${moisStr}-${jourStr}`;

    return addedLines.map(line => {
      return {
        id: line.id || null,
        date,
        n_saisie: document.getElementById('n_saisie_user').value,
        code_journal_id: codeJournalId,
        description_operation: description,
        reference_piece: reference,
        plan_comptable_id: line.plan_comptable_id,
        plan_tiers_id: line.plan_tiers_id,
        debit: line.debit,
        credit: line.credit,
        poste_tresorerie_id: line.poste_tresorerie_id,
        plan_analytique: line.plan_analytique,
        ventilations: line.ventilations,
      };
    });
  }

  async function enregistrer() {
    const journalSelect = document.getElementById('code_journal_id');
    const journalId = journalSelect ? journalSelect.value : '';

    if (!journalId) {
      if (journalSelect) {
        journalSelect.style.borderColor = '#ef4444';
        journalSelect.focus();
      }
      Swal.fire({
        toast: true, position: 'top-end', icon: 'warning',
        title: 'Veuillez sélectionner un Journal de saisie dans l\'en-tête.',
        timer: 3500
      });
      return;
    }

    const descInput = document.getElementById('description_operation');
    if (!descInput || !descInput.value.trim()) {
      if (descInput) {
        descInput.style.borderColor = '#ef4444';
        descInput.focus();
      }
      Swal.fire({
        toast: true, position: 'top-end', icon: 'warning',
        title: 'Veuillez remplir le libellé de l\'opération.',
        timer: 3000
      });
      return;
    }

    const rawJour = document.getElementById('jour_ecriture')?.value || '';
    if (!rawJour) {
      Swal.fire({
        toast: true, position: 'top-end', icon: 'warning',
        title: 'Veuillez sélectionner un jour de saisie.',
        timer: 3000
      });
      return;
    }

    // Auto-ajouter la ligne courante si elle a été saisie et permet de compléter l'écriture
    const compSelect = document.getElementById('input_compte_general');
    const debitInput = document.getElementById('input_debit');
    const creditInput = document.getElementById('input_credit');
    if (compSelect && compSelect.value && (parseFloat(debitInput.value) > 0 || parseFloat(creditInput.value) > 0)) {
      ajouterLigneEnCours(true);
    }

    if (addedLines.length === 0) {
      Swal.fire({
        toast: true, position: 'top-end', icon: 'warning',
        title: 'Veuillez ajouter au moins une ligne d\'écriture.',
        timer: 3000
      });
      return;
    }

    const { equilibre, debit, credit } = estEquilibre();
    if (!equilibre) {
      afficherCarteDesequilibre(debit, credit);
      carte.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }

    const btn = document.getElementById('btnValiderGrille');
    const origBtnText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Enregistrement…';

    try {
      let res, json;
      const lignes = collecterLignes();

      if (modeEdition) {
        res = await fetch(miseAJourMassiveUrl, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
          body: JSON.stringify({ lignes }),
        });
        json = await res.json();
      } else {
        const payload = {
          n_saisie: document.getElementById('n_saisie_user').value,
          code_journal_id: journalId,
          exercices_comptables_id: idExercice,
          ecritures: lignes,
          lignes: lignes,
          batch_id: currentBatchId
        };

        const fileInput = document.getElementById('piece_justificatif');
        if (fileInput && fileInput.files.length) {
          const fd = new FormData();
          fd.append('n_saisie', payload.n_saisie);
          fd.append('code_journal_id', payload.code_journal_id);
          fd.append('exercices_comptables_id', payload.exercices_comptables_id);
          fd.append('piece_justificatif', fileInput.files[0]);
          fd.append('ecritures', JSON.stringify(payload.ecritures));
          fd.append('lignes', JSON.stringify(payload.lignes));
          if (currentBatchId) fd.append('batch_id', currentBatchId);
          res = await fetch(storeMultipleUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: fd,
          });
        } else {
          res = await fetch(storeMultipleUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
          });
        }
        json = await res.json();
      }

      if (json.success) {
        // Enlever les anciennes versions de l'écriture en mode édition dans le tableau local et global
        if (modeEdition && nSaisieEnEdition) {
          for (let i = ecritures.length - 1; i >= 0; i--) {
            if (ecritures[i].n_saisie === nSaisieEnEdition) {
              ecritures.splice(i, 1);
            }
          }
          window.SAISIE_DATA.ecritures = ecritures;
        }

        if (json.ecritures && Array.isArray(json.ecritures)) {
          json.ecritures.forEach(ne => {
            ecritures.unshift(ne);
          });
        }

        // Message de confirmation discret de type toast, qui s'affiche brièvement sans gêner (1.2s)
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: modeEdition ? 'Écriture mise à jour' : 'Écriture enregistrée',
          showConfirmButton: false,
          timer: 1200,
          timerProgressBar: true
        });

        addedLines = [];
        rafraichirAddedLines();

        // Increment sequence
        const nsUser = document.getElementById('n_saisie_user');
        if (nsUser && nsUser.value && !modeEdition) {
          const match = nsUser.value.match(/^(.*?)(\d+)$/);
          if (match) {
            const prefix = match[1];
            const num = parseInt(match[2], 10) + 1;
            nsUser.value = prefix + String(num).padStart(match[2].length, '0');
          }
        }

        // Vider description/référence uniquement après sauvegarde globale réussie !
        if (descInput) descInput.value = '';
        const refInput = document.getElementById('reference_piece');
        if (refInput) refInput.value = '';
        const fileInput = document.getElementById('piece_justificatif');
        if (fileInput) fileInput.value = '';
        document.getElementById('pieceLabel').textContent = 'Pièce jointe (facultatif)';

        // Conserver la grille ouverte (pas de fermeture), réinitialiser uniquement l'état interne
        masquerCarteDesequilibre();
        modeEdition = false;
        nSaisieEnEdition = null;
        currentBatchId = null;

        rafraichirListe();
        appliquerFiltresConsultation();
      } else {
        Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: json.message || json.error || 'Erreur de sauvegarde.', timer: 3000 });
      }
    } catch (err) {
      Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Erreur réseau : ' + err.message, timer: 3000 });
    } finally {
      btn.disabled = false;
      btn.textContent = origBtnText;
    }
  }

  // ---------- Enregistrement en Brouillon ----------
  async function enregistrerBrouillon() {
    const journalId = document.getElementById('code_journal_id')?.value || '';
    if (!journalId) {
      Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'Veuillez sélectionner un Journal de saisie dans l\'en-tête.', timer: 3000 });
      return;
    }

    // Inclure la ligne courante si présente
    const compSelect = document.getElementById('input_compte_general');
    const debitInput = document.getElementById('input_debit');
    const creditInput = document.getElementById('input_credit');
    if (compSelect && compSelect.value && (parseFloat(debitInput.value) > 0 || parseFloat(creditInput.value) > 0)) {
      ajouterLigneEnCours(true);
    }

    const lines = collecterLignes();
    if (!lines.length) {
      Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Veuillez saisir au moins une ligne.', timer: 2500 });
      return;
    }
    const btn = document.getElementById('btnBrouillonGrille');
    if (btn) btn.disabled = true;

    try {
      const res = await fetch('/api/brouillons', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ batch_id: document.getElementById('n_saisie_user').value, ecritures: lines })
      });
      const json = await res.json();
      if (json.success) {
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Brouillon enregistré !', timer: 2500, showConfirmButton: false });
        fermer();
      } else {
        Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: json.error || 'Erreur de brouillon.', timer: 3000 });
      }
    } catch (e) {
      Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Erreur réseau : ' + e.message, timer: 3000 });
    } finally {
      if (btn) btn.disabled = false;
    }
  }

  // ---------- Charger un brouillon ----------
  async function chargerBrouillon(batchId) {
    try {
      const res = await fetch(`/api/brouillons/${batchId}`);
      const json = await res.json();
      if (json.success) {
        currentBatchId = batchId;
        modeEdition = false;
        nSaisieEnEdition = null;

        panel.style.display = 'block';
        document.getElementById('saisieTitre').textContent = 'Reprise de brouillon';
        const btnValider = document.getElementById('btnValiderGrille');
        if (btnValider) {
          btnValider.textContent = 'Valider & enregistrer';
          btnValider.classList.remove('btn-balanced-active');
        }

        const journalSelect = document.getElementById('code_journal_id');
        if (journalSelect && json.summary.code_journal_id) {
          journalSelect.value = json.summary.code_journal_id;
        }

        document.getElementById('description_operation').value = json.summary.description || '';
        const refInput = document.getElementById('reference_piece');
        if (refInput) {
          refInput.value = json.summary.reference || '';
        }
        document.getElementById('n_saisie_user').value = json.summary.n_saisie || '';

        // Parse date
        if (json.summary.date) {
          const d = new Date(json.summary.date);
          if (!isNaN(d.getTime())) {
            const moisSelect = document.getElementById('mois_ecriture');
            const jourSelect = document.getElementById('jour_ecriture');
            if (moisSelect) moisSelect.value = d.getMonth() + 1;
            if (jourSelect) jourSelect.value = d.getDate();
          }
        }

        // Map lines
        addedLines = json.brouillons.map(line => {
          return {
            id: null, // Force new creation
            plan_comptable_id: line.plan_comptable_id,
            plan_tiers_id: line.plan_tiers_id,
            debit: parseFloat(line.debit || 0),
            credit: parseFloat(line.credit || 0),
            poste_tresorerie_id: line.compte_tresorerie_id,
            plan_analytique: line.plan_analytique || 0,
            ventilations: line.ventilations || []
          };
        });

        rafraichirAddedLines();
        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
      } else {
        Swal.fire({ icon: 'error', title: 'Erreur', text: json.message || 'Impossible de charger le brouillon.' });
      }
    } catch (e) {
      Swal.fire({ icon: 'error', title: 'Erreur réseau', text: e.message });
    }
  }

  // ---------- Modèle de saisie ----------
  function appliquerModele(modeleId) {
    const mod = window.MODELES_SAISIE.find(m => m.id == modeleId);
    if (!mod || !mod.lignes) return;
    addedLines = mod.lignes.map(l => {
      return {
        id: null,
        plan_comptable_id: l.plan_comptable_id,
        plan_tiers_id: l.plan_tiers_id,
        debit: parseFloat(l.debit || 0),
        credit: parseFloat(l.credit || 0),
        poste_tresorerie_id: l.poste_tresorerie_id,
        plan_analytique: l.plan_analytique || 0,
        ventilations: l.ventilations || []
      };
    });
    rafraichirAddedLines();
    panel.style.display = 'block';
    panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function ouvrirModalCreerModele() {
    const modalEl = document.getElementById('modalCreateModeleSaisie');
    if (modalEl && window.bootstrap) {
      const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      modal.show();
    }
  }

  function enregistrerCommeModele() {
    // Auto-ajouter la ligne courante si elle est remplie
    const compSelect = document.getElementById('input_compte_general');
    const debitInput = document.getElementById('input_debit');
    const creditInput = document.getElementById('input_credit');
    if (compSelect && compSelect.value && (parseFloat(debitInput?.value) > 0 || parseFloat(creditInput?.value) > 0)) {
      ajouterLigneEnCours(true);
    }

    if (addedLines.length === 0) {
      Swal.fire({ icon: 'info', title: 'Aucune ligne', text: 'Veuillez d\'abord ajouter au moins une ligne avant de créer un modèle.' });
      return;
    }
    ouvrirModalCreerModele();
  }

  async function enregistrerNouveauModeleInline() {
    const nom = document.getElementById('nom_modele_saisie_input')?.value.trim();
    if (!nom) {
      alert('Veuillez saisir un nom pour le modèle.');
      return;
    }
    const lignes = collecterLignes();
    if (!lignes.length) {
      alert('Veuillez d\'abord ajouter des lignes à enregistrer comme modèle.');
      return;
    }

    try {
      const res = await fetch(ecritureModelesStoreUrl, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ nom, lignes }),
      });
      const json = await res.json();
      if (json.success && json.modele) {
        window.MODELES_SAISIE.push(json.modele);
        const select = document.getElementById('modele_saisie');
        const opt = document.createElement('option');
        opt.value = json.modele.id;
        opt.textContent = json.modele.nom;
        select.appendChild(opt);
        select.value = json.modele.id;

        const modalEl = document.getElementById('modalCreateModeleSaisie');
        if (modalEl && window.bootstrap) {
          const inst = bootstrap.Modal.getInstance(modalEl);
          if (inst) inst.hide();
        }
        Swal.fire({ icon: 'success', title: 'Modèle créé !', text: `Modèle "${nom}" créé et sélectionné.`, timer: 2000, showConfirmButton: false });
      } else {
        alert(json.message || 'Erreur lors de la création du modèle.');
      }
    } catch (e) {
      alert('Erreur réseau : ' + e.message);
    }
  }

  // ---------- Scanner facture ----------
  function scannerFacture() {
    const journalSelect = document.getElementById('code_journal_id');
    const moisSelect = document.getElementById('mois_ecriture');
    const annee = document.getElementById('annee_exercice')?.value || new Date().getFullYear();
    const idExerciceVal = document.getElementById('id_exercice')?.value || '';

    const params = new URLSearchParams({
      id_exercice: idExerciceVal || '',
      annee: annee,
    });

    if (journalSelect && journalSelect.value) {
      params.append('id_code', journalSelect.value);
      params.append('id_journal_code', journalSelect.value);
      const opt = journalSelect.selectedOptions[0];
      if (opt) {
        params.append('code', opt.dataset.code_journal_j || '');
        params.append('type', opt.dataset.type_j || '');
        params.append('intitule', opt.dataset.intitule_j || '');
      }
    }
    if (moisSelect && moisSelect.value) {
      params.append('mois', moisSelect.value);
    }

    window.location.href = window.SAISIE_DATA.ecritureScanUrl + '?' + params.toString();
  }

  // ---------- Dynamic days in mois_ecriture ----------
  function updateDaysForMonth() {
    const monthSelect = document.getElementById('mois_ecriture');
    const daySelect = document.getElementById('jour_ecriture');
    if (!monthSelect || !daySelect) return;

    const monthVal = parseInt(monthSelect.value);
    const yearVal = parseInt(document.getElementById('annee_exercice')?.value) || new Date().getFullYear();
    
    const prevDay = daySelect.value;
    daySelect.innerHTML = '<option value="">— Tous les jours —</option>';

    if (!monthVal) {
      for (let d = 1; d <= 31; d++) {
        daySelect.innerHTML += `<option value="${d}">${d}</option>`;
      }
      return;
    }

    const daysInMonth = new Date(yearVal, monthVal, 0).getDate();
    for (let d = 1; d <= daysInMonth; d++) {
      daySelect.innerHTML += `<option value="${d}">${d}</option>`;
    }

    if (prevDay && parseInt(prevDay) <= daysInMonth) {
      daySelect.value = prevDay;
    } else {
      daySelect.value = '';
    }
  }

  function estEquilibre() {
    let debit = 0, credit = 0;
    addedLines.forEach(l => {
      debit += l.debit;
      credit += l.credit;
    });
    const curDebit = parseFloat(document.getElementById('input_debit')?.value || 0);
    const curCredit = parseFloat(document.getElementById('input_credit')?.value || 0);
    
    const finalDebit = debit + curDebit;
    const finalCredit = credit + curCredit;
    const ecart = Math.abs(finalDebit - finalCredit);

    return { debit: finalDebit, credit: finalCredit, equilibre: ecart < 0.01 && finalDebit > 0 };
  }

  // ---------- Refresh Tableau Sage ----------
  function rafraichirListe() {
    const journalId = document.getElementById('code_journal_id')?.value || '';
    const moisVal = document.getElementById('mois_ecriture')?.value || '';

    let lignes = ecritures.slice();

    if (journalId) {
      lignes = lignes.filter(e => e.code_journal_id == journalId);
    }
    if (moisVal) {
      lignes = lignes.filter(e => {
        if (!e.date) return false;
        const p = e.date.split('-');
        const m = p.length === 3 ? parseInt(p[1]) : null;
        return m == moisVal;
      });
    }
    lignes.sort((a, b) => new Date(a.date) - new Date(b.date));

    if (filtreDesequilibre) {
      const parGroupe = {};
      lignes.forEach(e => { (parGroupe[e.n_saisie] = parGroupe[e.n_saisie] || []).push(e); });
      const groupesDesequilibres = Object.entries(parGroupe)
        .filter(([, l]) => Math.abs(l.reduce((s, x) => s + Number(x.debit), 0) - l.reduce((s, x) => s + Number(x.credit), 0)) > 0.01)
        .map(([ns]) => ns);
      lignes = lignes.filter(e => groupesDesequilibres.includes(e.n_saisie));
    }

    const totalDebitSage = lignes.reduce((s, x) => s + Number(x.debit || 0), 0);
    const totalCreditSage = lignes.reduce((s, x) => s + Number(x.credit || 0), 0);
    const nbGroupes = new Set(lignes.map(e => e.n_saisie)).size;

    const compteur = document.getElementById('compteurLignes');
    if (compteur) {
      compteur.innerHTML = `<span class="fw-bold text-slate-800">${lignes.length} ligne(s) · ${nbGroupes} groupe(s)</span> | Débit: <strong class="text-primary">${totalDebitSage.toLocaleString('fr-FR')} FCFA</strong> · Crédit: <strong class="text-primary">${totalCreditSage.toLocaleString('fr-FR')} FCFA</strong>`;
    }

    const statutClasses = {
      valid: 'bg-green-100 text-green-700', approved: 'bg-green-100 text-green-700',
      rejected: 'bg-red-100 text-red-700', pending: 'bg-slate-100 text-slate-700',
    };
    const statutLabels = {
      valid: 'Validé', approved: 'Validé',
      rejected: 'Rejeté', pending: 'En Attente',
    };

    const parGroupe = {};
    lignes.forEach(e => { (parGroupe[e.n_saisie] = parGroupe[e.n_saisie] || []).push(e); });
    const desequilibreParGroupe = {};
    Object.entries(parGroupe).forEach(([ns, l]) => {
      const d = l.reduce((s, x) => s + Number(x.debit), 0);
      const c = l.reduce((s, x) => s + Number(x.credit), 0);
      desequilibreParGroupe[ns] = Math.abs(d - c) > 0.01;
    });

    if (listeBody) {
      listeBody.innerHTML = lignes.map(e => construireLigneHTML(e, desequilibreParGroupe[e.n_saisie], statutClasses, statutLabels)).join('')
        || `<tr><td colspan="15" class="text-center text-muted py-4">Aucune écriture pour ces critères de filtre</td></tr>`;

      const container = document.getElementById('sageScrollContainer');
      if (container) {
        container.scrollTop = container.scrollHeight;
      }
    }
  }

  // ---------- Refresh Tableau Consultation Générale avec Filtres ----------
  function appliquerFiltresConsultation() {
    const journalCode = document.getElementById('filtre_consultation_journal')?.value || '';
    const moisVal = document.getElementById('filtre_consultation_mois')?.value || '';

    let lignes = ecritures.slice();

    if (journalCode) {
      lignes = lignes.filter(e => e.code_journal === journalCode);
    }
    if (moisVal) {
      lignes = lignes.filter(e => {
        if (!e.date) return false;
        const d = new Date(e.date);
        return (d.getMonth() + 1) == parseInt(moisVal);
      });
    }

    const totalDebitCons = lignes.reduce((s, x) => s + Number(x.debit || 0), 0);
    const totalCreditCons = lignes.reduce((s, x) => s + Number(x.credit || 0), 0);
    const nbGroupesCons = new Set(lignes.map(e => e.n_saisie)).size;

    const compteur = document.getElementById('compteurConsultationLignes');
    if (compteur) {
      compteur.innerHTML = `<span class="fw-bold text-slate-800">${lignes.length} ligne(s) · ${nbGroupesCons} groupe(s)</span> | Débit: <strong class="text-primary">${totalDebitCons.toLocaleString('fr-FR')} FCFA</strong> · Crédit: <strong class="text-primary">${totalCreditCons.toLocaleString('fr-FR')} FCFA</strong>`;
    }

    const statutClasses = {
      valid: 'bg-green-100 text-green-700', approved: 'bg-green-100 text-green-700',
      rejected: 'bg-red-100 text-red-700', pending: 'bg-slate-100 text-slate-700',
    };
    const statutLabels = {
      valid: 'Validé', approved: 'Validé',
      rejected: 'Rejeté', pending: 'En Attente',
    };

    const parGroupe = {};
    lignes.forEach(e => { (parGroupe[e.n_saisie] = parGroupe[e.n_saisie] || []).push(e); });
    const desequilibreParGroupe = {};
    Object.entries(parGroupe).forEach(([ns, l]) => {
      const d = l.reduce((s, x) => s + Number(x.debit), 0);
      const c = l.reduce((s, x) => s + Number(x.credit), 0);
      desequilibreParGroupe[ns] = Math.abs(d - c) > 0.01;
    });

    if (consultationBody) {
      consultationBody.innerHTML = lignes.map(e => construireLigneHTML(e, desequilibreParGroupe[e.n_saisie], statutClasses, statutLabels)).join('')
        || `<tr><td colspan="15" class="text-center text-muted py-4">Aucune écriture trouvée pour ces filtres</td></tr>`;
    }
  }

  function toggleFiltreDesequilibre() {
    filtreDesequilibre = !filtreDesequilibre;
    const chip = document.getElementById('chipDesequilibre');
    if (chip) {
      chip.style.background = filtreDesequilibre ? '#fff7ed' : '#fff';
      chip.style.borderColor = filtreDesequilibre ? '#f97316' : '#d1d5db';
      chip.style.color = filtreDesequilibre ? '#c2410c' : '';
    }
    rafraichirListe();
  }

  // ---------- HTML d'une ligne d'écriture dans le tableau ----------
  function construireLigneHTML(e, deseq, statutClasses, statutLabels) {
    const origVal = e.n_saisie_original || e.n_saisie_user;
    const numSaisieSec = (origVal && origVal !== e.n_saisie && String(origVal).trim().toUpperCase() !== 'DEFAULT') ? origVal : null;

    let dateFormatee = e.date || '';
    if (dateFormatee.includes('-')) {
      const p = dateFormatee.split('-');
      if (p.length === 3 && p[0].length === 4) {
        dateFormatee = `${p[2]}-${p[1]}-${p[0]}`;
      }
    }

    const btnAnalytiqueHTML = e.analytique
      ? `<button type="button" class="btn btn-xs btn-outline-primary px-2 py-0" title="Voir/Modifier la ventilation analytique" onclick="event.stopPropagation(); saisieGrille.ouvrirVentilationExisting('${e.id}')"><i class="bx bx-pie-chart-alt text-primary"></i> <span class="fw-bold small">Oui</span></button>`
      : `<button type="button" class="btn btn-xs btn-outline-secondary px-2 py-0" title="Ajouter une ventilation analytique" onclick="event.stopPropagation(); saisieGrille.ouvrirVentilationExisting('${e.id}')"><i class="bx bx-pie-chart-alt text-muted"></i> <span class="small">Non</span></button>`;

    const estClasse5 = String(e.compte_general || '').trim().startsWith('5');
    let posteTresorerieHTML = e.poste_tresorerie || '—';

    const pieceHTML = (e.piece_url || e.piece)
      ? `<a href="${e.piece_url || '#'}" target="_blank" onclick="event.stopPropagation()" class="btn btn-xs btn-outline-primary px-2 py-0" title="Voir la pièce justificative"><i class="bx bx-paperclip"></i> Voir</a>`
      : '—';

    return `
      <tr style="${deseq ? 'background:#fff7ed' : ''};cursor:pointer" onclick="saisieGrille.editerGroupe('${e.n_saisie}')" title="Cliquer pour modifier cette écriture">
        <td>${deseq ? '<span style="width:7px;height:7px;border-radius:50%;background:#f97316;display:inline-block" title="Groupe déséquilibré"></span>' : ''}</td>
        <td>${dateFormatee}</td>
        <td>
          <span class="fw-bold text-slate-800">${e.n_saisie}</span>
          ${numSaisieSec ? `<small class="text-muted d-block" style="font-size:10px">Orig: ${numSaisieSec}</small>` : ''}
        </td>
        <td><span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-bold uppercase tracking-wide ${statutClasses[e.statut] || 'bg-slate-100 text-slate-700'}">${statutLabels[e.statut] || e.statut}</span></td>
        <td>
          <span class="badge bg-label-secondary fw-semibold">${e.code_journal}</span>
          ${e.code_journal_original && e.code_journal_original !== e.code_journal ? `<small class="text-muted d-block mt-0.5" style="font-size:10px">Orig: ${e.code_journal_original}</small>` : ''}
        </td>
        <td>${posteTresorerieHTML}</td>
        <td>${e.reference_piece ?? '—'}</td>
        <td title="${e.description_operation ?? ''}">${e.description_operation ? (e.description_operation.length > 25 ? e.description_operation.substring(0,25) + '…' : e.description_operation) : ''}</td>
        <td title="${e.compte_general_intitule || ''}">
          <span class="fw-bold text-slate-800">${e.compte_general}</span>
          ${e.compte_general_intitule ? `<small class="text-muted d-block" style="font-size:10px">${e.compte_general_intitule}</small>` : ''}
          ${(e.compte_general_original && e.compte_general_original !== e.compte_general) ? `<small class="text-primary d-block" style="font-size:9.5px">Orig: ${e.compte_general_original}</small>` : ''}
        </td>
        <td title="${e.compte_tiers_intitule || ''}">
          ${e.compte_tiers ? `
            <span class="fw-bold text-slate-800">${e.compte_tiers}</span>
            ${e.compte_tiers_intitule ? `<small class="text-muted d-block" style="font-size:10px">${e.compte_tiers_intitule}</small>` : ''}
            ${(e.compte_tiers_original && e.compte_tiers_original !== e.compte_tiers) ? `<small class="text-primary d-block" style="font-size:9.5px">Orig: ${e.compte_tiers_original}</small>` : ''}
          ` : '—'}
        </td>
        <td class="text-center">${btnAnalytiqueHTML}</td>
        <td class="text-end" style="${deseq ? 'color:#c2410c;font-weight:700' : ''}">${Number(e.debit).toLocaleString('fr-FR')}</td>
        <td class="text-end" style="${deseq ? 'color:#c2410c;font-weight:700' : ''}">${Number(e.credit).toLocaleString('fr-FR')}</td>
        <td class="text-center">${pieceHTML}</td>
        <td onclick="event.stopPropagation()">
          <a href="/ecriture/${e.id}" class="text-info me-2" title="Voir les détails" target="_blank"><i class="bx bx-show" style="cursor:pointer;font-size:16px"></i></a>
          <i class="bx bx-edit-alt text-warning me-2" style="cursor:pointer;font-size:16px" title="Modifier" onclick="saisieGrille.editerGroupe('${e.n_saisie}')"></i>
          <i class="bx bx-trash text-danger" style="cursor:pointer;font-size:16px" title="Supprimer l'écriture" onclick="saisieGrille.supprimerGroupe('${e.n_saisie}')"></i>
        </td>
      </tr>`;
  }

  // ---------- Init & Event Listeners ----------
  document.addEventListener('DOMContentLoaded', () => {
    const compSelect = document.getElementById('input_compte_general');
    const debitInput = document.getElementById('input_debit');
    const creditInput = document.getElementById('input_credit');

    if (compSelect) {
      compSelect.addEventListener('change', onCompteChange);
      if (window.jQuery && typeof jQuery.fn.select2 === 'function') {
        const $selCpte = $(compSelect).select2({ width: '100%', dropdownAutoWidth: false });
        const $selTiers = $(document.getElementById('input_compte_tiers')).select2({ width: '100%', dropdownAutoWidth: false });
        $selCpte.on('change.select2', () => {
          onCompteChange();
          $selTiers.trigger('change.select2');
        });
      }
    }

    if (debitInput) debitInput.addEventListener('input', handleDebitCreditExclusion);
    if (creditInput) creditInput.addEventListener('input', handleDebitCreditExclusion);

    const btnTva = document.getElementById('input_btn_tva');
    if (btnTva) btnTva.addEventListener('click', appliquerAssistantTVA);

    const btnAnalytique = document.getElementById('input_btn_analytique');
    if (btnAnalytique) btnAnalytique.addEventListener('click', ouvrirModalVentilationRow);

    brancherBoutonPlus(document.querySelector('.btn-plus'), 'modalCenterCreate', 'compte');
    brancherBoutonPlus(document.querySelector('.btn-plus-tiers'), 'createTiersModal', 'tiers');
    brancherBoutonPlus(document.getElementById('input_btn_plus_poste'), 'modalCreatePoste', 'poste');

    const sJourn = localStorage.getItem('fc_saisie_journal_id');
    const sMois = localStorage.getItem('fc_saisie_mois_ecriture');
    const sJour = localStorage.getItem('fc_saisie_jour_ecriture');
    
    if (sJourn && document.getElementById('code_journal_id')) document.getElementById('code_journal_id').value = sJourn;
    if (sMois && document.getElementById('mois_ecriture')) {
      document.getElementById('mois_ecriture').value = sMois;
      updateDaysForMonth();
    }
    if (sJour && document.getElementById('jour_ecriture')) document.getElementById('jour_ecriture').value = sJour;

    document.getElementById('code_journal_id')?.addEventListener('change', function () {
      localStorage.setItem('fc_saisie_journal_id', this.value);
      rafraichirListe();
    });

    document.getElementById('mois_ecriture')?.addEventListener('change', function () {
      localStorage.setItem('fc_saisie_mois_ecriture', this.value);
      updateDaysForMonth();
      rafraichirListe();
    });

    document.getElementById('jour_ecriture')?.addEventListener('change', function () {
      localStorage.setItem('fc_saisie_jour_ecriture', this.value);
    });

    document.getElementById('modele_saisie')?.addEventListener('change', function () {
      if (this.value) appliquerModele(this.value);
    });

    // Traitement des paramètres URL
    const urlParams = new URLSearchParams(window.location.search);
    const batchId  = urlParams.get('batch_id');
    const nSaisieUrl = urlParams.get('n_saisie');

    if (batchId) {
      chargerBrouillon(batchId);
    } else if (nSaisieUrl) {
      // Venu depuis la liste des écritures : ouvrir directement en mode édition
      // Petit délai pour laisser le temps à rafraichirListe() de peupler le tableau
      setTimeout(() => {
        const grp = ecritures.filter(e => e.n_saisie === nSaisieUrl);
        if (grp.length) {
          editerGroupe(nSaisieUrl);
        } else {
          // Si les écritures ne sont pas encore dans le tableau local, attendre un peu plus
          setTimeout(() => editerGroupe(nSaisieUrl), 600);
        }
      }, 200);
    } else if (urlParams.get('open') === '1') {
      toggle();
    }

    rafraichirListe();
    appliquerFiltresConsultation();
  });

  return {
    toggle, fermer, editerGroupe, supprimerGroupe, ajouterLigneEnCours, supprimerLigneEnCours, editerLigneEnCours, calculerTotaux, enregistrer, enregistrerBrouillon,
    appliquerModele, enregistrerCommeModele, ouvrirModalCreerModele, enregistrerNouveauModeleInline,
    rafraichirListe, toggleFiltreDesequilibre, onFichierChoisi, scannerFacture,
    switchViewMode, toggleHeaderCard, appliquerFiltresConsultation, appliquerAssistantTVA, ouvrirModalVentilationRow,
    ouvrirVentilationExisting, chargerBrouillon
  };
})();
