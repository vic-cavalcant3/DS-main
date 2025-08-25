<?php
session_start();
require '../pages/utils/conexao.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Conectar ao banco de dados
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Buscar dados do usuário
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['usuario_id']);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        session_destroy();
        header('Location: login.php');
        exit();
    }
    
    // Buscar endereços do usuário
    $stmt = $pdo->prepare("SELECT * FROM enderecos WHERE usuario_id = :usuario_id ORDER BY principal DESC");
    $stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
    $stmt->execute();
    $enderecos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar pedidos do usuário
    $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE usuario_id = :usuario_id ORDER BY data_pedido DESC LIMIT 5");
    $stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
    $stmt->execute();
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Erro ao conectar com o banco de dados: " . $e->getMessage());
}

$page_title = "Minha Conta | Flamma";
$current_page = "usuario";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
        }
        .sidebar {
            transition: all 0.3s ease;
        }
        .sidebar-link {
            transition: all 0.3s ease;
        }
        .sidebar-link:hover {
            background-color: #f3f4f6;
            color: #ef4444;
        }
        .sidebar-link.active {
            background-color: #fee2e2;
            color: #ef4444;
            border-right: 3px solid #ef4444;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .avatar-upload {
            position: relative;
            cursor: pointer;
        }
        .avatar-upload:hover .avatar-overlay {
            opacity: 1;
        }
        .avatar-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
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
                    <a href="../../index.php" class="text-2xl font-bold text-gray-900 hover:opacity-90 transition-opacity">
                        <img src="../../client/src/Flamma-logo.png" alt="Flamma Index" class="h-10 md:h-12 w-auto">
                    </a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-6">
                    <a href="produtos.php" class="text-white hover:text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Produtos</a>
                    <a href="masculino.php" class="text-white hover:text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Masculino</a>
                    <a href="feminino.php" class="text-white hover:text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Feminino</a>
                    <a href="infantil.php" class="text-white hover:text-red-500 px-3 py-3 text-sm font-semibold transition-colors duration-200">Infantil</a>
                </div>

                <!-- Right Icons -->
                <div class="flex items-center space-x-6">
                    <a href="usuario.php" class="text-red-500 hover:text-red-500 transition-colors duration-200">
                        <i class="far fa-user text-xl"></i>
                    </a>
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
                <a href="produtos.php" class="block px-3 py-3 text-base font-medium text-white hover:bg-gray-900">Produtos</a>
                <a href="masculino.php" class="block px-3 py-3 text-base font-medium text-white hover:bg-gray-900">Masculino</a>
                <a href="feminino.php" class="block px-3 py-3 text-base font-medium text-white hover:bg-gray-900">Feminino</a>
                <a href="infantil.php" class="block px-3 py-3 text-base font-medium text-white hover:bg-gray-900">Infantil</a>
                <a href="usuario.php" class="block px-3 py-3 text-base font-medium text-white hover:bg-gray-900">Minha Conta</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col md:flex-row gap-8">
            <!-- Sidebar -->
            <div class="w-full md:w-1/4 lg:w-1/5">
                <div class="bg-white rounded-lg shadow-md p-6 sidebar">
                    <div class="text-center mb-6">
                        <div class="avatar-upload inline-block relative">
                            <img src="<?php echo '../uploads/avatars/' . $usuario['avatar']; ?>" 
                                 alt="Avatar" 
                                 class="w-24 h-24 rounded-full object-cover mx-auto border-2 border-gray-200">
                            <div class="avatar-overlay">
                                <i class="fas fa-camera text-white text-xl"></i>
                            </div>
                        </div>
                        <h2 class="text-xl font-bold mt-4"><?php echo htmlspecialchars($usuario['nome']); ?></h2>
                        <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($usuario['email']); ?></p>
                    </div>
                    
                    <nav class="space-y-2">
                        <a href="#dados-pessoais" class="sidebar-link block px-4 py-3 rounded-lg font-medium active" data-tab="dados-pessoais">
                            <i class="fas fa-user-circle mr-3"></i>Dados Pessoais
                        </a>
                        <a href="#enderecos" class="sidebar-link block px-4 py-3 rounded-lg font-medium" data-tab="enderecos">
                            <i class="fas fa-map-marker-alt mr-3"></i>Endereços
                        </a>
                        <a href="#pedidos" class="sidebar-link block px-4 py-3 rounded-lg font-medium" data-tab="pedidos">
                            <i class="fas fa-shopping-bag mr-3"></i>Meus Pedidos
                        </a>
                        <a href="#seguranca" class="sidebar-link block px-4 py-3 rounded-lg font-medium" data-tab="seguranca">
                            <i class="fas fa-lock mr-3"></i>Segurança
                        </a>
                        <a href="../actions/logout.php" class="sidebar-link block px-4 py-3 rounded-lg font-medium text-red-600">
                            <i class="fas fa-sign-out-alt mr-3"></i>Sair
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Content Area -->
            <div class="w-full md:w-3/4 lg:w-4/5">
                <!-- Dados Pessoais -->
                <div id="dados-pessoais" class="tab-content active bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-bold mb-6">Dados Pessoais</h2>
                    <form id="form-dados-pessoais" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome Completo</label>
                                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>
                            <div>
                                <label for="telefone" class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                                <input type="tel" id="telefone" name="telefone" value="<?php echo htmlspecialchars($usuario['telefone']); ?>" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>
                            <div>
                                <label for="data_nascimento" class="block text-sm font-medium text-gray-700 mb-1">Data de Nascimento</label>
                                <input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo $usuario['data_nascimento']; ?>" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>
                            <div>
                                <label for="cpf" class="block text-sm font-medium text-gray-700 mb-1">CPF</label>
                                <input type="text" id="cpf" name="cpf" value="<?php echo htmlspecialchars($usuario['cpf']); ?>" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-md font-medium">
                                Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Endereços -->
                <div id="enderecos" class="tab-content bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">Meus Endereços</h2>
                        <button id="btn-novo-endereco" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md font-medium">
                            <i class="fas fa-plus mr-2"></i>Novo Endereço
                        </button>
                    </div>

                    <div class="space-y-4">
                        <?php if (count($enderecos) > 0): ?>
                            <?php foreach ($enderecos as $endereco): ?>
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="font-semibold"><?php echo htmlspecialchars($endereco['logradouro']) . ', ' . htmlspecialchars($endereco['numero']); ?></h3>
                                            <p class="text-gray-600"><?php echo htmlspecialchars($endereco['complemento']); ?></p>
                                            <p class="text-gray-600"><?php echo htmlspecialchars($endereco['bairro']) . ' - ' . htmlspecialchars($endereco['cidade']) . '/' . htmlspecialchars($endereco['estado']); ?></p>
                                            <p class="text-gray-600">CEP: <?php echo htmlspecialchars($endereco['cep']); ?></p>
                                            <?php if ($endereco['principal']): ?>
                                                <span class="inline-block bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full mt-2">
                                                    Endereço Principal
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex space-x-2">
                                            <button class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <i class="fas fa-map-marker-alt text-gray-300 text-4xl mb-4"></i>
                                <p class="text-gray-600">Nenhum endereço cadastrado</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pedidos -->
                <div id="pedidos" class="tab-content bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-bold mb-6">Meus Pedidos</h2>
                    
                    <?php if (count($pedidos) > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="px-4 py-2 text-left">Nº do Pedido</th>
                                        <th class="px-4 py-2 text-left">Data</th>
                                        <th class="px-4 py-2 text-left">Status</th>
                                        <th class="px-4 py-2 text-left">Total</th>
                                        <th class="px-4 py-2 text-left">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pedidos as $pedido): ?>
                                        <tr class="border-b border-gray-200">
                                            <td class="px-4 py-3">#<?php echo htmlspecialchars($pedido['numero_pedido']); ?></td>
                                            <td class="px-4 py-3"><?php echo date('d/m/Y', strtotime($pedido['data_pedido'])); ?></td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 py-1 rounded-full text-xs 
                                                    <?php echo $pedido['status'] == 'entregue' ? 'bg-green-100 text-green-800' : 
                                                          ($pedido['status'] == 'enviado' ? 'bg-blue-100 text-blue-800' : 
                                                          ($pedido['status'] == 'processando' ? 'bg-yellow-100 text-yellow-800' : 
                                                          ($pedido['status'] == 'cancelado' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'))); ?>">
                                                    <?php echo ucfirst($pedido['status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?></td>
                                            <td class="px-4 py-3">
                                                <button class="text-red-600 hover:text-red-800">
                                                    <i class="fas fa-eye"></i> Ver Detalhes
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <i class="fas fa-shopping-bag text-gray-300 text-4xl mb-4"></i>
                            <p class="text-gray-600">Nenhum pedido realizado</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Segurança -->
                <div id="seguranca" class="tab-content bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-bold mb-6">Segurança</h2>
                    <form id="form-seguranca" class="space-y-6">
                        <div>
                            <label for="senha_atual" class="block text-sm font-medium text-gray-700 mb-1">Senha Atual</label>
                            <input type="password" id="senha_atual" name="senha_atual" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label for="nova_senha" class="block text-sm font-medium text-gray-700 mb-1">Nova Senha</label>
                            <input type="password" id="nova_senha" name="nova_senha" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label for="confirmar_senha" class="block text-sm font-medium text-gray-700 mb-1">Confirmar Nova Senha</label>
                            <input type="password" id="confirmar_senha" name="confirmar_senha" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-md font-medium">
                                Alterar Senha
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-black text-white pt-12 pb-6 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-8">
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider mb-4">FLAMMA</h3>
                    <p class="text-gray-400 text-sm">Camisetas premium de anime para verdadeiros fãs.</p>
                </div>

                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider mb-4">PRODUTOS</h3>
                    <ul class="space-y-2">
                        <li><a href="../pages/masculino.php" class="text-gray-400 hover:text-red-500 text-sm">Masculino</a></li>
                        <li><a href="../pages/feminino.php" class="text-gray-400 hover:text-red-500 text-sm">Feminino</a></li>
                        <li><a href="../pages/infantil.php" class="text-gray-400 hover:text-red-500 text-sm">Infantil</a></li>
                        <li><a href="../pages/produtos.php" class="text-gray-400 hover:text-red-500 text-sm">Todos os Produtos</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider mb-4">SUPORTE</h3>
                    <ul class="space-y-2">
                        <li><a href="../pages/ajuda.php" class="text-gray-400 hover:text-red-500 text-sm">Central de Ajuda</a></li>
                        <li><a href="../pages/trocas.php" class="text-gray-400 hover:text-red-500 text-sm">Trocas e Devoluções</a></li>
                        <li><a href="../pages/entregas.php" class="text-gray-400 hover:text-red-500 text-sm">Entregas</a></li>
                        <li><a href="../pages/contato.php" class="text-gray-400 hover:text-red-500 text-sm">Fale Conosco</a></li>
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

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });

        // Tab navigation
        document.querySelectorAll('.sidebar-link[data-tab]').forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.getAttribute('href') === '#') {
                    e.preventDefault();
                }
                
                // Remove active class from all links
                document.querySelectorAll('.sidebar-link').forEach(l => {
                    l.classList.remove('active');
                });
                
                // Add active class to clicked link
                this.classList.add('active');
                
                // Hide all tab contents
                document.querySelectorAll('.tab-content').forEach(tab => {
                    tab.classList.remove('active');
                });
                
                // Show selected tab content
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });

        // CPF mask
        const cpfInput = document.getElementById('cpf');
        if (cpfInput) {
            cpfInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 11) value = value.slice(0, 11);
                
                if (value.length > 9) {
                    value = value.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
                } else if (value.length > 6) {
                    value = value.replace(/^(\d{3})(\d{3})(\d{0,3})/, '$1.$2.$3');
                } else if (value.length > 3) {
                    value = value.replace(/^(\d{3})(\d{0,3})/, '$1.$2');
                }
                
                e.target.value = value;
            });
        }

        // Phone mask
        const telefoneInput = document.getElementById('telefone');
        if (telefoneInput) {
            telefoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 11) value = value.slice(0, 11);
                
                if (value.length > 10) {
                    value = value.replace(/^(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                } else if (value.length > 6) {
                    value = value.replace(/^(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
                } else if (value.length > 2) {
                    value = value.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
                } else if (value.length > 0) {
                    value = value.replace(/^(\d*)/, '($1');
                }
                
                e.target.value = value;
            });
        }
    </script>
</body>
</html>