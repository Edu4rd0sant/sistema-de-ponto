<?php
// =========================================================================
// public/index.php
// FRONT CONTROLLER PRINCIPAL DA APLICAÇÃO (MVC)
// =========================================================================

// Exibe erros em desenvolvimento (remover/em desenvolvimento em PRD)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Inicializa a sessão GLOBAIS
session_start();

// Carrega o arquivo de banco de dados
require_once __DIR__ . '/../config/database.php';

// Autoloader Simples (PSR-4 Pattern simplificado)
spl_autoload_register(function ($class) {
    // Exemplo: 'App\Controllers\PontoController' vira 'app/Controllers/PontoController.php'
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    // Transforma a primeira letra em minúsculo (App -> app)
    $path = lcfirst($path);
    $file = __DIR__ . '/../' . $path . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

use Core\Router;

$router = new Router();

// ==========================================
// REGISTRO DE ROTAS
// ==========================================
// Formato: $router->add(Method, URI, 'Controller@Action');

// Rota Raiz Fallback (Redireciona para login)
$router->add('GET', '/', 'AuthController@index');

// Auth controller endpoints (futuros/atuais migrados)
$router->add('GET', '/login', 'AuthController@index');
$router->add('POST', '/login', 'AuthController@login');

// Ponto controller endpoints
$router->add('GET', '/ponto', 'PontoController@index');
$router->add('POST', '/ponto/registrar', 'PontoController@registrar');

// Admin panel
$router->add('GET', '/admin', 'AdminController@dashboard');
$router->add('GET', '/admin/gestao_ponto', 'AdminController@gestao_ponto');
$router->add('GET', '/admin/escalas', 'AdminController@escalas');
$router->add('GET', '/admin/relatorios', 'AdminController@relatorios');
$router->add('GET', '/admin/perfil', 'AdminController@perfil');

// Admin Actions
$router->add('POST', '/admin/funcionario/criar', 'AdminController@criarFuncionario');
$router->add('POST', '/admin/funcionario/excluir', 'AdminController@excluirFuncionario');
$router->add('POST', '/admin/funcionario/editar_permissoes', 'AdminController@editarPermissoes');
$router->add('POST', '/admin/funcionario/resetar_senha', 'AdminController@resetarSenha');
$router->add('POST', '/admin/escalas/salvar', 'AdminController@salvarEscala');
$router->add('POST', '/admin/escalas/excluir', 'AdminController@excluirEscala');
$router->add('POST', '/admin/ponto/salvar', 'AdminController@salvarPonto');

// Funcionario panel
$router->add('GET', '/funcionario', 'FuncionarioController@dashboard');
$router->add('GET', '/funcionario/historico', 'FuncionarioController@historico');
$router->add('GET', '/funcionario/solicitacoes', 'FuncionarioController@solicitacoes');
$router->add('POST', '/funcionario/solicitacoes/criar', 'FuncionarioController@criarSolicitacao');
$router->add('GET', '/funcionario/perfil', 'FuncionarioController@perfil');

// Authentication extra
$router->add('GET', '/logout', 'AuthController@logout');
$router->add('POST', '/perfil/senha/alterar', 'AuthController@alterarSenha');

// APIs
$router->add('GET', '/api/ponto/hoje', 'Api\ApiPontoController@getPontoHoje');
$router->add('GET', '/api/solicitacoes', 'Api\ApiSolicitacoesController@getSolicitacoes');
$router->add('POST', '/api/solicitacoes/atualizar', 'Api\ApiSolicitacoesController@atualizarSolicitacao');
$router->add('GET', '/api/funcionario/notificacoes', 'Api\ApiSolicitacoesController@checarNotificacoesFuncionario');
$router->add('POST', '/api/funcionario/notificacoes/ler', 'Api\ApiSolicitacoesController@marcarLidasFuncionario');

// Arquivos Seguros e Proxies
$router->add('GET', '/storage/selfies', 'FileController@visualizar');

// ==========================================
// DESPACHO DA REQUISIÇÃO (DISPATCH)
// ==========================================
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Suporte universal: tenta pegar do mod_rewrite (GET url), senão cai pro REQUEST_URI (PHP Server nativo)
if (isset($_GET['url'])) {
    $url = '/' . rtrim($_GET['url'], '/');
} else {
    $url = '/' . ltrim($uri, '/');
    $url = rtrim($url, '/');
    if ($url === '') {
        $url = '/';
    }
}
$method = $_SERVER['REQUEST_METHOD'];

$router->dispatch($url, $method);


