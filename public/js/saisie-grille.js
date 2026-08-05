// saisie-grille.js
// Grille de saisie illimitée + conteneur Sage + liste des écritures filtrée en direct.
// Support complet du switch de vue, assistant TVA interactif, ventilation analytique, et création inline de modèles.

const saisieGrille = (() => {
  const {
    plansComptables, plansTiers, comptesTresorerie, idExercice, csrfToken,
    storeMultipleUrl, miseAJourMassiveUrl, ecritureModelesStoreUrl,
    ecritures, modelesSaisie,
  } = window.SAISIE_DATA;

  const body = document.getElementById('grilleBody');
  const listeBody = document.getElementById('listeEcrituresBody');
  const consultationBody = document.getElementById('listeConsultationBody');
  const hint = document.getElementById('contrepartieHint');
  const carte = document.getElementById('carteDesequilibre');
  const panel = document.getElementById('panelSaisie');

  let rowCount = 0;
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
  function classeCompte(numero) { return numero ? String(numero).charAt(0) : ''; }

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
    // Fallback: si aucun tiers spécifiquement rattaché, lister tous les tiers disponibles
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

  function brancherBoutonPlus(btn, modalId, rowId, cible) {
    if (!btn) return;
    btn.addEventListener('click', () => {
      window._fcCibleCreation = { rowId, cible };
      const modalEl = document.getElementById(modalId);
      if (modalEl && window.bootstrap) {
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
      }
    });
  }

  // Callback à brancher depuis les modales existantes de l'app après création réussie
  window.fcInjecterElementCree = function (type, item) {
    const cible = window._fcCibleCreation;
    if (!cible) return;
    const tr = document.getElementById(cible.rowId);
    if (!tr) return;
    if (type === 'compte') {
      plansComptables.push(item);
      const sel = tr.querySelector('.cpte-general');
      sel.innerHTML = optionsComptes(item.id);
      sel.dispatchEvent(new Event('change'));
    } else if (type === 'tiers') {
      plansTiers.push(item);
      tr.querySelector('.cpte-tiers').innerHTML = optionsTiers(tr.querySelector('.cpte-general').value, item.id);
    } else if (type === 'poste') {
      comptesTresorerie.push(item);
      tr.querySelector('.poste-treso').innerHTML = optionsPostes(item.id);
    }
    window._fcCibleCreation = null;
  };

  // ---------- Grille ----------
  function ajouterLigne(prefill = {}) {
    rowCount++;
    const rowId = `row_${rowCount}`;
    const tr = document.createElement('tr');
    tr.id = rowId;
    tr.dataset.ecritureId = prefill.id || '';
    if (prefill.ventilations) {
      tr.dataset.ventilations = JSON.stringify(prefill.ventilations);
    }
    tr.innerHTML = `
      <td>
        <div class="d-flex gap-1">
          <select class="form-select form-select-sm cpte-general">${optionsComptes(prefill.plan_comptable_id)}</select>
          <button type="button" class="btn btn-sm btn-plus" title="Créer un compte" style="background:linear-gradient(135deg,#2563eb,#1e3a8a);color:#fff;border:none;width:30px">+</button>
        </div>
      </td>
      <td>
        <div class="d-flex gap-1">
          <select class="form-select form-select-sm cpte-tiers"></select>
          <button type="button" class="btn btn-sm btn-plus-tiers" title="Créer un tiers" style="background:linear-gradient(135deg,#2563eb,#1e3a8a);color:#fff;border:none;width:30px">+</button>
        </div>
      </td>
      <td><input type="number" step="0.01" class="form-control form-control-sm text-end debit" value="${prefill.debit || ''}"></td>
      <td><input type="number" step="0.01" class="form-control form-control-sm text-end credit" value="${prefill.credit || ''}"></td>
      <td>
        <div class="d-flex gap-1">
          <select class="form-select form-select-sm poste-treso" disabled>${optionsPostes(prefill.poste_tresorerie_id)}</select>
          <button type="button" class="btn btn-sm btn-plus-poste" title="Créer un poste" style="background:linear-gradient(135deg,#2563eb,#1e3a8a);color:#fff;border:none;width:30px" disabled>+</button>
        </div>
      </td>
      <td class="text-center"><button type="button" class="btn btn-sm btn-tva" title="Assistant TVA" style="background:#eff6ff;color:#2563eb;border:none;width:30px"><i class="bx bx-receipt"></i></button></td>
      <td class="text-center"><button type="button" class="btn btn-sm btn-analytique" title="Ventiler sur les sections analytiques" style="background:#eff6ff;color:#2563eb;border:none;width:30px"><i class="bx bx-pie-chart-alt"></i></button></td>
      <td class="text-center"><i class="bx bx-trash text-muted" style="cursor:pointer" onclick="saisieGrille.supprimerLigne('${rowId}')"></i></td>
    `;
    body.appendChild(tr);

    const selCompte = tr.querySelector('.cpte-general');
    const selPoste = tr.querySelector('.poste-treso');
    const btnPlusPoste = tr.querySelector('.btn-plus-poste');
    const btnTva = tr.querySelector('.btn-tva');
    const btnAnalytique = tr.querySelector('.btn-analytique');

    function onCompteChange() {
      const compteObj = plansComptables.find(p => p.id == selCompte.value);
      const opt = selCompte.selectedOptions[0];
      const numero = compteObj ? String(compteObj.numero_de_compte) : (opt ? opt.dataset.numero || '' : '');
      const selTiers = tr.querySelector('.cpte-tiers');
      selTiers.innerHTML = optionsTiers(selCompte.value, prefill.plan_tiers_id);
      
      const estTresorerie = numero.trim().startsWith('5');
      selPoste.disabled = !estTresorerie;
      if (btnPlusPoste) btnPlusPoste.disabled = !estTresorerie;
      if (!estTresorerie) selPoste.value = '';
    }
    selCompte.addEventListener('change', onCompteChange);
    onCompteChange();

    if (window.jQuery && typeof jQuery.fn.select2 === 'function') {
      const $selCpte = $(selCompte).select2({ width: '100%', dropdownAutoWidth: false });
      const $selTiers = $(tr.querySelector('.cpte-tiers')).select2({ width: '100%', dropdownAutoWidth: false });
      $selCpte.on('change.select2', () => {
        onCompteChange();
        $selTiers.trigger('change.select2');
      });
    }

    brancherBoutonPlus(tr.querySelector('.btn-plus'), 'modalCenterCreate', rowId, 'compte');
    brancherBoutonPlus(tr.querySelector('.btn-plus-tiers'), 'createTiersModal', rowId, 'tiers');
    brancherBoutonPlus(btnPlusPoste, 'modalCreatePoste', rowId, 'poste');

    if (btnTva) btnTva.addEventListener('click', () => appliquerAssistantTVA(tr));
    if (btnAnalytique) btnAnalytique.addEventListener('click', () => ouvrirModalVentilationRow(tr));

    const debitInput = tr.querySelector('.debit');
    const creditInput = tr.querySelector('.credit');

    [debitInput, creditInput].forEach(el => {
      el.addEventListener('input', () => {
        calculerTotaux();
      });
      el.addEventListener('change', () => {
        proposerAutomatiquementContrepartie(tr);
      });
    });

    calculerTotaux();
    return tr;
  }

  function supprimerLigne(rowId) {
    document.getElementById(rowId)?.remove();
    if (!body.children.length) ajouterLigne();
    calculerTotaux();
  }

  // ---------- Équilibre + carte d'avertissement ----------
  function estEquilibre() {
    let debit = 0, credit = 0;
    body.querySelectorAll('tr').forEach(tr => {
      debit += parseFloat(tr.querySelector('.debit')?.value || 0);
      credit += parseFloat(tr.querySelector('.credit')?.value || 0);
    });
    const ecart = Math.abs(debit - credit);
    return { debit, credit, equilibre: ecart < 0.01 && debit > 0 && body.children.length >= 2 };
  }

  function calculerTotaux() {
    const { debit, credit, equilibre } = estEquilibre();
    document.getElementById('totalDebit').textContent = debit.toLocaleString('fr-FR');
    document.getElementById('totalCredit').textContent = credit.toLocaleString('fr-FR');

    const badge = document.getElementById('balanceBadge');
    const btn = document.getElementById('btnValiderGrille');

    if (equilibre) {
      badge.textContent = 'Équilibré';
      badge.className = 'badge bg-success rounded-pill px-3 py-2';
      btn.disabled = false;
      masquerCarteDesequilibre();
    } else {
      const ecart = Math.abs(debit - credit);
      badge.textContent = debit === credit ? 'Non équilibré' : `Écart ${ecart.toLocaleString('fr-FR')}`;
      badge.className = 'badge bg-danger rounded-pill px-3 py-2';
      btn.disabled = true;
      if (debit > 0 || credit > 0) afficherCarteDesequilibre(debit, credit);
    }
  }

  // Proposition automatique de contrepartie équilibrante
  function proposerAutomatiquementContrepartie(trSource) {
    let debit = 0, credit = 0;
    const rows = Array.from(body.querySelectorAll('tr'));
    rows.forEach(tr => {
      debit += parseFloat(tr.querySelector('.debit')?.value || 0);
      credit += parseFloat(tr.querySelector('.credit')?.value || 0);
    });
    const ecart = Math.round((debit - credit) * 100) / 100;
    if (Math.abs(ecart) < 0.01) return; // Déjà équilibré

    const journalOpt = document.getElementById('code_journal_id')?.selectedOptions[0];
    const journalContrepartie = journalOpt ? journalOpt.dataset.contrepartie : null;

    const lastTr = rows[rows.length - 1];
    const lastDebit = parseFloat(lastTr.querySelector('.debit')?.value || 0);
    const lastCredit = parseFloat(lastTr.querySelector('.credit')?.value || 0);
    const lastCompte = lastTr.querySelector('.cpte-general')?.value;

    if (!lastDebit && !lastCredit && lastTr !== trSource) {
      if (ecart > 0) {
        lastTr.querySelector('.credit').value = ecart;
      } else {
        lastTr.querySelector('.debit').value = Math.abs(ecart);
      }
      if (journalContrepartie && !lastCompte) {
        const compteObj = plansComptables.find(p => p.numero_de_compte == journalContrepartie || p.id == journalContrepartie);
        if (compteObj) {
          lastTr.querySelector('.cpte-general').value = compteObj.id;
          lastTr.querySelector('.cpte-general').dispatchEvent(new Event('change'));
        }
      }
    } else {
      let prefill = {};
      if (ecart > 0) {
        prefill.credit = ecart;
      } else {
        prefill.debit = Math.abs(ecart);
      }
      if (journalContrepartie) {
        const compteObj = plansComptables.find(p => p.numero_de_compte == journalContrepartie || p.id == journalContrepartie);
        if (compteObj) prefill.plan_comptable_id = compteObj.id;
      }
      ajouterLigne(prefill);
    }
    calculerTotaux();
  }

  function afficherCarteDesequilibre(debit, credit) {
    const journalOpt = document.getElementById('code_journal_id').selectedOptions[0];
    const journalLabel = journalOpt ? journalOpt.textContent.trim() : '—';
    const ns = document.getElementById('n_saisie_user').value;
    carte.style.setProperty('display', 'flex', 'important');
    document.getElementById('carteDesequilibreTitre').textContent =
      `L'écriture ${ns} du journal ${journalLabel} n'est pas équilibrée`;
    document.getElementById('carteDesequilibreTexte').textContent =
      `Écart de ${Math.abs(debit - credit).toLocaleString('fr-FR')} FCFA. Corrigez les montants avant de pouvoir fermer la saisie ou enregistrer.`;
  }

  function masquerCarteDesequilibre() {
    carte.style.setProperty('display', 'none', 'important');
  }

  function panneauDirty() {
    return Array.from(body.querySelectorAll('tr')).some(tr =>
      tr.querySelector('.debit').value || tr.querySelector('.credit').value || tr.querySelector('.cpte-general').value
    );
  }

  // ---------- Assistant TVA interactif ----------
  async function appliquerAssistantTVA(tr) {
    const debit = parseFloat(tr.querySelector('.debit').value || 0);
    const credit = parseFloat(tr.querySelector('.credit').value || 0);
    const montantHT = debit || credit;

    if (!montantHT) {
      if (window.Swal) Swal.fire('Assistant TVA', 'Veuillez d\'abord saisir un montant au débit ou au crédit.', 'warning');
      else alert('Veuillez d\'abord saisir un montant au débit ou au crédit.');
      return;
    }

    if (window.Swal) {
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

      const prefixeTVA = debit ? '4452' : '4431';
      const compteTVA = plansComptables.find(p => p.numero_de_compte.startsWith(prefixeTVA))
        || plansComptables.find(p => p.numero_de_compte.startsWith('445') || p.numero_de_compte.startsWith('443'));

      ajouterLigne({
        plan_comptable_id: compteTVA ? compteTVA.id : '',
        debit: debit ? montantTVA : '',
        credit: credit ? montantTVA : '',
      });

      Swal.fire({
        icon: 'success',
        title: 'TVA Ajoutée',
        text: `Ligne de TVA de ${montantTVA.toLocaleString('fr-FR')} FCFA ajoutée avec succès.`,
        timer: 2000,
        showConfirmButton: false
      });
    }
  }

  // ---------- Ventilation Analytique (Bouton 📊) ----------
  function ouvrirModalVentilationRow(tr) {
    const selCompte = tr.querySelector('.cpte-general');
    const compteVal = selCompte ? selCompte.value : '';
    const debit = parseFloat(tr.querySelector('.debit').value || 0);
    const credit = parseFloat(tr.querySelector('.credit').value || 0);
    const montant = debit || credit;

    if (!compteVal || !montant) {
      if (window.Swal) {
        Swal.fire('Ventilation Analytique', 'Veuillez choisir un compte général et saisir un montant au débit ou au crédit avant de ventiler cette ligne.', 'warning');
      } else {
        alert('Veuillez choisir un compte général et saisir un montant au débit ou au crédit avant de ventiler cette ligne.');
      }
      return;
    }

    window.currentRowForVentilation = tr;
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
    
    // Création d'une tr virtuelle pour attacher la ventilation
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
      panel.style.display = 'block';
      document.getElementById('saisieTitre').textContent = 'Nouvelle saisie d\'écriture';
      const btnValider = document.getElementById('btnValiderGrille');
      if (btnValider) btnValider.textContent = 'Valider & enregistrer';
      body.innerHTML = '';
      ajouterLigne();
      ajouterLigne();
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

    panel.style.display = 'block';
    document.getElementById('saisieTitre').textContent = `Modifier l'écriture ${nSaisie}`;
    const btnValider = document.getElementById('btnValiderGrille');
    if (btnValider) btnValider.textContent = 'Enregistrer les modifications';

    const journalSelect = document.getElementById('code_journal_id');
    if (journalSelect && lignesDuGroupe[0].code_journal_id) {
      journalSelect.value = lignesDuGroupe[0].code_journal_id;
    }

    document.getElementById('description_operation').value = lignesDuGroupe[0].description_operation || '';
    const refInput = document.getElementById('reference_piece');
    if (refInput) {
      refInput.value = lignesDuGroupe[0].reference_piece || '';
      if (lignesDuGroupe[0].reference_piece) {
        refInput.readOnly = true;
        refInput.title = 'Référence verrouillée pour ce groupe d\'écritures';
      }
    }
    const pieceInput = document.getElementById('piece_justificatif');
    if (pieceInput && lignesDuGroupe[0].piece) {
      pieceInput.disabled = true;
      pieceInput.title = 'Pièce jointe verrouillée pour ce groupe d\'écritures';
    }
    document.getElementById('n_saisie_user').value = nSaisie;

    body.innerHTML = '';
    lignesDuGroupe.forEach(e => {
      const compte = plansComptables.find(p => p.numero_de_compte === e.compte_general);
      const tiers = plansTiers.find(t => t.numero_de_tiers === e.compte_tiers);
      const poste = comptesTresorerie.find(c => c.name === e.poste_tresorerie);
      ajouterLigne({
        id: e.id,
        plan_comptable_id: compte ? compte.id : null,
        plan_tiers_id: tiers ? tiers.id : null,
        poste_tresorerie_id: poste ? poste.id : null,
        debit: e.debit,
        credit: e.credit,
      });
    });

    calculerTotaux();
    panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function fermer() {
    const { equilibre } = estEquilibre();
    if (!equilibre && panneauDirty()) {
      afficherCarteDesequilibre(document.getElementById('totalDebit').textContent, document.getElementById('totalCredit').textContent);
      carte.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }
    const refInput = document.getElementById('reference_piece');
    if (refInput) {
      refInput.readOnly = false;
      refInput.title = '';
    }
    const pieceInput = document.getElementById('piece_justificatif');
    if (pieceInput) {
      pieceInput.disabled = false;
      pieceInput.title = '';
    }
    panel.style.display = 'none';
    masquerCarteDesequilibre();
    modeEdition = false;
    nSaisieEnEdition = null;
  }

  function onFichierChoisi(input) {
    document.getElementById('pieceLabel').textContent = input.files[0] ? input.files[0].name : 'Pièce jointe (facultatif)';
  }

  // ---------- Collecte pour storeMultiple (nouvelle saisie) ----------
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

    return Array.from(body.querySelectorAll('tr')).map(tr => {
      const vnts = tr.dataset.ventilations ? JSON.parse(tr.dataset.ventilations) : [];
      return {
        id: tr.dataset.ecritureId || null,
        date,
        n_saisie: document.getElementById('n_saisie_user').value,
        code_journal_id: codeJournalId,
        description_operation: description,
        reference_piece: reference,
        plan_comptable_id: tr.querySelector('.cpte-general')?.value || '',
        plan_tiers_id: tr.querySelector('.cpte-tiers')?.value || null,
        debit: parseFloat(tr.querySelector('.debit')?.value || 0),
        credit: parseFloat(tr.querySelector('.credit')?.value || 0),
        poste_tresorerie_id: tr.querySelector('.poste-treso')?.value || null,
        plan_analytique: vnts.length > 0 ? 1 : 0,
        ventilations: vnts,
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
      if (window.Swal) {
        Swal.fire({
          toast: true, position: 'top-end', icon: 'warning',
          title: 'Veuillez sélectionner un Journal de saisie (ex: ACH1, CAI1, BQ01) dans l\'en-tête.',
          timer: 3500
        });
      } else {
        alert('Veuillez sélectionner un Journal de saisie dans l\'en-tête.');
      }
      return;
    } else if (journalSelect) {
      journalSelect.style.borderColor = '';
    }

    const descInput = document.getElementById('description_operation');
    if (!descInput || !descInput.value.trim()) {
      if (descInput) {
        descInput.style.borderColor = '#ef4444';
        descInput.focus();
      }
      if (window.Swal) {
        Swal.fire({
          toast: true, position: 'top-end', icon: 'warning',
          title: 'Veuillez remplir le libellé de l\'opération.',
          timer: 3000
        });
      } else {
        alert('Veuillez remplir le libellé de l\'opération.');
      }
      return;
    } else if (descInput) {
      descInput.style.borderColor = '';
    }

    const rows = Array.from(body.querySelectorAll('tr'));
    let compManquant = false;
    rows.forEach(tr => {
      const selCpte = tr.querySelector('.cpte-general');
      if (selCpte && !selCpte.value) {
        selCpte.style.borderColor = '#ef4444';
        compManquant = true;
      } else if (selCpte) {
        selCpte.style.borderColor = '';
      }
    });

    if (compManquant) {
      if (window.Swal) {
        Swal.fire({
          toast: true, position: 'top-end', icon: 'warning',
          title: 'Veuillez choisir un compte général pour chaque ligne.',
          timer: 3000
        });
      } else {
        alert('Veuillez choisir un compte général pour chaque ligne.');
      }
      return;
    }

    const { equilibre, debit, credit } = estEquilibre();
    if (!equilibre) {
      afficherCarteDesequilibre(debit, credit);
      carte.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }

    const btn = document.getElementById('btnValiderGrille');
    btn.disabled = true;
    btn.textContent = 'Enregistrement…';

    try {
      let res, json;
      if (modeEdition) {
        const lignes = collecterLignes();
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
          ecritures: collecterLignes(),
          lignes: collecterLignes(),
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
        if (json.ecritures && Array.isArray(json.ecritures)) {
          json.ecritures.forEach(ne => {
            ecritures.unshift(ne);
          });
        }
        if (window.Swal) {
          Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: modeEdition ? 'Écriture mise à jour !' : 'Écriture enregistrée avec succès !', timer: 2500, showConfirmButton: false });
        }
        
        body.innerHTML = '';
        ajouterLigne();
        calculerTotaux();

        rafraichirListe();
        appliquerFiltresConsultation();

        const nsUser = document.getElementById('n_saisie_user');
        if (nsUser && nsUser.value) {
          const match = nsUser.value.match(/^(.*?)(\d+)$/);
          if (match) {
            const prefix = match[1];
            const num = parseInt(match[2], 10) + 1;
            nsUser.value = prefix + String(num).padStart(match[2].length, '0');
          }
        }
      } else {
        if (window.Swal) {
          Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: json.message || json.error || 'Erreur de sauvegarde.', timer: 3000 });
        } else {
          alert(json.message || json.error || 'Erreur lors de la sauvegarde.');
        }
      }
    } catch (err) {
      if (window.Swal) {
        Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Erreur réseau : ' + err.message, timer: 3000 });
      } else {
        alert('Erreur réseau : ' + err.message);
      }
    } finally {
      btn.disabled = false;
      btn.textContent = modeEdition ? 'Enregistrer les modifications' : 'Valider & enregistrer';
    }
  }

  // ---------- Création rapide & assignation du poste de trésorerie (selon Images 1 et 2) ----------
  async function creerEtAssignerPosteTresorerie() {
    const nomInput = document.getElementById('nom_poste_tresorerie_input');
    const catSelect = document.getElementById('categorie_poste_tresorerie_select');
    const nom = nomInput ? nomInput.value.trim() : '';
    const cat = catSelect ? catSelect.value : '';

    if (!nom || !cat) {
      if (window.Swal) Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'Veuillez saisir le nom et la catégorie du poste.', timer: 3000 });
      return;
    }

    try {
      const res = await fetch('/api/comptes-tresorerie', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: nom, category_name: cat })
      });
      const json = await res.json();
      if (json.success || json.id) {
        const item = json.data || json;
        const newPoste = { id: item.id || Date.now(), name: item.name || nom };
        comptesTresorerie.push(newPoste);

        document.querySelectorAll('.poste-treso').forEach(sel => {
          sel.innerHTML = optionsPostes(sel.value || newPoste.id);
          sel.disabled = false;
        });

        const modalEl = document.getElementById('modalCreatePoste');
        if (modalEl && window.bootstrap) {
          const modal = bootstrap.Modal.getInstance(modalEl);
          if (modal) modal.hide();
        }

        if (nomInput) nomInput.value = '';
        if (catSelect) catSelect.value = '';

        if (window.Swal) Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Nouveau poste de trésorerie créé et assigné !', timer: 2500 });
      } else {
        if (window.Swal) Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: json.error || json.message || 'Erreur lors de la création.', timer: 3000 });
      }
    } catch (e) {
      if (window.Swal) Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Erreur réseau : ' + e.message, timer: 3000 });
    }
  }

  // Enregistrement en Brouillon
  async function enregistrerBrouillon() {
    const journalId = document.getElementById('code_journal_id')?.value || '';
    if (!journalId) {
      if (window.Swal) Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'Veuillez sélectionner un Journal de saisie dans l\'en-tête.', timer: 3000 });
      return;
    }

    const lines = collecterLignes();
    if (!lines.length) {
      if (window.Swal) Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Veuillez saisir au moins une ligne.', timer: 2500 });
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
        if (window.Swal) Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Brouillon enregistré !', timer: 2500, showConfirmButton: false });
        fermer();
      } else {
        if (window.Swal) Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: json.error || 'Erreur de brouillon.', timer: 3000 });
        else alert(json.error || 'Erreur lors de l\'enregistrement du brouillon.');
      }
    } catch (e) {
      if (window.Swal) Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Erreur réseau : ' + e.message, timer: 3000 });
      else alert('Erreur réseau : ' + e.message);
    } finally {
      if (btn) btn.disabled = false;
    }
  }

  // ---------- Modèle de saisie ----------
  function appliquerModele(modeleId) {
    const mod = window.MODELES_SAISIE.find(m => m.id == modeleId);
    if (!mod || !mod.lignes) return;
    body.innerHTML = '';
    mod.lignes.forEach(l => ajouterLigne(l));
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
        if (window.Swal) Swal.fire({ icon: 'success', title: 'Modèle créé !', text: `Modèle "${nom}" créé et sélectionné.`, timer: 2000, showConfirmButton: false });
      } else {
        alert(json.message || 'Erreur lors de la création du modèle.');
      }
    } catch (e) {
      alert('Erreur réseau : ' + e.message);
    }
  }

  async function enregistrerCommeModele() {
    ouvrirModalCreerModele();
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

  // ---------- Affectation rapide du Poste Trésorerie ----------
  async function ouvrirAffectationPoste(ecritureId) {
    const e = ecritures.find(x => x.id == ecritureId);
    if (!e) return;

    const modalEl = document.getElementById('modalCreatePoste');
    if (modalEl && window.bootstrap) {
      const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      modal.show();
    }
  }

  // ---------- Suppression d'un groupe d'écritures ----------
  async function supprimerGroupe(nSaisie) {
    if (!window.Swal) {
      if (!confirm(`Voulez-vous vraiment supprimer l'écriture ${nSaisie} ?`)) return;
    } else {
      const res = await Swal.fire({
        title: 'Supprimer l\'écriture ?',
        text: `Voulez-vous vraiment supprimer définitivement l'écriture ${nSaisie} ?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
      });
      if (!res.isConfirmed) return;
    }

    try {
      const resp = await fetch(`/accounting_entry_real_goupes/supprimer/${encodeURIComponent(nSaisie)}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
      });
      const data = await resp.json();
      if (data.success || resp.ok) {
        ecritures = ecritures.filter(x => x.n_saisie !== nSaisie);
        if (window.Swal) {
          Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Écriture supprimée !', timer: 2500, showConfirmButton: false });
        }
        rafraichirListe();
        appliquerFiltresConsultation();
      } else {
        if (window.Swal) Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: data.message || data.error || 'Erreur de suppression.', timer: 3000 });
        else alert(data.message || data.error || 'Erreur lors de la suppression.');
      }
    } catch (err) {
      if (window.Swal) Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Erreur réseau : ' + err.message, timer: 3000 });
      else alert('Erreur réseau : ' + err.message);
    }
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
    if (estClasse5 && !e.poste_tresorerie) {
      posteTresorerieHTML = `<button type="button" class="btn btn-xs btn-outline-warning px-2 py-0 fw-bold" title="Affecter un poste de trésorerie" onclick="event.stopPropagation(); saisieGrille.ouvrirAffectationPoste('${e.id}')"><i class="bx bx-plus me-1"></i>Affecter</button>`;
    }

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

  function ouvrirScanner() {
    scannerFacture();
  }

  // ---------- Refresh Tableau Sage ----------
  function rafraichirListe() {
    const journalId = document.getElementById('code_journal_id')?.value || '';
    const moisVal = document.getElementById('mois_ecriture')?.value || '';
    const jourVal = document.getElementById('jour_ecriture')?.value || '';

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
    if (jourVal) {
      lignes = lignes.filter(e => {
        if (!e.date) return false;
        const p = e.date.split('-');
        const j = p.length === 3 ? parseInt(p[2]) : null;
        return j == jourVal;
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

      // Auto-scroll vers le bas dans le conteneur Sage lors de la saisie
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

  // ---------- Init & Event Listeners ----------
  document.addEventListener('DOMContentLoaded', () => {
    const sJourn = localStorage.getItem('fc_saisie_journal_id');
    const sMois = localStorage.getItem('fc_saisie_mois_ecriture');
    const sJour = localStorage.getItem('fc_saisie_jour_ecriture');
    if (sJourn && document.getElementById('code_journal_id')) document.getElementById('code_journal_id').value = sJourn;
    if (sMois && document.getElementById('mois_ecriture')) document.getElementById('mois_ecriture').value = sMois;
    if (sJour && document.getElementById('jour_ecriture')) document.getElementById('jour_ecriture').value = sJour;

    document.getElementById('code_journal_id')?.addEventListener('change', function () {
      localStorage.setItem('fc_saisie_journal_id', this.value);
      if (!modeEdition && panel.style.display !== 'none' && body.children.length === 0) {
        ajouterLigne();
        ajouterLigne();
      }
      rafraichirListe();
    });

    document.getElementById('mois_ecriture')?.addEventListener('change', function () {
      localStorage.setItem('fc_saisie_mois_ecriture', this.value);
      rafraichirListe();
    });

    document.getElementById('jour_ecriture')?.addEventListener('change', function () {
      localStorage.setItem('fc_saisie_jour_ecriture', this.value);
      rafraichirListe();
    });

    document.getElementById('modele_saisie')?.addEventListener('change', function () {
      if (this.value) appliquerModele(this.value);
    });

    // Ouverture automatique du panneau si URL contient ?open=1
    if (new URLSearchParams(window.location.search).get('open') === '1') {
      toggle();
    }

    rafraichirListe();
    appliquerFiltresConsultation();
  });

  return {
    toggle, fermer, editerGroupe, supprimerGroupe, ajouterLigne, supprimerLigne, calculerTotaux, enregistrer, enregistrerBrouillon,
    appliquerModele, enregistrerCommeModele, ouvrirModalCreerModele, enregistrerNouveauModeleInline,
    rafraichirListe, toggleFiltreDesequilibre, onFichierChoisi, ouvrirScanner,
    switchViewMode, toggleHeaderCard, appliquerFiltresConsultation, appliquerAssistantTVA, ouvrirModalVentilationRow,
    ouvrirVentilationExisting, ouvrirAffectationPoste
  };
})();
