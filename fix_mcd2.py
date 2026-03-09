import re

with open('c:/laragon/www/COMPTAFLOW/RAPPORT-BIEN-FIXED.html', 'r', encoding='utf-8') as f:
    text = f.read()

def replacer(match):
    line_html = match.group(1)
    rect_html = match.group(2)
    text_html = match.group(3)
    
    # Extract coordinates from line
    m_line = re.search(r'x1="([\d\.]+)"\s+y1="([\d\.]+)"\s+x2="([\d\.]+)"\s+y2="([\d\.]+)"', line_html)
    if m_line:
        x1, y1, x2, y2 = map(float, m_line.groups())
        
        circles = [(250,80), (690,80), (110,220), (350,220), (600,220), (840,220), (110,460), (350,460), (600,460), (840,460)]
        
        cx, cy = x1, y1
        ex, ey = x2, y2
        if (x2, y2) in circles:
            cx, cy = x2, y2
            ex, ey = x1, y1
            
        # Position 35% along the line from the circle (association)
        tx = cx + 0.35 * (ex - cx)
        ty = cy + 0.35 * (ey - cy)
        
        new_rect = re.sub(r'x="[\d\.]+"', f'x="{tx-12:.1f}"', rect_html)
        new_rect = re.sub(r'y="[\d\.]+"', f'y="{ty-7.5:.1f}"', new_rect)
        
        new_text = re.sub(r'x="[\d\.]+"', f'x="{tx:.1f}"', text_html)
        new_text = re.sub(r'y="[\d\.]+"', f'y="{ty+4:.1f}"', new_text)
        
        return f"{line_html}\n{new_rect}\n{new_text}"
        
    return match.group(0)

pattern = r'(<line[^>]*?stroke="#34495E"[^>]*?(?:></line>|/>|>))\s*(<rect[^>]*?fill="white"[^>]*?(?:></rect>|/>|>))\s*(<text[^>]*?text-anchor="middle"[^>]*?>.*?</text>)'
text = re.sub(pattern, replacer, text)

# Fix CSS styling for the SVG container
mcd_css_fix = """
        .mcd-container {
            width: 100%;
            margin: 20px 0;
            page-break-inside: avoid;
            text-align: center;
        }

        .mcd-container svg {
            width: 100%;
            height: auto;
            max-width: 980px;
        }
"""
text = re.sub(r'\.mcd-container\s*\{.*?\.mcd-container svg\s*\{.*?\}', mcd_css_fix.strip(), text, flags=re.DOTALL)

# Remove the inline style min-width from the SVG
text = re.sub(r'(<svg viewBox="0 0 980 800"[^>]+)style="[^"]+"', r'\1 style="width:100%; height:auto; max-width:980px;"', text)

with open('c:/laragon/www/COMPTAFLOW/RAPPORT-BIEN-FIXED.html', 'w', encoding='utf-8') as f:
    f.write(text)
print("SVG dimensions fixed and cardinalities repositioned.")
