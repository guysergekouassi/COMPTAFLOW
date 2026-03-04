const fs = require('fs');
const htmlFile = 'rapport_de_stage.html';

const dir = 'logos-rapport/';
const b64Eco = fs.readFileSync(dir + 'ecole.png').toString('base64');
const b64Armoirie = fs.readFileSync(dir + "republique de cote d'ivoir.png").toString('base64');
const b64Mesrs = fs.readFileSync(dir + "ministere de l'enseignement superieur.png").toString('base64');
const b64Leader = fs.readFileSync(dir + 'entreprise.png').toString('base64');

let lines = fs.readFileSync(htmlFile, 'utf8').split('\n');

const findIndex = (searchString, startIndex = 0) => {
    return lines.findIndex((l, i) => i >= startIndex && l.includes(searchString));
};

let startCover = findIndex('<!-- HAUT : ARMOIRIES');
if (startCover === -1) startCover = findIndex('<!-- PAGE DE GARDE -->');
let endCover = findIndex('<!-- TYPE DE RAPPORT -->');

if (startCover !== -1 && endCover !== -1) {
    const newCover = `
            <!-- HAUT : ECOLE (gauche) + ARMOIRIES (droite) -->
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px; padding: 0 10px;">
                <div style="flex:1;text-align:left;">
                    <img src="data:image/png;base64,${b64Eco}" alt="Logo Ecole ISTEMA" style="max-height:100px; width:auto;">
                </div>
                <div style="flex:1;text-align:right;">
                    <img src="data:image/png;base64,${b64Armoirie}" alt="Armoiries CI" style="max-height:110px; width:auto;">
                </div>
            </div>
            <!-- SOUS HAUT : LEADER (gauche) + MESRS (droite) -->
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:40px; padding: 0 10px;">
                <div style="flex:1;text-align:left;">
                    <img src="data:image/png;base64,${b64Leader}" alt="Entreprise Leader" style="max-height:80px; width:auto;">
                </div>
                <div style="flex:1;text-align:right;">
                    <img src="data:image/png;base64,${b64Mesrs}" alt="Logo MESRS" style="max-height:80px; width:auto;">
                </div>
            </div>
`;
    lines.splice(startCover, endCover - startCover, newCover);
} else {
    console.log("Error: could not find Cover borders.");
}

const rects = [
    { id: 'C1', x: 380, y: 10, w: 230, h: 160, title: 'COMPANIES', fill: '#EBF5FB', stroke: '#1B365D', attrs: ['id (PK)', 'company_name', 'juridique_form', 'social_capital', 'adresse / city', 'email_adresse', 'is_blocked'] },
    { id: 'C2', x: 760, y: 30, w: 190, h: 120, title: 'USERS', fill: '#EBF5FB', stroke: '#1B365D', attrs: ['id (PK)', 'name / last_name', 'email_adresse', 'password (hash)', 'role', 'is_online'] },
    { id: 'C3', x: 10, y: 30, w: 200, h: 110, title: 'EXERCICES', fill: '#EBF5FB', stroke: '#1B365D', attrs: ['id (PK)', 'intitule / date_debut', 'date_fin', 'is_active / cloturer'] },
    { id: 'C4', x: 10, y: 300, w: 200, h: 120, title: 'PLAN_COMPTABLES', fill: '#FEF9E7', stroke: '#D35400', attrs: ['id (PK)', 'numero_de_compte', 'intitule / classe', 'type_de_compte', 'adding_strategy'] },
    { id: 'C5', x: 260, y: 300, w: 180, h: 100, title: 'PLAN_TIERS', fill: '#FEF9E7', stroke: '#D35400', attrs: ['id (PK)', 'numero_de_tiers', 'intitule', 'type_de_tiers'] },
    { id: 'C6', x: 490, y: 300, w: 220, h: 120, title: 'CODE_JOURNALS', fill: '#FEF9E7', stroke: '#D35400', attrs: ['id (PK)', 'code_journal / intitule', 'type / traite_anal', 'compte_contrepartie', 'rapprochement_sur'] },
    { id: 'C7', x: 760, y: 300, w: 180, h: 110, title: 'COMPTE_TRESORERIES', fill: '#EAFAF1', stroke: '#27ae60', attrs: ['id (PK)', 'name', 'type', 'solde_initial', 'solde_actuel'] },
    { id: 'C8', x: 300, y: 550, w: 380, h: 220, title: 'ECRITURE_COMPTABLES', fill: '#E8F4F8', stroke: '#1B365D', attrs: ['id (PK)', 'date', 'n_saisie_user', 'n_saisie', 'description_operation', 'reference_piece', 'piece_justificatif', 'debit', 'credit', 'statut', 'type_flux', 'plan_analytique'] }
];

const C = {};
rects.forEach(r => { C[r.id] = { x: r.x + r.w / 2, y: r.y + r.h / 2 }; });

