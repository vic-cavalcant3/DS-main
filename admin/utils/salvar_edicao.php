<?php
require '../conexao.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];

    try {
        $sql = "UPDATE produtos SET nome = ?, descricao = ?, preco = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $descricao, $preco, $id]);

        header('Location: ../pages/listar_produtos.php?success=Produto+atualizado+com+sucesso');
        exit;
    } catch (PDOException $e) {
        die("Erro ao atualizar produto: " . $e->getMessage());
    }
}
?>
