<?php
// test_login.php - Simple test for login functionality
require_once 'config/session.php';
require_once 'config/database.php';

// Test data
$test_email = 'test@example.com';
$test_password = 'password123';

// Simulate login
try {
    $stmt = $pdo->prepare("SELECT id, nome, email, senha, nivel_acesso FROM usuarios WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $test_email);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($test_password, $usuario['senha'])) {
        echo "Login test passed for user: " . $usuario['nome'];
    } else {
        echo "Login test failed: Invalid credentials";
    }
} catch (PDOException $e) {
    echo "Test error: " . $e->getMessage();
}
?>