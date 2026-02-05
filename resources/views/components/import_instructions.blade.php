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
                                <p class="text-sm text-blue-700 mb-4">Les colonnes peuvent être dans n'importe quel ordre. Le système identifiera automatiquement les intitulés.</p>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered bg-white">
                                        <thead>
                                            <tr>
                                                <th class="bg-blue-100">N° comptes</th>
                                                <th class="bg-blue-100">Intitulé</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td>601000</td><td>ACHATS DE MARCHANDISES</td></tr>
                                            <tr><td>701000</td><td>VENTES DE MARCHANDISES</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="alert alert-info border-0 text-sm">
                                <i class="fa-solid fa-lightbulb me-2"></i>
                                <strong>Intelligence :</strong> Vous pouvez importer des fichiers sans titres de colonnes. Le système analysera le contenu pour faire correspondre les données. Retirez les lignes de totaux ou informations inutiles du fichier.
                            </div>
                        </div>

                        <!-- TIERS -->
                        <div class="tab-pane fade" id="navs-tiers" role="tabpanel">
                            <div class="bg-indigo-50 p-6 rounded-2xl mb-6">
                                <h6 class="font-black text-indigo-900 mb-2">Structure attendue (Colonnes)</h6>
                                <p class="text-sm text-indigo-700 mb-4">Le <strong>Numéro de Tiers</strong> est généré automatiquement par le système. Vous n'avez pas besoin de le fournir.</p>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered bg-white">
                                        <thead>
                                            <tr>
                                                <th class="bg-indigo-100 text-muted">Numéro Tiers (Auto)</th>
                                                <th class="bg-indigo-100">Nom / Raison Sociale</th>
                                                <th class="bg-indigo-100">Compte général</th>
                                                <th class="bg-indigo-100">Catégorie</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td class="text-muted italic">Généré par Flow</td><td>ETS KOUASSI & FILS</td><td>401100</td><td>Fournisseur</td></tr>
                                            <tr><td class="text-muted italic">Généré par Flow</td><td>BOUTIQUE DU PLATEAU</td><td>411100</td><td>Client</td></tr>
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
                                <p class="text-sm text-emerald-700 mb-4">Définissez vos journaux standards. Les types permettent d'activer des fonctionnalités spécifiques (ex: rapprochement pour Banque/Caisse).</p>
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
                                                <th class="bg-amber-100">Date</th>
                                                <th class="bg-amber-100">Journal</th>
                                                <th class="bg-amber-100">Compte</th>
                                                <th class="bg-amber-100">Réf. Pièce</th>
                                                <th class="bg-amber-100">Libellé</th>
                                                <th class="bg-amber-100">Débit</th>
                                                <th class="bg-amber-100">Crédit</th>
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
