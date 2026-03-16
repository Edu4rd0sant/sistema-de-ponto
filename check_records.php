<?php
require_once __DIR__ . '/config/database.php';
try {
    $stmt = $pdo->query("SELECT id, usuario_id, data_hora, foto FROM registros_ponto ORDER BY id DESC LIMIT 10");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($records, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>
