import re

file_path = 'c:/laragon/www/COMPTAFLOW/RAPPORT-BIEN-FIXED.html'
with open(file_path, 'r', encoding='utf-8') as f:
    text = f.read()

# 1. Rename SAT
text = text.replace('Schéma Acteur-Transaction (SAT)', "Structure d'accès théorique (SAT)")
text = text.replace('III. Schéma Acteur-Transaction (SAT)', "III. Structure d'accès théorique (SAT)")

# 2. Fix Cardinalities positions
# The current SVG has lines with rects and texts for cardinalities. We want to move them near the Entities (rectangles), which are NOT the circles.
# Circles are at: (250,80), (690,80), (110,220), (350,220), (600,220), (840,220), (110,460), (350,460), (600,460), (840,460)
def move_cardinality(match):
    line_html = match.group(1)
    rect_html = match.group(2)
    text_html = match.group(3)
    
    m_line = re.search(r'x1="([\d\.]+)"\s+y1="([\d\.]+)"\s+x2="([\d\.]+)"\s+y2="([\d\.]+)"', line_html)
    if m_line:
        x1, y1, x2, y2 = map(float, m_line.groups())
        
        circles = [(250,80), (690,80), (110,220), (350,220), (600,220), (840,220), (110,460), (350,460), (600,460), (840,460)]
        
        # Identify which end is the circle (association) and which is the entity (rectangle)
        cx, cy = x1, y1
        ex, ey = x2, y2
        if (x2, y2) in circles:
            cx, cy = x2, y2
            ex, ey = x1, y1
            
        # We want the text near the Entity (ex, ey). Let's say 25% from Entity towards Circle.
        tx = ex + 0.25 * (cx - ex)
        ty = ey + 0.25 * (cy - ey)
        
        new_rect = re.sub(r'x="[^\"]+"', f'x="{tx-12:.1f}"', rect_html)
        new_rect = re.sub(r'y="[^\"]+"', f'y="{ty-7.5:.1f}"', new_rect)
        
        new_text = re.sub(r'x="[^\"]+"', f'x="{tx:.1f}"', text_html)
        new_text = re.sub(r'y="[^\"]+"', f'y="{ty+4:.1f}"', new_text)
        
        return f"{line_html}\n{new_rect}\n{new_text}"
        
    return match.group(0)

pattern = r'(<line[^>]*?stroke="#34495E"[^>]*?(?:></line>|/>|>))\s*(<rect[^>]*?fill="white"[^>]*?(?:></rect>|/>|>))\s*(<text[^>]*?text-anchor="middle"[^>]*?>.*?</text>)'
text = re.sub(pattern, move_cardinality, text)

# Update some specific cardinalities to match typical MERISE logic based on user's image if needed
# Actually, the user's image shows the cardinalities are close to the Entities. My repositioning above handles the position.

# 3. Page ordering
# Sommaire order:
# 1. DEDICACE
# 2. REMERCIEMENTS
# 3. RÉSUMÉ
# 4. SOMMAIRE
# 5. INTRODUCTION GÉNÉRALE

# Let's extract pages and sort them if they are out of order.
# Instead of full sorting, I will extract exactly what I need and re-stitch them.
# The pages are separated by: <div class="page... page-break">
pages_split_pattern = r'(<div class="page[^"]*?page-break">.*?(?:(?:</div>\s*<div class="page)|(?:</div>\s*</body>)))'

import bs4
soup = bs4.BeautifulSoup(text, "html.parser")
body = soup.find('body')

# Collect all top level div.page
pages = body.find_all('div', class_='page', recursive=False)
if not pages:
    # try recursive True just in case
    pages = soup.find_all('div', class_='page')

print(f"Found {len(pages)} pages.")

# Let's create a mapping of page titles to page objects
def get_page_title(page):
    h1 = page.find('h1')
    if h1: return h1.get_text(strip=True).upper()
    h2 = page.find('h2')
    if h2: return h2.get_text(strip=True).upper()
    pn = page.find('div', class_='part-number')
    if pn: return pn.get_text(strip=True).upper()
    cc = page.find('div', class_='cover-content')
    if cc: return 'COVER'
    return 'UNKNOWN'

page_list = [(get_page_title(p), p) for p in pages]

for idx, (title, p) in enumerate(page_list):
    print(f"Page {idx}: {title}")

# We want the order:
# COVER
# DÉDICACE
# REMERCIEMENTS
# RÉSUMÉ
# SOMMAIRE
# INTRODUCTION GÉNÉRALE
# PREMIÈRE PARTIE
# CHAPITRE I : PRÉSENTATION GÉNÉRALE DE L'ENTREPRISE (or just the chapters of part 1) -> contains CHAPITRE I
# DEUXIÈME PARTIE
# CHAPITRE I : ÉTUDE DE L'EXISTANT ET CAHIER DES CHARGES (contains chapters of part 2)
# TROISIÈME PARTIE
# CHAPITRE I : L'ÉCO-SYSTÈME LARAVEL MVC (contains chapters of part 3)
# CONCLUSION GÉNÉRALE
# PERSPECTIVES
# ANNEXES (if any)
# TABLE DES MATIÈRES (if any)

expected_order_keys = [
    'COVER',
    'DÉDICACE',
    'REMERCIEMENTS',
    'RÉSUMÉ',
    'SOMMAIRE',
    'INTRODUCTION GÉNÉRALE',
    "PREMIÈRE PARTIE",
    "CHAPITRE I : PRÉSENTATION GÉNÉRALE DE L'ENTREPRISE",
    "DEUXIÈME PARTIE",
    "CHAPITRE I : ÉTUDE DE L'EXISTANT ET CAHIER DES CHARGES",
    "TROISIÈME PARTIE",
    "CHAPITRE I : L'ÉCO-SYSTÈME LARAVEL MVC",
    "CONCLUSION GÉNÉRALE",
    "PERSPECTIVES"
]

ordered_pages = []
used_indices = set()

for key in expected_order_keys:
    for i, (title, p) in enumerate(page_list):
        if i in used_indices: continue
        if title.startswith(key) or key.startswith(title) or key in title:
            ordered_pages.append(p)
            used_indices.add(i)
            break

# Add any remaining pages that weren't matched
for i, (title, p) in enumerate(page_list):
    if i not in used_indices:
        ordered_pages.append(p)

# Clear the body and append pages in the new order
# but keep the elements that might not be in pages (like scripts or headers)
# We can just replace the page elements in the document
new_body_content = ""

for child in body.children:
    if child in pages:
        continue # skip old pages
        
    if isinstance(child, bs4.Tag) and child.name == 'script':
        continue # skip scripts temporarily

# Wait, simple way: Replace all body content with the ordered pages plus any scripts
for p in pages:
    p.extract()

for p in ordered_pages:
    body.append(p)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(str(soup))
print("Pages reordered according to Sommaire.")
