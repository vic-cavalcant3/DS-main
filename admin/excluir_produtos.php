<?php
require 'conexao.php';

if (!isset($_GET['id'])) {
    header('Location: listar_produtos.php');
    exit;
}

$id = $_GET['id'];

try {
    // Inicia transação
    $pdo->beginTransaction();

    // 1️⃣ Busca as imagens para excluir os arquivos físicos
    $sql_busca_img = "SELECT url_imagem FROM imagens WHERE produto_id = ?";
    $stmt_busca = $pdo->prepare($sql_busca_img);
    $stmt_busca->execute([$id]);
    $imagens = $stmt_busca->fetchAll(PDO::FETCH_ASSOC);

    foreach ($imagens as $img) {
    // Caminho absoluto até a pasta
    $caminho_absoluto = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'client' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'produtos' . DIRECTORY_SEPARATOR . basename($img['url_imagem']);
    
    // Debug: veja o caminho gerado
    // var_dump($caminho_absoluto);

    if (file_exists($caminho_absoluto)) {
        unlink($caminho_absoluto); // Apaga o arquivo
    }
}

    // 2️⃣ Exclui imagens do banco
    $sql_img = "DELETE FROM imagens WHERE produto_id = ?";
    $stmt_img = $pdo->prepare($sql_img);
    $stmt_img->execute([$id]);

    // 3️⃣ Exclui estoque
    $sql_est = "DELETE FROM estoque WHERE produto_id = ?";
    $stmt_est = $pdo->prepare($sql_est);
    $stmt_est->execute([$id]);

    // 4️⃣ Exclui produto
    $sql_prod = "DELETE FROM produtos WHERE id = ?";
    $stmt_prod = $pdo->prepare($sql_prod);
    $stmt_prod->execute([$id]);

    // Finaliza transação
    $pdo->commit();

    header("Location: listar_produtos.php?success=Produto excluído com sucesso");
    exit;
} catch (PDOException $e) {
    $pdo->rollBack();
    die("Erro ao excluir: " . $e->getMessage());
}
