<?php
require 'conexao.php';

if (!isset($_GET['id'])) {
    header('Location: listar_produtos.php');
    exit;
}

$id = $_GET['id'];

// Busca o produto
try {
    $sql_prod = "SELECT * FROM produtos WHERE id = ?";
    $stmt_prod = $pdo->prepare($sql_prod);
    $stmt_prod->execute([$id]);
    $produto = $stmt_prod->fetch(PDO::FETCH_ASSOC);
    
    if (!$produto) {
        die("Produto não encontrado");
    }
    
    // Busca imagens
    $sql_img = "SELECT * FROM imagens WHERE produto_id = ? ORDER BY ordem";
    $stmt_img = $pdo->prepare($sql_img);
    $stmt_img->execute([$id]);
    $imagens = $stmt_img->fetchAll();
    
    // Busca estoque
    $sql_est = "SELECT * FROM estoque WHERE produto_id = ?";
    $stmt_est = $pdo->prepare($sql_est);
    $stmt_est->execute([$id]);
    $estoque = [];
    while ($row = $stmt_est->fetch()) {
        $estoque[$row['tamanho']] = $row['quantidade'];
    }
    
} catch (PDOException $e) {
    die("Erro ao buscar produto: " . $e->getMessage());
}

// Processa o formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... (similar ao cadastro, mas com UPDATE)
    // Implemente conforme necessidade
}
?>

<!-- Formulário de edição (similar ao de cadastro, mas preenchido) -->