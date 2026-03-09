<?php
require_once 'config/database.php';

try {
    $stmt = $pdo->query("SELECT id, nome, email, senha, nivel_acesso FROM usuarios");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total de usuários: " . count($usuarios) . "\n\n";
    foreach ($usuarios as $u) {
        echo "ID: " . $u['id'] . "\n";
        echo "Nome: " . $u['nome'] . "\n";
        echo "Email: " . $u['email'] . "\n";
        echo "Senha: " . $u['senha'] . "\n";
        echo "Nível: " . $u['nivel_acesso'] . "\n";
        echo "--------------------------\n";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>
