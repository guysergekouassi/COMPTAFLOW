$filePath = 'c:\laragon\www\COMPTAFLOW\resources\views\accounting_entry_real.blade.php'
$content = [IO.File]::ReadAllText($filePath, [System.Text.Encoding]::UTF8)

# Define replacements as a list of arrays to avoid hash table issues and control order
$replacements = @(
    @('Ã‰', 'É'),
    @('Ã©', 'é'),
    @('Ã ', 'à'),
    @('Ã¨', 'è'),
    @('Ã«', 'ë'),
    @('Ãª', 'ê'),
    @('Ã®', 'î'),
    @('Ã´', 'ô'),
    @('Ã»', 'û'),
    @('Ã¹', 'ù'),
    @('Ã§', 'ç'),
    @('Ãˆ', 'È'),
    @('Ã€', 'À'),
    @('Ã‚', 'Â'),
    @('ÃŠ', 'Ê'),
    @('Ã”', 'Ô'),
    @('NÂ°', 'N°'),
    @('Ã ', 'à')
)

foreach ($pair in $replacements) {
    $target = $pair[0]
    $replacement = $pair[1]
    $content = $content.Replace($target, $replacement)
}

[IO.File]::WriteAllText($filePath, $content, [System.Text.Encoding]::UTF8)
