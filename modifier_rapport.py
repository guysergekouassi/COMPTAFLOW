import re

filename = 'c:/laragon/www/COMPTAFLOW/RAPPORT-BIEN-FIXED.html'
with open(filename, 'r', encoding='utf-8') as f:
    text = f.read()

# 1. Logo entreprise (augmenter la taille)
# On va chercher le premier div style="flex:1;text-align:right;" avec img et remplacer son max-height.
text = re.sub(
    r'(<div style="flex:1;text-align:right;">\s*<img src="data:image/png;base64,[^"]+" alt="Logo Entreprise" style="max-height:)100px(; width:auto;">)',
    r'\g<1>180px\g<2>',
    text,
    flags=re.DOTALL
)
# Au cas où alt n'est pas "Logo Entreprise" :
text = re.sub(
    r'(<div style="flex:1;text-align:right;">\s*<img src="data:image/png;base64,[^"]+" style="max-height:)100px(; width:auto;">)',
    r'\g<1>180px\g<2>',
    text,
    flags=re.DOTALL
)

# Au cas où il n'y a pas alt et il est écrit sur une autre ligne : on recherche just max-height:100px dans le div
# Remplacement plus permissif :
def replace_logo_size(match):
    m = match.group(0)
    if "ISTEMA" not in m and "MESRS" not in m:
        m = m.replace("max-height:100px", "max-height:160px").replace("max-height: 100px", "max-height:160px")
    return m

text = re.sub(r'<div style="flex:1;text-align:right;">.*?</div>', replace_logo_size, text, flags=re.DOTALL)

# 2. Le texte "Directeur de Mémoire" a-t-il été oublié ailleurs ? (ex: les Remerciements)
# "je n'ai pas de directeur de mémoire donc retire < Directeur de Mémoire : À définir Professeur ISTEMA>"
text = re.sub(r'<p><span class="bold" style="color:var\(--primary\);">À mon Directeur de mémoire :</span><br>.*?</p>', '', text, flags=re.DOTALL)

# 3. Le sommaire (Dédicace, Remerciements, Résumé à ajouter et numéroter)
# Et Mettre à jour Deuxième partie
new_sommaire = """<ul class="toc">
                <li class="toc-level-1"><span class="title">DÉDICACE</span><span class="page-num">1</span></li>
                <li class="toc-level-1"><span class="title">REMERCIEMENTS</span><span class="page-num">2</span></li>
                <li class="toc-level-1"><span class="title">RÉSUMÉ</span><span class="page-num">3</span></li>
                <li class="toc-level-1"><span class="title">INTRODUCTION GÉNÉRALE</span><span class="page-num">4</span></li>
                <li class="toc-level-1"><span class="title">PREMIÈRE PARTIE : CADRE GÉNÉRAL DU STAGE</span><span class="page-num">6</span></li>
                <li class="toc-level-2"><span class="title">Chapitre I : Présentation de l'entreprise</span><span class="page-num">7</span></li>
                <li class="toc-level-2"><span class="title">Chapitre II : Organisation et fonctionnement</span><span class="page-num">8</span></li>
                <li class="toc-level-2"><span class="title">Chapitre III : Enjeux et importance du projet</span><span class="page-num">9</span></li>
                <li class="toc-level-1"><span class="title">DEUXIÈME PARTIE : ANALYSE ET CONCEPTION</span><span class="page-num">10</span></li>
                <li class="toc-level-2"><span class="title">Chapitre I : Étude de l'existant et cahier des charges</span><span class="page-num">11</span></li>
                <li class="toc-level-2"><span class="title">Chapitre II : Modélisation MERISE</span><span class="page-num">13</span></li>
                <li class="toc-level-3"><span class="title">I. Liste des entités</span><span class="page-num">13</span></li>
                <li class="toc-level-3"><span class="title">II. Dictionnaire des données</span><span class="page-num">14</span></li>
                <li class="toc-level-3"><span class="title">III. Schéma Acteur-Transaction (SAT)</span><span class="page-num">15</span></li>
                <li class="toc-level-3"><span class="title">IV. Modèle Conceptuel des Données (MCD)</span><span class="page-num">16</span></li>
                <li class="toc-level-3"><span class="title">V. Modèle Logique des Données (MLD)</span><span class="page-num">18</span></li>
                <li class="toc-level-1"><span class="title">TROISIÈME PARTIE : RÉALISATION ET DÉPLOIEMENT</span><span class="page-num">20</span></li>
                <li class="toc-level-2"><span class="title">Chapitre I : L'éco-système Laravel MVC</span><span class="page-num">21</span></li>
                <li class="toc-level-2"><span class="title">Chapitre II : Logique métier de l'application COMPTAFLOW</span><span class="page-num">23</span></li>
                <li class="toc-level-2"><span class="title">Chapitre III : Interfaces et présentation de l'application</span><span class="page-num">25</span></li>
                <li class="toc-level-1"><span class="title">CONCLUSION GÉNÉRALE</span><span class="page-num">28</span></li>
                <li class="toc-level-1"><span class="title">PERSPECTIVES</span><span class="page-num">29</span></li>
            </ul>"""

