<?php
require 'conexao.php';

// 1. Buscar produtos ativos (máximo 4)
$sql = "
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
WHERE p.ativo = 1
ORDER BY p.vendas DESC, p.created_at DESC
LIMIT 4
";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Organizar produtos com imagens
$produtos_com_imagens = [];

if ($produtos) {
    foreach ($produtos as $produto) {
        // Buscar todas as imagens do produto, organizadas por cor
        $sql_imagens = "
            SELECT url_imagem, cor, ordem
            FROM imagens
            WHERE produto_id = ?
            ORDER BY cor, ordem
        ";
        $stmt_imagens = $pdo->prepare($sql_imagens);
        $stmt_imagens->execute([$produto['id']]);
        $imagens = $stmt_imagens->fetchAll(PDO::FETCH_ASSOC);

        // Agrupar imagens por cor
        $imagens_por_cor = [];
        foreach ($imagens as $img) {
            $cor = $img['cor'] ?? 'default';
            $imagens_por_cor[$cor][] = $img['url_imagem'];
        }

        // Definir imagem principal (primeira do banco ou primeira da cor)
        $primeira_imagem_src = $produto['primeira_imagem'] ?? null;
        if (!$primeira_imagem_src && !empty($imagens_por_cor)) {
            $primeira_cor = array_key_first($imagens_por_cor);
            $primeira_imagem_src = $imagens_por_cor[$primeira_cor][0] ?? null;
        }

        // Adicionar imagens ao produto
        $produto['imagens_por_cor'] = $imagens_por_cor;
        $produto['imagem_principal_src'] = $primeira_imagem_src;

        // Adicionar ao array final sem duplicar
        $produtos_com_imagens[] = $produto;
    }
}

