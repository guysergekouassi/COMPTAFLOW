# Script pour télécharger et encoder les logos en base64
$dst = "c:\laragon\www\COMPTAFLOW\public"

$logos = @{
    "logo_armoiries.png" = "https://upload.wikimedia.org/wikipedia/commons/thumb/c/ce/Armoiries_de_la_Cote_d%27Ivoire.svg/400px-Armoiries_de_la_Cote_d%27Ivoire.svg.png"
    "logo_mesrs.png"     = "https://upload.wikimedia.org/wikipedia/fr/thumb/2/2d/Logo_du_MESRS.png/220px-Logo_du_MESRS.png"
}

$client = New-Object System.Net.WebClient
$client.Headers.Add("User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36")

foreach ($name in $logos.Keys) {
    $url = $logos[$name]
    $path = Join-Path $dst $name
    try {
        $client.DownloadFile($url, $path)
        Write-Host "OK: $name ($([IO.File]::ReadAllBytes($path).Length) bytes)"
    } catch {
        Write-Host "FAIL: $name - $_"
    }
}
$client.Dispose()
