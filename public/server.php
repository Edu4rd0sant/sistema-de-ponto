<?php
// server.php router para o PHP Built-in Server
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Se a requisição for para um arquivo existente na pasta public, sirva o arquivo
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Caso contrário, envie para o index.php
$_GET['url'] = ltrim($uri, '/');
require_once __DIR__ . '/index.php';
