<?php
namespace App\Controllers\Api;

use Config\Database;
use PDO;
use PDOException;

class ApiPontoController {

    public function getPontoHoje() {
        
        header('Content-Type: application/json');

        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Não autorizado']);
            exit;
        }

        $usuario_id = $_SESSION['usuario_id'];
        $data_hoje = date('Y-m-d');

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT id, TIME(data_hora) as hora, tipo FROM pontos WHERE usuario_id = ? AND DATE(data_hora) = ? ORDER BY data_hora ASC");
            $stmt->execute([$usuario_id, $data_hoje]);
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($registros);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro no servidor: ' . $e->getMessage()]);
        }
    }
}

