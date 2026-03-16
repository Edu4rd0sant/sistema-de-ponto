<?php
require 'config/database.php';
$pdo = Config\Database::getConnection();
$stmt = $pdo->query("DESCRIBE solicitacoes");
print_r($stmt->fetchAll());
