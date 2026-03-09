<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['logado'])) {
    http_response_code(403);
    echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado']);
    exit;
}

header('Content-Type: application/json');

$usuario_id = $_SESSION['usuario_id'];
$hoje = date('Y-m-d');
$agora = date('Y-m-d H:i:s');

try {
    // Busca os registros de hoje para determinar qual é o próximo tipo de registro
    $stmt = $pdo->prepare("SELECT tipo FROM registros_ponto WHERE usuario_id = ? AND DATE(data_hora) = ? ORDER BY data_hora ASC");
    $stmt->execute([$usuario_id, $hoje]);
    $registrosHoje = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $qtdRegistros = count($registrosHoje);
    $proximoTipo = '';

    if ($qtdRegistros == 0) {
        $proximoTipo = 'entrada';
    } elseif ($qtdRegistros == 1) {
        $proximoTipo = 'saida_almoco';
    } elseif ($qtdRegistros == 2) {
        $proximoTipo = 'retorno_almoco';
    } elseif ($qtdRegistros == 3) {
        $proximoTipo = 'saida';
    } else {
        // Já bateu os 4 pontos do dia
        echo json_encode(['sucesso' => false, 'erro' => 'Você já registrou todos os horários de hoje.']);
        exit;
    }

    // Insere o novo registro
    $stmtInsert = $pdo->prepare("INSERT INTO registros_ponto (usuario_id, tipo, data_hora) VALUES (?, ?, ?)");
    $stmtInsert->execute([$usuario_id, $proximoTipo, $agora]);

    // Retorna os dados do novo ponto para o front-end
    echo json_encode([
        'sucesso' => true,
        'registro' => [
            'tipo' => $proximoTipo,
            'hora' => date('H:i', strtotime($agora)),
            'data' => date('d/m/Y', strtotime($agora))
        ],
        'mensagem' => 'Ponto registrado com sucesso!'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'erro' => 'Erro ao registrar ponto: ' . $e->getMessage()]);
}
