<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>PHP is working</h1>";
require_once __DIR__ . '/../config/database.php';
echo "<p>Database config loaded</p>";

require_once __DIR__ . '/../app/Controllers/AuthController.php';
echo "<p>AuthController loaded</p>";

$controller = new \App\Controllers\AuthController();
echo "<p>Calling index()</p>";
$controller->index();
