<?php
// Simula login
session_start();
$_SESSION['logado'] = true;
$_SESSION['usuario_id'] = 1;

// Dados de teste
$fakeImage = 'data:image/jpeg;base64,' . base64_encode('fake image content');
$payload = [
    'data_hora' => date('Y-m-d H:i:s'),
    'latitude' => -23.55052,
    'longitude' => -46.633308,
    'foto' => $fakeImage
];

// Configura o ambiente para rodar o script diretamente
$_SERVER['REQUEST_METHOD'] = 'POST';
$tempFile = tempnam(sys_get_temp_dir(), 'post');
file_put_contents($tempFile, json_encode($payload));

// Mock php://input
function mock_input($file) {
    return $file;
}

// Em vez de rodar via HTTP, vamos incluir o arquivo e ver se ele salva.
// Mas registrar_ponto.php usa file_get_contents('php://input').
// Não tem como dar mock nisso facilmente sem mudar o código.

// Vou criar um wrapper que injeta os dados.
?>
