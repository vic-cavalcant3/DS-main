<?php
require 'conexao.php';

// Verificar se foi passado um ID de produto
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$produto_id = (int)$_GET['id'];

// Buscar dados do produto
$sql = "
SELECT 
    p.*,
    c.nome AS categoria_nome,
    a.nome AS anime_nome
FROM produtos p
LEFT JOIN categorias c ON p.categoria_id = c.id
LEFT JOIN animes a ON p.anime_id = a.id
WHERE p.id = ? AND p.ativo = 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$produto_id]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

// Se produto não encontrado, redirecionar
if (!$produto) {
    header('Location: index.php');
    exit;
}

// Buscar todas as imagens do produto, organizadas por cor
$sql_imagens = "
    SELECT url_imagem, cor, ordem
    FROM imagens
    WHERE produto_id = ?
    ORDER BY cor, ordem
";
$stmt_imagens = $pdo->prepare($sql_imagens);
$stmt_imagens->execute([$produto_id]);
$imagens = $stmt_imagens->fetchAll(PDO::FETCH_ASSOC);

// Agrupar imagens por cor
$imagens_por_cor = [];
foreach ($imagens as $img) {
    $cor = $img['cor'] ?? 'default';
    $imagens_por_cor[$cor][] = $img['url_imagem'];
}

// Definir imagem principal
$primeira_imagem_src = null;
if (!empty($imagens_por_cor)) {
    $primeira_cor = array_key_first($imagens_por_cor);
    $primeira_imagem_src = $imagens_por_cor[$primeira_cor][0] ?? null;
}

// Buscar produtos relacionados (mesma categoria)
$sql_relacionados = "
SELECT 
    p.*,
    c.nome AS categoria_nome,
    a.nome AS anime_nome,
    (
        SELECT url_imagem
        FROM imagens
        WHERE produto_id = p.id
        ORDER BY ordem ASC
        LIMIT 1
    ) AS primeira_imagem
FROM produtos p
LEFT JOIN categorias c ON p.categoria_id = c.id
LEFT JOIN animes a ON p.anime_id = a.id
WHERE p.categoria_id = ? AND p.id != ? AND p.ativo = 1
ORDER BY p.vendas DESC
LIMIT 4
";

