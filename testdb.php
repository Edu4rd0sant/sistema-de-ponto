<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=sistemaponto', 'root', '');
    $stmt = $pdo->query('DESCRIBE usuarios');
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo $e->getMessage();
}
