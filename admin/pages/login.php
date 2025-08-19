<?php
session_start();
require '../conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];

    $sql = "SELECT * FROM admins WHERE usuario = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($senha, $admin['senha'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_nome'] = $admin['nome'];
        header("Location: cadastrar_produto.php");
        exit;
    } else {
        $erro = "Usuário ou senha inválidos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login - Flamma Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">Flamma Admin</h1>

        <?php if (isset($erro)): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-gray-700">Usuário</label>
                <input type="text" name="usuario" required 
                    class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-red-500">
            </div>
            <div>
                <label class="block text-gray-700">Senha</label>
                <input type="password" name="senha" required 
                    class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-red-500">
            </div>
            <button type="submit" 
                class="w-full bg-red-600 hover:bg-red-700 text-white p-3 rounded-lg font-semibold">
                Entrar
            </button>
        </form>
    </div>
</body>
</html>
