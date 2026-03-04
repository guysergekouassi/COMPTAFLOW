$brainDir = "C:\Users\PC MARKET CI\.gemini\antigravity\brain\78516931-48a9-4763-89b0-24a121a5a0dd"
$reportPath = "c:\laragon\www\COMPTAFLOW\rapport_de_stage.html"

$loginB64 = [Convert]::ToBase64String([IO.File]::ReadAllBytes("$brainDir\ui_login.png"))
$dashB64 = [Convert]::ToBase64String([IO.File]::ReadAllBytes("$brainDir\ui_dashboard.png"))
$ecrB64 = [Convert]::ToBase64String([IO.File]::ReadAllBytes("$brainDir\ui_ecritures.png"))
$planB64 = [Convert]::ToBase64String([IO.File]::ReadAllBytes("$brainDir\ui_plan_comptable.png"))
$tresB64 = [Convert]::ToBase64String([IO.File]::ReadAllBytes("$brainDir\ui_tresorerie.png"))

$content = [IO.File]::ReadAllText($reportPath)
$content = $content.Replace("data:image/png;base64,PLACEHOLDER_LOGIN", "data:image/png;base64,$loginB64")
$content = $content.Replace("data:image/png;base64,PLACEHOLDER_DASHBOARD", "data:image/png;base64,$dashB64")
$content = $content.Replace("data:image/png;base64,PLACEHOLDER_ECRITURES", "data:image/png;base64,$ecrB64")
$content = $content.Replace("data:image/png;base64,PLACEHOLDER_PLAN", "data:image/png;base64,$planB64")
$content = $content.Replace("data:image/png;base64,PLACEHOLDER_TRESORERIE", "data:image/png;base64,$tresB64")
[IO.File]::WriteAllText($reportPath, $content)

$size = [IO.File]::ReadAllBytes($reportPath).Length
Write-Host "SUCCESS - Rapport final: $size bytes ($([math]::Round($size/1024/1024,2)) MB)"
