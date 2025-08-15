<?php
require 'conexao.php';

if (!isset($_GET['id'])) {
    header('Location: listar_produtos.php');
    exit;
}

$id = $_GET['id'];

try {
    // Inicia transação para garantir integridade
    $pdo->beginTransaction();
    
    // Exclui imagens
    $sql_img = "DELETE FROM imagens WHERE produto_id = ?";
    $stmt_img = $pdo->prepare($sql_img);
    $stmt_img->execute([$id]);
    
    // Exclui estoque
    $sql_est = "DELETE FROM estoque WHERE produto_id = ?";
    $stmt_est = $pdo->prepare($sql_est);
    $stmt_est->execute([$id]);
    
    // Exclui produto
    $sql_prod = "DELETE FROM produtos WHERE id = ?";
    $stmt_prod = $pdo->prepare($sql_prod);
    $stmt_prod->execute([$id]);
    
    $pdo->commit();
    
 header("Location: .php?success=Produto excluído com sucesso");} catch (PDOException $e) {
    $pdo->rollBack();
    die("Erro ao excluir: " . $e->getMessage());
}