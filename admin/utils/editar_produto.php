<?php
require '../conexao.php';

// Verifica se é uma requisição JSON
$isJsonRequest = isset($_GET['json']) && $_GET['json'] == 1;

if (!isset($_GET['id'])) {
    if ($isJsonRequest) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'ID do produto não especificado']);
        exit;
    } else {
        header('Location: ../pages/listar_produtos.php');
        exit;
    }
}

$id = $_GET['id'];

try {
    // Busca produto
    $sql_prod = "SELECT * FROM produtos WHERE id = ?";
    $stmt_prod = $pdo->prepare($sql_prod);
    $stmt_prod->execute([$id]);
    $produto = $stmt_prod->fetch(PDO::FETCH_ASSOC);

    if (!$produto) {
        if ($isJsonRequest) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Produto não encontrado']);
            exit;
        } else {
            die("Produto não encontrado");
        }
    }

    // Busca imagens - CORRIGIDO: usando o nome correto da tabela
    $sql_img = "SELECT * FROM imagens WHERE produto_id = ? ORDER BY id";
    $stmt_img = $pdo->prepare($sql_img);
    $stmt_img->execute([$id]);
    $imagens = $stmt_img->fetchAll(PDO::FETCH_ASSOC);

    // Busca estoque
    $sql_est = "SELECT * FROM estoque WHERE produto_id = ?";
    $stmt_est = $pdo->prepare($sql_est);
    $stmt_est->execute([$id]);
    $estoque = [];
    while ($row = $stmt_est->fetch(PDO::FETCH_ASSOC)) {
        $estoque[$row['tamanho']] = $row['quantidade'];
    }

    // Retornar JSON se solicitado via AJAX
    if ($isJsonRequest) {
        header('Content-Type: application/json');
        echo json_encode([
            'id' => $produto['id'],
            'nome' => $produto['nome'],
            'descricao' => $produto['descricao'],
            'preco' => $produto['preco'],
            'preco_original' => $produto['preco_original'],
            'desconto' => $produto['desconto'],
            'categoria_id' => $produto['categoria_id'],
            'anime_id' => $produto['anime_id'],
            'cores' => $produto['cores'] ? explode(',', $produto['cores']) : [],
            'tags' => $produto['tags'],
            'estoque' => $estoque,
            'imagens' => $imagens
        ]);
        exit;
    }

} catch (PDOException $e) {
    if ($isJsonRequest) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erro ao buscar produto: ' . $e->getMessage()]);
        exit;
    } else {
        die("Erro ao buscar produto: " . $e->getMessage());
    }
}
// Processa o formulário de edição (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $preco_original = !empty($_POST['preco_original']) ? $_POST['preco_original'] : null;
    $desconto = !empty($_POST['desconto']) ? $_POST['desconto'] : 0;
    $categoria_id = !empty($_POST['categoria_id']) ? $_POST['categoria_id'] : null;
    $anime_id = !empty($_POST['anime_id']) ? $_POST['anime_id'] : null;
    $cores = !empty($_POST['cores']) ? implode(',', $_POST['cores']) : null;
    $tags = $_POST['tags'];

    try {
        $pdo->beginTransaction();

        // Atualiza produto
        $sql_update = "UPDATE produtos SET 
                        nome = ?, descricao = ?, preco = ?, preco_original = ?, desconto = ?, 
                        categoria_id = ?, anime_id = ?, cores = ?, tags = ?, updated_at = NOW() 
                        WHERE id = ?";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([
            $nome, $descricao, $preco, $preco_original, $desconto, 
            $categoria_id, $anime_id, $cores, $tags, $id
        ]);

        // Atualiza estoque
        $tamanhos = ['PP','P','M','G','GG','XG'];
        foreach ($tamanhos as $tamanho) {
            $quantidade = isset($_POST["estoque_$tamanho"]) ? (int)$_POST["estoque_$tamanho"] : 0;
            $sql_check = "SELECT id FROM estoque WHERE produto_id = ? AND tamanho = ?";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([$id, $tamanho]);
            $existe = $stmt_check->fetch();

            if ($existe) {
                if ($quantidade > 0) {
                    $sql_update_est = "UPDATE estoque SET quantidade = ?, updated_at = NOW() WHERE produto_id = ? AND tamanho = ?";
                    $stmt_update_est = $pdo->prepare($sql_update_est);
                    $stmt_update_est->execute([$quantidade, $id, $tamanho]);
                } else {
                    $sql_delete_est = "DELETE FROM estoque WHERE produto_id = ? AND tamanho = ?";
                    $stmt_delete_est = $pdo->prepare($sql_delete_est);
                    $stmt_delete_est->execute([$id, $tamanho]);
                }
            } else {
                if ($quantidade > 0) {
                    $sql_insert_est = "INSERT INTO estoque (produto_id, tamanho, quantidade) VALUES (?, ?, ?)";
                    $stmt_insert_est = $pdo->prepare($sql_insert_est);
                    $stmt_insert_est->execute([$id, $tamanho, $quantidade]);
                }
            }
        }

        // Upload de novas imagens
        if (!empty($_FILES['imagens']['name'][0])) {
            $uploadDir = '../uploads/produtos/';
            foreach ($_FILES['imagens']['tmp_name'] as $index => $tmpName) {
                $fileName = time() . '_' . basename($_FILES['imagens']['name'][$index]);
                $targetFile = $uploadDir . $fileName;
                if (move_uploaded_file($tmpName, $targetFile)) {
                    $sql_img = "INSERT INTO imagens_produtos (produto_id, caminho) VALUES (?, ?)";
                    $stmt_img = $pdo->prepare($sql_img);
                    $stmt_img->execute([$id, $fileName]);
                }
            }
        }

        $pdo->commit();
        header('Location: ../pages/listar_produtos.php?success=Produto+atualizado+com+sucesso');
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        header('Location: ../pages/listar_produtos.php?error=Erro+ao+atualizar+produto:' . urlencode($e->getMessage()));
        exit;
    }
}
?>
