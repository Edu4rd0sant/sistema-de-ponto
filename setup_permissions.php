<?php
require_once __DIR__ . '/config/database.php';

try {
    // 1. Adicionando a coluna "cargo"
    $pdo->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS cargo VARCHAR(100) DEFAULT 'Empregado'");
    
    // 2. Adicionando a coluna "permissoes" no formato JSON
    // O IF NOT EXISTS em colunas requer MySQL 8+, caso falhe, faremos um trycatch específico
    try {
        $pdo->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS permissoes JSON NULL");
    } catch(PDOException $e2) {
        // Fallback pra MariaDB antigo ou MySQL 5.7
        $pdo->exec("ALTER TABLE usuarios ADD COLUMN permissoes JSON NULL");
    }

    echo "Colunas 'cargo' e 'permissoes' adicionadas com sucesso na tabela de usuários.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
       echo "As colunas já existem no banco de dados.\n";
    } else {
       echo "Erro ao alterar o banco de dados: " . $e->getMessage() . "\n";
    }
}
?>
