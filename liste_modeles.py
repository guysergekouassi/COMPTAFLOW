import requests

API_KEY = "AIzaSyDuwMm9cdo_vTqBe9j3degykq4rL-kOKVU"
url = f"https://generativelanguage.googleapis.com/v1beta/models?key={API_KEY}"

try:
    response = requests.get(url)
    modeles = response.json()
    print("--- MODÈLES DISPONIBLES POUR VOTRE CLÉ ---")
    for m in modeles.get('models', []):
        print(f"- {m['name']}")
except Exception as e:
    print(f"Erreur : {e}")