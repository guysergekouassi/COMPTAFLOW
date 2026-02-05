<div class="modal fade" id="modalImportInstructions" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl rounded-3xl overflow-hidden">
            <div class="modal-header bg-slate-900 p-6">
                <h5 class="modal-title text-white font-black"><i class="fa-solid fa-circle-info me-2"></i> Guide d'Importation : Journaux</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-8">
                <!-- Structure -->
                <div class="bg-emerald-50 p-6 rounded-2xl mb-6 border border-emerald-100">
                    <h6 class="font-black text-emerald-900 mb-4">Structure attendue du fichier (Colonnes)</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered bg-white">
                            <thead class="bg-emerald-100">
                                <tr>
                                    <th class="font-black text-emerald-800">Code Journal</th>
                                    <th class="font-black text-emerald-800">Intitulé</th>
                                    <th class="font-black text-emerald-800">Type</th>
                                    <th class="font-black text-emerald-800">Compte Trésorerie</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <tr><td>ACH</td><td>JOURNAL DES ACHATS</td><td>Achats</td><td class="text-muted italic">-</td></tr>
                                <tr><td>VEN</td><td>JOURNAL DES VENTES</td><td>Ventes</td><td class="text-muted italic">-</td></tr>
                                <tr><td>BQ1</td><td>SOCIETE GENERALE CI</td><td>Banque</td><td>521100</td></tr>
                                <tr><td>CSH</td><td>CAISNE PRINCIPALE</td><td>Caisse</td><td>571100</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Règles Métiers -->
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card h-100 border-slate-100 shadow-sm rounded-2xl">
                            <div class="card-body p-5">
                                <h6 class="font-black mb-3"><i class="fa-solid fa-list-check text-blue-600 me-2"></i> Colonnes Critiques</h6>
                                <ul class="list-unstyled text-sm space-y-2">
                                    <li class="d-flex align-items-start gap-2">
                                        <i class="fa-solid fa-check text-green-500 mt-1"></i>
                                        <span><strong>Code Journal</strong> : Longueur définie dans la config (ex: 3 char).</span>
                                    </li>
                                    <li class="d-flex align-items-start gap-2">
                                        <i class="fa-solid fa-check text-green-500 mt-1"></i>
                                        <span><strong>Intitulé</strong> : Libellé clair identifiant le journal.</span>
                                    </li>
                                    <li class="d-flex align-items-start gap-2">
                                        <i class="fa-solid fa-check text-green-500 mt-1"></i>
                                        <span><strong>Type</strong> : Doit être l'une des valeurs prédéfinies.</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100 border-slate-100 shadow-sm rounded-2xl">
                            <div class="card-body p-5">
                                <h6 class="font-black mb-3"><i class="fa-solid fa-lightbulb text-warning me-2"></i> Astuces d'Expert</h6>
                                <ul class="list-unstyled text-sm space-y-2">
                                    <li class="d-flex align-items-start gap-2 text-slate-600">
                                        <i class="fa-solid fa-circle text-[6px] mt-2"></i>
                                        <span>Pour les types <strong>Banque</strong>, assurez-vous que le compte commence par <strong>52</strong>.</span>
                                    </li>
                                    <li class="d-flex align-items-start gap-2 text-slate-600">
                                        <i class="fa-solid fa-circle text-[6px] mt-2"></i>
                                        <span>Le système ignorera les lignes vides ou avec des codes déjà existants.</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning border-0 mt-6 rounded-2xl d-flex align-items-center gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                    <p class="mb-0 text-sm font-bold">L'importation écrase les paramètres de base si vous utilisez des codes identiques mais avec des intitulés différents.</p>
                </div>
            </div>
            <div class="modal-footer bg-slate-50 p-6 border-0">
                <button type="button" class="btn btn-primary font-black px-8 py-3 rounded-xl shadow-lg" data-bs-dismiss="modal">J'ai compris, lancer l'import</button>
            </div>
        </div>
    </div>
</div>
