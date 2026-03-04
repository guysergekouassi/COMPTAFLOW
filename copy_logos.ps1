$brain = "C:\Users\PC MARKET CI\.gemini\antigravity\brain\78516931-48a9-4763-89b0-24a121a5a0dd"
$pub = "c:\laragon\www\COMPTAFLOW\public"

# Copier les logos dans public
[IO.File]::Copy("$brain\logo_istema_1772502569888.png", "$pub\logo_istema.png", $true)
[IO.File]::Copy("$brain\logo_leader_world_perfect_1772502583913.png", "$pub\logo_lwp.png", $true)
[IO.File]::Copy("$brain\logo_mesrs_1772502612912.png", "$pub\logo_mesrs.png", $true)

Write-Host "Taille ISTEMA: $([IO.File]::ReadAllBytes("$pub\logo_istema.png").Length)"
Write-Host "Taille LWP: $([IO.File]::ReadAllBytes("$pub\logo_lwp.png").Length)"
Write-Host "Taille MESRS: $([IO.File]::ReadAllBytes("$pub\logo_mesrs.png").Length)"
Write-Host "OK"
