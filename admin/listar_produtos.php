<?php
require 'conexao.php';

try {
    $sql = "SELECT p.*, 
                   (SELECT url_imagem FROM imagens WHERE produto_id = p.id ORDER BY ordem LIMIT 1) as imagem_principal
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
        <h1 class="text-2xl font-bold mb-6">Camisetas Cadastradas</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($produtos as $produto): ?>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <?php if ($produto['imagem_principal']): ?>
                <img src="<?= htmlspecialchars($produto['imagem_principal']) ?>" 
                     alt="<?= htmlspecialchars($produto['nome']) ?>" 
                     class="w-full h-48 object-cover">
                <?php else: ?>
                <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                    <span class="text-gray-500">Sem imagem</span>
                </div>
                <?php endif; ?>
                
                <div class="p-4">
                    <h3 class="font-bold text-lg"><?= htmlspecialchars($produto['nome']) ?></h3>
                    <p class="text-gray-600 text-sm mt-1"><?= htmlspecialchars($produto['tags']) ?></p>
                    <div class="mt-4 flex justify-between items-center">
                        <span class="font-bold text-red-500">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></span>
                        <div class="flex space-x-2">
                            <a href="editar_produto.php?id=<?= $produto['id'] ?>" 
                               class="bg-blue-500 text-white px-3 py-1 rounded text-sm">
                                Editar
                            </a>
                            <a href="excluir_produtos.php?id=<?= $produto['id'] ?>" 
                               class="bg-red-500 text-white px-3 py-1 rounded text-sm"
                               onclick="return confirm('Tem certeza que deseja excluir?')">
                                Excluir
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <a href="cadastrar_produto.php" 
           class="inline-block mt-6 bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
            + Nova Camiseta
        </a>
    </div>
</body>
</html>