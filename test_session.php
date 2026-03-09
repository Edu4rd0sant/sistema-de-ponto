<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['test_counter'])) {
    $_SESSION['test_counter'] = 0;
}
$_SESSION['test_counter']++;

echo "Session ID: " . session_id() . "<br>";
echo "Counter: " . $_SESSION['test_counter'] . "<br>";

echo "<pre>";
print_r($_SESSION);
echo "</pre>";
