<?php
$content = file_get_contents('resources/views/admin/config/import_staging_tiers.blade.php');
if (preg_match_all('/<script>(.*?)<\/script>/ms', $content, $matches)) {
    $js = end($matches[1]); // The last script block
    file_put_contents('temp.js', $js);
    system('node -c temp.js', $retval);
    if ($retval === 0) {
        echo "No syntax errors found.";
    }
}
