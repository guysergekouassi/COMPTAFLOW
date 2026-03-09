<div class="card shadow-none border-0">
    <div class="card-header bg-label-primary py-3 mb-4">
        <h5 class="mb-0 text-primary fw-bold text-uppercase"><i class="bx bx-check-shield me-2"></i> NOTE 36 : VISA ET ATTESTATION</h5>
    </div>
    <div class="card-body p-0 text-center py-5">
        <div class="mb-4">
            <i class="bx bx-check-double text-success" style="font-size: 5rem;"></i>
        </div>
        <h4 class="fw-bold mb-3">Visa de l'Expert-Comptable</h4>
        <p class="text-muted mb-4 px-5">
            Je soussigné, Expert-Comptable agréé, atteste que la présente liasse fiscale composée de 36 pages (Bilan, Résultat, TFT et Notes annexes) est conforme à la comptabilité de l'entreprise pour l'exercice clos.
        </p>
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="border rounded p-3 bg-light">
                    <p class="mb-1 fw-bold">Fait à :</p>
                    <input type="text" class="form-control form-control-sm text-center mb-3" name="lieu_visa" value="{{ $data['lieu_visa'] ?? 'ABIDJAN' }}">
                    <p class="mb-1 fw-bold">Le :</p>
                    <input type="date" class="form-control form-control-sm text-center" name="date_visa" value="{{ $data['date_visa'] ?? date('Y-m-d') }}">
                </div>
            </div>
        </div>
        <div class="mt-5 alert alert-warning d-inline-block">
            <i class="bx bx-error me-2"></i> Assurez-vous d'avoir vérifié la cohérence des données avant de procéder à l'exportation XML e-SINTAX.
        </div>
    </div>
</div>
