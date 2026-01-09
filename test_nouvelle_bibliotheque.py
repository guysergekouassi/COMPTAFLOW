import google.genai as genai
import os

# Configuration
API_KEY = "AIzaSyDuwMm9cdo_vTqBe9j3degykq4rL-kOKVU"
client = genai.Client(api_key=API_KEY)

print("=== TEST AVEC GOOGLE-GENAI (NOUVELLE BIBLIOTHÈQUE) ===")

try:
    # Lister les modèles disponibles
    print("\n1. Modèles disponibles:")
    models = client.models.list()
    for model in models:
        if "generateContent" in model.supported_actions:
            print(f"   ✅ {model.name}")
    
    # Test simple avec gemini-flash-latest
    print("\n2. Test simple:")
    response = client.models.generate_content(
        model="gemini-flash-latest",
        contents="Dis simplement 'OK' si tu fonctionnes."
    )
    print(f"   ✅ Réponse: {response.text}")
    
    # Test avec image
    print("\n3. Test avec image:")
    import base64
    from PIL import Image
    from io import BytesIO
    
    # Créer une image de test
    img = Image.new('RGB', (100, 100), color='white')
    buffer = BytesIO()
    img.save(buffer, format='JPEG')
    img_data = base64.b64encode(buffer.getvalue()).decode()
    
    response = client.models.generate_content(
        model="gemini-flash-latest",
        contents=[
            "Analyse cette image de test et réponds 'Image reçue'",
            genai.types.Part.from_bytes(
                data=buffer.getvalue(),
                mime_type="image/jpeg"
            )
        ]
    )
    print(f"   ✅ Test image: {response.text}")
    
except Exception as e:
    print(f"❌ Erreur: {e}")
    import traceback
    traceback.print_exc()
