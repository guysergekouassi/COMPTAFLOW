const fs = require('fs');

const file = 'rapport_de_stage.html';
let html = fs.readFileSync(file, 'utf8');

// 1. Cover Modifications
const typeReportRegex = /<!-- TYPE DE RAPPORT -->[\s\S]*?(?=<!-- BOITE TITRE -->)/;
const newTypeReport = "<!-- TYPE DE RAPPORT -->\n" +
    "            <div style=\"text-align:center;margin:20px 0;\">\n" +
    "                <div style=\"display:inline-block; border: 3px solid var(--text); padding: 15px 30px; margin-bottom: 20px; border-radius: 8px;\">\n" +
    "                    <h4 style=\"font-size:18pt;margin:0;letter-spacing:2px;color:var(--text);text-transform:uppercase; font-weight:900;\">\n" +
    "                        RAPPORT DE STAGE DE FIN DE CYCLE</h4>\n" +
    "                </div>\n" +
    "                <p style=\"font-size:12pt;margin:8px 0 15px 0;\">En vue de l'obtention du <em>Brevet de Technicien Supérieur (BTS)</em></p>\n" +
    "                <div style=\"display:inline-block; border: 2px solid var(--primary); padding: 8px 20px; border-radius: 5px;\">\n" +
    "                    Filière : <span class=\"bold\" style=\"color:var(--primary);\">Informatique Développeur d'Application (IDA)</span>\n" +
    "                </div>\n" +
    "            </div>\n            ";
html = html.replace(typeReportRegex, newTypeReport);

const juryRegex = /<div class="jury-column" style="text-align:right;">[\s\S]*?<h5>Directeur de Mémoire :<\/h5>[\s\S]*?<\/div>/;
html = html.replace(juryRegex, '');

// 2. LOGOS
html = html.replace(/<img src="([^"]+)" alt="Logo Ecole ISTEMA" style="[^"]*">/g, '<img src="$1" alt="Logo Ecole ISTEMA" style="max-height:140px; max-width:180px; width:auto; display:block;">');
html = html.replace(/<img src="([^"]+)" alt="Armoiries CI" style="[^"]*">/g, '<img src="$1" alt="Armoiries CI" style="max-height:140px; max-width:180px; width:auto; display:block; margin-left:auto;">');

html = html.replace(/<img src="([^"]+)" alt="Entreprise Leader" style="[^"]*">/g, '<img src="$1" alt="Entreprise Leader" style="max-height:150px; max-width:200px; width:auto; display:block;">');
html = html.replace(/<img src="([^"]+)" alt="Logo MESRS" style="[^"]*">/g, '<img src="$1" alt="Logo MESRS" style="max-height:130px; max-width:180px; width:auto; display:block; margin-left:auto;">');

// 3. Liste des Sigles et Abréviations
const siglesHtml = "    <!-- SIGLES ET ABREVIATIONS -->\n" +
    "    <div class=\"page page-break\">\n" +
    "        <h1>LISTES DES SIGLES ET ABRÉVIATIONS</h1>\n" +
    "        <ul style=\"line-height:2.5; font-size:14pt; margin-top:30px;\">\n" +
    "            <li><strong>BTS</strong> : Brevet de Technicien Supérieur</li>\n" +
    "            <li><strong>IDA</strong> : Informatique Développeur d'Application</li>\n" +
    "            <li><strong>ISTEMA</strong> : Institut Supérieur de Technologie et de Management</li>\n" +
    "            <li><strong>SARL</strong> : Société à Responsabilité Limitée</li>\n" +
    "            <li><strong>SYSCOHADA</strong> : Système Comptable pour l'Organisation en Afrique du Droit des Affaires</li>\n" +
    "            <li><strong>MCD</strong> : Modèle Conceptuel de Données</li>\n" +
    "            <li><strong>MLD</strong> : Modèle Logique de Données</li>\n" +
    "            <li><strong>SAT</strong> : Schéma de l'Architecture Technique</li>\n" +
    "            <li><strong>MVC</strong> : Modèle - Vue - Contrôleur</li>\n" +
    "        </ul>\n" +
    "    </div>\n";
html = html.replace('<!-- REMERCIEMENTS -->', siglesHtml + '\n    <!-- REMERCIEMENTS -->');

