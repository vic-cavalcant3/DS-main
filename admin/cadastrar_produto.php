<?php
require 'conexao.php';

// Configurações de upload
$uploadDir = '../client/img/produtos/'; // Pasta onde as imagens serão salvas
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
        // Valida e move as imagens enviadas
        if (!empty($_FILES['imagens']['name'][0])) {
            foreach ($_FILES['imagens']['tmp_name'] as $key => $tmpName) {
                $fileType = $_FILES['imagens']['type'][$key];
                $fileSize = $_FILES['imagens']['size'][$key];
                
                // Validação
                if (!in_array($fileType, $allowedTypes)) {
                    throw new Exception("Tipo de arquivo não permitido: " . $_FILES['imagens']['name'][$key]);
                }
                
                if ($fileSize > $maxSize) {
                    throw new Exception("Arquivo muito grande: " . $_FILES['imagens']['name'][$key]);
                }
                
                // Gera nome único para o arquivo
                $ext = pathinfo($_FILES['imagens']['name'][$key], PATHINFO_EXTENSION);
                $fileName = uniqid() . '.' . $ext;
                $destino = $uploadDir . $fileName;
                
                if (move_uploaded_file($tmpName, $destino)) {
                    $imagens[] = 'img/produtos/' . $fileName; // Caminho relativo
                }
            }
        }

        // Cadastra o produto
        $sql = "INSERT INTO produtos (nome, descricao, preco, tags) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $descricao, $preco, $tags]);
        $produto_id = $pdo->lastInsertId();
        
        // Cadastra as imagens no banco
        foreach ($imagens as $ordem => $caminho) {
            $sql_img = "INSERT INTO imagens (produto_id, url_imagem, ordem) VALUES (?, ?, ?)";
            $stmt_img = $pdo->prepare($sql_img);
            $stmt_img->execute([$produto_id, $caminho, $ordem + 1]);
        }
        
        // Cadastra estoque (mantido igual)
        $tamanhos = ['PP', 'P', 'M', 'G', 'GG', 'XG'];
        foreach ($tamanhos as $tamanho) {
            if (!empty($_POST["estoque_$tamanho"])) {
                $sql_est = "INSERT INTO estoque (produto_id, tamanho, quantidade) VALUES (?, ?, ?)";
                $stmt_est = $pdo->prepare($sql_est);
                $stmt_est->execute([$produto_id, $tamanho, $_POST["estoque_$tamanho"]]);
            }
        }
        
        echo "<script>alert('Produto cadastrado com sucesso!'); window.location.href='listar_produtos.php';</script>";
    } catch (Exception $e) {
        // Remove imagens já salvas em caso de erro
        foreach ($imagens as $caminho) {
            if (file_exists('../client/' . $caminho)) {
                unlink('../client/' . $caminho);
            }
        }
        die("Erro: " . $e->getMessage());
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
        <!-- ATENÇÃO: Adicione enctype para permitir upload -->
        <form method="POST" enctype="multipart/form-data">
            <!-- Campos do produto (nome, descrição, preço, tags) -->
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
            
            <!-- Seção de upload de imagens -->
            <div class="mb-6">
                <label class="block text-gray-700 mb-2">Imagens do Produto</label>
                <div id="imagens-container">
                    <input type="file" name="imagens[]" accept="image/jpeg, image/png, image/webp" class="w-full p-2 border rounded mb-2">
                </div>
                <button type="button" onclick="adicionarCampoImagem()" class="bg-gray-200 px-4 py-2 rounded mt-2">
                    + Adicionar outra imagem
                </button>
            </div>
            
            <!-- Seção de estoque (mantida igual) -->
            <div class="mb-6">
                <h3 class="font-bold mb-3">Estoque por Tamanho</h3>
                <div class="grid grid-cols-3 gap-4">
                    <?php
                    $tamanhos = ['PP', 'P', 'M', 'G', 'GG', 'XG'];
                    foreach ($tamanhos as $tamanho) {
                        echo '
                        <div>
                            <label class="block text-gray-700 mb-1">Tamanho ' . $tamanho . '</label>
                            <input type="number" name="estoque_' . $tamanho . '" class="w-full p-2 border rounded" value="0">
                        </div>';
                    }
                    ?>
                </div>
            </div>
            
            <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700">
                Cadastrar Produto
            </button>
        </form>
    </div>

    <script>
        function adicionarCampoImagem() {
            const container = document.getElementById('imagens-container');
            const newInput = document.createElement('input');
            newInput.type = 'file';
            newInput.name = 'imagens[]';
            newInput.accept = 'image/jpeg, image/png, image/webp';
            newInput.className = 'w-full p-2 border rounded mb-2';
            container.appendChild(newInput);
        }
    </script>
</body>
</html>