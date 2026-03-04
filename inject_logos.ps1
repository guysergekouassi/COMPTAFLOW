$proj  = "c:\laragon\www\COMPTAFLOW"
$logos = "$proj\logos-rapport"
$html  = "$proj\rapport_de_stage.html"

# Lister les fichiers avec leurs bytes
$files = Get-ChildItem -LiteralPath $logos

$fileArm   = $null
$fileMesrs = $null
$fileLwp   = $null
$fileIst   = $null

foreach ($f in $files) {
    $n = $f.Name.ToLower()
    if ($n -match "republique") { $fileArm = $f.FullName }
    elseif ($n -match "ministere") { $fileMesrs = $f.FullName }
    elseif ($n -match "entreprise") { $fileLwp = $f.FullName }
    else { $fileIst = $f.FullName }   # école.png est le reste
}

Write-Host "Armoiries : $fileArm"
Write-Host "MESRS     : $fileMesrs"
Write-Host "LWP       : $fileLwp"
Write-Host "ISTEMA    : $fileIst"

# Encoder en base64
$armB64   = "data:image/png;base64," + [Convert]::ToBase64String([IO.File]::ReadAllBytes($fileArm))
$mesrsB64 = "data:image/png;base64," + [Convert]::ToBase64String([IO.File]::ReadAllBytes($fileMesrs))
$lwpB64   = "data:image/png;base64," + [Convert]::ToBase64String([IO.File]::ReadAllBytes($fileLwp))
$istB64   = "data:image/png;base64," + [Convert]::ToBase64String([IO.File]::ReadAllBytes($fileIst))

Write-Host "Longueurs b64 - ARM:$($armB64.Length) MESRS:$($mesrsB64.Length) LWP:$($lwpB64.Length) IST:$($istB64.Length)"

$content = [IO.File]::ReadAllText($html)

# Remplacement par correspondance alt= attribut
$content = [System.Text.RegularExpressions.Regex]::Replace(
    $content,
    'src="(?:data:image/png;base64,[A-Za-z0-9+/=]+|https://upload\.wikimedia\.org[^"]*)"(?=[^>]{0,300}alt="Armoiries)',
    "src=`"$armB64`""
)

$content = [System.Text.RegularExpressions.Regex]::Replace(
    $content,
    'src="(?:data:image/png;base64,[A-Za-z0-9+/=]+|http://localhost[^"]*mesrs[^"]*)"(?=[^>]{0,300}alt="Minist)',
    "src=`"$mesrsB64`""
)

$content = [System.Text.RegularExpressions.Regex]::Replace(
    $content,
    'src="(?:data:image/png;base64,[A-Za-z0-9+/=]+|http://localhost[^"]*istema[^"]*)"(?=[^>]{0,300}alt="ISTEMA)',
    "src=`"$istB64`""
)

$content = [System.Text.RegularExpressions.Regex]::Replace(
    $content,
    'src="(?:data:image/png;base64,[A-Za-z0-9+/=]+|http://localhost[^"]*lwp[^"]*)"(?=[^>]{0,300}alt="Leader)',
    "src=`"$lwpB64`""
)

[IO.File]::WriteAllText($html, $content)

$finalSize = [math]::Round([IO.File]::ReadAllBytes($html).Length / 1024, 1)
Write-Host "DONE - Taille rapport: $finalSize KB"
