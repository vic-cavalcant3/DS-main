<?php
require 'conexao.php';

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
                    <a href="#" class="flex items-center text-red-400 p-2 rounded hover:bg-gray-800">
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
                            <a href="editar_produto.php?id=<?= $produto['id'] ?>" 
                               class="text-blue-600 hover:text-blue-800 px-3 py-1 border border-blue-300 rounded">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="excluir_produto.php?id=<?= $produto['id'] ?>" 
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

    <script>
        // Ativar menu responsivo
        document.querySelectorAll('.sidebar-link').forEach(link => {
            if (link.href === window.location.href) {
                link.classList.add('active');
            }
        });
    </script>
</body>
</html>