<?php
require '../conexao.php';

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../pages/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $preco_original = !empty($_POST['preco_original']) ? $_POST['preco_original'] : null;
    $desconto = !empty($_POST['desconto']) ? $_POST['desconto'] : 0;
    $categoria_id = !empty($_POST['categoria_id']) ? $_POST['categoria_id'] : null;
    $anime_id = !empty($_POST['anime_id']) ? $_POST['anime_id'] : null;
    $cores = !empty($_POST['cores']) ? implode(',', $_POST['cores']) : null;
    $tags = $_POST['tags'];
    
    // Configurações de upload
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/ds-main/admin/src/uploads/';
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    $novasImagens = [];

    try {
        $pdo->beginTransaction();
        
        // Atualiza os dados básicos do produto
        $sql = "UPDATE produtos SET nome = ?, descricao = ?, preco = ?, preco_original = ?, 
                desconto = ?, categoria_id = ?, anime_id = ?, cores = ?, tags = ? WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $nome, $descricao, $preco, $preco_original, $desconto, 
            $categoria_id, $anime_id, $cores, $tags, $id
        ]);
        
        // Processa o estoque
        $tamanhos = ['PP', 'P', 'M', 'G', 'GG', 'XG'];
        foreach ($tamanhos as $tamanho) {
            $quantidade = $_POST["estoque_$tamanho"] ?? 0;
            
            // Verifica se já existe registro para este tamanho
            $sqlCheck = "SELECT id FROM estoque WHERE produto_id = ? AND tamanho = ?";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute([$id, $tamanho]);
            $exists = $stmtCheck->fetch();
            
            if ($exists) {
                // Atualiza o estoque existente
                $sqlUpdate = "UPDATE estoque SET quantidade = ? WHERE produto_id = ? AND tamanho = ?";
                $stmtUpdate = $pdo->prepare($sqlUpdate);
                $stmtUpdate->execute([$quantidade, $id, $tamanho]);
            } else {
                // Insere novo registro de estoque
                $sqlInsert = "INSERT INTO estoque (produto_id, tamanho, quantidade) VALUES (?, ?, ?)";
                $stmtInsert = $pdo->prepare($sqlInsert);
                $stmtInsert->execute([$id, $tamanho, $quantidade]);
            }
        }
        
        // Processa exclusão de imagens
        if (!empty($_POST['excluir_imagens'])) {
    foreach ($_POST['excluir_imagens'] as $idImagem) {
        // Busca a URL da imagem pra deletar o arquivo do servidor
        $stmt = $pdo->prepare("SELECT url_imagem FROM imagens WHERE id = ?");
        $stmt->execute([$idImagem]);
        $img = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($img && file_exists(__DIR__ . "/../uploads/" . $img['url_imagem'])) {
            unlink(__DIR__ . "/../uploads/" . $img['url_imagem']);
        }

        // Agora remove só essa imagem do banco
        $stmt = $pdo->prepare("DELETE FROM imagens WHERE id = ?");
        $stmt->execute([$idImagem]);
    }
}

        
        // Processa novas imagens
        if (!empty($_FILES['novas_imagens']['name'][0])) {
            // Verifica se o diretório existe, senão cria
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Obtém a ordem máxima atual para continuar a partir dela
            $sqlMaxOrdem = "SELECT MAX(ordem) as max_ordem FROM imagens WHERE produto_id = ?";
            $stmtMaxOrdem = $pdo->prepare($sqlMaxOrdem);
            $stmtMaxOrdem->execute([$id]);
            $maxOrdem = $stmtMaxOrdem->fetch(PDO::FETCH_ASSOC);
            $ordem = $maxOrdem['max_ordem'] ? $maxOrdem['max_ordem'] + 1 : 1;
            
            foreach ($_FILES['novas_imagens']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['novas_imagens']['error'][$key] === UPLOAD_ERR_OK) {
                    $fileType = $_FILES['novas_imagens']['type'][$key];
                    $fileSize = $_FILES['novas_imagens']['size'][$key];
                    $fileName = $_FILES['novas_imagens']['name'][$key];
                    
                    // Validação
                    if (in_array($fileType, $allowedTypes) && $fileSize <= $maxSize) {
                        // Gera nome único para o arquivo
                        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                        $uniqueFileName = uniqid() . '.' . $ext;
                        $destino = $uploadDir . $uniqueFileName;
                        
                        if (move_uploaded_file($tmpName, $destino)) {
                            // Caminho relativo para salvar no banco
                            $caminho = 'admin/src/uploads/' . $uniqueFileName;
                            
                            // Insere a nova imagem
                            $sqlImgInsert = "INSERT INTO imagens (produto_id, url_imagem, ordem) VALUES (?, ?, ?)";
                            $stmtImgInsert = $pdo->prepare($sqlImgInsert);
                            $stmtImgInsert->execute([$id, $caminho, $ordem]);
                            
                            $ordem++;
                        }
                    }
                }
            }
        }
        
        $pdo->commit();
        
        header("Location: ../pages/listar_produtos.php?success=Produto atualizado com sucesso!");
        exit;
        
    } catch (Exception $e) {
        $pdo->rollback();
        
        // Remove imagens já salvas em caso de erro
        foreach ($novasImagens as $caminho) {
            $fullPath = $uploadDir . basename($caminho);
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
        
        header("Location: ../pages/listar_produtos.php?error=" . urlencode("Erro ao atualizar produto: " . $e->getMessage()));
        exit;
    }
} else {
    header("Location: ../pages/listar_produtos.php");
    exit;
}
?>