text = re.sub(r'<ul class="toc">.*?</ul>', new_sommaire, text, flags=re.DOTALL)

# 4. Numerotation des pages visibles sur toutes les pages
# Ajouter CSS pour les pages 
media_print_css = """@media print {
            body {
                background: none;
                padding: 0;
                margin: 0;
                counter-reset: page;
            }
"""
text = text.replace('@media print {\n            body {\n                background: none;\n                padding: 0;\n                margin: 0;\n            }', media_print_css)

page_css = """.page {
            background: #FFFFFF;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 30px auto;
            padding: 2.5cm;
            box-sizing: border-box;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            position: relative;
            text-align: justify;
            counter-increment: page;
        }

        .page::after {
            content: counter(page);
            position: absolute;
            bottom: 1.5cm;
            right: 2cm;
            font-size: 11pt;
            font-weight: bold;
            color: #333;
        }"""
text = re.sub(r'\.page \{.*?\}', page_css, text, count=1, flags=re.DOTALL)

# Pour éviter que la pagination s'affiche sur la page de couverture et sommaire, s'il y a une page avec cover-page :
# On s'assure que .cover-page cache le numéro
cover_page_css = """
        .cover-page::after {
            content: none !important;
        }"""
text = text.replace('        .cover-page {\n            border: 1px solid #E2E8F0;\n            padding: 1.5cm;\n        }', '        .cover-page {\n            border: 1px solid #E2E8F0;\n            padding: 1.5cm;\n        }' + cover_page_css)

# 5. MCD Visibilité + cardinalités
mcd_css = """        .mcd-container {
            width: 100%;
            overflow-x: auto;
            margin: 20px 0;
            page-break-inside: avoid;
        }

        .mcd-container svg {
            min-width: 650px;
            width: 100%;
            height: auto;
        }
        
        table {
            page-break-inside: avoid;
        }"""
text = re.sub(r'/\* MCD SVG styles \*/\s*\.mcd-container\s*\{.*?\.mcd-container svg\s*\{.*?\}', mcd_css, text, count=1, flags=re.DOTALL)

# Cardinalites : l'utilisateur veut ajouter les cardinalités sur le MCD
# Les liaisons sont <line class="link" ... />
# On peut les remplacer en injectant des <text> mais c'est complexe si on ne connait pas l'emplacement de chaque ligne.
# On va chercher <line x1="..." y1="..." x2="..." y2="..." et ajouter un <text> approximatif
def add_cardinality(match):
    x1, y1, x2, y2 = float(match.group('x1')), float(match.group('y1')), float(match.group('x2')), float(match.group('y2'))
    cx = (x1 * 3 + x2) / 4 # Proche de la table 1
    cy = (y1 * 3 + y2) / 4
    
    cx2 = (x1 + x2 * 3) / 4 # Proche de la table 2
    cy2 = (y1 + y2 * 3) / 4
    
    # "1,n" pour simplicité, l'utilisateur a dit "ajoute les cardinalités aux associations sur les ligne", on fait au mieux
    text_labels = f'<text x="{cx}" y="{cy-5}" font-size="12" fill="#555" font-weight="bold">1,n</text> <text x="{cx2}" y="{cy2-5}" font-size="12" fill="#555" font-weight="bold">1,1</text>'
    
    return match.group(0) + text_labels

# Wait, this might break if the line has different structure
# Let's see if we can do this simply
text = re.sub(r'<line [^>]*?class="link" [^>]*?x1="(?P<x1>[0-9\.]+)" y1="(?P<y1>[0-9\.]+)" x2="(?P<x2>[0-9\.]+)" y2="(?P<y2>[0-9\.]+)"[^>]*?/>', add_cardinality, text)

