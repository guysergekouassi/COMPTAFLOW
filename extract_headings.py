import docx
import os

def extract_headings(file_path):
    if not os.path.exists(file_path):
        print(f"File not found: {file_path}")
        return

    doc = docx.Document(file_path)
    headings = []
    
    # We'll look for paragraphs that look like headings
    # 1. Styles with 'Heading' in them
    # 2. Bold paragraphs
    # 3. Paragraphs starting with Part, Chapter, etc. in French
    
    for i, para in enumerate(doc.paragraphs):
        text = para.text.strip()
        if not text:
            continue
            
        is_heading = False
        level = 0
        
        # Check style
        if para.style.name.startswith('Heading'):
            try:
                level = int(para.style.name.replace('Heading ', ''))
            except:
                level = 1
            is_heading = True
        
        # Check content keywords
        upper_text = text.upper()
        if upper_text.startswith("PARTIE") or "PREMIÈRE PARTIE" in upper_text or "DEUXIÈME PARTIE" in upper_text or "TROISIÈME PARTIE" in upper_text:
            is_heading = True
            level = 1
        elif upper_text.startswith("CHAPITRE"):
            is_heading = True
            level = 2
        elif text[0].isdigit() and ('.' in text[:3] or ' ' in text[:3]): # e.g. "1. Title" or "1 Introduction"
            is_heading = True
            level = 3
        elif len(text) < 100 and any(run.bold for run in para.runs): # Bold and short
            is_heading = True
            level = 4
        elif upper_text in ["DÉDICACE", "REMERCIEMENTS", "AVANT-PROPOS", "SOMMAIRE", "INTRODUCTION GÉNÉRALE", "CONCLUSION GÉNÉRALE", "ANNEXES", "BIBLIOGRAPHIE"]:
            is_heading = True
            level = 1

        if is_heading:
            headings.append((level, text))
            
    return headings

file_path = r"c:\laragon\www\COMPTAFLOW\rapport-soutenance-ANIMEL.docx"
headings = extract_headings(file_path)

if headings:
    for level, text in headings:
        print(f"{level} | {text}")
else:
    print("No headings found.")
