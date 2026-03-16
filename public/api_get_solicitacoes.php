<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

// Apenas Admin pode ver
if (!isset($_SESSION['logado']) || $_SESSION['nivel_acesso'] !== 'admin') {
	http_response_code(403);
	echo json_encode(['erro' => 'Acesso negado']);
	exit;
}

header('Content-Type: application/json');

try {
	$pdo = Database::getConnection();
	// Busca solicitações pendentes e une com a tabela usuarios para pegar o nome
	$query = "
		SELECT s.id, u.nome AS nome_funcionario, s.tipo, s.descricao, s.status, s.data_solicitacao as solicitada_em
		FROM solicitacoes s
		JOIN usuarios u ON s.usuario_id = u.id
		WHERE s.status = 'pendente'
		ORDER BY s.data_solicitacao DESC
	";
	$stmt = $pdo->prepare($query);
	$stmt->execute();
	$solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

	echo json_encode([
		'sucesso' => true,
		'count' => count($solicitacoes),
		'data' => $solicitacoes
	]);
} catch (PDOException $e) {
	http_response_code(500);
	echo json_encode([
		'sucesso' => false,
		'erro' => 'Erro ao buscar solicitações: ' . $e->getMessage()
	]);
}
