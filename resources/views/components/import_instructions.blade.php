<div class="modal fade" id="modalImportInstructions" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl rounded-3xl overflow-hidden">
            <div class="modal-header bg-slate-900 p-6">
                <h5 class="modal-title text-white font-black"><i class="fa-solid fa-circle-info me-2"></i> Guide d'Importation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-8">
                <div class="nav-align-top mb-4">
                    <ul class="nav nav-pills mb-3 gap-2 justify-content-center" role="tablist">
                        <li class="nav-item">
                            <button type="button" class="nav-link active rounded-pill font-bold" role="tab" data-bs-toggle="tab" data-bs-target="#navs-plan">
                                Modèle de Plan
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link rounded-pill font-bold" role="tab" data-bs-toggle="tab" data-bs-target="#navs-tiers">
                                Modèle de Tiers
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link rounded-pill font-bold" role="tab" data-bs-toggle="tab" data-bs-target="#navs-journaux">
                                Structure Journaux
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link rounded-pill font-bold" role="tab" data-bs-toggle="tab" data-bs-target="#navs-ecritures">
                                Écritures
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content p-0 border-0 shadow-none bg-transparent">
                        <!-- PLAN COMPTABLE -->
                        <div class="tab-pane fade show active" id="navs-plan" role="tabpanel">
                            <div class="bg-blue-50 p-6 rounded-2xl mb-6">
                                <h6 class="font-black text-blue-900 mb-2">Structure attendue (Colonnes)</h6>
                                <p class="text-sm text-blue-700 mb-4">Vérifier la configuration avant d'importer le plan général. Les numéros de compte généraux seront générés par le système en fonction de l'original et du nombre de caractères défini dans la configuration, tout en conservant l'origine pour avoir une trace.</p>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered bg-white">
                                        <thead>
                                            <tr>
                                                <th class="bg-blue-100 text-muted italic">Numéro de compte (Auto)</th>
                                                <th class="bg-blue-100">Intitulé du compte *</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td>601000</td><td>ACHATS DE MARCHANDISES</td></tr>
                                            <tr><td>701000</td><td>VENTES DE MARCHANDISES</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="alert alert-info border-0 text-sm font-bold">
                                <i class="fa-solid fa-circle-check me-2"></i>
                                Vérifier la configuration avant d'importer le plan général. Les numéros de compte généraux seront générés par le système en fonction de l'original et du nombre de caractères défini dans la configuration, tout en conservant l'origine pour avoir une trace.
                            </div>
                        </div>

                        <!-- TIERS -->
                        <div class="tab-pane fade" id="navs-tiers" role="tabpanel">
                            <div class="bg-indigo-50 p-6 rounded-2xl mb-6">
                                <h6 class="font-black text-indigo-900 mb-2">Structure attendue (Colonnes)</h6>
                                <p class="text-sm text-indigo-700 mb-4">Le <strong>Numéro de Tiers</strong> est généré automatiquement par le système en fonction du préfixe du tiers original et de la configuration (le nombre de caractères et le type de code). La catégorie ou type est aussi déterminée en fonction du préfixe du tiers original.</p>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered bg-white">
                                        <thead>
                                            <tr>
                                                <th class="bg-indigo-100 text-muted italic">Numéro de Tiers (Auto)</th>
                                                <th class="bg-indigo-100">Nom / Intitulé *</th>
                                                <th class="bg-indigo-100 text-muted italic">Catégorie / Type (Auto)</th>
                                                <th class="bg-indigo-100 text-muted italic">Compte général (Auto)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td>T0001</td><td>ETS KOUASSI & FILS</td><td>Fournisseur</td><td>401100</td></tr>
                                            <tr><td>T0002</td><td>BOUTIQUE DU PLATEAU</td><td>Client</td><td>411100</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="alert alert-warning border-0 text-sm font-bold">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                Le compte général doit exister dans votre plan comptable avant l'importation des tiers.
                            </div>
                        </div>

                        <!-- JOURNAUX -->
                        <div class="tab-pane fade" id="navs-journaux" role="tabpanel">
                            <div class="bg-emerald-50 p-6 rounded-2xl mb-6">
                                <h6 class="font-black text-emerald-900 mb-2">Structure attendue (Colonnes)</h6>
                                <p class="text-sm text-emerald-700 mb-4">Les journaux seront créés ou mis à jour selon la configuration. Les comptes de trésorerie associés doivent être configurés pour permettre le rapprochement automatique.</p>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered bg-white">
                                        <thead>
                                            <tr>
                                                <th class="bg-emerald-100">Code Journal</th>
                                                <th class="bg-emerald-100">Intitulé</th>
                                                <th class="bg-emerald-100">Type</th>
                                                <th class="bg-emerald-100">Compte Trésorerie</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td>ACH</td><td>JOURNAL DES ACHATS</td><td>Achats</td><td class="text-muted italic">-</td></tr>
                                            <tr><td>VEN</td><td>JOURNAL DES VENTES</td><td>Ventes</td><td class="text-muted italic">-</td></tr>
                                            <tr><td>BQ1</td><td>SOCIETE GENERALE CI</td><td>Banque</td><td>521100</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="alert alert-info border-0 text-sm h-100">
                                        <i class="fa-solid fa-tags me-2"></i>
                                        <strong>Types acceptés :</strong> Achats, Ventes, Caisse, Banque, Opérations Diverses. L'orthographe doit être exacte.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-warning border-0 text-sm h-100 font-bold">
                                        <i class="fa-solid fa-university me-2"></i>
                                        Pour les types <strong>Banque</strong> ou <strong>Caisse</strong>, le compte de trésorerie (Classe 5) est fortement recommandé pour l'imputation automatique.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ECRITURES -->
                        <div class="tab-pane fade" id="navs-ecritures" role="tabpanel">
                            <div class="bg-amber-50 p-6 rounded-2xl mb-6">
                                <h6 class="font-black text-amber-900 mb-2">Structure attendue (Colonnes)</h6>
                                <p class="text-sm text-amber-700 mb-4">L'importation des écritures nécessite une structure précise. Assurez-vous que vos totaux Débit/Crédit sont équilibrés.</p>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered bg-white" style="font-size: 0.75rem;">
                                        <thead>
                                            <tr>
                                                <th class="bg-amber-100">Date / Jour *</th>
                                                <th class="bg-amber-100 text-muted italic">N° Saisie / Écriture</th>
                                                <th class="bg-amber-100">Code Journal *</th>
                                                <th class="bg-amber-100">Référence Pièce</th>
                                                <th class="bg-amber-100">Numéro Compte *</th>
                                                <th class="bg-amber-100">Libellé Opération *</th>
                                                <th class="bg-amber-100">Montant Débit *</th>
                                                <th class="bg-amber-100">Montant Crédit *</th>
                                                <th class="bg-amber-100">Compte Tiers</th>
                                                <th class="bg-amber-100 text-muted italic">Type Écriture (A/G)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td>01/01/2024</td><td>AN</td><td>101000</td><td>REP001</td><td>REPORT A NOUVEAU</td><td>0</td><td>1000000</td></tr>
                                            <tr><td>05/01/2024</td><td>ACH</td><td>601000</td><td>FAC-123</td><td>ACHAT MARCHANDISES</td><td>50000</td><td>0</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                             <div class="alert alert-warning border-0 text-sm font-bold">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                Les comptes (601000) et journaux (AN, ACH) utilisés doivent impérativement exister dans la configuration avant l'import.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-slate-50 p-6 border-0">
                <button type="button" class="btn btn-primary font-black px-8 py-3 rounded-xl" data-bs-dismiss="modal">J'ai compris</button>
            </div>
        </div>
    </div>
</div>
