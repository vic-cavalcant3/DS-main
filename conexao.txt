<?php
$host = 'localhost';     // Servidor MySQL (geralmente 'localhost')
$dbname = 'FlammeIndex';  // Nome do banco que você criou
$user = 'root';          // Usuário do MySQL (padrão é 'root')
$pass = '';              // Senha do MySQL (vazia se não tiver)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Conectado com sucesso!"; // pode usar pra testar
} catch (PDOException $e) {
    die("Erro ao conectar: " . $e->getMessage());
}
?>