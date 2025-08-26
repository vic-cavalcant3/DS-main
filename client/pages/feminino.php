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

// Construir query SQL com filtros para produtos femininos
$sql = "SELECT p.*, c.nome as categoria_nome, a.nome as anime_nome,
        (SELECT url_imagem FROM imagens WHERE produto_id = p.id ORDER BY ordem ASC LIMIT 1) as primeira_imagem
        FROM produtos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        LEFT JOIN animes a ON p.anime_id = a.id
        WHERE p.ativo = 1";

$params = [];

// CORREÇÃO PRINCIPAL: Força filtro por categoria feminino
$sql .= " AND c.slug = 'feminino'";

// Só aplica filtros adicionais se houver categoria feminino
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
$count_sql = "SELECT COUNT(*) FROM produtos p
              LEFT JOIN categorias c ON p.categoria_id = c.id
              LEFT JOIN animes a ON p.anime_id = a.id
              WHERE p.ativo = 1 AND c.slug = 'feminino'";

// Adicionar filtros à query de contagem
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

// Buscar animes para os filtros
$animes_stmt = $pdo->query("SELECT * FROM animes WHERE ativo = 1 ORDER BY nome");
$animes = $animes_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moda Feminina | Flamma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <a href="../../index.php" class="text-2xl font-bold text-gray-900 hover:opacity-90 transition-opacity">
                        <img src="../../client/src/Flamma-logo.png" alt="Flamma Index" class="h-10 md:h-12 w-auto">
                    </a>
                </div>


                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-6">
                    <a href="produtos.php"
                        class="text-white hover:text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Produtos</a>
                    <a href="masculino.php"
                        class="text-white hover:text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Masculino</a>
                    <a href="feminino.php"
                        class="text-red-500 hover:text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Feminino</a>
                    <a href="infantil.php"
                        class="text-white hover:text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Infantil</a>
                    <a href="ajuda.php"
                        class="text-white hover:text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Central de Ajuda</a>
                </div>

                <!-- Right Icons -->
                <!-- Right Icons -->
                 <div class="flex items-center space-x-6">
                    <button onclick="window.location.href='usuario.php'" class="text-white hover:text-red-500 transition-colors duration-200">
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
                <a href="masculino.php" class="block px-3 py-3 text-base font-medium text-white hover:bg-gray-900">Masculino</a>
                <a href="feminino.php" class="block px-3 py-3 text-base font-medium text-white hover:bg-gray-900">Feminino</a>
                <a href="infantil.php" class="block px-3 py-3 text-base font-medium text-white hover:bg-gray-900">Infantil</a>
                <a href="ajuda.php" class="block px-3 py-3 text-base font-medium text-white hover:bg-gray-900">Central de Ajuda</a>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="bg-black text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    <span class="text-white">MODA</span> 
                    <span class="text-red-500">FEMININA</span>
                </h1>
                <p class="text-gray-300 text-lg max-w-2xl mx-auto">
                    Descubra nossa coleção de roupas femininas com estilo, conforto e tendências atuais.
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
                    <form method="GET" action="feminino.php" id="filter-form">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-900">Filtros</h2>
                            <a href="feminino.php" class="text-red-500 text-sm hover:text-red-600">Limpar Tudo</a>
                        </div>

                        <!-- Anime Filter -->
                        <div class="mb-6 border-b border-gray-200 pb-4">
                            <h3 class="font-medium text-gray-900 py-2">Anime</h3>
                            <div class="mt-2 max-h-48 overflow-y-auto">
                                <?php foreach ($animes as $a): ?>
                                <label class="flex items-center py-1">
                                    <input type="radio" name="anime" value="<?= $a['slug'] ?>" 
                                           <?= $anime === $a['slug'] ? 'checked' : '' ?>
                                           class="form-radio h-4 w-4 text-red-500" 
                                           onchange="this.form.submit()">
                                    <span class="ml-2 text-sm text-gray-700"><?= htmlspecialchars($a['nome']) ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-6 border-b border-gray-200 pb-4">
                            <h3 class="font-medium text-gray-900 py-2">Preço</h3>
                            <div class="space-y-3">
                                <input type="range" name="preco_max" min="30" max="200" value="<?= $preco_max ?>" 
                                       class="w-full" id="price-range" onchange="updatePriceDisplay(this.value)">
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>R$30</span>
                                    <span id="price-value">até R$<?= $preco_max ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Color Filter -->
                        <div class="mb-6">
                            <h3 class="font-medium text-gray-900 py-2">Cor</h3>
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

                        <!-- Sort -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ordenar por:</label>
                            <select name="ordenar" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" onchange="this.form.submit()">
                                <option value="nome" <?= $ordenar === 'nome' ? 'selected' : '' ?>>Nome A-Z</option>
                                <option value="preco_asc" <?= $ordenar === 'preco_asc' ? 'selected' : '' ?>>Menor Preço</option>
                                <option value="preco_desc" <?= $ordenar === 'preco_desc' ? 'selected' : '' ?>>Maior Preço</option>
                                <option value="mais_novos" <?= $ordenar === 'mais_novos' ? 'selected' : '' ?>>Mais Recentes</option>
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
                            <?= $total_produtos ?> produto<?= $total_produtos !== 1 ? 's' : '' ?> feminino<?= $total_produtos !== 1 ? 's' : '' ?> encontrado<?= $total_produtos !== 1 ? 's' : '' ?>
                        </h2>
                        <p class="text-gray-600 text-sm">Camisetas de anime para mulheres</p>
                    </div>
                </div>

                <!-- Products Grid -->
                <?php if (empty($produtos)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum produto encontrado</h3>
                    <p class="text-gray-600">Tente ajustar os filtros ou cadastre produtos femininos no admin.</p>
                </div>
                <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($produtos as $produto): ?>
                    <div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow">
                        <div class="relative group">
                            <?php 
                            $imagem_src = '';
                            if ($produto['imagem_principal']) {
                                if (strpos($produto['imagem_principal'], 'admin/src/uploads/') !== false) {
                                    $imagem_src = '/ds-main/' . $produto['imagem_principal'];
                                } else {
                                    $imagem_src = '/ds-main/admin/src/uploads/' . basename($produto['imagem_principal']);
                                }
                            } else {
                                $imagem_src = '/ds-main/client/src/no-image.png';
                            }
                            ?>
                            
                            <img src="<?= $imagem_src ?>" 
                                 alt="<?= htmlspecialchars($produto['nome']) ?>" 
                                 class="w-full h-80 object-cover"
                                 onerror="this.src='/ds-main/client/src/no-image.png'">
                        </div>
                        
                        <div class="p-4">
                            <h3 class="font-medium text-gray-900 mb-1"><?= htmlspecialchars($produto['nome']) ?></h3>
                            <p class="text-gray-500 text-sm mb-3"><?= htmlspecialchars($produto['descricao']) ?></p>
                            <p class="text-gray-400 text-xs mb-2"><?= htmlspecialchars($produto['anime_nome']) ?></p>
                            
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
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script>
        function updatePriceDisplay(value) {
            document.getElementById('price-value').textContent = 'até R$' + value;
            document.getElementById('filter-form').submit();
        }

        document.querySelectorAll('.color-filter-btn').forEach(button => {
            button.addEventListener('click', function() {
                const color = this.dataset.color;
                document.getElementById('color-input').value = color;
                document.getElementById('filter-form').submit();
            });
        });
    </script>
</body>
</html>