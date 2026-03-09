<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['logado'])) {
    http_response_code(403);
    echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado']);
    exit;
}

header('Content-Type: application/json');

$usuario_id = $_SESSION['usuario_id'];
$hoje = date('Y-m-d');

try {
    $stmt = $pdo->prepare("SELECT tipo, DATE_FORMAT(data_hora, '%H:%i') as hora FROM registros_ponto WHERE usuario_id = ? AND DATE(data_hora) = ? ORDER BY data_hora ASC");
    $stmt->execute([$usuario_id, $hoje]);
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'sucesso' => true,
        'data' => $registros
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'erro' => 'Erro ao buscar registros: ' . $e->getMessage()]);
}
