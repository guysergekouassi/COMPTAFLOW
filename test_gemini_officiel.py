import google.generativeai as genai
import os

# Configuration
API_KEY = "AIzaSyDuwMm9cdo_vTqBe9j3degykq4rL-kOKVU"
genai.configure(api_key=API_KEY)

print("=== TEST AVEC GOOGLE-GENERATIVEAI ===")

try:
    # Test simple
    model = genai.GenerativeModel('gemini-1.5-flash')
    response = model.generate_content("Dis simplement 'OK' si tu fonctionnes.")
    print(f"✅ Succès: {response.text}")
    
    # Test avec image
    print("\n=== TEST AVEC IMAGE ===")
    import base64
    from PIL import Image
    
    # Créer une image de test
    from io import BytesIO
    img = Image.new('RGB', (100, 100), color='white')
    buffer = BytesIO()
    img.save(buffer, format='JPEG')
    img_data = base64.b64encode(buffer.getvalue()).decode()
    
    # Test avec l'image
    model = genai.GenerativeModel('gemini-1.5-flash')
    response = model.generate_content([
        "Analyse cette image de test et réponds 'Image reçue'",
        {"mime_type": "image/jpeg", "data": img_data}
    ])
    print(f"✅ Test image: {response.text}")
    
except Exception as e:
    print(f"❌ Erreur: {e}")
