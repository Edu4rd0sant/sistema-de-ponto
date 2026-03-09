<?php
require 'config/database.php';
$tables_stmt = $pdo->query("SHOW TABLES");
$tables = $tables_stmt->fetchAll(PDO::FETCH_COLUMN);
$schema = [];
foreach ($tables as $table) {
    $stmt = $pdo->query("DESCRIBE $table");
    $schema[$table] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
file_put_contents('full_schema.json', json_encode($schema, JSON_PRETTY_PRINT));
echo "Full schema dumped to full_schema.json\n";
