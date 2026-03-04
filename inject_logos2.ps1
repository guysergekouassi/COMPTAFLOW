$htmlPath = "c:\laragon\www\COMPTAFLOW\rapport_de_stage.html"
$logoDir = "c:\laragon\www\COMPTAFLOW\logos-rapport"

Function Get-Base64 ($imgPath) {
    if (Test-Path -LiteralPath $imgPath) {
        $bytes = [System.IO.File]::ReadAllBytes($imgPath)
        return [System.Convert]::ToBase64String($bytes)
    }
    return $null
}

$files = Get-ChildItem -LiteralPath $logoDir

$content = [System.IO.File]::ReadAllText($htmlPath)

# 1. Armoiries (republique de cote d'ivoire.png)
$armFile = ($files | Where-Object { $_.Name -match "republique" })[0].FullName
$armExt = [System.IO.Path]::GetExtension($armFile).Trim('.')
$b64Arm = Get-Base64 $armFile
$content = $content -replace '(<img[^>]*alt="Armoiries[^"]*"[^>]*src=")[^"]*(")', "`${1}data:image/$armExt;base64,$b64Arm`$2"

# 2. MESRS (ministere de l'enseignement superieur et de la recherche scientifique.png)
$mesrsFile = ($files | Where-Object { $_.Name -match "ministere" })[0].FullName
$mesrsExt = [System.IO.Path]::GetExtension($mesrsFile).Trim('.')
$b64Mesrs = Get-Base64 $mesrsFile
$content = $content -replace '(<img[^>]*alt="Logo du MESRS"[^>]*src=")[^"]*(")', "`${1}data:image/$mesrsExt;base64,$b64Mesrs`$2"

# 3. ISTEMA (école.png)
$istFile = ($files | Where-Object { $_.Name -match "cole" -or $_.Name -match "ecole" })[0].FullName
$istExt = [System.IO.Path]::GetExtension($istFile).Trim('.')
$b64Ist = Get-Base64 $istFile
$content = $content -replace '(<img[^>]*alt="Logo ISTEMA"[^>]*src=")[^"]*(")', "`${1}data:image/$istExt;base64,$b64Ist`$2"

# 4. LWP (entreprise.png)
$lwpFile = ($files | Where-Object { $_.Name -match "entreprise" })[0].FullName
$lwpExt = [System.IO.Path]::GetExtension($lwpFile).Trim('.')
$b64Lwp = Get-Base64 $lwpFile
$content = $content -replace '(<img[^>]*alt="Logo Leader World Perfect"[^>]*src=")[^"]*(")', "`${1}data:image/$lwpExt;base64,$b64Lwp`$2"

[System.IO.File]::WriteAllText($htmlPath, $content)
Write-Output "Injection Réussie !"