const assocs = [
    { id: 'A1', x: 250, y: 80, name: 'Gérer', links: [{ to: 'C1', card: '1,n' }, { to: 'C3', card: '1,1' }] },
    { id: 'A2', x: 690, y: 80, name: 'Employer', links: [{ to: 'C1', card: '1,n' }, { to: 'C2', card: '1,1' }] },
    { id: 'A3', x: 110, y: 220, name: 'Constituer', links: [{ to: 'C1', card: '1,n' }, { to: 'C4', card: '1,1' }] },
    { id: 'A6', x: 350, y: 220, name: 'Avoir', links: [{ to: 'C1', card: '1,n' }, { to: 'C5', card: '1,1' }] },
    { id: 'A4', x: 600, y: 220, name: 'Définir', links: [{ to: 'C1', card: '1,n' }, { to: 'C6', card: '1,1' }] },
    { id: 'A5', x: 840, y: 220, name: 'Ouvrir', links: [{ to: 'C1', card: '1,n' }, { to: 'C7', card: '1,1' }] },

    { id: 'A7', x: 110, y: 460, name: 'Imputer', links: [{ to: 'C4', card: '0,n' }, { to: 'C8', card: '1,1' }] },
    { id: 'A8', x: 350, y: 460, name: 'Concerner', links: [{ to: 'C5', card: '0,n' }, { to: 'C8', card: '1,1' }] },
    { id: 'A9', x: 600, y: 460, name: 'Typifier', links: [{ to: 'C6', card: '0,n' }, { to: 'C8', card: '1,1' }] },
    { id: 'A10', x: 840, y: 460, name: 'Trésorerie', links: [{ to: 'C7', card: '0,n' }, { to: 'C8', card: '1,1' }] }
];

let svg = `<div class="mcd-container" style="overflow-x:auto;">\n<svg viewBox="0 0 980 800" xmlns="http://www.w3.org/2000/svg" font-family="Arial, sans-serif" font-size="11" style="min-width:700px;width:100%;max-width:980px;background:white;">\n`;

assocs.forEach(a => {
    a.links.forEach(l => {
        let p2 = C[l.to];
        svg += `  <line x1="${a.x}" y1="${a.y}" x2="${p2.x}" y2="${p2.y}" stroke="#34495E" stroke-width="2" />\n`;
        let tx = Math.round(p2.x + (a.x - p2.x) * 0.35);
        let ty = Math.round(p2.y + (a.y - p2.y) * 0.35);
        svg += `  <rect x="${tx - 12}" y="${ty - 9}" width="24" height="15" fill="white"/>\n`;
        svg += `  <text x="${tx}" y="${ty + 3}" font-size="11" fill="#C0392B" font-weight="bold" text-anchor="middle">${l.card}</text>\n`;
    });
});

rects.forEach(r => {
    svg += `  <rect x="${r.x}" y="${r.y}" width="${r.w}" height="${r.h}" rx="6" fill="${r.fill}" stroke="${r.stroke}" stroke-width="2" />\n`;
    svg += `  <rect x="${r.x}" y="${r.y}" width="${r.w}" height="28" rx="6" fill="${r.stroke}" />\n`;
    svg += `  <rect x="${r.x}" y="${r.y + 16}" width="${r.w}" height="12" fill="${r.stroke}" />\n`;
    svg += `  <text x="${r.x + r.w / 2}" y="${r.y + 19}" text-anchor="middle" fill="white" font-weight="bold" font-size="12">${r.title}</text>\n`;
    let ay = r.y + 45;
    r.attrs.forEach(attr => {
        svg += `  <text x="${r.x + 8}" y="${ay}" fill="#333" font-size="11">${attr}</text>\n`;
        ay += 17;
    });
});

assocs.forEach(a => {
    svg += `  <circle cx="${a.x}" cy="${a.y}" r="38" fill="#F8F9FA" stroke="#1B365D" stroke-width="2" />\n`;
    svg += `  <text x="${a.x}" y="${a.y + 4}" text-anchor="middle" font-size="11" font-weight="bold" fill="#1B365D">${a.name}</text>\n`;
});

svg += `  <g transform="translate(10, 750)">
    <rect x="0" y="0" width="230" height="30" rx="4" fill="#F8F9FA" stroke="#CBD5E0" />
    <rect x="10" y="9" width="16" height="12" fill="#EBF5FB" stroke="#1B365D" stroke-width="1" />
    <text x="32" y="19" font-size="11" fill="#555">Entité</text>
    <circle cx="85" cy="15" r="9" fill="#F8F9FA" stroke="#1B365D" stroke-width="1" />
    <text x="100" y="19" font-size="11" fill="#555">Association</text>
    <text x="175" y="19" font-size="11" fill="#C0392B" font-weight="bold">1,n / 1,1</text>
    <text x="215" y="19" font-size="11" fill="#555">Cardinalité</text>
  </g>
</svg>
</div>
<div class="alert-success" style="margin-top:15px; border-left:4px solid #1B365D; background:#f1f8ff; padding:15px; font-size:13px; line-height:1.5;">
    <strong>Lecture du MCD MERISE :</strong> Les entités (rectangles) regroupent les attributs propres. Les clés primaires sont notées <strong>id (PK)</strong>. Les associations (cercles) matérialisent les liens sémantiques entre entités, accompagnées de leurs cardinalités.
    <br><br>La table <strong style="color:#1B365D;">ECRITURE_COMPTABLES</strong> est le cœur transactionnel du système : elle est connectée à l'ensemble du réseau des entités (journaux, plan comptable, trésorerie).
</div>
</div>
`;

let startMCD = findIndex('<div class="mcd-container"');
let endMCD = findIndex('<!-- SAT RÉELLE PAGE -->');

if (startMCD !== -1 && endMCD !== -1) {
    lines.splice(startMCD, endMCD - startMCD, svg);
} else {
    console.log("Error: could not find MCD borders.");
}

fs.writeFileSync(htmlFile, lines.join('\n'), 'utf8');
console.log("HTML successfully updated");
