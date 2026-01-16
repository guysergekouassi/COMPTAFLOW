import google.genai as genai
import os
import sys

# Force utf-8 encoding for output
sys.stdout.reconfigure(encoding='utf-8')

API_KEY = "AIzaSyDGpee7xhZSwHKVOhNDND2VGEjvo5KkHpM"
client = genai.Client(api_key=API_KEY)

print(f"=== TEST SCAN FACTURE 4 (Key ends in ...{API_KEY[-4:]}) ===")

try:
    print("Checking available models...")
    models = client.models.list()
    available_models = []
    for model in models:
        try:
            available_models.append(model.name)
            # print(f" - {model.name}") # Uncomment to see all
        except:
            pass
    
    print(f"Found {len(available_models)} models.")
    
    # Priority list
    priorities = [
        "gemini-1.5-flash", 
        "models/gemini-1.5-flash", 
        "gemini-1.5-flash-latest",
        "gemini-flash-latest",
        "models/gemini-flash-latest"
    ]
    
    selected_model = None
    for p in priorities:
        if p in available_models:
            selected_model = p
            break
            
    if not selected_model:
        # Fallback to any flash
        for m in available_models:
            if "flash" in m and "gemini" in m:
                selected_model = m
                break

    print(f"Selected model: {selected_model}")

    image_path = "facture4.jpg"
    if not os.path.exists(image_path):
        print(f"Error: {image_path} not found.")
        sys.exit(1)

    print(f"Reading {image_path}...")
    with open(image_path, "rb") as f:
        image_bytes = f.read()

    print("Sending request to Gemini...")
    
    response = client.models.generate_content(
        model=selected_model,
        contents=[
            "Analyse cette facture et extrais : Date, Numéro de facture, Fournisseur, Montant HT, Montant TVA, Montant TTC. Suggère aussi le numéro de compte de charge (Format 8 chiffres, ex: 60500000) le plus approprié du plan comptable (SYSCOHADA) avec son LIBELLÉ EXACT du plan comptable, et le compte fournisseur (Format 8 chiffres, ex: 40110000) avec son LIBELLÉ. Formate la réponse en JSON avec les clés : date, numero, fournisseur, montant_ht, montant_tva, montant_ttc, compte_charge, libelle_compte_charge, compte_fournisseur, libelle_compte_fournisseur.",
            genai.types.Part.from_bytes(
                data=image_bytes,
                mime_type="image/jpeg"
            )
        ]
    )
    
    print("\nResult:")
    print(response.text)

except Exception as e:
    print(f"Error: {e}")
    if hasattr(e, 'response'):
        print(f"Response: {e.response}")
    import traceback
    traceback.print_exc()
