<?php
namespace App\Controllers;

class FileController {

    // Servir arquivos do storage evitando acesso direto
    public function visualizar() {
        // Proteção 1: Valide a sessão aqui
        // 
        // if (!isset($_SESSION['user_id'])) {
        //    http_response_code(403);
        //    die('Acesso Proibido');
        // }

        $fileName = $_GET['file'] ?? '';

        // Proteção 2: Tratamento de Path Traversal (evitar ../../etc/passwd)
        if (empty($fileName) || strpos($fileName, '..') !== false || strpos($fileName, '/') !== false || strpos($fileName, '\\') !== false) {
            http_response_code(400);
            die('Arquivo de selfie inválido ou não especificado');
        }

        $filePath = __DIR__ . '/../../storage/selfies/' . $fileName;

        if (file_exists($filePath)) {
            $mime = mime_content_type($filePath);
            header("Content-Type: $mime");
            header("Content-Length: " . filesize($filePath));
            readfile($filePath);
            exit;
        } else {
            http_response_code(404);
            die('Imagem não encontrada');
        }
    }
}

