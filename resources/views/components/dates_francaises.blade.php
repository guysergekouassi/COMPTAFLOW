{{--
    Dates affichées en jj/mm/aaaa, quel que soit le poste.

    Un champ « date » natif s'affiche dans la langue du navigateur : le même
    écran montre 15/01/2024 sur un poste réglé en français et 01-15-2024 sur un
    poste réglé en anglais. Ni le HTML ni la feuille de style ne peuvent le
    changer — c'est le navigateur qui décide. La valeur envoyée, elle, est
    toujours la même, donc rien n'a jamais été enregistré de travers ; seule la
    lecture variait, et c'est bien assez pour se tromper de saisie.

    On pose donc un calendrier en français par-dessus les champs visés. Il
    montre jj/mm/aaaa à tout le monde et continue d'envoyer la date au format
    attendu par le serveur. Si la bibliothèque ne se charge pas, les champs
    restent des champs « date » natifs : on n'a rien perdu.

    Variable : $champs — sélecteur CSS des champs concernés.
--}}
@php $champs = $champs ?? 'input[type="date"]'; @endphp

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/fr.js"></script>

<script>
(function () {
    function poser() {
        if (typeof flatpickr === 'undefined') return;   // sans la bibliotheque, on garde le champ natif

        if (flatpickr.l10ns && flatpickr.l10ns.fr) {
            flatpickr.localize(flatpickr.l10ns.fr);
        }

        document.querySelectorAll(@json($champs)).forEach(function (champ) {
            if (champ.dataset.calendrierFr === 'pose') return;
            champ.dataset.calendrierFr = 'pose';

            // Le champ garde son identifiant et sa valeur au format du serveur ;
            // c'est le champ jumeau, visible, qui affiche jj/mm/aaaa.
            flatpickr(champ, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                allowInput: true,
                locale: 'fr',
                onReady: function (dates, valeur, instance) {
                    instance.altInput.placeholder = 'jj/mm/aaaa';
                    instance.altInput.className = champ.className;
                },
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', poser);
    } else {
        poser();
    }
})();
</script>