$stmt_relacionados = $pdo->prepare($sql_relacionados);
$stmt_relacionados->execute([$produto['categoria_id'], $produto_id]);
$produtos_relacionados = $stmt_relacionados->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($produto['nome']) ?> | Flamma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Bangers&family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .product-image-main {
            transition: all 0.3s ease;
        }
        
        .thumbnail {
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .thumbnail:hover {
            transform: scale(1.05);
            border-color: #ef4444;
        }
        
        .thumbnail.active {
            border-color: #ef4444;
            transform: scale(1.05);
        }
        
        .color-option {
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .color-option:hover {
            transform: scale(1.1);
        }
        
        .color-option.selected {
            border: 3px solid #ef4444 !important;
            transform: scale(1.15);
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.3);
        }
        
        .size-option {
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .size-option:hover {
            border-color: #ef4444;
            background-color: rgba(239, 68, 68, 0.1);
        }
        
        .size-option.selected {
            border-color: #ef4444;
            background-color: #ef4444;
            color: white;
        }
        
        .zoom-container {
            position: relative;
            overflow: hidden;
        }
        
        .zoom-container img {
            transition: transform 0.3s ease;
        }
        
        .zoom-container:hover img {
            transform: scale(1.1);
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Navigation (mesmo da página inicial) -->
    <nav class="bg-black sticky top-0 z-50 shadow-md" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <!-- Mobile menu button -->
                <div class="flex items-center md:hidden">
                    <button id="mobile-menu-button" class="text-white hover:text-red-500">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>

                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center justify-center">
                    <a href="index.php" class="text-2xl font-bold text-gray-900 hover:opacity-90 transition-opacity">
                        <img src="./client/src/Flamma-logo.png" alt="Flamma Index" class="h-10 md:h-12 w-auto">
                    </a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-6">
                    <a href="client/pages/produtos.php" class="text-white hover:text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Produtos</a>
                    <a href="client/pages/masculino.php" class="text-white hover:text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Masculino</a>
                    <a href="client/pages/feminino.php" class="text-white hover:text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Feminino</a>
                    <a href="client/pages/infantil.php" class="text-white hover:text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Infantil</a>
                    <a href="client/pages/ajuda.php" class="text-white hover:text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Central de Ajuda</a>
                </div>

                <!-- Right Icons -->
                <div class="flex items-center space-x-6">
                    <button onclick="window.location.href='client/pages/usuario.php'" class="text-white hover:text-red-500 transition-colors duration-200">
                        <i class="far fa-user text-xl"></i>
                    </button>
                    
                    <button id="cart-button" class="text-white hover:text-red-500 transition-colors duration-200 relative">
                        <i class="fas fa-shopping-bag text-xl"></i>
                        <span id="cart-count" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">0</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-black border-t border-gray-800">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="client/pages/produtos.php" class="block px-3 py-3 text-base font-medium text-white hover:bg-gray-900">Produtos</a>
                <a href="client/pages/masculino.php" class="block px-3 py-3 text-base font-medium text-white hover:bg-gray-900">Masculino</a>
                <a href="client/pages/feminino.php" class="block px-3 py-3 text-base font-medium text-white hover:bg-gray-900">Feminino</a>
                <a href="client/pages/infantil.php" class="block px-3 py-3 text-base font-medium text-white hover:bg-gray-900">Infantil</a>
                <a href="client/pages/ajuda.php" class="block px-3 py-3 text-base font-medium text-white hover:bg-gray-900">Central de Ajuda</a>
            </div>
        </div>
    </nav>

    <!-- Breadcrumb -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-sm">
                    <li><a href="index.php" class="text-gray-500 hover:text-red-500">Início</a></li>
                    <li><span class="text-gray-400">/</span></li>
                    <li><a href="client/pages/produtos.php" class="text-gray-500 hover:text-red-500">Produtos</a></li>
                    <li><span class="text-gray-400">/</span></li>
                    <?php if ($produto['categoria_nome']): ?>
                        <li><span class="text-gray-500"><?= htmlspecialchars($produto['categoria_nome']) ?></span></li>
                        <li><span class="text-gray-400">/</span></li>
                    <?php endif; ?>
                    <li><span class="text-gray-900 font-medium"><?= htmlspecialchars($produto['nome']) ?></span></li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Product Details -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="lg:grid lg:grid-cols-2 lg:gap-12">
            <!-- Product Images -->
            <div class="mb-8 lg:mb-0">
                <!-- Main Image -->
                <div class="mb-4">
                    <?php
                    $src = '/ds-main/client/src/no-image.png';
                    if (!empty($primeira_imagem_src)) {
                        $img = $primeira_imagem_src;
                        if (strpos($img, 'admin/src/uploads/') !== false) {
                            $src = '/ds-main/' . $img;
                        } else {
                            $src = '/ds-main/admin/src/uploads/' . basename($img);
                        }
                    }
                    ?>
                    <div class="zoom-container aspect-square bg-gray-100 rounded-lg overflow-hidden">
                        <img id="main-product-image" 
                             src="<?= htmlspecialchars($src) ?>" 
                             alt="<?= htmlspecialchars($produto['nome']) ?>"
                             class="w-full h-full object-cover"
                             onerror="this.src='/ds-main/client/src/no-image.png'">
                    </div>
                </div>

                <!-- Thumbnails -->
                <?php if (!empty($imagens_por_cor)): ?>
                    <div class="grid grid-cols-4 gap-3">
                        <?php 
                        $thumbnail_index = 0;
                        foreach ($imagens_por_cor as $cor => $imgs): 
                            foreach ($imgs as $img_url): 
                                $thumb_src = '/ds-main/client/src/no-image.png';
                                if (!empty($img_url)) {
                                    if (strpos($img_url, 'admin/src/uploads/') !== false) {
                                        $thumb_src = '/ds-main/' . $img_url;
                                    } else {
                                        $thumb_src = '/ds-main/admin/src/uploads/' . basename($img_url);
                                    }
                                }
                        ?>
                            <div class="thumbnail aspect-square bg-gray-100 rounded-lg overflow-hidden border-2 border-transparent <?= $thumbnail_index === 0 ? 'active' : '' ?>"
                                 data-image="<?= htmlspecialchars($thumb_src) ?>">
                                <img src="<?= htmlspecialchars($thumb_src) ?>" 
                                     alt="<?= htmlspecialchars($produto['nome']) ?>"
                                     class="w-full h-full object-cover"
                                     onerror="this.src='/ds-main/client/src/no-image.png'">
                            </div>
                        <?php 
                            $thumbnail_index++;
                            endforeach; 
                        endforeach; 
                        ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div>
                <!-- Title and Price -->
                <div class="mb-6">
                    <?php if ($produto['anime_nome']): ?>
                        <p class="text-red-500 font-medium text-sm mb-2"><?= htmlspecialchars($produto['anime_nome']) ?></p>
                    <?php endif; ?>
                    <h1 class="text-3xl font-bold text-gray-900 mb-4"><?= htmlspecialchars($produto['nome']) ?></h1>
                    
                    <div class="flex items-baseline space-x-2 mb-4">
                        <?php if (!empty($produto['preco_original']) && $produto['preco_original'] > $produto['preco']): ?>
                            <span class="text-2xl text-gray-400 line-through">
                                R$<?= number_format($produto['preco_original'], 2, ',', '.') ?>
                            </span>
                            <span class="text-sm bg-red-100 text-red-600 px-2 py-1 rounded">
                                <?= round((($produto['preco_original'] - $produto['preco']) / $produto['preco_original']) * 100) ?>% OFF
                            </span>
                        <?php endif; ?>
                        <span class="text-3xl font-bold text-gray-900">
                            R$<?= number_format($produto['preco'], 2, ',', '.') ?>
                        </span>
                    </div>
                    
                    <p class="text-gray-600 leading-relaxed"><?= htmlspecialchars($produto['descricao']) ?></p>
                </div>

                <!-- Colors -->
                <?php if (!empty($imagens_por_cor) && count($imagens_por_cor) > 1): ?>
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">Cores Disponíveis</h3>
                        <div class="flex space-x-3">
                            <?php 
                            $color_index = 0;
                            foreach ($imagens_por_cor as $cor => $imgs): 
                                $color_class = '';
                                switch(strtolower($cor)) {
                                    case 'preto':
                                    case 'black':
                                        $color_class = 'bg-black';
                                        break;
                                    case 'branco':
                                    case 'white':
                                        $color_class = 'bg-white border-2 border-gray-300';
                                        break;
                                    case 'azul':
                                    case 'blue':
                                        $color_class = 'bg-blue-500';
                                        break;
                                    case 'vermelho':
                                    case 'red':
                                        $color_class = 'bg-red-500';
                                        break;
                                    case 'verde':
                                    case 'green':
                                        $color_class = 'bg-green-500';
                                        break;
                                    default:
                                        $color_class = 'bg-gray-400';
                                }
                            ?>
                                <div class="color-option w-8 h-8 rounded-full <?= $color_class ?> border-2 border-transparent <?= $color_index === 0 ? 'selected' : '' ?>"
                                     data-color="<?= htmlspecialchars($cor) ?>"
                                     title="<?= htmlspecialchars(ucfirst($cor)) ?>">
                                </div>
                            <?php 
                            $color_index++;
                            endforeach; 
                            ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Sizes -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Tamanho</h3>
                    <div class="grid grid-cols-4 gap-3">
                        <?php
                        $tamanhos = ['P', 'M', 'G', 'GG'];
                        foreach ($tamanhos as $index => $tamanho):
                        ?>
                            <button class="size-option border border-gray-300 py-3 px-4 text-center font-medium rounded-md hover:border-red-500 transition-all duration-200 <?= $index === 1 ? 'selected' : '' ?>"
                                    data-size="<?= $tamanho ?>">
                                <?= $tamanho ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">
                        <a href="#" class="text-red-500 hover:text-red-600">Guia de tamanhos</a>
                    </p>
                </div>

                <!-- Quantity and Add to Cart -->
                <div class="mb-8">
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="flex items-center border border-gray-300 rounded-md">
                            <button id="decrease-qty" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                                <i class="fas fa-minus"></i>
                            </button>
                            <span id="quantity" class="px-4 py-2 font-medium">1</span>
                            <button id="increase-qty" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <span class="text-sm text-gray-500">Em estoque</span>
                    </div>
                    
                    <button id="add-to-cart-btn" 
                            class="w-full bg-red-600 text-white py-4 rounded-md font-medium hover:bg-red-700 transition duration-300 mb-3"
                            data-id="<?= $produto['id'] ?>"
                            data-name="<?= htmlspecialchars($produto['nome']) ?>"
                            data-price="<?= $produto['preco'] ?>"
                            data-image="<?= htmlspecialchars($src) ?>">
                        <i class="fas fa-shopping-bag mr-2"></i>
                        Adicionar ao Carrinho
                    </button>
                    
                    <button class="w-full border border-gray-300 py-4 rounded-md font-medium hover:bg-gray-50 transition duration-300">
                        <i class="far fa-heart mr-2"></i>
                        Adicionar aos Favoritos
                    </button>
                </div>

                <!-- Product Details -->
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Detalhes do Produto</h3>
                    <ul class="text-sm text-gray-600 space-y-2">
                        <li><strong>Categoria:</strong> <?= htmlspecialchars($produto['categoria_nome']) ?></li>
                        <?php if ($produto['anime_nome']): ?>
                            <li><strong>Anime:</strong> <?= htmlspecialchars($produto['anime_nome']) ?></li>
                        <?php endif; ?>
                        <li><strong>Material:</strong> 100% Algodão</li>
                        <li><strong>Cuidados:</strong> Lavar à mão, secar à sombra</li>
                        <li><strong>Origem:</strong> Nacional</li>
                    </ul>
                </div>

                <!-- Shipping -->
                <div class="border-t border-gray-200 pt-6 mt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Calcular Frete</h3>
                    <div class="flex space-x-3">
                        <input type="text" placeholder="Digite seu CEP" class="flex-1 border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <button class="bg-gray-100 hover:bg-gray-200 px-6 py-2 rounded-md font-medium transition duration-300">
                            Calcular
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($produtos_relacionados)): ?>
        <section class="py-12 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-8">Produtos Relacionados</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <?php foreach ($produtos_relacionados as $produto_rel): ?>
                        <div class="group">
                            <a href="produto.php?id=<?= $produto_rel['id'] ?>" class="block">
                                <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden mb-4">
                                    <?php
                                    $rel_src = '/ds-main/client/src/no-image.png';
                                    if (!empty($produto_rel['primeira_imagem'])) {
                                        $img = $produto_rel['primeira_imagem'];
                                        if (strpos($img, 'admin/src/uploads/') !== false) {
                                            $rel_src = '/ds-main/' . $img;
                                        } else {
                                            $rel_src = '/ds-main/admin/src/uploads/' . basename($img);
                                        }
                                    }
                                    ?>
                                    <img src="<?= htmlspecialchars($rel_src) ?>"
                                         alt="<?= htmlspecialchars($produto_rel['nome']) ?>"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                         onerror="this.src='/ds-main/client/src/no-image.png'">
                                </div>
                                <h3 class="font-medium text-gray-900 mb-1"><?= htmlspecialchars($produto_rel['nome']) ?></h3>
                                <p class="text-gray-500 text-sm mb-2"><?= htmlspecialchars($produto_rel['anime_nome']) ?></p>
                                <p class="font-bold text-gray-900">R$<?= number_format($produto_rel['preco'], 2, ',', '.') ?></p>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Shopping Cart Sidebar (mesmo da página inicial) -->
    <div id="cart-sidebar" class="fixed inset-y-0 right-0 w-full md:w-96 bg-white shadow-xl transform translate-x-full transition-transform duration-300 ease-in-out z-50 overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold">Seu Carrinho</h2>
                <button id="close-cart" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="cart-items" class="space-y-4">
                <!-- Cart items will be added here dynamically -->
            </div>
            <div class="mt-6 border-t border-gray-200 pt-4">
                <div class="flex justify-between text-lg font-semibold mb-4">
                    <span>Total:</span>
                    <span id="cart-total">R$0,00</span>
                </div>
                <button class="w-full bg-red-600 text-white py-3 rounded-md hover:bg-red-700 transition duration-300">
                    Finalizar Compra
                </button>
            </div>
        </div>
    </div>

    <!-- Data for JavaScript -->
    <script type="application/json" id="product-images-data">
        <?= json_encode($imagens_por_cor) ?>
    </script>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });

        // Image gallery
        document.querySelectorAll('.thumbnail').forEach(thumb => {
            thumb.addEventListener('click', function() {
                const mainImage = document.getElementById('main-product-image');
                const newSrc = this.dataset.image;
                
                // Remove active class from all thumbnails
                document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
                // Add active class to clicked thumbnail
                this.classList.add('active');
                
                // Change main image
                mainImage.src = newSrc;
            });
        });

        // Color selection
        const productImagesData = JSON.parse(document.getElementById('product-images-data').textContent);
        
        document.querySelectorAll('.color-option').forEach(colorOption => {
            colorOption.addEventListener('click', function() {
                const selectedColor = this.dataset.color;
                
                // Remove selection from all colors
                document.querySelectorAll('.color-option').forEach(option => {
                    option.classList.remove('selected');
                });
                
                // Add selection to clicked color
                this.classList.add('selected');
                
                // Update images if available
                if (productImagesData[selectedColor] && productImagesData[selectedColor].length > 0) {
                    const mainImage = document.getElementById('main-product-image');
                    let newSrc = productImagesData[selectedColor][0];
                    
                    // Fix image path
                    if (newSrc.includes('admin/src/uploads/')) {
                        newSrc = '/ds-main/' + newSrc;
                    } else {
                        newSrc = '/ds-main/admin/src/uploads/' + newSrc.split('/').pop();
                    }
                    
                    mainImage.src = newSrc;
                    
                    // Update thumbnails
                    const thumbnailContainer = document.querySelector('.grid.grid-cols-4');
                    thumbnailContainer.innerHTML = '';
                    
                    productImagesData[selectedColor].forEach((imgUrl, index) => {
                        let thumbSrc = imgUrl;
                        if (imgUrl.includes('admin/src/uploads/')) {
                            thumbSrc = '/ds-main/' + imgUrl;
                        } else {
                            thumbSrc = '/ds-main/admin/src/uploads/' + imgUrl.split('/').pop();
                        }
                        
                        const thumbDiv = document.createElement('div');
                        thumbDiv.className = `thumbnail aspect-square bg-gray-100 rounded-lg overflow-hidden border-2 border-transparent ${index === 0 ? 'active' : ''}`;
                        thumbDiv.dataset.image = thumbSrc;
                        thumbDiv.innerHTML = `<img src="${thumbSrc}" alt="Produto" class="w-full h-full object-cover" onerror="this.src='/ds-main/client/src/no-image.png'">`;
                        
                        thumbDiv.addEventListener('click', function() {
                            document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
                            this.classList.add('active');
                            document.getElementById('main-product-image').src = this.dataset.image;
                        });
                        
                        thumbnailContainer.appendChild(thumbDiv);
                    });
                }
            });
        });

        // Size selection
        document.querySelectorAll('.size-option').forEach(sizeOption => {
            sizeOption.addEventListener('click', function() {
                document.querySelectorAll('.size-option').forEach(option => {
                    option.classList.remove('selected');
                });
                this.classList.add('selected');
            });
        });

        // Quantity controls
        let quantity = 1;
        const quantitySpan = document.getElementById('quantity');
        const decreaseBtn = document.getElementById('decrease-qty');
        const increaseBtn = document.getElementById('increase-qty');

        decreaseBtn.addEventListener('click', function() {
            if (quantity > 1) {
                quantity--;
                quantitySpan.textContent = quantity;
            }
        });

        increaseBtn.addEventListener('click', function() {
            quantity++;
            quantitySpan.textContent = quantity;
        });

        // Cart functionality
        const cartButton = document.getElementById('cart-button');
        const closeCart = document.getElementById('close-cart');
        const cartSidebar = document.getElementById('cart-sidebar');
        const cartItems = document.getElementById('cart-items');
        const cartTotal = document.getElementById('cart-total');
        const cartCount = document.getElementById('cart-count');
        
        // Usar variáveis em memória em vez de localStorage
        let cart = [];
        
        function updateCartDisplay() {
            cartItems.innerHTML = '';
            let total = 0;
            
            if (cart.length === 0) {
                cartItems.innerHTML = '<p class="text-gray-500 text-center py-8">Seu carrinho está vazio</p>';
            } else {
                cart.forEach((item, index) => {
                    total += item.price * item.quantity;
                    
                    const cartItem = document.createElement('div');
                    cartItem.className = 'flex items-center space-x-4 border-b border-gray-200 pb-4';
                    cartItem.innerHTML = `
                        <img src="${item.image}" alt="${item.name}" class="w-16 h-16 object-cover rounded">
                        <div class="flex-1">
                            <h4 class="text-gray-900 font-medium">${item.name}</h4>
                            <p class="text-gray-500 text-sm">R${item.price.toFixed(2).replace('.', ',')}</p>
                            <p class="text-gray-400 text-xs">${item.size || 'M'} | ${item.color || 'Padrão'}</p>
                            <div class="flex items-center mt-2 space-x-2">
                                <button class="decrease-qty bg-gray-200 px-2 py-1 rounded text-sm" data-index="${index}">-</button>
                                <span class="text-gray-700">${item.quantity}</span>
                                <button class="increase-qty bg-gray-200 px-2 py-1 rounded text-sm" data-index="${index}">+</button>
                            </div>
                        </div>
                        <button class="remove-item text-red-500 hover:text-red-700" data-index="${index}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    `;
                    cartItems.appendChild(cartItem);
                });
            }

            cartTotal.textContent = `R${total.toFixed(2).replace('.', ',')}`;
            cartCount.textContent = cart.reduce((acc, item) => acc + item.quantity, 0);

            // Attach events to new buttons
            document.querySelectorAll('.increase-qty').forEach(btn => {
                btn.addEventListener('click', function() {
                    const idx = this.dataset.index;
                    cart[idx].quantity++;
                    updateCartDisplay();
                });
            });

            document.querySelectorAll('.decrease-qty').forEach(btn => {
                btn.addEventListener('click', function() {
                    const idx = this.dataset.index;
                    if (cart[idx].quantity > 1) {
                        cart[idx].quantity--;
                    } else {
                        cart.splice(idx, 1);
                    }
                    updateCartDisplay();
                });
            });

            document.querySelectorAll('.remove-item').forEach(btn => {
                btn.addEventListener('click', function() {
                    const idx = this.dataset.index;
                    cart.splice(idx, 1);
                    updateCartDisplay();
                });
            });
        }

        // Open/Close cart
        cartButton.addEventListener('click', () => {
            cartSidebar.classList.remove('translate-x-full');
        });

        closeCart.addEventListener('click', () => {
            cartSidebar.classList.add('translate-x-full');
        });

        // Add to cart functionality
        document.getElementById('add-to-cart-btn').addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const price = parseFloat(this.dataset.price);
            const image = this.dataset.image;
            
            // Get selected options
            const selectedSize = document.querySelector('.size-option.selected')?.dataset.size || 'M';
            const selectedColor = document.querySelector('.color-option.selected')?.dataset.color || 'Padrão';
            
            // Create unique item key based on product + size + color
            const itemKey = `${id}-${selectedSize}-${selectedColor}`;
            
            // Check if item already exists in cart
            const existingIndex = cart.findIndex(item => item.key === itemKey);
            
            if (existingIndex >= 0) {
                cart[existingIndex].quantity += quantity;
            } else {
                cart.push({
                    key: itemKey,
                    id,
                    name,
                    price,
                    image,
                    size: selectedSize,
                    color: selectedColor,
                    quantity: quantity
                });
            }

            updateCartDisplay();
            // Open cart automatically
            cartSidebar.classList.remove('translate-x-full');
            
            // Reset quantity to 1
            quantity = 1;
            quantitySpan.textContent = quantity;
            
            // Show success message
            const btn = this;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check mr-2"></i>Adicionado!';
            btn.classList.add('bg-green-600');
            btn.classList.remove('bg-red-600');
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.remove('bg-green-600');
                btn.classList.add('bg-red-600');
            }, 2000);
        });

        // Initialize cart display
        updateCartDisplay();

        // Close cart when clicking outside
        document.addEventListener('click', function(e) {
            if (!cartSidebar.contains(e.target) && !cartButton.contains(e.target)) {
                cartSidebar.classList.add('translate-x-full');
            }
        });

        // Prevent cart from closing when clicking inside
        cartSidebar.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    </script>
</body>
</html>