// 3. Consultar categorias para menu (limitado a 5)
$categorias_stmt = $pdo->query("
    SELECT *
    FROM categorias
    WHERE ativo = 1
    ORDER BY nome
    LIMIT 5
");
$categorias = $categorias_stmt->fetchAll(PDO::FETCH_ASSOC);
?>



<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flamma | Camisetas de Anime</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Bangers&family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            scroll-behavior: smooth;
        }
        
        .font-bangers {
            font-family: 'Bangers', cursive;
            letter-spacing: 1px;
        }
        
        .hero-gradient {
            background: linear-gradient(135deg, #111827 0%, #1f2937 100%);
        }
        
        .product-card {
            transition: all 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .nav-link {
            position: relative;
        }
        
        .nav-link:hover::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #ef4444;
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
        
        .product-image {
            transition: opacity 0.3s ease;
        }
        
        .product-image.changing {
            opacity: 0.7;
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Gradiente de conexão entre header e hero */
        .header-hero-connector {
            position: relative;
            height: 30px;
            width: 100%;
            background: linear-gradient(to bottom, 
                rgba(0, 0, 0, 1) 0%, 
                rgba(0, 0, 0, 0.8) 40%,
                rgba(0, 0, 0, 0.4) 70%,
                rgba(0, 0, 0, 0) 100%);
            z-index: 5;
            margin-top: -1px;
        }
        
        /* Ajuste na hero section para melhor integração */
        .hero-section {
            margin-top: -40px;
        }
        
        /* Efeito hover na imagem do produto */
        .product-image-container {
            position: relative;
            overflow: hidden;
        }
        
        .product-image-hover {
            position: absolute;
            top: 0;
            left: 0;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .product-card:hover .product-image-hover {
            opacity: 1;
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Navigation -->
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
                    <a href="client/pages/produtos.php"
                        class="text-white hover:text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Produtos</a>
                    <a href="client/pages/masculino.php"
                        class="text-white hover:text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Masculino</a>
                    <a href="client/pages/feminino.php"
                        class="text-white hover:text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Feminino</a>
                    <a href="client/pages/infantil.php"
                        class="text-white hover:text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Infantil</a>
                    <a href="client/pages/ajuda.php"
                        class="text-white hover:text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Central de Ajuda</a>
                </div>

                <!-- Right Icons -->
                <div class="flex items-center space-x-6">
                    <button onclick="window.location.href='client/pages/usuario.php'" class="text-white hover:text-red-500 transition-colors duration-200">
                    <i class="far fa-user text-xl"></i>
                    </button>
                    
                    <button id="cart-button"
                        class="text-white hover:text-red-500 transition-colors duration-200 relative">
                        <i class="fas fa-shopping-bag text-xl"></i>
                        <span id="cart-count"
                            class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">0</span>
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

    <!-- Elemento gradiente de conexão -->
    <div class="header-hero-connector"></div>

    <!-- Hero Section -->
    <section class="hero-section relative bg-black text-white overflow-hidden">
        <!-- Fundo com gradiente e textura -->
        <div class="absolute inset-0">
            <div class="absolute inset-0 bg-gradient-to-br from-black via-gray-900 to-red-900 opacity-80"></div>
            <img src="https://images.unsplash.com/photo-1606112219348-204d7d8b94ee?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80" 
                alt="Camisetas Anime" 
                class="w-full h-full object-cover mix-blend-overlay opacity-60">
        </div>

        <!-- Conteúdo -->
        <div class="relative z-10 flex flex-col items-center justify-center text-center px-6 py-24 lg:py-40">
            <h1 class="text-4xl md:text-6xl font-bold tracking-tight">
                <span class="text-white">FLAMMA</span> 
                <span class="text-red-500">INDEX</span>
            </h1>
            <p class="mt-6 max-w-2xl text-lg md:text-xl text-gray-300">
                Camisetas de Anime com estilo único e qualidade premium.
            </p>
            <div class="mt-8 flex gap-4">
                <a href="client/pages/produtos.php" 
                    class="px-6 py-3 rounded-2xl bg-red-600 hover:bg-red-700 transition font-medium">
                    Ver Camisetas
                </a>
                <a href="client/pages/sobre.php" 
                    class="px-6 py-3 rounded-2xl border border-white/30 hover:bg-white/10 transition font-medium">
                    Sobre a Loja
                </a>
            </div>
        </div>
    </section>

    <!-- Products Grid -->
    <!-- Products Grid -->
   <section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">
                <span class="text-gray-900">PRODUTOS EM</span>
                <span class="text-red-500">DESTAQUE</span>
            </h2>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                Confira nossa seleção especial de camisetas de anime
            </p>
        </div>

        <?php if (empty($produtos_com_imagens)): ?>
            <div class="text-center py-12">
                <i class="fas fa-tshirt text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum produto cadastrado</h3>
                <p class="text-gray-600">Em breve teremos produtos incríveis para você!</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                <?php foreach ($produtos_com_imagens as $produto): ?>
                    <div class="product-card bg-white rounded-lg overflow-hidden shadow-md fade-in">
                        <div class="relative group product-image-container">
                            <?php
                                // Definir o caminho da imagem principal corretamente
                                $src = '/ds-main/client/src/no-image.png';
                                if (!empty($produto['imagem_principal_src'])) {
                                    $img = $produto['imagem_principal_src'];
                                    if (strpos($img, 'admin/src/uploads/') !== false) {
                                        $src = '/ds-main/' . $img;
                                    } else {
                                        $src = '/ds-main/admin/src/uploads/' . basename($img);
                                    }
                                }
                            ?>
                            <!-- Link clicável na imagem -->
                            <a href="/ds-main/produto/<?= $produto['id'] ?>" class="block">
                                <img src="<?= htmlspecialchars($src) ?>"
                                     alt="<?= htmlspecialchars($produto['nome']) ?>"
                                     class="product-image w-full h-80 object-cover hover:scale-105 transition-transform duration-300"
                                     onerror="this.src='/ds-main/client/src/no-image.png'">
                            </a>
                            
                            <!-- Hover (se houver outra imagem) -->
                            <?php
                                // Exibe segunda imagem como hover se existir
                                if (!empty($produto['imagens_por_cor'])) {
                                    // Primeiro grupo de cor
                                    $first_cor = array_key_first($produto['imagens_por_cor']);
                                    $imgs_da_cor = $produto['imagens_por_cor'][$first_cor];
                                    if (isset($imgs_da_cor[1])) {
                                        $hover_img = $imgs_da_cor[1];
                                        if (strpos($hover_img, 'admin/src/uploads/') !== false) {
                                            $hover_src = '/ds-main/' . $hover_img;
                                        } else {
                                            $hover_src = '/ds-main/admin/src/uploads/' . basename($hover_img);
                                        }
                                        ?>
                                        <a href="/ds-main/produto/<?= $produto['id'] ?>" class="block absolute inset-0">
                                            <img src="<?= htmlspecialchars($hover_src) ?>"
                                                 alt="<?= htmlspecialchars($produto['nome']) ?> - Hover"
                                                 class="product-image-hover w-full h-80 object-cover opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                        </a>
                                        <?php
                                    }
                                }
                            ?>

                            <script type="application/json" class="product-images-data">
                                <?= json_encode($produto['imagens_por_cor']) ?>
                            </script>
                        </div>

                        <div class="p-4">
                            <!-- Nome do produto clicável -->
                            <a href="/ds-main/produto/<?= $produto['id'] ?>" class="block">
                                <h3 class="font-medium text-gray-900 mb-1 hover:text-red-600 transition-colors duration-200"><?= htmlspecialchars($produto['nome']) ?></h3>
                            </a>
                            <p class="text-gray-500 text-sm mb-3"><?= htmlspecialchars($produto['descricao']) ?></p>
                            <?php if ($produto['anime_nome']): ?>
                            <p class="text-gray-400 text-xs mb-2"><?= htmlspecialchars($produto['anime_nome']) ?></p>
                            <?php endif; ?>

                            <div class="flex flex-col mb-3">
                                <?php if (!empty($produto['preco_original']) && $produto['preco_original'] > $produto['preco']): ?>
                                    <span class="text-gray-400 text-sm line-through">
                                        R$<?= number_format($produto['preco_original'], 2, ',', '.') ?>
                                    </span>
                                <?php endif; ?>
                                <span class="font-bold text-gray-900">
                                    R$<?= number_format($produto['preco'], 2, ',', '.') ?>
                                </span>
                            </div>

                            <div class="flex gap-2">
                                <!-- Botão ver produto -->
                                <a href="/ds-main/produto/<?= $produto['id'] ?>" 
                                   class="flex-1 bg-gray-800 text-white px-3 py-2 rounded text-sm text-center hover:bg-gray-700 transition duration-300">
                                    Ver Produto
                                </a>
                                
                                <!-- Botão adicionar carrinho -->
                                <button class="add-to-cart bg-red-600 text-white px-3 py-2 rounded text-sm hover:bg-red-700 transition duration-300"
                                        data-id="<?= $produto['id'] ?>"
                                        data-name="<?= htmlspecialchars($produto['nome']) ?>"
                                        data-price="<?= $produto['preco'] ?>"
                                        data-image="<?= htmlspecialchars($src) ?>">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-12">
                <a href="client/pages/produtos.php"
                   class="inline-flex items-center px-6 py-3 rounded-lg bg-red-600 text-white font-medium hover:bg-red-700 transition duration-300">
                    Ver Todos os Produtos
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>


    <!-- Newsletter -->
    <section class="py-12 bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl font-bold mb-4">JUNTE-SE À COMUNIDADE FLAMMA</h2>
            <p class="text-gray-300 mb-6 max-w-2xl mx-auto">Receba novidades, lançamentos exclusivos e ofertas especiais diretamente no seu e-mail.</p>
            <div class="max-w-md mx-auto flex">
                <input type="email" placeholder="Seu melhor e-mail" class="px-4 py-3 rounded-l-md w-full text-gray-900 focus:outline-none">
                <button class="bg-red-600 hover:bg-red-700 px-6 py-3 rounded-r-md font-medium transition duration-300">
                    Assinar
                </button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-black text-white pt-12 pb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-8">
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider mb-4">FLAMMA</h3>
                <p class="text-gray-400 text-sm">Camisetas premium de anime para verdadeiros fãs.</p>
            </div>

            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider mb-4">PRODUTOS</h3>
                <ul class="space-y-2">
                    <li><a href="client/pages/masculino.php" class="text-gray-400 hover:text-red-500 text-sm">Masculino</a></li>
                    <li><a href="client/pages/feminino.php" class="text-gray-400 hover:text-red-500 text-sm">Feminino</a></li>
                    <li><a href="client/pages/infantil.php" class="text-gray-400 hover:text-red-500 text-sm">Infantil</a></li>
                    <li><a href="client/pages/produtos.php" class="text-gray-400 hover:text-red-500 text-sm">Todos os Produtos</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider mb-4">SUPORTE</h3>
                <ul class="space-y-2">
                    <li><a href="client/pages/ajuda.php" class="text-gray-400 hover:text-red-500 text-sm">Central de Ajuda</a></li>
                    <li><a href="client/pages/ajuda.php#exchanges" class="text-gray-400 hover:text-red-500 text-sm">Trocas e Devoluções</a></li>
                    <li><a href="client/pages/ajuda.php#delivery" class="text-gray-400 hover:text-red-500 text-sm">Entregas</a></li>
                    <li><a href="client/pages/contato.php" class="text-gray-400 hover:text-red-500 text-sm">Fale Conosco</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider mb-4">REDES SOCIAIS</h3>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-red-500"><i class="fab fa-instagram text-lg"></i></a>
                    <a href="#" class="text-gray-400 hover:text-red-500"><i class="fab fa-twitter text-lg"></i></a>
                    <a href="#" class="text-gray-400 hover:text-red-500"><i class="fab fa-facebook-f text-lg"></i></a>
                    <a href="#" class="text-gray-400 hover:text-red-500"><i class="fab fa-tiktok text-lg"></i></a>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-800 pt-6 text-center">
            <p class="text-gray-400 text-sm">© 2025 Flamma. Todos os direitos reservados.</p>
        </div>
    </div>
</footer>

    <!-- Shopping Cart Sidebar -->
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

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
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
                            <p class="text-gray-500 text-sm">R$${item.price.toFixed(2)}</p>
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

            cartTotal.textContent = `R$${total.toFixed(2).replace('.', ',')}`;
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
                        // Se quiser, pode remover automaticamente ao chegar em 0
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

        cartButton.addEventListener('click', () => {
            cartSidebar.classList.remove('translate-x-full');
        });

        closeCart.addEventListener('click', () => {
            cartSidebar.classList.add('translate-x-full');
        });

        // Adicionar produtos ao carrinho
        document.querySelectorAll('.add-to-cart').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const price = parseFloat(this.dataset.price);
                const image = this.dataset.image;

                // Verifica se o produto já está no carrinho
                const existingIndex = cart.findIndex(item => item.id == id);
                if (existingIndex >= 0) {
                    cart[existingIndex].quantity++;
                } else {
                    cart.push({ id, name, price, image, quantity: 1 });
                }

                updateCartDisplay();
                // Abrir carrinho automaticamente
                cartSidebar.classList.remove('translate-x-full');
            });
        });

        // Inicializa o display
        updateCartDisplay();

        // Fechar carrinho ao clicar fora (opcional)
        document.addEventListener('click', function(e) {
            if (!cartSidebar.contains(e.target) && !cartButton.contains(e.target)) {
                cartSidebar.classList.add('translate-x-full');
            }
        });



        // Função para trocar imagens ao selecionar uma cor
document.querySelectorAll('.color-option').forEach(colorOption => {
    colorOption.addEventListener('click', function() {
        const productCard = this.closest('.product-card');
        const productId = productCard.dataset.produtoId;
        const selectedColor = this.dataset.color;
        
        // Remover a seleção anterior
        productCard.querySelectorAll('.color-option').forEach(option => {
            option.classList.remove('selected');
        });
        
        // Adicionar seleção à cor clicada
        this.classList.add('selected');
        
        // Obter as imagens para esta cor
        const imagesDataElement = productCard.querySelector('.product-images-data');
        if (imagesDataElement) {
            const imagesData = JSON.parse(imagesDataElement.textContent);
            
            if (imagesData[selectedColor] && imagesData[selectedColor].length > 0) {
                const productImage = productCard.querySelector('.product-image');
                const productImageHover = productCard.querySelector('.product-image-hover');
                
                // Adicionar classe de transição
                if (productImage) {
                    productImage.classList.add('changing');
                }
                
                // Trocar a imagem principal após um breve delay para a animação
                setTimeout(() => {
                    if (productImage) {
                        productImage.src = imagesData[selectedColor][0];
                        productImage.classList.remove('changing');
                    }
                    
                    // Se houver imagem hover, tentar encontrar uma correspondente
                    if (productImageHover && imagesData[selectedColor].length > 1) {
                        productImageHover.src = imagesData[selectedColor][1];
                    }
                }, 150);
            }
        }
    });
});
    </script>
</body>
</html>
