$filePath = 'c:\laragon\www\COMPTAFLOW\resources\views\accounting_entry_real.blade.php'
$content = [IO.File]::ReadAllText($filePath, [System.Text.Encoding]::UTF8)

$content = $content.Replace("Ã‰", "É")
$content = $content.Replace("Ã©", "é")
$content = $content.Replace("Ã ", "à")
$content = $content.Replace("Ã ", "à")
$content = $content.Replace("Ã¨", "è")
$content = $content.Replace("Ãª", "ê")
$content = $content.Replace("Ã®", "î")
$content = $content.Replace("Ã´", "ô")
$content = $content.Replace("Ã»", "û")
$content = $content.Replace("Ã¹", "ù")
$content = $content.Replace("Ã§", "ç")
$content = $content.Replace("Ãˆ", "È")
$content = $content.Replace("Ã€", "À")
$content = $content.Replace("NÂ°", "N°")

# Specific cleanup for words
$content = $content.Replace("Ã‰CHÃ‰ANT", "ÉCHÉANT")
$content = $content.Replace("REGISTRÃ‰ES", "REGISTRÉES")
$content = $content.Replace("VÃ©rification", "Vérification")
$content = $content.Replace("catÃ©gorie", "catégorie")
$content = $content.Replace("GÃ©nÃ©rÃ©", "Généré")
$content = $content.Replace("entitÃ©", "entité")

[IO.File]::WriteAllText($filePath, $content, [System.Text.Encoding]::UTF8)
