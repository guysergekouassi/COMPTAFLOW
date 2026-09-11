{{--
    Navigation instantanée du menu.

    Les onglets de Mon Espace changent sans recharger : la page est déjà là,
    on la montre. Ailleurs, chaque page est une adresse à part. On reprend le
    même effet en allant chercher la page en arrière-plan, dès le survol du
    lien, puis en remplaçant le contenu affiché au clic.

    Prudence : au moindre doute — lien externe, réponse inattendue, erreur
    pendant l'échange — on laisse le navigateur charger la page comme avant.
    Rien ne se perd, c'est simplement moins immédiat.
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
        transition: width 0.2s ease, opacity 0.2s ease;
        pointer-events: none;
    }
    .menu-link-new.est-en-route { opacity: 0.75; }
</style>
<div id="barreNavigation"></div>

<script>
(function () {
    if (window.__navigationInstantanee) return;
    window.__navigationInstantanee = true;

    const PAGES = new Map();   // adresse -> promesse du HTML
    const PLAFOND = 12;
    const barre = document.getElementById('barreNavigation');
    let enCours = null;

    // ── Retour visuel ────────────────────────────────────────────────────────
    let minuterie = null;
    function demarrerBarre() {
        clearTimeout(minuterie);
        barre.style.opacity = '1';
        barre.style.width = '35%';
        minuterie = setTimeout(() => { barre.style.width = '70%'; }, 250);
    }
    function acheverBarre() {
        clearTimeout(minuterie);
        barre.style.width = '100%';
        setTimeout(() => {
            barre.style.opacity = '0';
            setTimeout(() => { barre.style.width = '0'; }, 200);
        }, 150);
    }

    // ── Quels liens nous concernent ──────────────────────────────────────────
    function lienNavigable(a) {
        if (!a || !a.href || a.target || a.hasAttribute('download')) return false;
        if (a.dataset.nav === 'complet') return false;
        if (a.getAttribute('href').startsWith('#')) return false;
        let url;
        try { url = new URL(a.href); } catch (e) { return false; }
        if (url.origin !== location.origin) return false;
        if (!/^https?:$/.test(url.protocol)) return false;
        // Un menu, pas toute la page : on ne détourne que la barre latérale.
        return !!a.closest('.sidebar-new, .menu-section, .navbar');
    }

    // ── Aller chercher la page à l'avance ────────────────────────────────────
    function precharger(adresse) {
        if (PAGES.has(adresse)) return PAGES.get(adresse);

        const promesse = fetch(adresse, {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'NavigationInstantanee' },
        }).then(reponse => {
            const type = reponse.headers.get('content-type') || '';
            if (!reponse.ok || !type.includes('text/html')) {
                throw new Error('reponse inattendue');
            }
            return reponse.text();
        });

        if (PAGES.size >= PLAFOND) PAGES.delete(PAGES.keys().next().value);
        PAGES.set(adresse, promesse);
        promesse.catch(() => PAGES.delete(adresse));

        return promesse;
    }

    // ── Feuilles de style propres à la page ─────────────────────────────────
    function empreinte(noeud) {
        if (noeud.tagName === 'LINK') return 'L|' + (noeud.getAttribute('href') || '');
        if (noeud.tagName === 'STYLE') return 'S|' + noeud.textContent.length + '|' + noeud.textContent.slice(0, 120);
        return null;
    }

    function accorderLaTete(nouveauDocument) {
        const anciennes = new Map();
        document.head.querySelectorAll('style, link[rel="stylesheet"]').forEach(n => {
            const cle = empreinte(n);
            if (cle) anciennes.set(cle, n);
        });

        const attendues = new Set();
        nouveauDocument.head.querySelectorAll('style, link[rel="stylesheet"]').forEach(n => {
            const cle = empreinte(n);
            if (!cle) return;
            attendues.add(cle);
            if (!anciennes.has(cle)) {
                document.head.appendChild(n.cloneNode(true));
            }
        });

        // Les styles de la page quittée s'en vont avec elle
        anciennes.forEach((noeud, cle) => {
            if (!attendues.has(cle)) noeud.remove();
        });
    }

    // ── Les scripts insérés par innerHTML ne s'exécutent pas : on les rejoue ─
    function rejouer(script) {
        return new Promise(resolve => {
            const neuf = document.createElement('script');
            for (const attribut of script.attributes) {
                neuf.setAttribute(attribut.name, attribut.value);
            }
            if (script.src) {
                neuf.addEventListener('load', resolve, { once: true });
                neuf.addEventListener('error', resolve, { once: true });
            }
            neuf.textContent = script.textContent;
            script.parentNode.replaceChild(neuf, script);
            if (!script.src) resolve();
        });
    }

    async function rejouerLesScripts() {
        const scripts = Array.from(document.body.querySelectorAll('script'));
        for (const script of scripts) {
            await rejouer(script);
        }
    }

    // ── Le remplacement ──────────────────────────────────────────────────────
    async function aller(adresse, empiler) {
        if (enCours === adresse) return;
        enCours = adresse;
        demarrerBarre();

        let html;
        try {
            html = await precharger(adresse);
        } catch (e) {
            location.href = adresse;
            return;
        }

        try {
            const nouveau = new DOMParser().parseFromString(html, 'text/html');

            // Une page hors modèle (connexion, erreur, téléchargement) se charge normalement
            if (!nouveau.querySelector('.layout-page') || !nouveau.body) {
                location.href = adresse;
                return;
            }

            accorderLaTete(nouveau);
            document.title = nouveau.title || document.title;
            document.body.className = nouveau.body.className;
            document.body.innerHTML = nouveau.body.innerHTML;

            if (empiler) history.pushState({ instantanee: true }, '', adresse);
            window.scrollTo(0, 0);

            await rejouerLesScripts();
            document.dispatchEvent(new Event('DOMContentLoaded', { bubbles: true }));
        } catch (e) {
            location.href = adresse;
            return;
        } finally {
            enCours = null;
            acheverBarre();
        }
    }

    // ── Écoutes, posées sur le document : elles survivent au remplacement ────
    let survol = null;
    document.addEventListener('mouseover', function (evenement) {
        const a = evenement.target.closest ? evenement.target.closest('a') : null;
        if (!lienNavigable(a)) return;
        clearTimeout(survol);
        survol = setTimeout(() => precharger(a.href).catch(() => {}), 90);
    });

    document.addEventListener('mouseout', () => clearTimeout(survol));

    document.addEventListener('click', function (evenement) {
        if (evenement.defaultPrevented) return;              // onglets de Mon Espace
        if (evenement.button !== 0 || evenement.metaKey || evenement.ctrlKey
            || evenement.shiftKey || evenement.altKey) return;

        const a = evenement.target.closest ? evenement.target.closest('a') : null;
        if (!lienNavigable(a)) return;
        if (a.href === location.href) return;

        evenement.preventDefault();

        // Le menu réagit tout de suite, avant même que la page arrive
        document.querySelectorAll('.menu-link-new.active').forEach(l => l.classList.remove('active'));
        if (a.classList.contains('menu-link-new')) a.classList.add('active');

        aller(a.href, true);
    });

    window.addEventListener('popstate', function (evenement) {
        if (evenement.state && evenement.state.instantanee) {
            aller(location.href, false);
        }
    });
})();
</script>
