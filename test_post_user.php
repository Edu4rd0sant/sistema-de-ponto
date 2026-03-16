<?php
require 'config/database.php';

session_start();
$_SESSION['logado'] = true;
$_SESSION['nivel_acesso'] = 'admin';
$_SESSION['csrf'] = 'test-token';
$_SESSION['usuario_id'] = 1;

$_POST = [
    'csrf' => 'test-token',
    'nome' => 'Test User',
    'email' => 'testuser' . time() . '@primus.com',
    'senha' => '123456',
    'cargo' => 'Tester',
    'status_trabalho' => 'Trabalhando',
    'escala_id' => '',
    'permissoes' => []
];

$_SERVER["REQUEST_METHOD"] = "POST";

require 'app/Controllers/AdminController.php';
$controller = new App\Controllers\AdminController();

ob_start();
$controller->criarFuncionario();
$output = ob_get_clean();

var_dump(http_response_code());
var_dump(headers_list());
var_dump($output);
