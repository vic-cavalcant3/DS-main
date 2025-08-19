<?php
require '../conexao.php';

if (!isset($_GET['id'])) {
    header('Location: ../pages/listar_produtos.php');
    exit;
}

$id = $_GET['id'];

try {
    // Busca produto
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

    // **PASSO 4** - Retornar JSON se solicitado via AJAX
    if (isset($_GET['json']) && $_GET['json'] == 1) {
        header('Content-Type: application/json');
        echo json_encode([
            'id' => $produto['id'],
            'nome' => $produto['nome'],
            'descricao' => $produto['descricao'],
            'preco' => $produto['preco'],
            'tags' => $produto['tags'],
            'estoque' => $estoque,
            'imagens' => $imagens
        ]);
        exit;
    }

} catch (PDOException $e) {
    die("Erro ao buscar produto: " . $e->getMessage());
}

// Processa o formulário de edição (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aqui você faz o UPDATE do produto no banco
    // Por exemplo:

    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];

    $sql_update = "UPDATE produtos SET nome = ?, descricao = ?, preco = ? WHERE id = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$nome, $descricao, $preco, $id]);

    header('Location: ../pages/listar_produtos.php?success=Produto+atualizado+com+sucesso');
    exit;

}
?>
