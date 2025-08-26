<?php
require '../conexao.php';

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

try {
    $sql = "SELECT produtos.*, 
                   (SELECT imagens.url_imagem 
                    FROM imagens 
                    WHERE imagens.produto_id = produtos.id 
                    ORDER BY imagens.ordem ASC, imagens.id ASC 
                    LIMIT 1) AS imagem
            FROM produtos
            ORDER BY produtos.data_cadastro DESC";

    $stmt = $pdo->query($sql);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro ao listar produtos: " . $e->getMessage());
}

// Buscar categorias e animes para os selects do modal
$categorias = [];
$animes = [];

try {
    $stmt = $pdo->query("SELECT * FROM categorias WHERE ativo = 1 ORDER BY nome");
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("SELECT * FROM animes WHERE ativo = 1 ORDER BY nome");
    $animes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Se as tabelas não existirem, continua sem erro
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Admin Flamma - Produtos</title>
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
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .tab-button {
            transition: all 0.3s;
        }
        .tab-button.active {
            background-color: #f3f4f6;
            border-color: #ef4444;
            color: #ef4444;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <div class="flex h-screen">
        <!-- Menu Lateral -->
        <div class="sidebar w-64 fixed h-full text-white">
            <div class="p-4 border-b border-gray-700 flex items-center">
                <h1 class="text-xl font-bold">Flamma Admin</h1>
            </div>
            
            <nav class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="cadastrar_produto.php" class="sidebar-link  flex items-center p-3 rounded">
                            <i class="fas fa-tshirt mr-3"></i>
                            Novo Produto
                        </a>
                    </li>
                   
                    <li>
                        <a href="listar_produtos.php" class="sidebar-link active flex items-center p-3 rounded">
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
                <h1 class="text-2xl font-bold text-gray-800">Gerenciar Produtos</h1>
                <a href="cadastrar_produto.php" 
                   class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded flex items-center">
                    <i class="fas fa-plus mr-2"></i> Novo Produto
                </a>
            </div>
            
            <!-- Mensagens de Status -->
            <?php if (isset($_GET['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <?= htmlspecialchars($_GET['success']) ?>
                </div>
            <?php endif; ?>
            
            <!-- Grid de Produtos -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($produtos as $produto): ?>
                <div class="product-card bg-white rounded-lg shadow-md overflow-hidden transition-all">
                    <?php if (!empty($produto['imagem'])): ?>
                        <?php 
                        $nomeImagem = basename($produto['imagem']);
                        $imagePath = '/ds-main/admin/src/uploads/' . $nomeImagem;
                        ?>
                        <img src="<?= $imagePath ?>" 
                             alt="<?= htmlspecialchars($produto['nome']) ?>" 
                             class="w-full h-64 object-cover"
                             onerror="this.onerror=null; this.src='https://via.placeholder.com/300x300?text=Imagem+Não+Encontrada'">
                    <?php else: ?>
                        <div class="w-full h-64 bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-tshirt text-4xl text-gray-400"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-bold text-lg text-gray-800"><?= htmlspecialchars($produto['nome']) ?></h3>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    <?php 
                                    $tags = explode(',', $produto['tags']);
                                    foreach ($tags as $tag): 
                                        if (!empty(trim($tag))):
                                    ?>
                                        <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded"><?= htmlspecialchars(trim($tag)) ?></span>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </div>
                            </div>
                            <span class="bg-red-100 text-red-800 text-sm font-semibold px-2 py-1 rounded">
                                R$ <?= number_format($produto['preco'], 2, ',', '.') ?>
                            </span>
                        </div>
                        
                        <div class="mt-4 flex justify-end space-x-2">
                            <button 
                                class="abrir-modal-editar text-blue-600 hover:text-blue-800 px-3 py-1 border border-blue-300 rounded"
                                data-id="<?= $produto['id'] ?>">
                                <i class="fas fa-edit"></i> Editar
                            </button>

                            <a href="../utils/excluir_produto.php?id=<?= $produto['id'] ?>" 
                               class="text-red-600 hover:text-red-800 px-3 py-1 border border-red-300 rounded"
                               onclick="return confirm('Tem certeza que deseja excluir este produto?')">
                                <i class="fas fa-trash"></i> Excluir
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Modal Editar Produto -->
    <div id="modal-editar" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto p-6 relative">
            
            <!-- Fechar -->
            <button onclick="document.getElementById('modal-editar').classList.add('hidden')" 
                class="absolute top-4 right-4 text-gray-600 hover:text-black text-xl font-bold">&times;</button>

            <h2 class="text-2xl font-bold text-gray-800 mb-6">Editar Produto</h2>

            <!-- Abas de navegação -->
            <div class="flex border-b mb-6">
                <button class="tab-button py-2 px-4 border-b-2 border-transparent active" data-tab="info-basicas">Informações Básicas</button>
                <button class="tab-button py-2 px-4 border-b-2 border-transparent" data-tab="estoque">Estoque</button>
                <button class="tab-button py-2 px-4 border-b-2 border-transparent" data-tab="imagens">Imagens</button>
            </div>

           <form id="form-editar" action="../utils/editar_produto.php" method="POST" class="space-y-6" enctype="multipart/form-data">   
                <input type="hidden" name="id" id="editar-id">

                <!-- Aba Informações Básicas -->
                <div id="tab-info-basicas" class="tab-content active">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Nome</label>
                            <input type="text" name="nome" id="editar-nome" class="w-full p-3 border rounded-lg" required>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Preço (R$)</label>
                            <input type="number" step="0.01" name="preco" id="editar-preco" class="w-full p-3 border rounded-lg" required>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Preço Original (R$)</label>
                            <input type="number" step="0.01" name="preco_original" id="editar-preco_original" class="w-full p-3 border rounded-lg">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Desconto (%)</label>
                            <input type="number" name="desconto" id="editar-desconto" class="w-full p-3 border rounded-lg" min="0" max="100">
                        </div>

                        <?php if (!empty($categorias)): ?>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Categoria</label>
                            <select name="categoria_id" id="editar-categoria_id" class="w-full p-3 border rounded-lg">
                                <option value="">Selecione uma categoria</option>
                                <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= $categoria['id'] ?>"><?= htmlspecialchars($categoria['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($animes)): ?>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Anime</label>
                            <select name="anime_id" id="editar-anime_id" class="w-full p-3 border rounded-lg">
                                <option value="">Selecione um anime</option>
                                <?php foreach ($animes as $anime): ?>
                                <option value="<?= $anime['id'] ?>"><?= htmlspecialchars($anime['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="md:col-span-2">
                            <label class="block text-gray-700 font-semibold mb-2">Descrição</label>
                            <textarea name="descricao" id="editar-descricao" rows="3" class="w-full p-3 border rounded-lg"></textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-gray-700 font-semibold mb-2">Cores Disponíveis</label>
                            <div class="flex flex-wrap gap-4" id="editar-cores-container">
                                <label class="flex items-center">
                                    <input type="checkbox" name="cores[]" value="preto" class="mr-2 editar-cor">
                                    <span class="w-6 h-6 bg-black rounded-full mr-2"></span> Preto
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="cores[]" value="branco" class="mr-2 editar-cor">
                                    <span class="w-6 h-6 bg-white border rounded-full mr-2"></span> Branco
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="cores[]" value="cinza" class="mr-2 editar-cor">
                                    <span class="w-6 h-6 bg-gray-500 rounded-full mr-2"></span> Cinza
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="cores[]" value="vermelho" class="mr-2 editar-cor">
                                    <span class="w-6 h-6 bg-red-500 rounded-full mr-2"></span> Vermelho
                                </label>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-gray-700 font-semibold mb-2">Tags</label>
                            <input type="text" name="tags" id="editar-tags" class="w-full p-3 border rounded-lg" placeholder="Ex: Anime, Naruto, Dragon Ball">
                            <p class="text-sm text-gray-500 mt-1">Separe as tags por vírgula</p>
                        </div>
                    </div>
                </div>

                <!-- Aba Estoque -->
                <div id="tab-estoque" class="tab-content">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        <?php
                        $tamanhos = ['PP', 'P', 'M', 'G', 'GG', 'XG'];
                        foreach ($tamanhos as $tamanho): ?>
                        <div>
                            <label class="block text-gray-700 mb-1">Tamanho <?= $tamanho ?></label>
                            <input type="number" name="estoque_<?= $tamanho ?>" id="editar-estoque_<?= $tamanho ?>" class="w-full p-3 border rounded-lg" value="0" min="0">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Aba Imagens -->
                <div id="tab-imagens" class="tab-content">
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Adicionar Novas Imagens</label>
                        <div class="file-upload mb-4 rounded-lg p-6 text-center border-2 border-dashed border-gray-300">
                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                            <p class="text-gray-600 mb-2">Arraste e solte imagens aqui ou clique para selecionar</p>
                            <input type="file" name="novas_imagens[]" accept="image/jpeg, image/png, image/webp" class="hidden" id="editar-file-input" multiple>
                            <button type="button" onclick="document.getElementById('editar-file-input').click()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg mt-2">
                                Selecionar Arquivos
                            </button>
                            <p class="text-xs text-gray-500 mt-3">Formatos aceitos: JPEG, PNG, WebP | Máx. 2MB cada</p>
                        </div>
                        <div id="editar-preview-container" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4"></div>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Imagens Atuais</label>
                        <div id="imagens-atuais-container" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <!-- As imagens atuais serão carregadas aqui via JavaScript -->
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t">
                    <button type="submit" class="px-6 py-3 bg-red-600 text-white rounded-lg shadow hover:bg-red-700">
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
// Sistema de abas
document.querySelectorAll('.tab-button').forEach(button => {
    button.addEventListener('click', () => {
        document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        button.classList.add('active');
        const tabId = button.getAttribute('data-tab');
        document.getElementById(`tab-${tabId}`).classList.add('active');
    });
});

// Preview de novas imagens
const editarFileInput = document.getElementById('editar-file-input');
const editarPreviewContainer = document.getElementById('editar-preview-container');

editarFileInput.addEventListener('change', function() {
    editarPreviewContainer.innerHTML = '';
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
                    preview.querySelector('button').addEventListener('click', () => preview.remove());
                    editarPreviewContainer.appendChild(preview);
                }
                reader.readAsDataURL(file);
            }
        });
    }
});

// Drag and drop
const editarUploadArea = document.querySelector('#tab-imagens .file-upload');
editarUploadArea.addEventListener('dragover', e => {
    e.preventDefault();
    editarUploadArea.classList.add('border-red-400', 'bg-red-50');
});
editarUploadArea.addEventListener('dragleave', () => {
    editarUploadArea.classList.remove('border-red-400', 'bg-red-50');
});
editarUploadArea.addEventListener('drop', e => {
    e.preventDefault();
    editarUploadArea.classList.remove('border-red-400', 'bg-red-50');
    editarFileInput.files = e.dataTransfer.files;
    editarFileInput.dispatchEvent(new Event('change'));
});

// Abrir modal e preencher campos
document.querySelectorAll('.abrir-modal-editar').forEach(btn => {
    btn.addEventListener('click', async () => {
        const id = btn.dataset.id;
        try {
            const response = await fetch(`../utils/editar_produto.php?id=${id}&json=1`);
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                throw new Error(`Resposta não é JSON: ${text.substring(0, 100)}...`);
            }
            const data = await response.json();
            if (data.error) {
                alert('Erro: ' + data.error);
                return;
            }

            // Campos básicos
            document.getElementById('editar-id').value = data.id;
            document.getElementById('editar-nome').value = data.nome || '';
            document.getElementById('editar-preco').value = data.preco || '';
            document.getElementById('editar-preco_original').value = data.preco_original || '';
            document.getElementById('editar-desconto').value = data.desconto || '';
            document.getElementById('editar-descricao').value = data.descricao || '';
            document.getElementById('editar-tags').value = data.tags || '';

            // Categoria e Anime
            if (data.categoria_id) document.getElementById('editar-categoria_id').value = data.categoria_id;
            if (data.anime_id) document.getElementById('editar-anime_id').value = data.anime_id;

            // Cores
            if (Array.isArray(data.cores)) {
                document.querySelectorAll('.editar-cor').forEach(chk => {
                    chk.checked = data.cores.includes(chk.value);
                });
            }

            // Estoque
            if (data.estoque) {
                Object.keys(data.estoque).forEach(tam => {
                    const input = document.getElementById(`editar-estoque_${tam}`);
                    if (input) input.value = data.estoque[tam];
                });
            }

            // Imagens atuais
            const imagensContainer = document.getElementById('imagens-atuais-container');
            imagensContainer.innerHTML = '';
            if (data.imagens && data.imagens.length > 0) {
                data.imagens.forEach(imagem => {
                    const nomeImagem = imagem.caminho ? basename(imagem.caminho) : imagem.url_imagem ? basename(imagem.url_imagem) : '';
                    const imagePath = '/ds-main/admin/src/uploads/' + nomeImagem;
                    const imgDiv = document.createElement('div');
                    imgDiv.className = 'relative';
                    imgDiv.innerHTML = `
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <img src="${imagePath}" alt="Imagem do produto" class="w-full h-32 object-cover"
                                 onerror="this.onerror=null; this.src='https://via.placeholder.com/200x200?text=Imagem+Não+Encontrada'">
                            <div class="absolute bottom-2 right-2 flex space-x-1">
                                <button type="button" class="bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center excluir-imagem" data-imagem-id="${imagem.id}">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>
                        </div>
                        <input type="hidden" name="imagens_atuais[]" value="${imagem.id}">
                    `;
                    imagensContainer.appendChild(imgDiv);
                });

                // Botão excluir imagens
                document.querySelectorAll('.excluir-imagem').forEach(button => {
                    button.addEventListener('click', function() {
                        const imagemId = this.getAttribute('data-imagem-id');
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'excluir_imagens[]';
                        input.value = imagemId;
                        document.getElementById('form-editar').appendChild(input);
                        this.closest('.relative').remove();
                    });
                });
            } else {
                imagensContainer.innerHTML = `
                    <div class="col-span-full bg-gray-200 rounded-lg flex items-center justify-center h-32">
                        <i class="fas fa-tshirt text-4xl text-gray-400"></i>
                    </div>
                    <p class="col-span-full text-gray-500 text-center">Nenhuma imagem encontrada para este produto.</p>
                `;
            }

            // Mostra modal
            document.getElementById('modal-editar').classList.remove('hidden');

        } catch (error) {
            console.error('Erro ao carregar dados do produto:', error);
            alert('Erro ao carregar dados do produto: ' + error.message);
        }
    });
});

// Extrair nome do arquivo
function basename(path) {
    return path.split('/').pop().split('\\').pop();
}

// Ativar menu responsivo
document.querySelectorAll('.sidebar-link').forEach(link => {
    if (link.href === window.location.href) link.classList.add('active');
});

        
    </script>
</body>
</html>