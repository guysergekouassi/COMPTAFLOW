<div class="card shadow-none border-0">
    <div class="card-header bg-label-dark py-3 mb-4">
        <h5 class="mb-0 text-dark fw-bold text-uppercase"><i class="bx bx-list-ul me-2"></i> NOTE 35 : AUTRES TABLEAUX ANNEXES</h5>
    </div>
    <div class="card-body p-0">
        <p class="text-muted mb-3">Veuillez renseigner tout autre élément requis par l'administration fiscale non couvert par les notes précédentes.</p>
        <div class="mb-3">
            <label class="form-label fw-bold">Détails libres :</label>
            <textarea class="form-control" name="note_35_free_text" rows="10" placeholder="Saisissez ici les informations complémentaires...">{{ $data['note_35_free_text'] ?? '' }}</textarea>
        </div>
    </div>
</div>
