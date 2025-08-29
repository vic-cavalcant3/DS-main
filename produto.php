<?php
require 'conexao.php';

// Verificar se o ID do produto foi fornecido
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

// Buscar todas as imagens do produto
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
$imagem_principal = null;
if (!empty($imagens_por_cor)) {
    $primeira_cor = array_key_first($imagens_por_cor);
    $imagem_principal = $imagens_por_cor[$primeira_cor][0] ?? null;
}

// Buscar estoque disponível
$sql_estoque = "SELECT tamanho, quantidade FROM estoque WHERE produto_id = ? AND quantidade > 0";
$stmt_estoque = $pdo->prepare($sql_estoque);
$stmt_estoque->execute([$produto_id]);
$estoque = $stmt_estoque->fetchAll(PDO::FETCH_ASSOC);

// Buscar produtos relacionados (mesma categoria)
$sql_relacionados = "
SELECT 
    p.*,
    c.nome AS categoria_nome,
    (
        SELECT url_imagem
        FROM imagens
        WHERE produto_id = p.id
        ORDER BY ordem ASC
        LIMIT 1
    ) AS primeira_imagem
FROM produtos p
LEFT JOIN categorias c ON p.categoria_id = c.id
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
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            scroll-behavior: smooth;
            font-size: 0.95rem;
        }
        
        .product-image {
            transition: all 0.3s ease;
        }
        
        .thumbnail {
            transition: all 0.2s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }
        
        .thumbnail:hover {
            transform: scale(1.05);
            border-color: #ef4444;
        }
        
        .thumbnail.active {
            border: 2px solid #ef4444;
            transform: scale(1.05);
        }
        
        .color-option {
            transition: all 0.2s ease;
            cursor: pointer;
            position: relative;
        }
        
        .color-option:hover {
            transform: scale(1.1);
        }
        
        .color-option.selected {
            border: 2px solid #ef4444 !important;
            transform: scale(1.15);
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.3);
        }
        
        .color-option.selected::after {
            content: '✓';
            position: absolute;
            color: white;
            font-size: 10px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-shadow: 0 0 2px rgba(0, 0, 0, 0.5);
        }
        
        .color-option.white.selected::after {
            color: #000;
            text-shadow: 0 0 2px rgba(255, 255, 255, 0.5);
        }
        
        .size-option {
            transition: all 0.2s ease;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
        }
        
        .size-option:hover {
            background-color: #f3f4f6;
            transform: translateY(-2px);
        }
        
        .size-option.selected {
            background-color: #ef4444;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.3);
        }
        
        .size-option.disabled {
            opacity: 0.4;
            cursor: not-allowed;
            transform: none !important;
        }
        
        .zoom-container {
            position: relative;
            overflow: hidden;
            cursor: zoom-in;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .zoom-image {
            transition: transform 0.5s ease;
        }
        
        .zoom-container:hover .zoom-image {
            transform: scale(1.5);
        }
        
        .quantity-btn {
            transition: all 0.2s ease;
            padding: 0.4rem 0.75rem;
        }
        
        .quantity-btn:hover {
            background-color: #f3f4f6;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(239, 68, 68, 0.25);
            border: none;
            font-weight: 600;
            letter-spacing: 0.025em;
            padding: 0.75rem 1.5rem;
            font-size: 0.95rem;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(239, 68, 68, 0.3);
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
            font-weight: 600;
            letter-spacing: 0.025em;
            padding: 0.75rem 1.5rem;
            font-size: 0.95rem;
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            background: linear-gradient(135deg, #374151 0%, #4b5563 100%);
        }
        
        .related-product {
            transition: all 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .related-product:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        
        .detail-item {
            transition: all 0.2s ease;
            padding: 0.5rem 0;
            font-size: 0.95rem;
        }
        
        .detail-item:hover {
            background-color: #f9fafb;
            padding-left: 0.5rem;
            border-radius: 6px;
        }
        
        .breadcrumb-item {
            transition: all 0.2s ease;
            font-size: 0.9rem;
        }
        
        .breadcrumb-item:hover {
            color: #ef4444;
        }
        
        .discount-badge {
            animation: pulse 2s infinite;
            font-size: 0.8rem;
            padding: 0.2rem 0.6rem;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .product-title {
            background: linear-gradient(90deg, #1f2937, #374151);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 1.75rem;
        }
        
        .section-title {
            position: relative;
            display: inline-block;
            font-size: 1.1rem;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -6px;
            left: 0;
            width: 30px;
            height: 2px;
            background-color: #ef4444;
            border-radius: 2px;
        }
        
        .nav-item {
            font-size: 0.95rem;
        }
        
        .footer-heading {
            font-size: 0.95rem;
        }
        
        .footer-link {
            font-size: 0.9rem;
        }

                /* Estilos dos botões melhorados */
        .btn-custom {
            border: none;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 10px 20px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
        }
        
        .btn-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.5s ease;
        }
        
        .btn-custom:hover::before {
            left: 100%;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
            flex: 2;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        }
        
        .btn-secondary-custom {
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
            flex: 1;
        }
        
        .btn-secondary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            background: linear-gradient(135deg, #374151 0%, #4b5563 100%);
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin: 20px 0;
        }
        
        .quantity-btn {
            width: 34px;
            height: 34px;
            /* border-radius: 50%; */
            /* border: 1px solid #e5e7eb; */
            background: #f9fafb;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 600;
        }
        
        .quantity-btn:hover {
            background: #f3f4f6;
            border-color: #d1d5db;
            transform: scale(1.1);
        }
        
        .quantity-value {
            font-weight: 600;
            min-width: 30px;
            text-align: center;
            font-size: 1.1rem;
        }
        
        .label {
            font-size: 0.9rem;
            color: #6b7280;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        /* Efeito de clique */
        .btn-custom:active {
            transform: translateY(1px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
        }

        .buttons-container {
            display: flex;
            gap: 12px;
            margin-top: 25px;
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-black sticky top-0 z-50 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <!-- Mobile menu button -->
                <div class="flex items-center md:hidden">
                    <button id="mobile-menu-button" class="text-white hover:text-red-500">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                </div>

                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center justify-center">
                    <a href="/ds-main/index.php" class="text-2xl font-bold text-gray-900 hover:opacity-90 transition-opacity">
                        <img src="/ds-main/client/src/Flamma-logo.png" alt="Flamma Index" class="h-8 md:h-10 w-auto">
                    </a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-5">
                    <a href="/ds-main/client/pages/produtos.php"
                        class="text-white hover:text-red-500 px-3 py-2 text-sm font-semibold nav-item">Produtos</a>
                    <a href="/ds-main/client/pages/masculino.php"
                        class="text-white hover:text-red-500 px-3 py-2 text-sm font-semibold nav-item">Masculino</a>
                    <a href="/ds-main/client/pages/feminino.php"
                        class="text-white hover:text-red-500 px-3 py-2 text-sm font-semibold nav-item">Feminino</a>
                    <a href="/ds-main/client/pages/infantil.php"
                        class="text-white hover:text-red-500 px-3 py-2 text-sm font-semibold nav-item">Infantil</a>
                    <a href="/ds-main/client/pages/ajuda.php"
                        class="text-white hover:text-red-500 px-3 py-2 text-sm font-semibold nav-item">Ajuda</a>
                </div>

                <!-- Right Icons -->
                <div class="flex items-center space-x-5">
                    <button onclick="window.location.href='/ds-main/client/pages/usuario.php'" class="text-white hover:text-red-500 transition-colors duration-200">
                        <i class="far fa-user text-lg"></i>
                    </button>
                    
                    <button id="cart-button"
                        class="text-white hover:text-red-500 transition-colors duration-200 relative">
                        <i class="fas fa-shopping-bag text-lg"></i>
                        <span id="cart-count"
                            class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">0</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-black border-t border-gray-800">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="/ds-main/client/pages/produtos.php" class="block px-3 py-2 text-sm font-medium text-white hover:bg-gray-900">Produtos</a>
                <a href="/ds-main/client/pages/masculino.php" class="block px-3 py-2 text-sm font-medium text-white hover:bg-gray-900">Masculino</a>
                <a href="/ds-main/client/pages/feminino.php" class="block px-3 py-2 text-sm font-medium text-white hover:bg-gray-900">Feminino</a>
                <a href="/ds-main/client/pages/infantil.php" class="block px-3 py-2 text-sm font-medium text-white hover:bg-gray-900">Infantil</a>
                <a href="/ds-main/client/pages/ajuda.php" class="block px-3 py-2 text-sm font-medium text-white hover:bg-gray-900">Ajuda</a>
            </div>
        </div>
    </nav>

    <!-- Breadcrumb -->
    <nav class="bg-white border-b border-gray-200 py-3">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <ol class="flex items-center space-x-2 text-sm">
                <li><a href="/ds-main/index.php" class="text-gray-500 hover:text-red-500 breadcrumb-item">Início</a></li>
                <li><i class="fas fa-chevron-right text-gray-400 text-xs"></i></li>
                <li><a href="/ds-main/client/pages/produtos.php" class="text-gray-500 hover:text-red-500 breadcrumb-item">Produtos</a></li>
                <?php if ($produto['categoria_nome']): ?>
                <li><i class="fas fa-chevron-right text-gray-400 text-xs"></i></li>
                <li><a href="/ds-main/client/pages/<?= strtolower($produto['categoria_nome']) ?>.php" class="text-gray-500 hover:text-red-500 breadcrumb-item"><?= htmlspecialchars($produto['categoria_nome']) ?></a></li>
                <?php endif; ?>
                <li><i class="fas fa-chevron-right text-gray-400 text-xs"></i></li>
                <li class="text-gray-900 font-medium truncate max-w-xs text-sm"><?= htmlspecialchars($produto['nome']) ?></li>
            </ol>
        </div>
    </nav>

    <!-- Product Details -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <!-- Images -->
            <div class="space-y-5">
                <!-- Main Image -->
<div class="zoom-container w-98 aspect-square bg-gray-100 rounded-lg overflow-hidden">
                    <?php
                        $src = '/ds-main/client/src/no-image.png';
                        if ($imagem_principal) {
                            if (strpos($imagem_principal, 'admin/src/uploads/') !== false) {
                                $src = '/ds-main/' . $imagem_principal;
                            } else {
                                $src = '/ds-main/admin/src/uploads/' . basename($imagem_principal);
                            }
                        }
                    ?>
                    <img id="main-image" 
                         src="<?= htmlspecialchars($src) ?>"
                         alt="<?= htmlspecialchars($produto['nome']) ?>"
                         class="zoom-image w-full h-full object-cover"
                         onerror="this.src='/ds-main/client/src/no-image.png'">
                </div>

                <!-- Thumbnails -->
                <?php if (!empty($imagens_por_cor)): ?>
                <div class="grid grid-cols-4 gap-2">
                    <?php 
                    $thumbnail_index = 0;
                    foreach ($imagens_por_cor as $cor => $imgs): 
                        foreach ($imgs as $img):
                            $thumb_src = '/ds-main/client/src/no-image.png';
                            if ($img) {
                                if (strpos($img, 'admin/src/uploads/') !== false) {
                                    $thumb_src = '/ds-main/' . $img;
                                } else {
                                    $thumb_src = '/ds-main/admin/src/uploads/' . basename($img);
                                }
                            }
                    ?>
                    <div class="thumbnail aspect-square bg-gray-100 rounded-lg overflow-hidden <?= $thumbnail_index === 0 ? 'active' : '' ?>"
                         onclick="changeMainImage('<?= htmlspecialchars($thumb_src) ?>', this)">
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
            <div class="space-y-6">
                <!-- Title and Price -->
                <div>
                    <h1 class="text-2xl font-bold product-title mb-2"><?= htmlspecialchars($produto['nome']) ?></h1>
                    <?php if ($produto['anime_nome']): ?>
                    <p class="text-gray-600 mb-3 text-base"><?= htmlspecialchars($produto['anime_nome']) ?></p>
                    <?php endif; ?>
                    
                    <div class="flex items-center space-x-3 mb-4">
                        <?php if ($produto['preco_original'] && $produto['preco_original'] > $produto['preco']): ?>
                        <span class="text-lg text-gray-400 line-through">
                            R$<?= number_format($produto['preco_original'], 2, ',', '.') ?>
                        </span>
                        <?php endif; ?>
                        <span class="text-2xl font-bold text-gray-900">
                            R$<?= number_format($produto['preco'], 2, ',', '.') ?>
                        </span>
                        <?php if ($produto['preco_original'] && $produto['preco_original'] > $produto['preco']): ?>
                        <span class="bg-red-500 text-white px-2 py-1 rounded-full text-xs font-bold discount-badge">
                            <?= round((($produto['preco_original'] - $produto['preco']) / $produto['preco_original']) * 100) ?>% OFF
                        </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Description -->
                <?php if ($produto['descricao']): ?>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-base font-semibold mb-2 section-title">Descrição</h3>
                    <p class="text-gray-600 leading-relaxed text-sm"><?= nl2br(htmlspecialchars($produto['descricao'])) ?></p>
                </div>
                <?php endif; ?>

                <!-- Colors -->
                <?php if (!empty($imagens_por_cor) && count($imagens_por_cor) > 1): ?>
                <div>
                    <h3 class="text-base font-semibold mb-3 section-title">Cores</h3>
                    <div class="flex space-x-3">
                        <?php 
                        $cor_index = 0;
                        foreach ($imagens_por_cor as $cor => $imgs): 
                            $color_class = '';
                            $color_name = '';
                            switch(strtolower($cor)) {
                                case 'preto': case 'black': 
                                    $color_class = 'bg-black'; 
                                    $color_name = 'Preto';
                                    break;
                                case 'branco': case 'white': 
                                    $color_class = 'bg-white border-2 border-gray-300 white'; 
                                    $color_name = 'Branco';
                                    break;
                                case 'azul': case 'blue': 
                                    $color_class = 'bg-blue-500'; 
                                    $color_name = 'Azul';
                                    break;
                                case 'vermelho': case 'red': 
                                    $color_class = 'bg-red-500'; 
                                    $color_name = 'Vermelho';
                                    break;
                                case 'verde': case 'green': 
                                    $color_class = 'bg-green-500'; 
                                    $color_name = 'Verde';
                                    break;
                                default: 
                                    $color_class = 'bg-gray-400'; 
                                    $color_name = $cor;
                                    break;
                            }
                        ?>
                        <div class="color-option w-10 h-10 rounded-full <?= $color_class ?> <?= $cor_index === 0 ? 'selected' : '' ?>"
                             data-color="<?= htmlspecialchars($cor) ?>"
                             title="<?= htmlspecialchars($color_name) ?>">
                        </div>
                        <?php 
                            $cor_index++;
                        endforeach; 
                        ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Sizes -->
                <?php if (!empty($estoque)): ?>
                <div>
                    <h3 class="text-base font-semibold mb-3 section-title">Tamanhos</h3>
                    <div class="grid grid-cols-6 gap-2">
                        <?php 
                        $tamanhos_disponiveis = array_column($estoque, 'tamanho');
                        $todos_tamanhos = ['PP', 'P', 'M', 'G', 'GG', 'XG'];
                        foreach ($todos_tamanhos as $tamanho):
                            $disponivel = in_array($tamanho, $tamanhos_disponiveis);
                        ?>
                        <button class="size-option border border-gray-300 text-center font-medium rounded <?= !$disponivel ? 'disabled' : '' ?>"
                                data-size="<?= $tamanho ?>"
                                <?= !$disponivel ? 'disabled' : '' ?>>
                            <?= $tamanho ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Add to Cart -->
                <div class="space-y-4 pt-3">
                    <div class="flex items-center space-x-3">
                        <span class="text-gray-700 font-medium text-sm">Quantidade:</span>
                        <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                            <button id="decrease-qty" class="quantity-btn px-3 py-1">-</button>
                            <span id="quantity" class="px-3 py-1 border-l border-r border-gray-300 font-medium text-sm">1</span>
                            <button id="increase-qty" class="quantity-btn px-3 py-1">+</button>
                        </div>
                    </div>

                    <div class="buttons-container">
                        <button id="add-to-cart-btn"
                                class="btn-custom btn-primary-custom"
                                data-id="<?= $produto['id'] ?>"
                                data-name="<?= htmlspecialchars($produto['nome']) ?>"
                                data-price="<?= $produto['preco'] ?>"
                                data-image="<?= htmlspecialchars($src) ?>">
                            <i class="fas fa-shopping-cart mr-2"></i>
                            Adicionar
                        </button>
                        <button class="btn-custom btn-secondary-custom">
                            Comprar
                        </button>
                    </div>
                </div>

                <!-- Product Details -->
                <div class="border-t border-gray-200 pt-4">
                    <h3 class="text-base font-semibold mb-3 section-title">Detalhes</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li class="detail-item text-sm"><i class="fas fa-check text-green-500 mr-2"></i>Tecido 100% algodão</li>
                        <li class="detail-item text-sm"><i class="fas fa-check text-green-500 mr-2"></i>Estampa de alta qualidade</li>
                        <li class="detail-item text-sm"><i class="fas fa-check text-green-500 mr-2"></i>Lavável em máquina</li>
                        <li class="detail-item text-sm"><i class="fas fa-truck text-blue-500 mr-2"></i>Frete grátis</li>
                        <?php if ($produto['categoria_nome']): ?>
                        <li class="detail-item text-sm"><i class="fas fa-tag text-purple-500 mr-2"></i>Categoria: <?= htmlspecialchars($produto['categoria_nome']) ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($produtos_relacionados)): ?>
    <section class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-xl font-bold text-gray-900 mb-8 text-center section-title mx-auto">Relacionados</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($produtos_relacionados as $relacionado): ?>
                <div class="related-product bg-white rounded-lg overflow-hidden shadow-md">
                    <div class="aspect-square bg-gray-100 overflow-hidden">
                        <?php
                            $rel_src = '/ds-main/client/src/no-image.png';
                            if ($relacionado['primeira_imagem']) {
                                if (strpos($relacionado['primeira_imagem'], 'admin/src/uploads/') !== false) {
                                    $rel_src = '/ds-main/' . $relacionado['primeira_imagem'];
                                } else {
                                    $rel_src = '/ds-main/admin/src/uploads/' . basename($relacionado['primeira_imagem']);
                                }
                            }
                        ?>
                        <a href="/ds-main/produto/<?= $relacionado['id'] ?>">
                            <img src="<?= htmlspecialchars($rel_src) ?>"
                                 alt="<?= htmlspecialchars($relacionado['nome']) ?>"
                                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-500"
                                 onerror="this.src='/ds-main/client/src/no-image.png'">
                        </a>
                    </div>
                    <div class="p-4">
                        <a href="/ds-main/produto/<?= $relacionado['id'] ?>">
                            <h3 class="font-medium text-gray-900 hover:text-red-600 transition-colors duration-200 text-sm mb-1"><?= htmlspecialchars($relacionado['nome']) ?></h3>
                        </a>
                        <div class="mt-2">
                            <?php if ($relacionado['preco_original'] && $relacionado['preco_original'] > $relacionado['preco']): ?>
                            <span class="text-gray-400 text-xs line-through">
                                R$<?= number_format($relacionado['preco_original'], 2, ',', '.') ?>
                            </span>
                            <?php endif; ?>
                            <span class="font-bold text-gray-900 block text-base">
                                R$<?= number_format($relacionado['preco'], 2, ',', '.') ?>
                            </span>
                        </div>
                        <button class="mt-3 w-full bg-gray-900 text-white py-1.5 rounded text-xs hover:bg-red-600 transition-colors duration-300 font-medium">
                            Adicionar
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="bg-black text-white pt-10 pb-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider mb-3 footer-heading">FLAMMA</h3>
                    <p class="text-gray-400 text-xs">Camisetas premium de anime para verdadeiros fãs.</p>
                </div>

                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider mb-3 footer-heading">PRODUTOS</h3>
                    <ul class="space-y-1">
                        <li><a href="/ds-main/client/pages/masculino.php" class="text-gray-400 hover:text-red-500 text-xs footer-link">Masculino</a></li>
                        <li><a href="/ds-main/client/pages/feminino.php" class="text-gray-400 hover:text-red-500 text-xs footer-link">Feminino</a></li>
                        <li><a href="/ds-main/client/pages/infantil.php" class="text-gray-400 hover:text-red-500 text-xs footer-link">Infantil</a></li>
                        <li><a href="/ds-main/client/pages/produtos.php" class="text-gray-400 hover:text-red-500 text-xs footer-link">Todos</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider mb-3 footer-heading">SUPORTE</h3>
                    <ul class="space-y-1">
                        <li><a href="/ds-main/client/pages/ajuda.php" class="text-gray-400 hover:text-red-500 text-xs footer-link">Ajuda</a></li>
                        <li><a href="/ds-main/client/pages/ajuda.php#exchanges" class="text-gray-400 hover:text-red-500 text-xs footer-link">Trocas</a></li>
                        <li><a href="/ds-main/client/pages/ajuda.php#delivery" class="text-gray-400 hover:text-red-500 text-xs footer-link">Entregas</a></li>
                        <li><a href="/ds-main/client/pages/contato.php" class="text-gray-400 hover:text-red-500 text-xs footer-link">Contato</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider mb-3 footer-heading">REDES</h3>
                    <div class="flex space-x-3">
                        <a href="#" class="text-gray-400 hover:text-red-500"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-gray-400 hover:text-red-500"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-red-500"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-gray-400 hover:text-red-500"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-4 text-center">
                <p class="text-gray-400 text-xs">© 2025 Flamma. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Shopping Cart Sidebar -->
    <div id="cart-sidebar" class="fixed inset-y-0 right-0 w-full md:w-96 bg-white shadow-xl transform translate-x-full transition-transform duration-300 ease-in-out z-50 overflow-y-auto">
        <div class="p-5">
            <div class="flex justify-between items-center mb-5">
                <h2 class="text-lg font-bold">Carrinho</h2>
                <button id="close-cart" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="cart-items" class="space-y-3">
                <!-- Cart items will be added here dynamically -->
            </div>
            <div class="mt-5 border-t border-gray-200 pt-3">
                <div class="flex justify-between text-base font-semibold mb-3">
                    <span>Total:</span>
                    <span id="cart-total">R$0,00</span>
                </div>
                <button class="w-full bg-red-600 text-white py-2 rounded text-sm hover:bg-red-700 transition duration-300">
                    Finalizar Compra
                </button>
            </div>
        </div>
    </div>

    <script type="application/json" id="product-images-data">
        <?= json_encode($imagens_por_cor) ?>
    </script>

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
                cartItems.innerHTML = '<p class="text-gray-500 text-center py-6 text-sm">Seu carrinho está vazio</p>';
            } else {
                cart.forEach((item, index) => {
                    total += item.price * item.quantity;
                    
                    const cartItem = document.createElement('div');
                    cartItem.className = 'flex items-center space-x-3 border-b border-gray-200 pb-3';
                    cartItem.innerHTML = `
                        <img src="${item.image}" alt="${item.name}" class="w-12 h-12 object-cover rounded">
                        <div class="flex-1">
                            <h4 class="text-gray-900 font-medium text-sm">${item.name}</h4>
                            <p class="text-gray-500 text-xs">R${item.price.toFixed(2)}</p>
                            <div class="flex items-center mt-1 space-x-1">
                                <button class="decrease-qty bg-gray-200 px-1.5 py-0.5 rounded text-xs" data-index="${index}">-</button>
                                <span class="text-gray-700 text-sm">${item.quantity}</span>
                                <button class="increase-qty bg-gray-200 px-1.5 py-0.5 rounded text-xs" data-index="${index}">+</button>
                            </div>
                        </div>
                        <button class="remove-item text-red-500 hover:text-red-700 text-sm" data-index="${index}">
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

        // Cart sidebar controls
        cartButton.addEventListener('click', () => {
            cartSidebar.classList.remove('translate-x-full');
        });

        closeCart.addEventListener('click', () => {
            cartSidebar.classList.add('translate-x-full');
        });

        // Image gallery functionality
        function changeMainImage(src, thumbnail) {
            const mainImage = document.getElementById('main-image');
            mainImage.src = src;
            
            // Update active thumbnail
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });
            thumbnail.classList.add('active');
        }

        // Color selection
        const imagesData = JSON.parse(document.getElementById('product-images-data').textContent);
        
        document.querySelectorAll('.color-option').forEach(colorOption => {
            colorOption.addEventListener('click', function() {
                const selectedColor = this.dataset.color;
                
                // Remove previous selection
                document.querySelectorAll('.color-option').forEach(option => {
                    option.classList.remove('selected');
                });
                
                // Add selection to clicked color
                this.classList.add('selected');
                
                // Update main image if color has images
                if (imagesData[selectedColor] && imagesData[selectedColor].length > 0) {
                    let newSrc = imagesData[selectedColor][0];
                    if (newSrc.indexOf('admin/src/uploads/') !== -1) {
                        newSrc = '/ds-main/' + newSrc;
                    } else {
                        newSrc = '/ds-main/admin/src/uploads/' + newSrc.split('/').pop();
                    }
                    
                    const mainImage = document.getElementById('main-image');
                    mainImage.src = newSrc;
                    
                    // Update thumbnails for this color
                    const thumbnailContainer = document.querySelector('.grid.grid-cols-4');
                    if (thumbnailContainer) {
                        thumbnailContainer.innerHTML = '';
                        imagesData[selectedColor].forEach((img, index) => {
                            let imgSrc = img;
                            if (img.indexOf('admin/src/uploads/') !== -1) {
                                imgSrc = '/ds-main/' + img;
                            } else {
                                imgSrc = '/ds-main/admin/src/uploads/' + img.split('/').pop();
                            }
                            
                            const thumbDiv = document.createElement('div');
                            thumbDiv.className = `thumbnail aspect-square bg-gray-100 rounded-lg overflow-hidden ${index === 0 ? 'active' : ''}`;
                            thumbDiv.onclick = () => changeMainImage(imgSrc, thumbDiv);
                            thumbDiv.innerHTML = `<img src="${imgSrc}" alt="Thumbnail" class="w-full h-full object-cover" onerror="this.src='/ds-main/client/src/no-image.png'">`;
                            thumbnailContainer.appendChild(thumbDiv);
                        });
                    }
                }
            });
        });

        // Size selection
        document.querySelectorAll('.size-option').forEach(sizeOption => {
            sizeOption.addEventListener('click', function() {
                if (this.disabled) return;
                
                // Remove previous selection
                document.querySelectorAll('.size-option').forEach(option => {
                    option.classList.remove('selected');
                });
                
                // Add selection to clicked size
                this.classList.add('selected');
            });
        });

        // Quantity controls
        let quantity = 1;
        const quantityElement = document.getElementById('quantity');
        const decreaseBtn = document.getElementById('decrease-qty');
        const increaseBtn = document.getElementById('increase-qty');

        decreaseBtn.addEventListener('click', function() {
            if (quantity > 1) {
                quantity--;
                quantityElement.textContent = quantity;
            }
        });

        increaseBtn.addEventListener('click', function() {
            quantity++;
            quantityElement.textContent = quantity;
        });

        // Add to cart functionality
        document.getElementById('add-to-cart-btn').addEventListener('click', function() {
            // Check if size is selected (if sizes are available)
            const sizeOptions = document.querySelectorAll('.size-option');
            const selectedSize = document.querySelector('.size-option.selected');
            
            if (sizeOptions.length > 0 && !selectedSize) {
                alert('Por favor, selecione um tamanho');
                return;
            }

            const id = this.dataset.id;
            const name = this.dataset.name;
            const price = parseFloat(this.dataset.price);
            const image = this.dataset.image;
            const selectedColor = document.querySelector('.color-option.selected');
            const color = selectedColor ? selectedColor.dataset.color : 'default';
            const size = selectedSize ? selectedSize.dataset.size : '';

            // Create product name with variations
            let productName = name;
            if (color && color !== 'default') {
                productName += ` - ${color}`;
            }
            if (size) {
                productName += ` - ${size}`;
            }

            // Check if exact product (with same color and size) exists in cart
            const productKey = `${id}-${color}-${size}`;
            const existingIndex = cart.findIndex(item => item.key === productKey);
            
            if (existingIndex >= 0) {
                cart[existingIndex].quantity += quantity;
            } else {
                cart.push({ 
                    id, 
                    name: productName, 
                    price, 
                    image, 
                    quantity: quantity,
                    key: productKey,
                    color,
                    size
                });
            }

            updateCartDisplay();
            cartSidebar.classList.remove('translate-x-full');
            
            // Reset quantity to 1
            quantity = 1;
            quantityElement.textContent = quantity;
            
            // Show success message
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check mr-1"></i>Adicionado!';
            this.disabled = true;
            
            setTimeout(() => {
                this.innerHTML = originalText;
                this.disabled = false;
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


        // Pegar dados das imagens por cor
const imagensPorCor = JSON.parse(document.getElementById('product-images-data').textContent);

// Selecionar os botões de cor
const colorOptions = document.querySelectorAll('.color-option');
const mainImage = document.getElementById('main-image');
const thumbnailsContainer = document.querySelector('.grid.grid-cols-4'); // container das miniaturas

colorOptions.forEach(option => {
    option.addEventListener('click', () => {
        // Remove "selected" das outras bolinhas
        colorOptions.forEach(o => o.classList.remove('selected'));
        option.classList.add('selected');

        const cor = option.dataset.color.toLowerCase();
        const imagens = imagensPorCor[cor];

        if (imagens && imagens.length > 0) {
            // Troca a imagem principal
            let src = imagens[0];
            if (!src.includes('admin/src/uploads/')) {
                src = '/ds-main/admin/src/uploads/' + src.split('/').pop();
            } else {
                src = '/ds-main/' + src;
            }
            mainImage.src = src;

            // Limpa miniaturas antigas
            thumbnailsContainer.innerHTML = '';

            // Renderiza novas miniaturas
            imagens.forEach((img, idx) => {
                let thumbSrc = img;
                if (!thumbSrc.includes('admin/src/uploads/')) {
                    thumbSrc = '/ds-main/admin/src/uploads/' + thumbSrc.split('/').pop();
                } else {
                    thumbSrc = '/ds-main/' + thumbSrc;
                }

                const thumbDiv = document.createElement('div');
                thumbDiv.className = `thumbnail aspect-square bg-gray-100 rounded-lg overflow-hidden ${idx === 0 ? 'active' : ''}`;
                thumbDiv.innerHTML = `
                    <img src="${thumbSrc}" class="w-full h-full object-cover" 
                         onerror="this.src='/ds-main/client/src/no-image.png'">
                `;

                thumbDiv.addEventListener('click', () => {
                    mainImage.src = thumbSrc;
                    document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
                    thumbDiv.classList.add('active');
                });

                thumbnailsContainer.appendChild(thumbDiv);
            });
        }
    });
});

    </script>
</body>
</html>