const entitiesList = "        <h3 style=\"margin-top:20px; color:var(--primary);\">Liste des entités et règles de gestion</h3>\n" +
    "        <p>Les règles de gestion (Celles de fait ou de droit) ont permis d'identifier les entités suivantes :</p>\n" +
    "        <ul style=\"line-height: 1.8;\">\n" +
    "            <li><strong>COMPANIES</strong> : Représente les entreprises ou instances utilisant le système.</li>\n" +
    "            <li><strong>USERS</strong> : Les employés, comptables ou administrateurs.</li>\n" +
    "            <li><strong>EXERCICES</strong> : Périodes comptables annuelles.</li>\n" +
    "            <li><strong>PLAN_COMPTABLES</strong> : Les comptes du plan SYSCOHADA.</li>\n" +
    "            <li><strong>PLAN_TIERS</strong> : Les clients, fournisseurs et autres tiers.</li>\n" +
    "            <li><strong>CODE_JOURNALS</strong> : Les journaux comptables (Achats, Ventes, Banque...).</li>\n" +
    "            <li><strong>COMPTE_TRESORERIES</strong> : Les comptes de liquidité (Banque, Caisse).</li>\n" +
    "            <li><strong>ECRITURE_COMPTABLES</strong> : Le cœur du système pour tracer tous les mouvements financiers.</li>\n" +
    "        </ul>\n";
html = html.replace('<!-- MCD SVG -->', entitiesList + '\n    <!-- MCD SVG -->');

const rects = [
    { id: 'C1', x: 380, y: 10, w: 230, h: 160, title: 'COMPANIES', fill: '#ffffff', stroke: '#1B365D', attrs: ['id (PK)', 'company_name', 'juridique_form', 'social_capital', 'adresse / city', 'email_adresse', 'is_blocked'] },
    { id: 'C2', x: 760, y: 30, w: 190, h: 140, title: 'USERS', fill: '#ffffff', stroke: '#1B365D', attrs: ['id (PK)', 'name / last_name', 'email_adresse', 'password (hash)', 'role', 'is_online'] },
    { id: 'C3', x: 10, y: 30, w: 200, h: 120, title: 'EXERCICES', fill: '#ffffff', stroke: '#1B365D', attrs: ['id (PK)', 'intitule / date_debut', 'date_fin', 'is_active / cloturer'] },
    { id: 'C4', x: 10, y: 320, w: 200, h: 120, title: 'PLAN_COMPTABLES', fill: '#ffffff', stroke: '#1B365D', attrs: ['id (PK)', 'numero_de_compte', 'intitule / classe', 'type_de_compte', 'adding_strategy'] },
    { id: 'C5', x: 260, y: 320, w: 180, h: 100, title: 'PLAN_TIERS', fill: '#ffffff', stroke: '#1B365D', attrs: ['id (PK)', 'numero_de_tiers', 'intitule', 'type_de_tiers'] },
    { id: 'C6', x: 490, y: 320, w: 220, h: 120, title: 'CODE_JOURNALS', fill: '#ffffff', stroke: '#1B365D', attrs: ['id (PK)', 'code_journal / intitule', 'type / traite_anal', 'compte_contrepartie', 'rapprochement_sur'] },
    { id: 'C7', x: 760, y: 320, w: 190, h: 120, title: 'COMPTE_TRESORERIES', fill: '#ffffff', stroke: '#1B365D', attrs: ['id (PK)', 'name', 'type', 'solde_initial', 'solde_actuel'] },
    { id: 'C8', x: 300, y: 550, w: 380, h: 220, title: 'ECRITURE_COMPTABLES', fill: '#ffffff', stroke: '#1B365D', attrs: ['id (PK)', 'date', 'n_saisie_user', 'n_saisie', 'description_operation', 'reference_piece', 'piece_justificatif', 'debit', 'credit', 'statut', 'type_flux', 'plan_analytique'] }
];

const C = {};
rects.forEach(r => { C[r.id] = { x: r.x + r.w / 2, y: r.y + r.h / 2 }; });

const assocs = [
    { id: 'A1', x: 250, y: 80, name: 'Gérer', links: [{ to: 'C1', card: '1,n' }, { to: 'C3', card: '1,1' }] },
    { id: 'A2', x: 690, y: 80, name: 'Employer', links: [{ to: 'C1', card: '1,n' }, { to: 'C2', card: '1,1' }] },
    { id: 'A3', x: 110, y: 240, name: 'Constituer', links: [{ to: 'C1', card: '1,n' }, { to: 'C4', card: '1,1' }] },
    { id: 'A6', x: 350, y: 240, name: 'Avoir', links: [{ to: 'C1', card: '1,n' }, { to: 'C5', card: '1,1' }] },
    { id: 'A4', x: 600, y: 240, name: 'Définir', links: [{ to: 'C1', card: '1,n' }, { to: 'C6', card: '1,1' }] },
    { id: 'A5', x: 840, y: 240, name: 'Ouvrir', links: [{ to: 'C1', card: '1,n' }, { to: 'C7', card: '1,1' }] },
    { id: 'A7', x: 110, y: 480, name: 'Imputer', links: [{ to: 'C4', card: '0,n' }, { to: 'C8', card: '1,1' }] },
    { id: 'A8', x: 350, y: 480, name: 'Concerner', links: [{ to: 'C5', card: '0,n' }, { to: 'C8', card: '1,1' }] },
    { id: 'A9', x: 600, y: 480, name: 'Typifier', links: [{ to: 'C6', card: '0,n' }, { to: 'C8', card: '1,1' }] },
    { id: 'A10', x: 840, y: 480, name: 'Trésorerie', links: [{ to: 'C7', card: '0,n' }, { to: 'C8', card: '1,1' }] }
];

