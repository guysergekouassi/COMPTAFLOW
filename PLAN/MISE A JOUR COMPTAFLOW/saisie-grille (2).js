// saisie-grille.js
// Grille de saisie illimitée + liste des écritures filtrée en direct par journal.
// L'utilisateur peut manipuler autant de lignes qu'il veut, même déséquilibrées (orange),
// MAIS ne peut ni fermer le panneau ni enregistrer tant que le groupe n'est pas équilibré :
// une carte d'avertissement l'indique, et le serveur (storeMultiple) bloque de toute façon
// tout envoi déséquilibré (422) — ce garde-fou front évite juste l'aller-retour inutile.

const saisieGrille = (() => {
  const { plansComptables, plansTiers, comptesTresorerie, idExercice, csrfToken, storeMultipleUrl, ecritures } = window.SAISIE_DATA;

  const body = document.getElementById('grilleBody');
  const listeBody = document.getElementById('listeEcrituresBody');
  const hint = document.getElementById('contrepartieHint');
  const carte = document.getElementById('carteDesequilibre');
  const panel = document.getElementById('panelSaisie');

  let rowCount = 0;
  let filtreDesequilibre = false;
  const contrepartiesFrequentes = JSON.parse(localStorage.getItem('fc_contreparties') || '{}');

  // ---------- Helpers options ----------
  function classeCompte(numero) { return numero ? numero.charAt(0) : ''; }

  function optionsComptes(selectedId) {
    return '<option value="">— Choisir —</option>' + plansComptables.map(p =>
      `<option value="${p.id}" data-numero="${p.numero_de_compte}" ${p.id == selectedId ? 'selected' : ''}>
        ${p.numero_de_compte} - ${p.intitule}
      </option>`
    ).join('');
  }
  function optionsTiers(compteGeneralId, selectedId) {
    const tiers = plansTiers.filter(t => t.compte_general == compteGeneralId);
    if (!tiers.length) return '<option value="">—</option>';
    return '<option value="">—</option>' + tiers.map(t =>
      `<option value="${t.id}" ${t.id == selectedId ? 'selected' : ''}>${t.numero_de_tiers} - ${t.intitule}</option>`
    ).join('');
  }
  function optionsPostes(selectedId) {
    return '<option value="">—</option>' + comptesTresorerie.map(c =>
      `<option value="${c.id}" ${c.id == selectedId ? 'selected' : ''}>${c.name}</option>`
    ).join('');
  }

  function brancherBoutonPlus(btn, modalId, rowId, cible) {
    if (!btn) return;
    btn.addEventListener('click', () => {
      window._fcCibleCreation = { rowId, cible };
      const modalEl = document.getElementById(modalId);
      if (modalEl && window.bootstrap) new bootstrap.Modal(modalEl).show();
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
      <td class="text-center"><button type="button" class="btn btn-sm btn-tva" title="Appliquer TVA" style="background:#eff6ff;color:#2563eb;border:none;width:30px"><i class="bx bx-receipt"></i></button></td>
      <td class="text-center"><button type="button" class="btn btn-sm btn-analytique" title="Ventiler" style="background:#eff6ff;color:#2563eb;border:none;width:30px"><i class="bx bx-pie-chart-alt"></i></button></td>
      <td class="text-center"><i class="bx bx-trash text-muted" style="cursor:pointer" onclick="saisieGrille.supprimerLigne('${rowId}')"></i></td>
    `;
    body.appendChild(tr);

    const selCompte = tr.querySelector('.cpte-general');
    const selPoste = tr.querySelector('.poste-treso');
    const btnPlusPoste = tr.querySelector('.btn-plus-poste');

    function onCompteChange() {
      const opt = selCompte.selectedOptions[0];
      const numero = opt ? opt.dataset.numero : '';
      tr.querySelector('.cpte-tiers').innerHTML = optionsTiers(selCompte.value, prefill.plan_tiers_id);
      const estTresorerie = classeCompte(numero) === '5';
      selPoste.disabled = !estTresorerie;
      btnPlusPoste.disabled = !estTresorerie;
      if (!estTresorerie) selPoste.value = '';
    }
    selCompte.addEventListener('change', onCompteChange);
    onCompteChange();

    brancherBoutonPlus(tr.querySelector('.btn-plus'), 'modalCenterCreate', rowId, 'compte');
    brancherBoutonPlus(tr.querySelector('.btn-plus-tiers'), 'createTiersModal', rowId, 'tiers');
    brancherBoutonPlus(btnPlusPoste, 'modalCreatePoste', rowId, 'poste');

    const debitInput = tr.querySelector('.debit');
    const creditInput = tr.querySelector('.credit');

    creditInput.addEventListener('keydown', (e) => {
      if ((e.key === 'Enter' || e.key === 'Tab') && body.lastElementChild === tr) {
        e.preventDefault();
        proposerContrepartie(tr);
      }
    });
    [debitInput, creditInput].forEach(el => el.addEventListener('input', calculerTotaux));

    calculerTotaux();
    return tr;
  }

  function proposerContrepartie(tr) {
    const compteId = tr.querySelector('.cpte-general').value;
    const debit = parseFloat(tr.querySelector('.debit').value || 0);
    const credit = parseFloat(tr.querySelector('.credit').value || 0);
    if (!compteId || (!debit && !credit)) {
      ajouterLigne();
      body.lastElementChild.querySelector('.cpte-general').focus();
      return;
    }
    const journalOpt = document.getElementById('code_journal_id').selectedOptions[0];
    const contrepartieSuggeree = contrepartiesFrequentes[compteId] || journalOpt?.dataset.contrepartie || '';

    const nouvelle = ajouterLigne({
      plan_comptable_id: contrepartieSuggeree,
      debit: credit ? credit : '',
      credit: debit ? debit : '',
    });

    if (contrepartieSuggeree) {
      const nomCompte = plansComptables.find(p => p.id == contrepartieSuggeree);
      if (nomCompte) {
        hint.style.setProperty('display', 'flex', 'important');
        hint.querySelector('span').textContent =
          `Contrepartie ${nomCompte.numero_de_compte} · ${nomCompte.intitule} proposée automatiquement — modifiable avant validation.`;
      }
    }
    contrepartiesFrequentes[compteId] = contrepartieSuggeree || contrepartiesFrequentes[compteId];
    localStorage.setItem('fc_contreparties', JSON.stringify(contrepartiesFrequentes));

    nouvelle.querySelector('.cpte-general').focus();
    calculerTotaux();
  }

  function supprimerLigne(rowId) {
    document.getElementById(rowId)?.remove();
    if (!body.children.length) ajouterLigne();
    calculerTotaux();
  }

  // ---------- Équilibre + carte d'avertissement + blocage de sortie ----------
  function estEquilibre() {
    let debit = 0, credit = 0;
    body.querySelectorAll('tr').forEach(tr => {
      debit += parseFloat(tr.querySelector('.debit')?.value || 0);
      credit += parseFloat(tr.querySelector('.credit')?.value || 0);
    });
    return { debit, credit, equilibre: Math.abs(debit - credit) < 0.01 && debit > 0 && body.children.length >= 2 };
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
      badge.textContent = debit === credit ? 'Non équilibré' : `Écart ${Math.abs(debit - credit).toLocaleString('fr-FR')}`;
      badge.className = 'badge bg-danger rounded-pill px-3 py-2';
      btn.disabled = true;
      if (debit > 0 || credit > 0) afficherCarteDesequilibre(debit, credit);
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

  // ---------- Ouverture / fermeture du panneau ----------
  function toggle() {
    if (panel.style.display === 'none') {
      panel.style.display = 'block';
      document.getElementById('saisieTitre').textContent = 'Nouvelle saisie';
      body.innerHTML = '';
      ajouterLigne();
      ajouterLigne();
    } else {
      fermer();
    }
  }

  function fermer() {
    const { equilibre } = estEquilibre();
    if (!equilibre && panneauDirty()) {
      // Blocage : impossible de fermer tant que le groupe n'est pas équilibré
      afficherCarteDesequilibre(document.getElementById('totalDebit').textContent, document.getElementById('totalCredit').textContent);
      carte.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }
    panel.style.display = 'none';
    masquerCarteDesequilibre();
  }

  // Empêche de quitter la page (fermer l'onglet, naviguer ailleurs) tant qu'une saisie est ouverte et déséquilibrée
  window.addEventListener('beforeunload', function (e) {
    if (panel.style.display !== 'none' && panneauDirty() && !estEquilibre().equilibre) {
      e.preventDefault();
      e.returnValue = '';
    }
  });

  function onFichierChoisi(input) {
    document.getElementById('pieceLabel').textContent = input.files[0] ? input.files[0].name : 'Pièce jointe (facultatif)';
  }

  // ---------- Collecte + envoi ----------
  function collecterLignes() {
    const nSaisie = document.getElementById('n_saisie_user').value;
    const codeJournalId = document.getElementById('code_journal_id').value;
    const mois = document.getElementById('mois_ecriture').value.padStart(2, '0');
    const jour = document.getElementById('jour_ecriture').value.padStart(2, '0');
    const annee = new Date().getFullYear();
    const date = `${annee}-${mois}-${jour}`;
    const description = document.getElementById('description_operation').value;
    const reference = document.getElementById('reference_piece').value;

    return Array.from(body.querySelectorAll('tr')).map(tr => ({
      n_saisie: nSaisie,
      code_journal_id: codeJournalId,
      exercices_comptables_id: idExercice,
      date,
      description_operation: description,
      reference_piece: reference,
      plan_comptable_id: tr.querySelector('.cpte-general').value,
      plan_tiers_id: tr.querySelector('.cpte-tiers').value || null,
      debit: parseFloat(tr.querySelector('.debit').value || 0),
      credit: parseFloat(tr.querySelector('.credit').value || 0),
      poste_tresorerie_id: tr.querySelector('.poste-treso').value || null,
    }));
  }

  async function enregistrer() {
    const { equilibre, debit, credit } = estEquilibre();
    if (!equilibre) {
      // Pas d'appel serveur inutile : storeMultiple refusera de toute façon (422)
      afficherCarteDesequilibre(debit, credit);
      carte.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }

    const lignes = collecterLignes();
    const formData = new FormData();
    formData.append('ecritures', JSON.stringify(lignes));

    const fichier = document.getElementById('piece_justificatif').files[0];
    if (fichier) formData.append('piece_justificatif', fichier);

    const btn = document.getElementById('btnValiderGrille');
    btn.disabled = true;
    btn.textContent = 'Enregistrement…';

    try {
      const res = await fetch(storeMultipleUrl, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: formData,
      });
      const json = await res.json();
      if (json.success) {
        window.location.reload();
      } else {
        // Le serveur reste la source de vérité finale (422 si déséquilibré malgré tout)
        afficherCarteDesequilibre(debit, credit);
        document.getElementById('carteDesequilibreTexte').textContent = json.error || 'Erreur lors de l\'enregistrement.';
        btn.disabled = false;
        btn.textContent = 'Valider & enregistrer';
      }
    } catch (e) {
      alert('Erreur réseau : ' + e.message);
      btn.disabled = false;
      btn.textContent = 'Valider & enregistrer';
    }
  }

  function appliquerModele(modeleId) {
    const modele = (window.MODELES_SAISIE || []).find(m => m.id == modeleId);
    if (!modele) return;
    body.innerHTML = '';
    modele.lignes.forEach(l => ajouterLigne(l));
    calculerTotaux();
  }

  // ---------- Scanner facture : reprend la résolution journaux_saisis.find du modal d'origine ----------
  async function ouvrirScanner() {
    const journalSelect = document.getElementById('code_journal_id');
    const moisSelect = document.getElementById('mois_ecriture');
    const idExerciceVal = idExercice;
    const annee = document.getElementById('annee_exercice').value;

    if (!journalSelect.value || !moisSelect.value) {
      alert('Choisis un journal et un mois avant de scanner une facture.');
      return;
    }

    const opt = journalSelect.selectedOptions[0];
    const btn = document.getElementById('btnScannerFacture');
    btn.disabled = true;

    try {
      const res = await fetch(
        window.SAISIE_DATA.journauxSaisisFindUrl + '?' + new URLSearchParams({
          exercice_id: idExerciceVal, annee, code_journal_id: journalSelect.value, mois: moisSelect.value,
        })
      );
      const result = await res.json();
      if (!result.success) {
        alert('Aucun journal trouvé pour ces critères.');
        btn.disabled = false;
        return;
      }

      const params = new URLSearchParams({
        id_exercice: idExerciceVal,
        id_journal: result.id, // id JournauxSaisis résolu, pas le CodeJournal
        annee,
        mois: moisSelect.value,
        code: opt.dataset.code_journal_j,
        type: opt.dataset.type_j,
        intitule: opt.dataset.intitule_j,
        id_code: journalSelect.value, // id CodeJournal
      });
      window.location.href = window.SAISIE_DATA.ecritureScanUrl + '?' + params.toString();
    } catch (e) {
      alert('Erreur réseau : ' + e.message);
      btn.disabled = false;
    }
  }

  // ---------- Filtrage instantané de la liste par journal (aucun aller-retour serveur) ----------
  function rafraichirListe() {
    const journalId = document.getElementById('code_journal_id').value;
    let lignes = journalId ? ecritures.filter(e => e.code_journal_id == journalId) : ecritures;

    if (filtreDesequilibre) {
      const parGroupe = {};
      lignes.forEach(e => { (parGroupe[e.n_saisie] = parGroupe[e.n_saisie] || []).push(e); });
      const groupesDesequilibres = Object.entries(parGroupe)
        .filter(([, l]) => Math.abs(l.reduce((s, x) => s + Number(x.debit), 0) - l.reduce((s, x) => s + Number(x.credit), 0)) > 0.01)
        .map(([ns]) => ns);
      lignes = lignes.filter(e => groupesDesequilibres.includes(e.n_saisie));
    }

    document.getElementById('compteurLignes').textContent = `${lignes.length} écriture(s)`;

    const statutClasses = {
      approved: 'bg-success-subtle text-success', validé: 'bg-success-subtle text-success',
      pending: 'bg-warning-subtle text-warning', 'en attente': 'bg-warning-subtle text-warning',
      rejected: 'bg-danger-subtle text-danger',
    };

    // Détection déséquilibre par groupe pour la colonne débit/crédit en orange
    const parGroupe = {};
    lignes.forEach(e => { (parGroupe[e.n_saisie] = parGroupe[e.n_saisie] || []).push(e); });
    const desequilibreParGroupe = {};
    Object.entries(parGroupe).forEach(([ns, l]) => {
      const d = l.reduce((s, x) => s + Number(x.debit), 0);
      const c = l.reduce((s, x) => s + Number(x.credit), 0);
      desequilibreParGroupe[ns] = Math.abs(d - c) > 0.01;
    });

    listeBody.innerHTML = lignes.map(e => {
      const deseq = desequilibreParGroupe[e.n_saisie];
      return `
      <tr style="${deseq ? 'background:#fff7ed' : ''}">
        <td>${deseq ? '<span style="width:7px;height:7px;border-radius:50%;background:#f97316;display:inline-block" title="Groupe déséquilibré"></span>' : ''}</td>
        <td>${e.date}</td>
        <td>${e.n_saisie}</td>
        <td><span class="badge ${statutClasses[e.statut] || 'bg-secondary-subtle'} rounded-pill">${e.statut}</span></td>
        <td>${e.code_journal}</td>
        <td>${e.poste_tresorerie || '—'}</td>
        <td>${e.reference_piece ?? '—'}</td>
        <td>${e.description_operation ?? ''}</td>
        <td>${e.compte_general}</td>
        <td>${e.compte_tiers || '—'}</td>
        <td>${e.analytique ? '<i class="bx bx-pie-chart-alt"></i>' : '—'}</td>
        <td class="text-end" style="${deseq ? 'color:#c2410c;font-weight:700' : ''}">${Number(e.debit).toLocaleString('fr-FR')}</td>
        <td class="text-end" style="${deseq ? 'color:#c2410c;font-weight:700' : ''}">${Number(e.credit).toLocaleString('fr-FR')}</td>
        <td>${e.piece ? '<i class="bx bx-paperclip"></i>' : '—'}</td>
        <td><i class="bx bx-dots-horizontal-rounded" style="cursor:pointer"></i></td>
      </tr>`;
    }).join('') || `<tr><td colspan="15" class="text-center text-muted py-4">Aucune écriture pour ce journal</td></tr>`;
  }

  function toggleFiltreDesequilibre() {
    filtreDesequilibre = !filtreDesequilibre;
    const chip = document.getElementById('chipDesequilibre');
    chip.style.background = filtreDesequilibre ? '#fff7ed' : '#fff';
    chip.style.borderColor = filtreDesequilibre ? '#f97316' : '#d1d5db';
    chip.style.color = filtreDesequilibre ? '#c2410c' : '';
    rafraichirListe();
  }

  // ---------- Init ----------
  document.getElementById('code_journal_id').addEventListener('change', function () {
    body.innerHTML = '';
    if (panel.style.display !== 'none') ajouterLigne();
    rafraichirListe();
  });
  document.getElementById('modele_saisie').addEventListener('change', function () {
    if (this.value) appliquerModele(this.value);
  });

  rafraichirListe();

  return {
    toggle, fermer, ajouterLigne, supprimerLigne, calculerTotaux, enregistrer,
    appliquerModele, rafraichirListe, toggleFiltreDesequilibre, onFichierChoisi, ouvrirScanner,
  };
})();
