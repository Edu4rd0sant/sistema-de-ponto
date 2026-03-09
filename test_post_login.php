<?php
$ch = curl_init('http://localhost:8000/actions/login_action.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'email' => 'admin@primus.com',
    'senha' => '123456'
]));
// Capturar headers para ver redirecionamento
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
curl_close($ch);

echo "Resposta do servidor HTTP:\n";
echo $response;
?>
