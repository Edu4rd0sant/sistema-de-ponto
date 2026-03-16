<?php
namespace App\Controllers;

use Config\Database;
use PDO;
use PDOException;

class PontoController {

    // Coordenadas centrais (Maceió) para a cerca virtual
    private const LAT_EMPRESA = -9.66;
    private const LNG_EMPRESA = -35.70;
    // Raio máximo permitido de 200m (0.2 km)
    private const RAIO_MAXIMO_KM = 0.2; 

    public function index() {
        // Exemplo: Renderizar a View do html (substitui o registro solto do html)
        // require_once __DIR__ . '/../Views/ponto/index.html';
        echo "Aqui será renderizada a View do Ponto (HTML/AJAX)";
    }

    // Método chamado via AJAX (Fetch API) para registrar ponto
    public function registrar() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['sucesso' => false, 'erro' => 'Método não permitido']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        // Extrai as variáveis
        $lat = $input['lat'] ?? null;
        $lng = $input['lng'] ?? null;
        $selfieBase64 = $input['selfie'] ?? null;
        
        // Em um ambiente real, pegamos o ID do usuário da sessão validada
        // 
        // $userId = $_SESSION['user_id'] ?? null;
        $userId = $input['user_id'] ?? 1; // Fixo para fins de transição inicial

        if (!$lat || !$lng || !$selfieBase64 || !$userId) {
            http_response_code(400);
            echo json_encode(['sucesso' => false, 'erro' => 'Dados incompletos fornecidos. Requer Localização e Selfie.']);
            return;
        }

        // 1. Validar Haversine (Cerca Virtual)
        $distancia = $this->calcularHaversine(self::LAT_EMPRESA, self::LNG_EMPRESA, (float)$lat, (float)$lng);
        if ($distancia > self::RAIO_MAXIMO_KM) {
            http_response_code(403);
            echo json_encode(['sucesso' => false, 'erro' => 'Registro negado! Você está fora do raio permitido de 200m do local de trabalho.']);
            return;
        }

        // 2. Salvar Selfie Segurança via Proxy
        $fileName = $this->salvarSelfie($selfieBase64, $userId);
        if (!$fileName) {
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'erro' => 'Não foi possível salvar a selfie protegida.']);
            return;
        }

        // 3. Salvar no Banco usando Prepared Statements
        try {
            $pdo = Database::getConnection();
            // A query abaixo assume tabela `pontos` no seu banco - adapte à estrutura real
            $stmt = $pdo->prepare("INSERT INTO pontos (id_usuario, data_hora, latitude, longitude, foto) VALUES (?, NOW(), ?, ?, ?)");
            $stmt->execute([$userId, clone_val($lat), clone_val($lng), $fileName]);

            echo json_encode(['sucesso' => true, 'mensagem' => 'Ponto registrado com sucesso dentro da área permitida!']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'erro' => 'Erro interno de Banco de Dados: ' . escapeshellarg($e->getMessage())]);
        }
    }

    // Auxiliar para a Cerca Virtual usando Haversine
    private function calcularHaversine($lat1, $lon1, $lat2, $lon2) {
        $raioTerra = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distancia = $raioTerra * $c;
        
        return $distancia;
    }

    // Retorna apenas o nome do arquivo para esconder paths do banco de dados
    private function salvarSelfie($base64Data, $userId) {
        $storageDir = __DIR__ . '/../../storage/selfies';
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        // Verifica integridade da string Base64
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
            $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
            $type = strtolower($type[1]);

            if (!in_array($type, ['jpg', 'jpeg', 'png'])) {
                return false;
            }

            $imgData = base64_decode($base64Data);
            if ($imgData === false) {
                return false;
            }

            $fileName = 'user_' . $userId . '_' . time() . '.' . $type;
            $filePath = $storageDir . '/' . $fileName;

            if (file_put_contents($filePath, $imgData)) {
                return $fileName;
            }
        }
        return false;
    }
}

// Utility inline for avoiding strict standard errors with references if any
function clone_val($val) { return $val; }

