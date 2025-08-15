<?php
require 'conexao.php';

// Configurações de upload
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/ds-main/client/src/produtos/';
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
$maxSize = 2 * 1024 * 1024; // 2MB

// Processa o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $tags = $_POST['tags'];
    $imagens = [];

    try {
        // Verifica se o diretório existe, senão cria
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Valida e move as imagens enviadas
        if (!empty($_FILES['imagens']['name'][0])) {
            foreach ($_FILES['imagens']['tmp_name'] as $key => $tmpName) {
                // Verifica se o arquivo foi enviado sem erro
                if ($_FILES['imagens']['error'][$key] !== UPLOAD_ERR_OK) {
                    throw new Exception("Erro no upload da imagem: " . $_FILES['imagens']['name'][$key]);
                }
                
                $fileType = $_FILES['imagens']['type'][$key];
                $fileSize = $_FILES['imagens']['size'][$key];
                $fileName = $_FILES['imagens']['name'][$key];
                
                // Validação
                if (!in_array($fileType, $allowedTypes)) {
                    throw new Exception("Tipo de arquivo não permitido: " . $fileName);
                }
                
                if ($fileSize > $maxSize) {
                    throw new Exception("Arquivo muito grande: " . $fileName);
                }
                
                // Gera nome único para o arquivo
                $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                $uniqueFileName = uniqid() . '.' . $ext;
                $destino = $uploadDir . $uniqueFileName;
                
                if (move_uploaded_file($tmpName, $destino)) {
                    // Caminho relativo para salvar no banco
                    $imagens[] = 'client/src/produtos/' . $uniqueFileName;
                } else {
                    throw new Exception("Erro ao mover arquivo: " . $fileName);
                }
            }
        }

        // Inicia transação
        $pdo->beginTransaction();
        
        // Cadastra o produto
        $sql = "INSERT INTO produtos (nome, descricao, preco, tags) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $descricao, $preco, $tags]);
        $produto_id = $pdo->lastInsertId();
        
        // Debug: verifica se o produto foi inserido
        if (!$produto_id) {
            throw new Exception("Erro ao inserir produto no banco de dados");
        }
        
        // Cadastra as imagens no banco
        if (!empty($imagens)) {
            foreach ($imagens as $ordem => $caminho) {
                $sql_img = "INSERT INTO imagens (produto_id, url_imagem, ordem) VALUES (?, ?, ?)";
                $stmt_img = $pdo->prepare($sql_img);
                $result = $stmt_img->execute([$produto_id, $caminho, $ordem + 1]);
                
                // Debug: verifica se a imagem foi inserida
                if (!$result) {
                    throw new Exception("Erro ao inserir imagem no banco: " . $caminho);
                }
            }
        }
        
        // Cadastra estoque
        $tamanhos = ['PP', 'P', 'M', 'G', 'GG', 'XG'];
        foreach ($tamanhos as $tamanho) {
            if (!empty($_POST["estoque_$tamanho"]) && $_POST["estoque_$tamanho"] > 0) {
                $sql_est = "INSERT INTO estoque (produto_id, tamanho, quantidade) VALUES (?, ?, ?)";
                $stmt_est = $pdo->prepare($sql_est);
                $stmt_est->execute([$produto_id, $tamanho, $_POST["estoque_$tamanho"]]);
            }
        }
        
        // Confirma transação
        $pdo->commit();
        
        echo "<script>alert('Produto cadastrado com sucesso!'); window.location.href='listar_produtos.php';</script>";
        
    } catch (Exception $e) {
        // Desfaz transação
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        
        // Remove imagens já salvas em caso de erro
        foreach ($imagens as $caminho) {
            $fullPath = $uploadDir . basename($caminho);
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
        
        echo "<script>alert('Erro: " . addslashes($e->getMessage()) . "');</script>";
        echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>";
        echo "Erro: " . htmlspecialchars($e->getMessage());
        echo "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Camiseta</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow">
        <h1 class="text-2xl font-bold mb-6">Cadastrar Nova Camiseta</h1>
        
        <!-- Formulário com debug adicional -->
        <form method="POST" enctype="multipart/form-data">
            <!-- Campos do produto -->
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Nome da Camiseta</label>
                <input type="text" name="nome" class="w-full p-2 border rounded" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Descrição</label>
                <textarea name="descricao" class="w-full p-2 border rounded h-24"></textarea>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 mb-2">Preço (R$)</label>
                    <input type="number" step="0.01" name="preco" class="w-full p-2 border rounded" required>
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">Tags (separadas por vírgula)</label>
                    <input type="text" name="tags" class="w-full p-2 border rounded" placeholder="Ex: Naruto, Anime, Uzumaki">
                </div>
            </div>
            
            <!-- Seção de upload de imagens com melhor validação -->
            <div class="mb-6">
                <label class="block text-gray-700 mb-2">Imagens do Produto</label>
                <p class="text-sm text-gray-600 mb-2">Formatos aceitos: JPEG, PNG, WebP | Tamanho máximo: 2MB por imagem</p>
                <div id="imagens-container">
                    <input type="file" name="imagens[]" accept="image/jpeg, image/png, image/webp" class="w-full p-2 border rounded mb-2" onchange="validarImagem(this)">
                </div>
                <button type="button" onclick="adicionarCampoImagem()" class="bg-gray-200 px-4 py-2 rounded mt-2">
                    + Adicionar outra imagem
                </button>
            </div>
            
            <!-- Seção de estoque -->
            <div class="mb-6">
                <h3 class="font-bold mb-3">Estoque por Tamanho</h3>
                <div class="grid grid-cols-3 gap-4">
                    <?php
                    $tamanhos = ['PP', 'P', 'M', 'G', 'GG', 'XG'];
                    foreach ($tamanhos as $tamanho) {
                        echo '
                        <div>
                            <label class="block text-gray-700 mb-1">Tamanho ' . $tamanho . '</label>
                            <input type="number" name="estoque_' . $tamanho . '" class="w-full p-2 border rounded" value="0" min="0">
                        </div>';
                    }
                    ?>
                </div>
            </div>
            
            <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700">
                Cadastrar Produto
            </button>
        </form>
        
        <!-- Debug: mostrar informações do PHP -->
        <?php if (isset($_POST['nome'])): ?>
        <div class="mt-6 p-4 bg-blue-100 rounded">
            <h3 class="font-bold">Debug Info:</h3>
            <p>Upload máximo PHP: <?= ini_get('upload_max_filesize') ?></p>
            <p>Post máximo PHP: <?= ini_get('post_max_size') ?></p>
            <p>Diretório de upload existe: <?= is_dir($uploadDir) ? 'Sim' : 'Não' ?></p>
            <p>Diretório tem permissão de escrita: <?= is_writable(dirname($uploadDir)) ? 'Sim' : 'Não' ?></p>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function adicionarCampoImagem() {
            const container = document.getElementById('imagens-container');
            const newInput = document.createElement('input');
            newInput.type = 'file';
            newInput.name = 'imagens[]';
            newInput.accept = 'image/jpeg, image/png, image/webp';
            newInput.className = 'w-full p-2 border rounded mb-2';
            newInput.onchange = function() { validarImagem(this); };
            container.appendChild(newInput);
        }
        
        function validarImagem(input) {
            const file = input.files[0];
            if (file) {
                // Validar tamanho (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Arquivo muito grande! Máximo 2MB.');
                    input.value = '';
                    return;
                }
                
                // Validar tipo
                const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Tipo de arquivo não permitido! Use JPEG, PNG ou WebP.');
                    input.value = '';
                    return;
                }
                
                console.log('Arquivo válido:', file.name, file.type, file.size);
            }
        }
        
    </script>
</body>
</html>