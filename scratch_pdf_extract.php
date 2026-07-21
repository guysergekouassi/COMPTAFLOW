<?php
// Let's write a simple script to read PDF content if possible, or print metadata
// Since we don't have pdftotext easily, let's look if we can extract text using standard PHP or just read raw text patterns.
$content = file_get_contents("c:\\laragon\\www\\COMPTAFLOW\\PLAN\\ANALYTIQUE\\Grand-livre_analytique pearl.pdf");
// Find some text elements
preg_match_all('/[\x20-\x7E]{4,}/', $content, $matches);
file_put_contents("c:\\laragon\\www\\COMPTAFLOW\\scratch_pdf_text.txt", implode("\n", array_slice($matches[0], 0, 300)));
echo "Done";