let mcdSvg = "<div class=\"mcd-container\" style=\"overflow-x:auto; text-align:center; padding: 20px 0;\">\n" +
    "<svg viewBox=\"0 0 980 840\" xmlns=\"http://www.w3.org/2000/svg\" font-family=\"Arial, sans-serif\" font-size=\"12\" style=\"min-width:800px;width:100%;max-width:980px;background:white; border:1px solid #ccc; padding:10px;\">\n";

assocs.forEach(a => {
    a.links.forEach(l => {
        let p2 = C[l.to];
        mcdSvg += '  <line x1="' + a.x + '" y1="' + a.y + '" x2="' + p2.x + '" y2="' + p2.y + '" stroke="#A0A0A0" stroke-width="2" />\n';
    });
});

rects.forEach(r => {
    mcdSvg += '  <rect x="' + r.x + '" y="' + r.y + '" width="' + r.w + '" height="' + r.h + '" fill="' + r.fill + '" stroke="' + r.stroke + '" stroke-width="1.5" />\n';
    mcdSvg += '  <line x1="' + r.x + '" y1="' + (r.y + 25) + '" x2="' + (r.x + r.w) + '" y2="' + (r.y + 25) + '" stroke="' + r.stroke + '" stroke-width="1.5"/>\n';
    mcdSvg += '  <text x="' + (r.x + r.w / 2) + '" y="' + (r.y + 17) + '" text-anchor="middle" fill="#000" font-weight="bold" font-size="13">' + r.title + '</text>\n';
    let ay = r.y + 42;
    r.attrs.forEach(attr => {
        if (attr.includes('(PK)')) {
            mcdSvg += '  <text x="' + (r.x + 10) + '" y="' + ay + '" fill="#000" font-size="12" font-weight="bold" text-decoration="underline">' + attr + '</text>\n';
        } else {
            mcdSvg += '  <text x="' + (r.x + 10) + '" y="' + ay + '" fill="#333" font-size="12">' + attr + '</text>\n';
        }
        ay += 18;
    });
});

assocs.forEach(a => {
    mcdSvg += '  <circle cx="' + a.x + '" cy="' + a.y + '" r="40" fill="#ffffff" stroke="#1B365D" stroke-width="1.5" />\n';
    mcdSvg += '  <text x="' + a.x + '" y="' + (a.y + 4) + '" text-anchor="middle" font-size="12" font-weight="bold" fill="#000">' + a.name + '</text>\n';
});

assocs.forEach(a => {
    a.links.forEach(l => {
        let p2 = C[l.to];
        let tx = Math.round(p2.x + (a.x - p2.x) * 0.35);
        let ty = Math.round(p2.y + (a.y - p2.y) * 0.35);
        if (l.card === '0,n' || l.card === '1,1' || l.card === '1,n') {
            mcdSvg += '  <rect x="' + (tx - 15) + '" y="' + (ty - 10) + '" width="30" height="20" fill="#ffffff" />\n';
            mcdSvg += '  <text x="' + tx + '" y="' + (ty + 4) + '" font-size="12" fill="#d32f2f" font-weight="bold" text-anchor="middle">' + l.card + '</text>\n';
        }
    });
});

mcdSvg += "</svg>\n</div>\n" +
    "<div class=\"alert-success\" style=\"margin-top:15px; border-left:4px solid #1B365D; background:#f1f8ff; padding:15px; font-size:13px; line-height:1.5;\">\n" +
    "    <strong>Lecture du MCD MERISE :</strong> Les entités (rectangles) regroupent les attributs propres. Les clés primaires sont soulignées et notées <strong>id (PK)</strong>. Les associations (cercles) matérialisent les liens sémantiques entre entités, accompagnées de leurs cardinalités.\n" +
    "    <br><br>La table <strong style=\"color:#1B365D;\">ECRITURE_COMPTABLES</strong> est le cœur transactionnel du système : elle est connectée à l'ensemble du réseau des entités (journaux, plan comptable, trésorerie).\n" +
    "</div>\n";

