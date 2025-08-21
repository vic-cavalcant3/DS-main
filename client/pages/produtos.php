<?php
require '../pages/utils/conexao.php';

// Parâmetros de filtro da URL
$categoria = $_GET['categoria'] ?? '';
$anime = $_GET['anime'] ?? '';
$cor = $_GET['cor'] ?? '';
$preco_max = $_GET['preco_max'] ?? 200;
$ordenar = $_GET['ordenar'] ?? 'nome';
$pagina = $_GET['pagina'] ?? 1;
$itens_por_pagina = 12;
$offset = ($pagina - 1) * $itens_por_pagina;

// Construir query SQL com filtros - CORREÇÃO: Aspas faltando
$sql = "SELECT p.*, c.nome as categoria_nome, a.nome as anime_nome 
        FROM produtos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        LEFT JOIN animes a ON p.anime_id = a.id 
        WHERE p.ativo = 1"; // CORREÇÃO: Aspas adicionadas e p.ativo para evitar ambiguidade

$params = [];

if ($categoria) {
    $sql .= " AND c.slug = :categoria";
    $params['categoria'] = $categoria;
}

if ($anime) {
    $sql .= " AND a.slug = :anime";
    $params['anime'] = $anime;
}

if ($cor) {
    $sql .= " AND p.cores LIKE :cor";
    $params['cor'] = "%$cor%";
}

if ($preco_max) {
    $sql .= " AND p.preco <= :preco_max";
    $params['preco_max'] = $preco_max;
}

// Ordenação
switch ($ordenar) {
    case 'preco_asc':
        $sql .= " ORDER BY p.preco ASC";
        break;
    case 'preco_desc':
        $sql .= " ORDER BY p.preco DESC";
        break;
    case 'mais_novos':
        $sql .= " ORDER BY p.created_at DESC";
        break;
    case 'populares':
        $sql .= " ORDER BY p.vendas DESC";
        break;
    default:
        $sql .= " ORDER BY p.nome ASC";
}

// Contar total de produtos para paginação - CORREÇÃO: Query de contagem mais segura
$count_sql = "SELECT COUNT(*) 
             FROM produtos p 
             LEFT JOIN categorias c ON p.categoria_id = c.id 
             LEFT JOIN animes a ON p.anime_id = a.id 
             WHERE p.ativo = 1";

// Adicionar filtros à query de contagem
if ($categoria) {
    $count_sql .= " AND c.slug = :categoria";
}
if ($anime) {
    $count_sql .= " AND a.slug = :anime";
}
if ($cor) {
    $count_sql .= " AND p.cores LIKE :cor";
}
if ($preco_max) {
    $count_sql .= " AND p.preco <= :preco_max";
}

$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_produtos = $count_stmt->fetchColumn();
$total_paginas = ceil($total_produtos / $itens_por_pagina);

// Adicionar LIMIT para paginação
$sql .= " LIMIT :offset, :limit";
$params['offset'] = $offset;
$params['limit'] = $itens_por_pagina;

$stmt = $pdo->prepare($sql);

// Bind dos parâmetros
foreach ($params as $key => $value) {
    if ($key === 'offset' || $key === 'limit') {
        $stmt->bindValue(":$key", (int)$value, PDO::PARAM_INT);
    } else {
        $stmt->bindValue(":$key", $value);
    }
}

$stmt->execute();
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar categorias e animes para os filtros
$categorias_stmt = $pdo->query("SELECT * FROM categorias WHERE ativo = 1 ORDER BY nome");
$categorias = $categorias_stmt->fetchAll(PDO::FETCH_ASSOC);

