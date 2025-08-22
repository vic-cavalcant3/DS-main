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

// Construir query SQL com filtros para produtos masculinos
$sql = "SELECT p.*, 
               c.nome as categoria_nome, 
               a.nome as anime_nome,
               (SELECT url_imagem 
                FROM imagens 
                WHERE produto_id = p.id 
                ORDER BY ordem ASC 
                LIMIT 1) as primeira_imagem
        FROM produtos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        LEFT JOIN animes a ON p.anime_id = a.id 
        WHERE p.ativo = 1 AND c.slug = 'masculino'";

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

// Contar total de produtos para paginação
$count_sql = "SELECT COUNT(*) 
              FROM produtos p 
              LEFT JOIN categorias c ON p.categoria_id = c.id 
              LEFT JOIN animes a ON p.anime_id = a.id 
              WHERE p.ativo = 1 AND c.slug = 'masculino'";

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


<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moda Masculina | Flamma - Camisetas de Anime</title>
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
        
        .hero-banner {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1617137968429-3a798f838c82?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1770&q=80');
            background-size: cover;
            background-position: center;
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
                    <a href="../../index.html" class="text-2xl font-bold text-gray-900 hover:opacity-90 transition-opacity">
                        <img src="../../client/src/Flamma-logo.png" alt="Flamma Index" class="h-10 md:h-12 w-auto">
                    </a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-6">
                    <a href="produtos.php"
                        class="text-white hover:text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Produtos</a>
                    <a href="masculino.php"
                        class="text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Masculino</a>
                      <a href="feminino.php"
                        class="text-white px-3 py-3 text-sm font-semibold transition-colors duration-200">Feminina</a>
                    <a href="infantil.php"
                        class="text-white hover:text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Infantil</a>
                    <a href="#"
                        class="text-white hover:text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Sobre</a>
                    <a href="#"
                        class="text-white hover:text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Contato</a>
                </div>

                <!-- Right Icons -->
                <div class="flex items-center space-x-6">
                    <button class="text-white hover:text-red-500 transition-colors duration-200">
                        <i class="fas fa-search text-xl"></i>
                    </button>
                    <button class="text-white hover:text-red-500 transition-colors duration-200">
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
                <a href="produtos.php" class="block px-3 py-3 text-base font-medium text-white hover:bg-gray-900">Produtos</a>
                <a href="masculino.php" class="block px-3 py-3 text-base font-medium text-white bg-gray-900">Masculino</a>
                <a href="feminino.php" class="block px-3 py-3 text-base font-medium text-white hover:bg-gray-900">Feminino</a>
                <a href="infantil.php" class="block px-3 py-3 text-base font-medium text-white hover:bg-gray-900">Infantil</a>
                <a href="#" class="block px-3 py-3 text-base font-medium text-white hover:bg-gray-900">Sobre</a>
                <a href="#" class="block px-3 py-3 text-base font-medium text-white hover:bg-gray-900">Contato</a>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="bg-black hero-banner text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    <span class="text-white">MODA</span> 
                    <span class="text-red-500">MASCULINA</span>
                </h1>
                <p class="text-gray-300 text-lg max-w-2xl mx-auto">
                    Estilo e atitude em camisetas de anime feitas especialmente para homens
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
                    <form method="GET" action="masculino.php" id="filter-form">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-900">Filtros</h2>
                            <a href="masculino.php" class="text-red-500 text-sm hover:text-red-600">
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
                        <!-- <div class="mb-6 border-b border-gray-200 pb-4">
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
                        </div> -->

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
                            <?= $total_produtos ?> produto<?= $total_produtos !== 1 ? 's' : '' ?> masculino<?= $total_produtos !== 1 ? 's' : '' ?> encontrado<?= $total_produtos !== 1 ? 's' : '' ?>
                        </h2>
                        <p class="text-gray-600 text-sm">Camisetas de anime para homens</p>
                    </div>
                </div>

                <!-- Products Grid -->
                <?php if (empty($produtos)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum produto encontrado</h3>
                    <p class="text-gray-600">Tente ajustar os filtros para encontrar o que procura.</p>
                </div>
                <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($produtos as $produto): ?>
                    <div class="product-card bg-white rounded-lg overflow-hidden shadow-md fade-in">
                        <div class="relative group">
                            <?php 
                            // Construir caminho correto da imagem
                            $imagem_src = '';
                            if ($produto['imagem_principal']) {
                                // Se já contém o caminho completo admin/src/uploads/
                                if (strpos($produto['imagem_principal'], 'admin/src/uploads/') !== false) {
                                    $imagem_src = '/ds-main/' . $produto['imagem_principal'];
                                } else {
                                    // Se é apenas o nome do arquivo
                                    $imagem_src = '/ds-main/admin/src/uploads/' . basename($produto['imagem_principal']);
                                }
                            } else {
                                $imagem_src = '/ds-main/client/src/no-image.png'; // imagem padrão
                            }
                            ?>
                            
                            <img src="<?= $imagem_src ?>" 
                                 alt="<?= htmlspecialchars($produto['nome']) ?>" 
                                 class="w-full h-80 object-cover"
                                 onerror="this.src='/ds-main/client/src/no-image.png'">
                            
                            <?php if ($produto['imagem_hover']): ?>
                                <?php 
                                $imagem_hover_src = '';
                                if (strpos($produto['imagem_hover'], 'admin/src/uploads/') !== false) {
                                    $imagem_hover_src = '/ds-main/' . $produto['imagem_hover'];
                                } else {
                                    $imagem_hover_src = '/ds-main/admin/src/uploads/' . basename($produto['imagem_hover']);
                                }
                                ?>
                                <img src="<?= $imagem_hover_src ?>" 
                                     alt="<?= htmlspecialchars($produto['nome']) ?> - Hover" 
                                     class="w-full h-80 object-cover absolute top-0 left-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
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

    <!-- Footer -->
    <footer class="bg-black text-white py-12 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">FLAMMA</h3>
                    <p class="text-gray-400">Camisetas de anime com design exclusivo e qualidade premium.</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">PRODUTOS</h3>
                    <ul class="space-y-2">
                        <li><a href="masculino.php" class="text-gray-400 hover:text-red-500">Masculino</a></li>
                        <li><a href="feminino.php" class="text-gray-400 hover:text-red-500">Feminino</a></li>
                        <li><a href="infantil.php" class="text-gray-400 hover:text-red-500">Infantil</a></li>
                        <li><a href="produtos.php" class="text-gray-400 hover:text-red-500">Todos os Produtos</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">SUPORTE</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-red-500">Central de Ajuda</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-red-500">Trocas e Devoluções</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-red-500">Entregas</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-red-500">Fale Conosco</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">NEWSLETTER</h3>
                    <p class="text-gray-400 mb-4">Cadastre-se para receber nossas novidades e promoções.</p>
                    <form class="flex">
                        <input type="email" placeholder="Seu e-mail" class="px-4 py-2 rounded-l-md w-full text-gray-900">
                        <button type="submit" class="bg-red-600 px-4 py-2 rounded-r-md hover:bg-red-700">OK</button>
                    </form>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400">© 2023 Flamma. Todos os direitos reservados.</p>
                <div class="flex space-x-4 mt-4 md:mt-0">
                    <a href="#" class="text-gray-400 hover:text-red-500"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-gray-400 hover:text-red-500"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-gray-400 hover:text-red-500"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-gray-400 hover:text-red-500"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });

        // Filter dropdown toggles
        document.querySelectorAll('.filter-toggle').forEach(button => {
            button.addEventListener('click', function() {
                const target = document.getElementById(this.dataset.target);
                target.classList.toggle('open');
                const icon = this.querySelector('i');
                icon.classList.toggle('rotate-180');
            });
        });

        // Price range display
        function updatePriceDisplay(value) {
            document.getElementById('price-value').textContent = 'até R$' + value;
            document.getElementById('filter-form').submit();
        }

        // Color filter buttons
        document.querySelectorAll('.color-filter-btn').forEach(button => {
            button.addEventListener('click', function() {
                const color = this.dataset.color;
                document.getElementById('color-input').value = color;
                document.getElementById('filter-form').submit();
            });
        });

        // Cart functionality
        const cartButton = document.getElementById('cart-button');
        const closeCart = document.getElementById('close-cart');
        const cartSidebar = document.getElementById('cart-sidebar');
        const cartItems = document.getElementById('cart-items');
        const cartTotal = document.getElementById('cart-total');
        const cartCount = document.getElementById('cart-count');
        
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        
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

            cartTotal.textContent = `R$${total.toFixed(2)}`;
            cartCount.textContent = cart.reduce((acc, item) => acc + item.quantity, 0);
            localStorage.setItem('cart', JSON.stringify(cart));

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
                        updateCartDisplay();
                    }
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

        // Show/hide cart sidebar
        cartButton.addEventListener('click', () => {
            cartSidebar.classList.remove('translate-x-full');
        });

        closeCart.addEventListener('click', () => {
            cartSidebar.classList.add('translate-x-full');
        });

        // Add to cart buttons
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.dataset.id;
                const name = button.dataset.name;
                const price = parseFloat(button.dataset.price);
                const image = button.dataset.image || '/ds-main/client/src/no-image.png';

                const existing = cart.find(item => item.id == id);
                if (existing) {
                    existing.quantity++;
                } else {
                    cart.push({ id, name, price, image, quantity: 1 });
                }

                updateCartDisplay();
                cartSidebar.classList.remove('translate-x-full'); // open cart on add
            });
        });

        // Initialize cart display
        updateCartDisplay();
    </script>
</body>
</html>