const mcdRegex = /<div class="mcd-container"[\s\S]*?<\/svg>\s*<\/div>[\s\S]*?<\/div>\s*<\/div>/;
html = html.replace(mcdRegex, mcdSvg);

const annexesHtml = "    <!-- ANNEXES -->\n" +
    "    <div class=\"page page-break\">\n" +
    "        <h1>ANNEXES</h1>\n" +
    "        \n" +
    "        <div style=\"margin-top:40px;\">\n" +
    "            <h3 style=\"color:var(--primary); text-align:center; margin-bottom:10px;\">ANNEXE 1 : Extrait du Code de l'Application (Modèle)</h3>\n" +
    "            <div style=\"border:1px solid #ccc; padding:15px; background:#f8f9fa;\">\n" +
    "                <pre style=\"font-size:11px; color:#333;\">\n" +
    "namespace App\\Models;\n\n" +
    "use Illuminate\\Database\\Eloquent\\Model;\n\n" +
    "class EcritureComptable extends Model\n" +
    "{\n" +
    "    protected $fillable = [\n" +
    "        'date', 'n_saisie', 'reference_piece', \n" +
    "        'piece_justificatif', 'debit', 'credit', 'statut'\n" +
    "    ];\n    \n" +
    "    public function journal() {\n" +
    "        return $this->belongsTo(CodeJournal::class);\n" +
    "    }\n" +
    "}\n" +
    "                </pre>\n" +
    "            </div>\n" +
    "            <p style=\"text-align:center; font-style:italic; font-size:11pt; margin-top:5px;\">Légende : annexe 1 pour Extrait du code de la classe EcritureComptable dans Laravel.</p>\n" +
    "        </div>\n\n" +
    "        <div style=\"margin-top:50px;\">\n" +
    "            <h3 style=\"color:var(--primary); text-align:center; margin-bottom:10px;\">ANNEXE 2 : Interface de Suivi du SyscoHada</h3>\n" +
    "            <div style=\"border:1px dashed #ccc; padding:20px; text-align:center; background:#fff;\">\n" +
    "                <i>(Capture d'écran de l'interface de gestion de plan comptable)</i>\n" +
    "            </div>\n" +
    "            <p style=\"text-align:center; font-style:italic; font-size:11pt; margin-top:5px;\">Légende : annexe 2 pour Formulaire d'ajout d'une écriture selon le plan comptable.</p>\n" +
    "        </div>\n" +
    "    </div>\n";
html += annexesHtml;

let newToc = "    <!-- SOMMAIRE -->\n" +
    "    <div class=\"page page-break\">\n" +
    "        <h1>SOMMAIRE</h1>\n" +
    "        <div class=\"toc-container\">\n" +
    "            <ul class=\"toc\" style=\"line-height:2;\">\n";

const hRegex = /<h([1-3])[^>]*>([\s\S]*?)<\/h\1>/g;
let match;
while ((match = hRegex.exec(html)) !== null) {
    let level = parseInt(match[1]);
    let title = match[2].replace(/<[^>]+>/g, '').trim();
    if (title === 'SOMMAIRE' || title.includes('Légende') || title.includes('Mots-clés') || title.includes('ANNEXE') || title === 'Lecture du MCD MERISE :') continue;

    if (level === 1) {
        newToc += '                <li class="toc-level-1"><span class="title">' + title + '</span><span class="page-dots"></span><span class="page-num">...</span></li>\n';
    } else if (level === 2) {
        newToc += '                <li class="toc-level-2" style="margin-left:20px;"><span class="title">' + title + '</span><span class="page-dots"></span><span class="page-num">...</span></li>\n';
    } else if (level === 3 && !title.startsWith("ANNEXE")) {
        newToc += '                <li class="toc-level-3" style="margin-left:40px; font-size:10pt;"><span class="title">- ' + title + '</span><span class="page-dots"></span><span class="page-num">...</span></li>\n';
    }
}
newToc += '                <li class="toc-level-1"><span class="title">ANNEXES</span><span class="page-dots"></span><span class="page-num">...</span></li>\n';
newToc += "            </ul>\n        </div>\n    </div>\n";

const oldTocRegex = /<!-- SOMMAIRE -->[\s\S]*?<div class="page page-break">\s*<h1>INTRODUCTION GÉNÉRALE<\/h1>/;
html = html.replace(oldTocRegex, newToc + '\n    <!-- INTRODUCTION -->\n    <div class="page page-break">\n        <h1>INTRODUCTION GÉNÉRALE</h1>');

fs.writeFileSync('rapport_de_stage.html', html, 'utf8');
console.log("SUCCESSFULLY UPDATED REPORT V4");
