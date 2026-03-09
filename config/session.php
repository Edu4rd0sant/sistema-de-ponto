<?php
// config/session.php

// Define a vida útil da sessão para 7 dias em segundos
const SESSION_LIFETIME = 60 * 60 * 24 * 7;
$lifetime = SESSION_LIFETIME;

// Configurações do servidor PHP para Garbage Collection (limpeza de sessões velhas)
ini_set('session.gc_maxlifetime', $lifetime);

// Configurações do cookie no navegador do usuário
ini_set('session.cookie_lifetime', $lifetime);
session_set_cookie_params($lifetime);

// Inicia a sessão se ela já não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
