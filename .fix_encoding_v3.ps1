$filePath = 'c:\laragon\www\COMPTAFLOW\resources\views\accounting_entry_real.blade.php'
$content = [IO.File]::ReadAllText($filePath, [System.Text.Encoding]::UTF8)

function Fix-Pattern($target, $replacement) {
    global:content = global:content.Replace($target, $replacement)
}

# The corrupted characters are typically UTF-8 bytes interpreted as Windows-1252
Fix-Pattern "Ã‰" "É"
Fix-Pattern "Ã©" "é"
Fix-Pattern "Ã " "à"
Fix-Pattern "Ã " "à"
Fix-Pattern "Ã¨" "è"
Fix-Pattern "Ãª" "ê"
Fix-Pattern "Ã®" "î"
Fix-Pattern "Ã´" "ô"
Fix-Pattern "Ã»" "û"
Fix-Pattern "Ã¹" "ù"
Fix-Pattern "Ã§" "ç"
Fix-Pattern "Ãˆ" "È"
Fix-Pattern "Ã€" "À"
Fix-Pattern "NÂ°" "N°"
Fix-Pattern "NÂ°" "N°"
Fix-Pattern "Ã»" "û"
Fix-Pattern "Ã¹" "ù"
Fix-Pattern "Ã¨" "è"

# Cleanup specific cases from the screenshot
Fix-Pattern "Ã‰CHÃ‰ANT" "ÉCHÉANT"
Fix-Pattern "REGISTRÃ‰ES" "REGISTRÉES"
Fix-Pattern "VÃ©rification" "Vérification"
Fix-Pattern "catÃ©gorie" "catégorie"
Fix-Pattern "GÃ©nÃ©rÃ©" "Généré"
Fix-Pattern "entitÃ©" "entité"

[IO.File]::WriteAllText($filePath, $content, [System.Text.Encoding]::UTF8)
