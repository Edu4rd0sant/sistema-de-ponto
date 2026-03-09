<?php
require_once __DIR__ . '/config/database.php';

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS solicitacoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        tipo ENUM('ferias', 'atestado', 'banco_horas', 'outro') NOT NULL,
        descricao TEXT NOT NULL,
        data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pendente', 'aprovada', 'recusada') DEFAULT 'pendente',
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    );
    ";
    
    $pdo->exec($sql);
    echo "Tabela 'solicitacoes' criada ou verificada com sucesso!\n";
} catch (PDOException $e) {
    die("Erro ao criar tabela: " . $e->getMessage());
}
?>