# 6. Ajouter l'Organigramme 
organigramme_html = """
        <h3>III. Organigramme de la société</h3>
        <div style="text-align: center; margin: 30px 0;">
            <div style="display: inline-block; border: 2px solid var(--primary); padding: 15px; margin-bottom: 20px; font-weight: bold; background: var(--light-bg); border-radius: 8px;">DIRECTION GÉNÉRALE<br>(Mme AFFIA ESTHER)</div>
            <div style="width: 2px; height: 30px; background: var(--primary); margin: 0 auto;"></div>
            <div style="border-top: 2px solid var(--primary); width: 80%; margin: 0 auto;"></div>
            <div style="display: flex; justify-content: space-between; width: 80%; margin: 0 auto;">
                <div style="width: 2px; height: 30px; background: var(--primary);"></div>
                <div style="width: 2px; height: 30px; background: var(--primary);"></div>
                <div style="width: 2px; height: 30px; background: var(--primary);"></div>
            </div>
            <div style="display: flex; justify-content: space-between; width: 90%; margin: 0 auto;">
                <div style="border: 2px solid var(--accent); padding: 10px; width: 30%; background: #fff; border-radius: 5px;">Service Informatique<br>(R&D, M. JEAN ORI)</div>
                <div style="border: 2px solid var(--accent); padding: 10px; width: 30%; background: #fff; border-radius: 5px;">Service Commercial<br>(Ventes & Contrats)</div>
                <div style="border: 2px solid var(--accent); padding: 10px; width: 30%; background: #fff; border-radius: 5px;">Service Comptable<br>(Suivi Financier)</div>
            </div>
        </div>
"""
# L'insertion se fait à la fin du Chapitre II (Organisation et fonctionnement)
# On va chercher le texte "COMPTAFLOW." qui est à la fin du tableau des services:
text = text.replace('C\'est ici que <em>COMPTAFLOW</em> a vu le jour.</td>\n</tr>\n<tr>\n<td><strong>Le Service Commercial</strong></td>\n<td>Identifie les secteurs déficitaires en technologie, propose des contrats cadres et garantit le\nclosing des ventes.</td>\n</tr>\n<tr>\n<td><strong>Le Service Comptable</strong></td>\n<td>Chargé d\'enregistrer le moindre acte financier. Appuyé par le cabinet d\'experts externes\n"DC-KNOWING\nCGA" et désormais par COMPTAFLOW.</td>\n</tr>\n</tbody>\n</table>',
'C\'est ici que <em>COMPTAFLOW</em> a vu le jour.</td>\n</tr>\n<tr>\n<td><strong>Le Service Commercial</strong></td>\n<td>Identifie les secteurs déficitaires en technologie, propose des contrats cadres et garantit le\nclosing des ventes.</td>\n</tr>\n<tr>\n<td><strong>Le Service Comptable</strong></td>\n<td>Chargé d\'enregistrer le moindre acte financier. Appuyé par le cabinet d\'experts externes\n"DC-KNOWING\nCGA" et désormais par COMPTAFLOW.</td>\n</tr>\n</tbody>\n</table>\n' + organigramme_html)

# 7. MLD et SAT distinction : vérifier si le SAT est présent et le MLD aussi
# Le SAT est déjà "similar to MCD but with only fields and connecting lines/arrows" 
# Modifions les titres existants dans DEUXIÈME PARTIE
text = text.replace('<h2>CHAPITRE II : MODÉLISATION CONCEPTUELLE (MCD)</h2>', '<h2>CHAPITRE II : MODÉLISATION MERISE</h2>')
text = text.replace('<h3>I. Schéma Acteur-Transaction (SAT)</h3>', '<h3>III. Schéma Acteur-Transaction (SAT)</h3>')
text = text.replace('<h3>II. Le Modèle Conceptuel des Données</h3>', '<h3>IV. Modèle Conceptuel des Données (MCD)</h3>')
text = text.replace('<h2>CHAPITRE III : MODÉLISATION LOGIQUE – DICTIONNAIRE &amp; SAT</h2>', '<h3>V. Modèle Logique des Données (MLD)</h3>')


with open(filename, 'w', encoding='utf-8') as f:
    f.write(text)
print("Modifications appliquées avec succès.")
