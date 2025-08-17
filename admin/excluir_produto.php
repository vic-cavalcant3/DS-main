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

    // Exclui arquivos físicos das imagens
    foreach ($imagens as $img) {
        $nomeArquivo = basename($img['url_imagem']);
        
        // Baseado no seu cadastrar_produto.php, o caminho absoluto é:
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/ds-main/admin/src/uploads/';
        $caminhoCompleto = $uploadDir . $nomeArquivo;
        
        // Debug temporário - remova após testar
        error_log("Tentando excluir: " . $caminhoCompleto);
        error_log("Arquivo existe? " . (file_exists($caminhoCompleto) ? 'Sim' : 'Não'));
        
        if (file_exists($caminhoCompleto)) {
            if (unlink($caminhoCompleto)) {
                error_log("Arquivo excluído com sucesso: " . $nomeArquivo);
            } else {
                error_log("Erro ao excluir arquivo: " . $nomeArquivo);
            }
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

    // Redirecionamento com mensagem de sucesso
    header("Location: listar_produtos.php?success=" . urlencode("Produto excluído com sucesso"));
    exit;

} catch (PDOException $e) {
    // Rollback em caso de erro
    $pdo->rollBack();
    
    // Redirecionamento com mensagem de erro
    header("Location: listar_produtos.php?error=" . urlencode("Erro ao excluir produto: " . $e->getMessage()));
    exit;
} catch (Exception $e) {
    // Rollback para outros tipos de erro
    $pdo->rollBack();
    
    header("Location: listar_produtos.php?error=" . urlencode("Erro inesperado: " . $e->getMessage()));
    exit;
}
?>