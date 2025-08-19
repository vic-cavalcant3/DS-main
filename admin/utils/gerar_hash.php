<?php
// senha que você quer transformar em hash
$senha = "123456";

// gera o hash com algoritmo padrão (bcrypt)
$hash = password_hash($senha, PASSWORD_DEFAULT);

echo "Senha original: " . $senha . "<br>";
echo "Hash gerado: " . $hash;
?>
