<?php
$host = 'localhost';
$dbname = 'FlammeIndex';
$username = 'root';    // Mudou de $user para $username
$password = '';        // Mudou de $pass para $password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar: " . $e->getMessage());
}
?>