import re

with open('c:/laragon/www/COMPTAFLOW/RAPPORT-BIEN-FIXED.html', 'r', encoding='utf-8') as f:
    text = f.read()

# Custom parser to extract pages
pages = []
start = 0
while True:
    idx = text.find('<div class="page', start)
    if idx == -1:
        if start < len(text):
            pages.append(text[start:])
        break
    if start < idx:
        pages.append(text[start:idx])
    
    # find the end of this page div
    # count divs
    div_count = 1
    curr = idx + 16 # skip '<div class="page'
    while div_count > 0 and curr < len(text):
        next_open = text.find('<div', curr)
        next_close = text.find('</div>', curr)
        if next_close == -1:
            break
        if next_open != -1 and next_open < next_close:
            div_count += 1
            curr = next_open + 4
        else:
            div_count -= 1
            curr = next_close + 6
            
    pages.append(text[idx:curr])
    start = curr

print(f"Total pieces: {len(pages)}")
page_titles = []
for i, p in enumerate(pages):
    if p.startswith('<div class="page'):
        m = re.search(r'<h[1234][^>]*>(.*?)</h[1234]>', p)
        if not m: m = re.search(r'<div class="part-number">(.*?)</div>', p)
        if not m: m = re.search(r'<div class="report-title[^>]*>(.*?)</div>', p)
        if not m: m = re.search(r'<h4[^>]*>(.*?)</h[1234]>', p)
        title = m.group(1).strip() if m else 'No title'
        page_titles.append((i, title[:80]))
        print(f"Page chunk {i}: {title[:80]}...")

# Identify expected order
order_keys = [
    'RAPPORT DE STAGE DE FIN DE CYCLE', # Cover
    'DÉDICACE',
    'REMERCIEMENTS',
    'RÉSUMÉ',
    'SOMMAIRE',
    'INTRODUCTION GÉNÉRALE',
    'PREMIÈRE PARTIE',
    "CHAPITRE I : PRÉSENTATION GÉNÉRALE DE L'ENTREPRISE",
    'DEUXIÈME PARTIE',
    "CHAPITRE I : ÉTUDE DE L'EXISTANT ET CAHIER DES CHARGES",
    'TROISIÈME PARTIE',
    "CHAPITRE I : L'ÉCO-SYSTÈME LARAVEL MVC",
    "CONCLUSION GÉNÉRALE",
    "PERSPECTIVES"
]

ordered_chunks = []
used = set()
for key in order_keys:
    for i, title in page_titles:
        if i in used: continue
        if title.upper().startswith(key.upper()) or key.upper() in title.upper():
            ordered_chunks.append(pages[i])
            used.add(i)
            break

# Add remaining pages
new_text = ""
for i, p in enumerate(pages):
    if not p.startswith('<div class="page'):
        new_text += p
    else:
        if len(ordered_chunks) > 0:
            new_text += ordered_chunks.pop(0)
        else:
            new_text += p # append what's left if we run out (shouldn't happen)
            
text = new_text

# 1. Rename SAT
text = text.replace('Schéma Acteur-Transaction (SAT)', "Structure d'accès théorique (SAT)")
text = text.replace('III. Schéma Acteur-Transaction (SAT)', "III. Structure d'accès théorique (SAT)")

with open('c:/laragon/www/COMPTAFLOW/RAPPORT-BIEN-FIXED-reordered.html', 'w', encoding='utf-8') as f:
    f.write(text)
print("Reordering done.")