$animes_stmt = $pdo->query("SELECT * FROM animes WHERE ativo = 1 ORDER BY nome");
$animes = $animes_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
]

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos | Flamma - Camisetas de Anime</title>
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
        
        .product-card {
            transition: all 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .filter-dropdown {
            transition: all 0.3s ease;
            max-height: 0;
            overflow: hidden;
        }
        
        .filter-dropdown.open {
            max-height: 300px;
        }
        
        .color-option {
            transition: all 0.2s ease;
        }
        
        .color-option.selected {
            border: 2px solid #ef4444;
            transform: scale(1.1);
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .filter-badge {
            animation: slideInFromTop 0.3s ease;
        }
        
        @keyframes slideInFromTop {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
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
                        <img src="./client/src/Flamma-logo.png" alt="Flamma" class="h-10 md:h-12 w-auto">
                    </a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-6">
                    <a href="produtos.php" class="text-red-500 px-3 py-3 text-sm font-semibold border-b-2 border-red-500">Produtos</a>
                    <a href="produtos.php?categoria=masculino" class="text-white hover:text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Masculino</a>
                    <a href="produtos.php?categoria=feminino" class="text-white hover:text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Feminino</a>
                    <a href="produtos.php?categoria=infantil" class="text-white hover:text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Infantil</a>
                    <a href="sobre.php" class="text-white hover:text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Sobre</a>
                    <a href="contato.php" class="text-white hover:text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Contato</a>
                </div>

                <!-- Right Icons -->
                <div class="flex items-center space-x-6">
                    <button id="search-button" class="text-white hover:text-red-500 transition-colors duration-200">
                        <i class="fas fa-search text-xl"></i>
                    </button>
                    <button class="text-white hover:text-red-500 transition-colors duration-200">
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
                <a href="produtos.php" class="block px-3 py-3 text-base font-medium text-red-500 border-l-4 border-red-500 bg-gray-900">Produtos</a>
                <a href="produtos.php?categoria=masculino" class="block px-3 py-3 text-base font-medium text-white hover:bg-gray-900">Masculino</a>
                <a href="produtos.php?categoria=feminino" class="block px-3 py-3 text-base font-medium text-white hover:bg-gray-900">Feminino</a>
                <a href="produtos.php?categoria=infantil" class="block px-3 py-3 text-base font-medium text-white hover:bg-gray-900">Infantil</a>
                <a href="sobre.php" class="block px-3 py-3 text-base font-medium text-white hover:bg-gray-900">Sobre</a>
                <a href="contato.php" class="block px-3 py-3 text-base font-medium text-white hover:bg-gray-900">Contato</a>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="bg-black text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-3xl md:text-4xl font-bold mb-4">
                    <span class="text-white">NOSSOS</span> 
                    <span class="text-red-500">PRODUTOS</span>
                </h1>
                <p class="text-gray-300 text-lg max-w-2xl mx-auto">
                    Descubra nossa coleção completa de camisetas de anime com designs únicos e qualidade premium
                </p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar Filters -->
            <aside class="lg:w-1/4">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-24">
                    <form method="GET" action="produtos.php" id="filter-form">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-900">Filtros</h2>
                            <a href="produtos.php" class="text-red-500 text-sm hover:text-red-600">
                                Limpar Tudo
                            </a>
                        </div>

                        <!-- Active Filters Display -->
                        <?php if ($categoria || $anime || $cor || $preco_max < 200): ?>
                        <div class="mb-6 space-y-2">
                            <?php if ($categoria): ?>
                            <div class="filter-badge inline-flex items-center px-3 py-1 rounded-full text-sm bg-red-100 text-red-700">
                                Categoria: <?= htmlspecialchars(ucfirst($categoria)) ?>
                                <a href="<?= http_build_query(array_merge($_GET, ['categoria' => ''])) ?>" class="ml-2 text-red-500 hover:text-red-700">×</a>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($anime): ?>
                            <div class="filter-badge inline-flex items-center px-3 py-1 rounded-full text-sm bg-red-100 text-red-700">
                                Anime: <?= htmlspecialchars(ucfirst(str_replace('-', ' ', $anime))) ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['anime' => ''])) ?>" class="ml-2 text-red-500 hover:text-red-700">×</a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Category Filter -->
                        <div class="mb-6 border-b border-gray-200 pb-4">
                            <button type="button" class="filter-toggle flex items-center justify-between w-full text-left font-medium text-gray-900 py-2" data-target="category-filter">
                                <span>Categoria</span>
                                <i class="fas fa-chevron-down transform transition-transform"></i>
                            </button>
                            <div id="category-filter" class="filter-dropdown open mt-2">
                                <?php foreach ($categorias as $cat): ?>
                                <label class="flex items-center py-1">
                                    <input type="radio" name="categoria" value="<?= $cat['slug'] ?>" 
                                           <?= $categoria === $cat['slug'] ? 'checked' : '' ?>
                                           class="form-radio h-4 w-4 text-red-500 border-gray-300" 
                                           onchange="this.form.submit()">
                                    <span class="ml-2 text-sm text-gray-700"><?= htmlspecialchars($cat['nome']) ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Anime Filter -->
                        <div class="mb-6 border-b border-gray-200 pb-4">
                            <button type="button" class="filter-toggle flex items-center justify-between w-full text-left font-medium text-gray-900 py-2" data-target="anime-filter">
                                <span>Anime</span>
                                <i class="fas fa-chevron-down transform transition-transform"></i>
                            </button>
                            <div id="anime-filter" class="filter-dropdown open mt-2">
                                <?php foreach ($animes as $a): ?>
                                <label class="flex items-center py-1">
                                    <input type="radio" name="anime" value="<?= $a['slug'] ?>" 
                                           <?= $anime === $a['slug'] ? 'checked' : '' ?>
                                           class="form-radio h-4 w-4 text-red-500 border-gray-300" 
                                           onchange="this.form.submit()">
                                    <span class="ml-2 text-sm text-gray-700"><?= htmlspecialchars($a['nome']) ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-6 border-b border-gray-200 pb-4">
                            <button type="button" class="filter-toggle flex items-center justify-between w-full text-left font-medium text-gray-900 py-2" data-target="price-filter">
                                <span>Preço</span>
                                <i class="fas fa-chevron-down transform transition-transform"></i>
                            </button>
                            <div id="price-filter" class="filter-dropdown open mt-2">
                                <div class="space-y-3">
                                    <input type="range" name="preco_max" min="30" max="200" value="<?= $preco_max ?>" 
                                           class="w-full" id="price-range" onchange="updatePriceDisplay(this.value)">
                                    <div class="flex justify-between text-sm text-gray-600">
                                        <span>R$30</span>
                                        <span id="price-value">até R$<?= $preco_max ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Color Filter -->
                        <div class="mb-6">
                            <button type="button" class="filter-toggle flex items-center justify-between w-full text-left font-medium text-gray-900 py-2" data-target="color-filter">
                                <span>Cor</span>
                                <i class="fas fa-chevron-down transform transition-transform"></i>
                            </button>
                            <div id="color-filter" class="filter-dropdown open mt-2">
                                <div class="flex space-x-2">
                                    <button type="button" class="color-filter-btn w-8 h-8 rounded-full bg-black border-2 <?= $cor === 'preto' ? 'border-red-500' : 'border-gray-300' ?> hover:border-red-500 transition-all" 
                                            data-color="preto" title="Preto"></button>
                                    <button type="button" class="color-filter-btn w-8 h-8 rounded-full bg-white border-2 <?= $cor === 'branco' ? 'border-red-500' : 'border-gray-300' ?> hover:border-red-500 transition-all" 
                                            data-color="branco" title="Branco"></button>
                                    <button type="button" class="color-filter-btn w-8 h-8 rounded-full bg-gray-500 border-2 <?= $cor === 'cinza' ? 'border-red-500' : 'border-gray-300' ?> hover:border-red-500 transition-all" 
                                            data-color="cinza" title="Cinza"></button>
                                    <button type="button" class="color-filter-btn w-8 h-8 rounded-full bg-red-500 border-2 <?= $cor === 'vermelho' ? 'border-red-500' : 'border-gray-300' ?> hover:border-red-500 transition-all" 
                                            data-color="vermelho" title="Vermelho"></button>
                                </div>
                                <input type="hidden" name="cor" value="<?= $cor ?>" id="color-input">
                            </div>
                        </div>

                        <!-- Sort -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ordenar por:</label>
                            <select name="ordenar" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" onchange="this.form.submit()">
                                <option value="nome" <?= $ordenar === 'nome' ? 'selected' : '' ?>>Nome A-Z</option>
                                <option value="preco_asc" <?= $ordenar === 'preco_asc' ? 'selected' : '' ?>>Menor Preço</option>
                                <option value="preco_desc" <?= $ordenar === 'preco_desc' ? 'selected' : '' ?>>Maior Preço</option>
                                <option value="mais_novos" <?= $ordenar === 'mais_novos' ? 'selected' : '' ?>>Mais Recentes</option>
                                <option value="populares" <?= $ordenar === 'populares' ? 'selected' : '' ?>>Mais Populares</option>
                            </select>
                        </div>
                    </form>
                </div>
            </aside>

            <!-- Main Product Area -->
            <main class="lg:w-3/4">
                <!-- Results Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
                    <div class="mb-4 sm:mb-0">
                        <h2 class="text-xl font-semibold text-gray-900">
                            <?= $total_produtos ?> produto<?= $total_produtos !== 1 ? 's' : '' ?> encontrado<?= $total_produtos !== 1 ? 's' : '' ?>
                        </h2>
                        <p class="text-gray-600 text-sm">Camisetas de anime premium</p>
                    </div>
                </div>

                <!-- Products Grid -->
                <?php if (empty($produtos)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum produto encontrado</h3>
                    <p class="text-gray-600">Tente ajustar os filtros para encontrar o que procura.</p>
                    <a href="produtos.php" class="inline-block mt-4 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Ver todos os produtos
                    </a>
                </div>
                <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($produtos as $produto): ?>
                    <div class="product-card bg-white rounded-lg overflow-hidden shadow-md fade-in">
                        <div class="relative group">
                            <img src="<?= htmlspecialchars($produto['imagem_principal']) ?>" 
                                 alt="<?= htmlspecialchars($produto['nome']) ?>" 
                                 class="w-full h-80 object-cover">
                            
                            <?php if ($produto['imagem_hover']): ?>
                            <img src="<?= htmlspecialchars($produto['imagem_hover']) ?>" 
                                 alt="<?= htmlspecialchars($produto['nome']) ?> - Hover" 
                                 class="w-full h-80 object-cover absolute top-0 left-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <?php endif; ?>
                            
                            <div class="absolute top-2 right-2">
                                <button class="wishlist-btn bg-white rounded-full p-2 shadow-md hover:bg-gray-100" 
                                        data-product-id="<?= $produto['id'] ?>">
                                    <i class="far fa-heart text-gray-700"></i>
                                </button>
                            </div>

                            <?php if ($produto['desconto'] > 0): ?>
                            <div class="absolute top-2 left-2 bg-red-500 text-white px-2 py-1 rounded text-xs font-bold">
                                -<?= $produto['desconto'] ?>%
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="p-4">
                            <h3 class="font-medium text-gray-900 mb-1"><?= htmlspecialchars($produto['nome']) ?></h3>
                            <p class="text-gray-500 text-sm mb-3"><?= htmlspecialchars($produto['descricao']) ?></p>
                            <p class="text-gray-400 text-xs mb-2"><?= htmlspecialchars($produto['anime_nome']) ?></p>
                            
                            <!-- Cores disponíveis -->
                            <?php if ($produto['cores']): ?>
                            <div class="flex space-x-2 mb-3">
                                <?php 
                                $cores = explode(',', $produto['cores']);
                                foreach ($cores as $cor_item): 
                                    $cor_item = trim($cor_item);
                                    $cor_class = match($cor_item) {
                                        'preto' => 'bg-black',
                                        'branco' => 'bg-white border-gray-400',
                                        'cinza' => 'bg-gray-500',
                                        'vermelho' => 'bg-red-500',
                                        default => 'bg-gray-300'
                                    };
                                ?>
                                <div class="color-option w-6 h-6 rounded-full <?= $cor_class ?> border-2 border-gray-300 cursor-pointer" 
                                     data-color="<?= $cor_item ?>" title="<?= ucfirst($cor_item) ?>"></div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="flex justify-between items-center">
                                <div class="flex flex-col">
                                    <?php if ($produto['preco_original'] && $produto['preco_original'] > $produto['preco']): ?>
                                    <span class="text-gray-400 text-sm line-through">R$<?= number_format($produto['preco_original'], 2, ',', '.') ?></span>
                                    <?php endif; ?>
                                    <span class="font-bold text-gray-900">R$<?= number_format($produto['preco'], 2, ',', '.') ?></span>
                                </div>
                                <button class="add-to-cart bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700 transition duration-300"
                                        data-id="<?= $produto['id'] ?>" 
                                        data-name="<?= htmlspecialchars($produto['nome']) ?>" 
                                        data-price="<?= $produto['preco'] ?>"
                                        data-image="<?= htmlspecialchars($produto['imagem_principal']) ?>">
                                    Adicionar
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_paginas > 1): ?>
                <div class="mt-12 flex justify-center">
                    <nav class="inline-flex rounded-md shadow">
                        <?php if ($pagina > 1): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])) ?>" 
                           class="px-3 py-2 rounded-l-md border border-gray-300 bg-white text-gray-500 hover:bg-gray-50">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <?php endif; ?>

                        <?php 
                        $start = max(1, $pagina - 2);
                        $end = min($total_paginas, $pagina + 2);
                        
                        for ($i = $start; $i <= $end; $i++): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>" 
                           class="px-4 py-2 border <?= $i === (int)$pagina ? 'bg-red-500 text-white border-red-500' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50' ?> font-medium">
                            <?= $i ?>
                        </a>
                        <?php endfor; ?>

                        <?php if ($pagina < $total_paginas): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])) ?>" 
                           class="px-3 py-2 rounded-r-md border border-gray-300 bg-white text-gray-500 hover:bg-gray-50">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <?php endif; ?>
                    </nav>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Shopping Cart Sidebar -->
    <div id="cart-sidebar" class="fixed inset-y-0 right-0 w-full md:w-96 bg-white shadow-xl transform translate-x-full transition-transform duration-300 ease-in-out z-50 overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold">Seu Carrinho</h2>
                <button id="close-cart" class="text-gray-500 hover:text-gray-700">