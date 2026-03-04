$src = "C:\Users\PC MARKET CI\.gemini\antigravity\brain\78516931-48a9-4763-89b0-24a121a5a0dd"
$dst = "c:\laragon\www\COMPTAFLOW\public"

[IO.File]::Copy("$src\login_page_1772474092364.png", "$dst\rapport_img_login.png", $true)
[IO.File]::Copy("$src\dashboard_1772474143355.png", "$dst\rapport_img_dashboard.png", $true)
[IO.File]::Copy("$src\escritures_comptables_1772474264404.png", "$dst\rapport_img_ecritures.png", $true)
[IO.File]::Copy("$src\plan_comptable_1772474325885.png", "$dst\rapport_img_plan.png", $true)
[IO.File]::Copy("$src\tresorerie_1772474462635.png", "$dst\rapport_img_tresorerie.png", $true)
Write-Host "OK"
