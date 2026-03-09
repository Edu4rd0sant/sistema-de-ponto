<?php
require 'config/database.php';

echo "Verificando constraints e integridade...\n";

// 1. Verificar se existem registros órfãos que deveriam ter sido deletados em cascata (se houver constraints)
// Na verdade, vamos verificar se as constraints EXISTEM.
$tables = ['registros_ponto', 'solicitacoes'];
foreach ($tables as $table) {
    echo "\nEstrutura de constraints para $table:\n";
    $stmt = $pdo->query("SHOW CREATE TABLE $table");
    $row = $stmt->fetch();
    echo $row['Create Table'] . "\n";
}

// 2. Testar se a exclusão de um usuário (teste) funciona
try {
    $pdo->beginTransaction();
    
    // Criar um usuário de teste
    $pdo->exec("INSERT INTO usuarios (nome, email, senha, nivel_acesso) VALUES ('Teste Integridade', 'teste_integ@primus.com', '123456', 'funcionario')");
    $test_id = $pdo->lastInsertId();
    
    // Criar um ponto para ele
    $pdo->exec("INSERT INTO registros_ponto (usuario_id, tipo, data_hora) VALUES ($test_id, 'entrada', NOW())");
    
    // Tentar excluir
    echo "\nTestando exclusão do usuário ID $test_id...\n";
    
    // O script excluir_funcionario.php faz o delete manual de pontos e solicitações.
    // Vamos simular a lógica dele:
    $pdo->exec("DELETE FROM solicitacoes WHERE usuario_id = $test_id");
    $pdo->exec("DELETE FROM registros_ponto WHERE usuario_id = $test_id");
    $rowCount = $pdo->exec("DELETE FROM usuarios WHERE id = $test_id AND nivel_acesso != 'admin'");
    
    if ($rowCount > 0) {
        echo "Exclusão bem-sucedida!\n";
    } else {
        echo "Falha na exclusão: Nenhuma linha afetada.\n";
    }
    
    $pdo->rollBack(); // Não queremos manter o lixo
    echo "Rollback executado.\n";
    
} catch (Exception $e) {
    echo "ERRO DURANTE TESTE: " . $e->getMessage() . "\n";
    if ($pdo->inTransaction()) $pdo->rollBack();
}
