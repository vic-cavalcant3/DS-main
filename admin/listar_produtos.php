<?php
require 'conexao.php';

try {
    $sql = "SELECT p.*, 
                   (SELECT url_imagem 
                    FROM imagens 
                    WHERE produto_id = p.id 
                    ORDER BY ordem ASC, id ASC 
                    LIMIT 1) AS imagem_principal
            FROM produtos p
            ORDER BY p.data_cadastro DESC";

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
    <title>Lista de Camisetas</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold mb-8 text-center">Camisetas Cadastradas</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($produtos as $produto): ?>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden transition-transform hover:scale-105">
                <?php if (!empty($produto['imagem_principal'])): ?>
                    <img src="<?= 'produtos/' . htmlspecialchars($produto['imagem_principal']) ?>" 
                         alt="<?= htmlspecialchars($produto['nome']) ?>" 
                         class="w-full h-64 object-cover">
                <?php else: ?>
                    <div class="w-full h-64 bg-gray-200 flex items-center justify-center">
                        <span class="text-gray-500">Sem imagem</span>
                    </div>
                <?php endif; ?>
                
                <div class="p-6">
                    <h3 class="font-bold text-xl mb-2"><?= htmlspecialchars($produto['nome']) ?></h3>
                    <p class="text-gray-600 text-sm mb-4"><?= htmlspecialchars($produto['tags']) ?></p>
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-red-600 text-lg">
                            R$ <?= number_format($produto['preco'], 2, ',', '.') ?>
                        </span>
                        <div class="flex space-x-2">
                            <a href="editar_produto.php?id=<?= $produto['id'] ?>" 
                               class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                                Editar
                            </a>
                            <a href="excluir_produto.php?id=<?= $produto['id'] ?>" 
                               class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition"
                               onclick="return confirm('Tem certeza que deseja excluir?')">
                                Excluir
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-10">
            <a href="cadastrar_produto.php" 
               class="inline-block bg-green-600 text-white px-8 py-3 rounded-lg hover:bg-green-700 transition text-lg font-semibold">
                + Nova Camiseta
            </a>
        </div>
    </div>
</body>
</html>