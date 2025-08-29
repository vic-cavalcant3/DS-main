<?php
require '../conexao.php';

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../pages/login.php");
    exit;
}

// Habilitar exibição de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Buscar categorias e animes para os selects
$categorias = [];
$animes = [];

try {
    $stmt = $pdo->query("SELECT * FROM categorias WHERE ativo = 1 ORDER BY nome");
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("SELECT * FROM animes WHERE ativo = 1 ORDER BY nome");
    $animes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>";
    echo "Erro ao carregar dados básicos: " . htmlspecialchars($e->getMessage());
    echo "</div>";
}

// Configurações de upload
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/ds-main/admin/src/uploads/';
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
$maxSize = 2 * 1024 * 1024; // 2MB

// Processa o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $preco = floatval($_POST['preco']);
    $preco_original = !empty($_POST['preco_original']) ? floatval($_POST['preco_original']) : null;
    $desconto = !empty($_POST['desconto']) ? intval($_POST['desconto']) : 0;
    $categoria_id = !empty($_POST['categoria_id']) ? intval($_POST['categoria_id']) : null;
    $anime_id = !empty($_POST['anime_id']) ? intval($_POST['anime_id']) : null;
    $cores = !empty($_POST['cores']) ? implode(',', $_POST['cores']) : null;
    $tags = trim($_POST['tags']);
    $todasImagens = [];

    // Validações básicas
    if (empty($nome)) {
        echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>";
        echo "Erro: Nome do produto é obrigatório";
        echo "</div>";
        exit;
    }
    
    if ($preco <= 0) {
        echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>";
        echo "Erro: Preço deve ser maior que zero";
        echo "</div>";
        exit;
    }

    try {
        // Verifica se o diretório existe, senão cria
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                throw new Exception("Não foi possível criar o diretório de upload: " . $uploadDir);
            }
        }

        // Processa imagens gerais (sem cor específica)
        if (!empty($_FILES['imagens']['name'][0])) {
            foreach ($_FILES['imagens']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['imagens']['error'][$key] === UPLOAD_ERR_OK) {
                    $fileType = $_FILES['imagens']['type'][$key];
                    $fileSize = $_FILES['imagens']['size'][$key];
                    $fileName = $_FILES['imagens']['name'][$key];
                    
                    if (in_array($fileType, $allowedTypes) && $fileSize <= $maxSize) {
                        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                        $uniqueFileName = uniqid() . '_default.' . $ext;
                        $destino = $uploadDir . $uniqueFileName;
                        
                        if (move_uploaded_file($tmpName, $destino)) {
                            $todasImagens[] = [
                                'caminho' => 'admin/src/uploads/' . $uniqueFileName, 
                                'cor' => 'default'
                            ];
                        }
                    }
                }
            }
        }

        // Processa imagens por cor
        $coresSelecionadas = !empty($_POST['cores']) ? $_POST['cores'] : [];
        foreach ($coresSelecionadas as $cor) {
            $campoImagem = "imagens_" . str_replace('#', '', $cor);
            
            if (!empty($_FILES[$campoImagem]['name'][0])) {
                foreach ($_FILES[$campoImagem]['tmp_name'] as $key => $tmpName) {
                    if ($_FILES[$campoImagem]['error'][$key] === UPLOAD_ERR_OK) {
                        $fileType = $_FILES[$campoImagem]['type'][$key];
                        $fileSize = $_FILES[$campoImagem]['size'][$key];
                        $fileName = $_FILES[$campoImagem]['name'][$key];
                        
                        if (in_array($fileType, $allowedTypes) && $fileSize <= $maxSize) {
                            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                            $uniqueFileName = uniqid() . '_' . $cor . '.' . $ext;
                            $destino = $uploadDir . $uniqueFileName;
                            
                            if (move_uploaded_file($tmpName, $destino)) {
                                $todasImagens[] = [
                                    'caminho' => 'admin/src/uploads/' . $uniqueFileName, 
                                    'cor' => $cor
                                ];
                            }
                        }
                    }
                }
            }
        }

        // Inicia transação
        $pdo->beginTransaction();
        
        // Define imagem principal (primeira imagem ou null)
        $imagem_principal = !empty($todasImagens) ? $todasImagens[0]['caminho'] : null;
        
        // Cadastra o produto
        $sql = "INSERT INTO produtos (nome, descricao, preco, preco_original, desconto, categoria_id, anime_id, cores, tags, status, ativo, imagem_principal) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'ativo', 1, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $nome, 
            $descricao, 
            $preco, 
            $preco_original, 
            $desconto, 
            $categoria_id, 
            $anime_id, 
            $cores, 
            $tags,
            $imagem_principal
        ]);
        
        $produto_id = $pdo->lastInsertId();
        
        if (!$produto_id || $produto_id == 0) {
            throw new Exception("Erro: ID do produto não foi gerado.");
        }

        // Cadastra as imagens no banco
        if (!empty($todasImagens)) {
            foreach ($todasImagens as $ordem => $dadosImg) {
                $sql_img = "INSERT INTO imagens (produto_id, url_imagem, ordem, cor) VALUES (?, ?, ?, ?)";
                $stmt_img = $pdo->prepare($sql_img);
                $stmt_img->execute([
                    $produto_id, 
                    $dadosImg['caminho'], 
                    $ordem + 1, 
                    $dadosImg['cor']
                ]);
            }
        }
        
        // Cadastra estoque
        $tamanhos = ['PP', 'P', 'M', 'G', 'GG', 'XG'];
        
        foreach ($tamanhos as $tamanho) {
            $campoEstoque = "estoque_$tamanho";
            if (!empty($_POST[$campoEstoque]) && $_POST[$campoEstoque] > 0) {
                $quantidade = intval($_POST[$campoEstoque]);
                $sql_est = "INSERT INTO estoque (produto_id, tamanho, quantidade) VALUES (?, ?, ?)";
                $stmt_est = $pdo->prepare($sql_est);
                $stmt_est->execute([$produto_id, $tamanho, $quantidade]);
            }
        }
        
        // Confirma transação
        $pdo->commit();
        
        echo "<script>
            alert('Produto cadastrado com sucesso! ID: $produto_id'); 
            window.location.href='listar_produtos.php';
        </script>";
        exit;
        
    } catch (Exception $e) {
        // Desfaz transação
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        
        // Remove imagens já salvas em caso de erro
        foreach ($todasImagens as $dadosImagem) {
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/ds-main/' . $dadosImagem['caminho'];
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
        
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
    <title>Cadastrar Produto - Flamma Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar {
            transition: all 0.3s;
            background-color: #111827;
        }
        .sidebar-link {
            transition: all 0.2s;
        }
        .sidebar-link:hover {
            background-color: #1f2937;
        }
        .sidebar-link.active {
            background-color: #1f2937;
            border-left: 4px solid #ef4444;
        }


    </style>
</head>
<body class="bg-gray-50 font-sans">
    <div class="flex h-screen">
        <!-- Menu Lateral -->
        <div class="sidebar w-64 fixed h-full text-white">
            <div class="p-4 border-b border-gray-700 flex items-center">
                <!-- <img src="/ds-main/client/src/Flamma-logo.png" alt="Logo" class="h-10 mr-3"> -->
                <h1 class="text-xl font-bold">Flamma Admin</h1>
            </div>
            
            <nav class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="cadastrar_produto.php" class="sidebar-link active flex items-center p-3 rounded">
                            <i class="fas fa-tshirt mr-3"></i>
                            Novo Produto
                        </a>
                    </li>
                   
                    <li>
                        <a href="listar_produtos.php" class="sidebar-link flex items-center p-3 rounded">
                            <i class="fas fa-shopping-cart mr-3"></i>
                            Produtos
                        </a>
                    </li>

                    <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-700">
                        <a href="../utils/logout.php" class="flex items-center text-red-400 p-2 rounded hover:bg-gray-800">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            Sair
                        </a>
                    </div>

                </ul>
            </nav>
        </div>

        <!-- Conteúdo Principal -->
        <div class="ml-64 flex-1 p-8 overflow-auto">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-gray-800">Cadastrar Novo Produto</h1>
                <a href="listar_produtos.php" 
                   class="text-gray-600 hover:text-gray-800 flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Voltar
                </a>
            </div>
            
            <!-- Mensagens de Erro -->
            <?php if (isset($e)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?= htmlspecialchars($e->getMessage()) ?>
                </div>
            <?php endif; ?>
            
            <!-- Formulário -->
<form action="cadastrar_produto.php" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-6">
                <!-- Seção Básica -->
                <div class="mb-8">
                    <h2 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">Informações Básicas</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 mb-2">Nome do Produto*</label>
                            <input type="text" name="nome" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Preço (R$)*</label>
                            <input type="number" step="0.01" name="preco" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Preço Original (R$)</label>
                            <input type="number" step="0.01" name="preco_original" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            <p class="text-sm text-gray-500 mt-1">Para produtos em promoção</p>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Desconto (%)</label>
                            <input type="number" name="desconto" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" min="0" max="100" value="0">
                        </div>
                        <?php if (!empty($categorias)): ?>
                        <div>
                            <label class="block text-gray-700 mb-2">Categoria</label>
                            <select name="categoria_id" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Selecione uma categoria</option>
                                <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= $categoria['id'] ?>"><?= htmlspecialchars($categoria['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                                 <div class="md:col-span-2">
                            <label class="block text-gray-700 mb-2">Tags</label>
                            <input type="text" name="tags" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="Ex: Anime, Naruto, Dragon Ball">
                                </div>
                        <?php endif; ?>
                        <?php if (!empty($animes)): ?>
                        <div>
                            <label class="block text-gray-700 mb-2">Anime</label>
                            <select name="anime_id" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Selecione um anime</option>
                                <?php foreach ($animes as $anime): ?>
                                <option value="<?= $anime['id'] ?>"><?= htmlspecialchars($anime['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 mb-2">Descrição</label>
                            <textarea name="descricao" rows="3" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"></textarea>
                        </div>
                        <!-- <div class="md:col-span-2">
                            <label class="block text-gray-700 mb-2">Cores Disponíveis</label>
                            <div class="flex flex-wrap gap-4">
                                <label class="flex items-center">
                                    <input type="checkbox" name="cores[]" value="preto" class="mr-2">
                                    <span class="w-6 h-6 bg-black rounded-full mr-2"></span> Preto
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="cores[]" value="branco" class="mr-2">
                                    <span class="w-6 h-6 bg-white border rounded-full mr-2"></span> Branco
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="cores[]" value="cinza" class="mr-2">
                                    <span class="w-6 h-6 bg-gray-500 rounded-full mr-2"></span> Cinza
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="cores[]" value="vermelho" class="mr-2">
                                    <span class="w-6 h-6 bg-red-500 rounded-full mr-2"></span> Vermelho
                                </label>
                            </div>
                        </div> -->

                
                        
                    </div>
                </div>
                
                <!-- Seção Imagens -->
                <div class="mb-8">
                    <div id="imagens-container">
                        <div class="file-upload "></div>
                    </div>
                </div>

        <div class="md:col-span-2">
        <h2 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">Imagens do Produto</h2>
            <div class="flex flex-wrap gap-4">
                <label class="flex items-center">
                    <input type="checkbox" name="cores[]" value="preto" class="mr-2 cor-checkbox">
                    <span class="w-6 h-6 bg-black rounded-full mr-2"></span> Preto
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="cores[]" value="branco" class="mr-2 cor-checkbox">
                    <span class="w-6 h-6 bg-white border rounded-full mr-2"></span> Branco
                </label>
                <input type="file" name="imagens[]" accept="image/jpeg, image/png, image/webp" class="hidden" id="file-input" multiple>
                <div id="preview-container" class="grid grid-cols-2 md:grid-cols-4 gap-5 mt-2"></div>
            </div>
        </div>


<div id="uploads-cores" class="mt-4 space-y-6"></div>

<div class="mb-10" ></div>
                
                <!-- Seção Estoque -->
                <div class="mb-8">
                    <h2 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">Estoque</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        <?php
                        $tamanhos = ['PP', 'P', 'M', 'G', 'GG', 'XG'];
                        foreach ($tamanhos as $tamanho): ?>
                        <div>
                            <label class="block text-gray-700 mb-1">Tamanho <?= $tamanho ?></label>
                            <input type="number" name="estoque_<?= $tamanho ?>" class="w-full p-3 border rounded-lg" value="0" min="0">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Botão Submit -->
                <div class="flex justify-end">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-lg font-semibold flex items-center">
                        <i class="fas fa-save mr-2"></i> Salvar Produto
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Gerenciamento de upload de imagens
        const fileInput = document.getElementById('file-input');
        const previewContainer = document.getElementById('preview-container');
        
        fileInput.addEventListener('change', function() {
            previewContainer.innerHTML = '';
            
            if (this.files) {
                Array.from(this.files).forEach(file => {
                    if (file.type.startsWith('image/') && file.size <= 2 * 1024 * 1024) {
                        const reader = new FileReader();
                        
                        reader.onload = function(e) {
                            const preview = document.createElement('div');
                            preview.className = 'relative';
                            preview.innerHTML = `
                                <img src="${e.target.result}" class="w-full h-32 object-cover rounded-lg">
                                <button type="button" class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            `;
                            preview.querySelector('button').addEventListener('click', () => {
                                preview.remove();
                                // Aqui você pode implementar a remoção do arquivo da lista
                            });
                            previewContainer.appendChild(preview);
                        }
                        
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
        
        // Drag and drop
        const uploadArea = document.querySelector('.file-upload');
        
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('border-red-400', 'bg-red-50');
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('border-red-400', 'bg-red-50');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('border-red-400', 'bg-red-50');
            fileInput.files = e.dataTransfer.files;
            const event = new Event('change');
            fileInput.dispatchEvent(event);
        });

        const uploadsContainer = document.getElementById('uploads-cores');
const corCheckboxes = document.querySelectorAll('.cor-checkbox');

corCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', () => {
        const cor = checkbox.value;
        const id = `upload-${cor}`;

        if (checkbox.checked) {
            // Cria a área de upload dessa cor
            const div = document.createElement('div');
            div.id = id;
            div.innerHTML = `
                <h3 class="font-semibold mb-2">Imagens - Cor ${cor.charAt(0).toUpperCase() + cor.slice(1)}</h3>
                <input type="file" name="imagens_${cor}[]" multiple 
                       accept="image/jpeg,image/png,image/webp" 
                       class="block w-full p-2 border rounded">
            `;
            uploadsContainer.appendChild(div);
        } else {
            // Remove a área de upload se desmarcar a cor
            const div = document.getElementById(id);
            if (div) div.remove();
        }
    });
});


        
    </script>
</body>
</html>