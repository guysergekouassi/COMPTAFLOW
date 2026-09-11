{{--
    Retour immédiat au clic sur le menu.

    Une première version remplaçait le contenu de la page sans la recharger,
    pour imiter les onglets de Mon Espace. C'était une erreur : chaque page
    installe ses propres écouteurs sur le document, et ceux-ci survivaient au
    remplacement. Les scripts du Grand Livre restaient actifs sur la Balance
    des tiers, réclamaient une plage de comptes qui n'y existe pas, et les
    listes se remplissaient autant de fois qu'on avait visité de pages.

    Les onglets de Mon Espace sont instantanés parce que tout est déjà dans la
    page. Ailleurs, chaque page apporte son propre code : on la charge donc
    normalement. Il reste ce qui ne coûte rien et se voit tout de suite —
    l'onglet qui s'active et une barre de progression pendant le chargement.
--}}
<style>
    #barreNavigation {
        position: fixed;
        top: 0;
        left: 0;
        height: 3px;
        width: 0;
        background: linear-gradient(90deg, #2563eb, #8b5cf6);
        box-shadow: 0 0 10px rgba(37, 99, 235, 0.5);
        z-index: 20000;
        opacity: 0;
        transition: width 0.35s ease, opacity 0.2s ease;
        pointer-events: none;
    }
</style>
<div id="barreNavigation"></div>

<script>
(function () {
    if (window.__retourNavigation) return;
    window.__retourNavigation = true;

    const barre = document.getElementById('barreNavigation');
    if (!barre) return;

    function estUnLienDePage(a) {
        if (!a || !a.href || a.target || a.hasAttribute('download')) return false;
        if (a.getAttribute('href').startsWith('#')) return false;
        let url;
        try { url = new URL(a.href); } catch (e) { return false; }
        if (url.origin !== location.origin || !/^https?:$/.test(url.protocol)) return false;
        return !!a.closest('.sidebar-new, .menu-section, .navbar');
    }

    document.addEventListener('click', function (evenement) {
        if (evenement.defaultPrevented) return;                    // onglets de Mon Espace
        if (evenement.button !== 0 || evenement.metaKey || evenement.ctrlKey
            || evenement.shiftKey || evenement.altKey) return;

        const a = evenement.target.closest ? evenement.target.closest('a') : null;
        if (!estUnLienDePage(a) || a.href === location.href) return;

        // Le menu répond avant que la page n'arrive. Le navigateur, lui,
        // charge la page comme il l'a toujours fait.
        document.querySelectorAll('.menu-link-new.active').forEach(l => l.classList.remove('active'));
        if (a.classList.contains('menu-link-new')) a.classList.add('active');

        barre.style.opacity = '1';
        barre.style.width = '80%';
    });

    // Retour arrière depuis le cache du navigateur : la barre ne doit pas rester.
    window.addEventListener('pageshow', function () {
        barre.style.opacity = '0';
        barre.style.width = '0';
    });
})();
</script>
