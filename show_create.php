<?php

$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=projetcomptabilite', 'root', '');
foreach (['plan_comptables', 'plan_tiers', 'code_journals'] as $table) {
    $stmt = $pdo->query("SHOW CREATE TABLE {$table}");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "=== CREATE TABLE {$table} ===\n";
    echo $row['Create Table'] . "\n\n";
}
