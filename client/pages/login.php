<?php
session_start();
require '../pages/utils/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $usuario = $stmt->fetch();
        
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            
            // Atualizar último login
            $stmt = $pdo->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = :id");
            $stmt->bindParam(':id', $usuario['id']);
            $stmt->execute();
            
            header('Location: usuario.php');
            exit();
        } else {
            $erro = "E-mail ou senha inválidos";
        }
    } catch (PDOException $e) {
        $erro = "Erro ao conectar com o banco de dados";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Flamma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: 100vh;
        }
        
        .card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.85);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(239, 68, 68, 0.25);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(239, 68, 68, 0.3);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .input-field {
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }
        
        .input-field:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
        }
        
        .flamma-logo {
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
        }
        
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
            backdrop-filter: blur(5px);
        }
        
        .overlay.active {
            display: flex;
            opacity: 1;
        }
        
        .overlay-content {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            margin: auto;
            padding: 2.5rem;
            border-radius: 20px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            transform: translateY(-50px) scale(0.95);
            transition: transform 0.4s ease;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            position: relative;
        }
        
        .overlay.active .overlay-content {
            transform: translateY(0) scale(1);
        }
        
        .close-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #64748b;
            font-size: 1.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .close-btn:hover {
            background: #e2e8f0;
            transform: rotate(90deg);
            color: #334155;
        }
        
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: #64748b;
            font-size: 0.875rem;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .divider::before {
            margin-right: 0.5em;
        }
        
        .divider::after {
            margin-left: 0.5em;
        }
        
        .password-toggle {
            cursor: pointer;
            transition: color 0.2s;
        }
        
        .password-toggle:hover {
            color: #ef4444;
        }
        
        .floating-label {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .floating-input {
            width: 100%;
            padding: 1.2rem 1rem 0.6rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s;
        }
        
        .floating-input:focus {
            outline: none;
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
        }
        
        .floating-label-text {
            position: absolute;
            top: 50%;
            left: 1rem;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
            transition: all 0.2s;
        }
        
        .floating-input:focus + .floating-label-text,
        .floating-input:not(:placeholder-shown) + .floating-label-text {
            top: 0.6rem;
            transform: translateY(0);
            font-size: 0.75rem;
            color: #ef4444;
            background: white;
            padding: 0 0.4rem;
        }
        
        @media (max-width: 640px) {
            .overlay-content {
                padding: 1.5rem;
                border-radius: 16px;
            }
        }
        
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="card w-full max-w-md p-8">
        <div class="text-center mb-8">
            <img class="mx-auto h-16 w-auto flamma-logo animate-float" src="../../client/src/Flamma-logo.png" alt="Flamma">
            <h1 class="mt-6 text-3xl font-bold text-gray-900">
                Bem-vindo de volta
            </h1>
            <p class="mt-2 text-gray-600">Entre na sua conta para continuar</p>
        </div>
        
        <form class="space-y-5" method="POST">
            <?php if (isset($erro)): ?>
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo $erro; ?>
                </div>
            <?php endif; ?>
            
            <div class="floating-label">
                <input type="email" id="email" name="email" class="floating-input" placeholder=" " required>
                <label for="email" class="floating-label-text">E-mail</label>
                <div class="absolute right-3 top-3 text-gray-400">
                    <i class="fas fa-envelope"></i>
                </div>
            </div>
            
            <div class="floating-label">
                <input type="password" id="senha" name="senha" class="floating-input" placeholder=" " required>
                <label for="senha" class="floating-label-text">Senha</label>
                <div class="absolute right-3 top-3 text-gray-400 password-toggle" id="togglePassword">
                    <i class="fas fa-eye-slash"></i>
                </div>
            </div>
            
            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center">
                    <input type="checkbox" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                    <span class="ml-2 text-gray-600">Lembrar-me</span>
                </label>
                <a href="#" class="text-red-600 hover:text-red-500 font-medium">Esqueceu a senha?</a>
            </div>

            <button type="submit" class="btn-primary w-full py-3 px-4 rounded-xl text-white font-medium text-lg">
                Entrar
            </button>
            
            <div class="divider text-gray-500">ou</div>
            
            <div class="grid grid-cols-2 gap-3">
                <a href="#" class="flex items-center justify-center py-2.5 px-4 border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
                    <i class="fab fa-google text-red-600 mr-2"></i>
                    <span class="text-sm font-medium">Google</span>
                </a>
                <a href="#" class="flex items-center justify-center py-2.5 px-4 border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
                    <i class="fab fa-facebook-f text-blue-600 mr-2"></i>
                    <span class="text-sm font-medium">Facebook</span>
                </a>
            </div>
            
            <div class="text-center pt-4">
                <p class="text-gray-600">
                    Não tem uma conta? 
                    <a href="#" id="open-cadastro" class="text-red-600 hover:text-red-500 font-medium ml-1">
                        Cadastre-se
                    </a>
                </p>
            </div>
        </form>
    </div>

    <!-- Overlay de Cadastro -->
    <div id="cadastro-overlay" class="overlay">
        <div class="overlay-content">
            <span class="close-btn" id="close-cadastro">&times;</span>
            
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Criar Conta</h2>
                <p class="text-gray-600">Junte-se à comunidade Flamma</p>
            </div>
            
            <form id="form-cadastro" class="space-y-4">
                <div class="floating-label">
                    <input type="text" id="nome-completo" name="nome-completo" class="floating-input" placeholder=" " required>
                    <label for="nome-completo" class="floating-label-text">Nome Completo</label>
                    <div class="absolute right-3 top-3 text-gray-400">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
                
                <div class="floating-label">
                    <input type="email" id="email-cadastro" name="email-cadastro" class="floating-input" placeholder=" " required>
                    <label for="email-cadastro" class="floating-label-text">E-mail</label>
                    <div class="absolute right-3 top-3 text-gray-400">
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>
                
                <div class="floating-label">
                    <input type="password" id="senha-cadastro" name="senha-cadastro" class="floating-input" placeholder=" " required>
                    <label for="senha-cadastro" class="floating-label-text">Senha</label>
                    <div class="absolute right-3 top-3 text-gray-400 password-toggle" id="togglePasswordCadastro">
                        <i class="fas fa-eye-slash"></i>
                    </div>
                </div>
                
                <div class="floating-label">
                    <input type="password" id="confirmar-senha" name="confirmar-senha" class="floating-input" placeholder=" " required>
                    <label for="confirmar-senha" class="floating-label-text">Confirmar Senha</label>
                    <div class="absolute right-3 top-3 text-gray-400 password-toggle" id="togglePasswordConfirm">
                        <i class="fas fa-eye-slash"></i>
                    </div>
                </div>
                
                <div class="flex items-start pt-2">
                    <div class="flex items-center h-5">
                        <input type="checkbox" id="termos" name="termos" required 
                            class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                    </div>
                    <label for="termos" class="ml-2 block text-sm text-gray-700">
                        Concordo com os <a href="#" class="text-red-600 hover:text-red-500">termos e condições</a> e política de privacidade
                    </label>
                </div>
                
                <button type="submit" class="btn-primary w-full py-3 px-4 rounded-xl text-white font-medium text-lg mt-4">
                    Criar Conta
                </button>
                
                <div class="divider text-gray-500">ou</div>
                
                <div class="grid grid-cols-2 gap-3">
                    <a href="#" class="flex items-center justify-center py-2.5 px-4 border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
                        <i class="fab fa-google text-red-600 mr-2"></i>
                        <span class="text-sm font-medium">Google</span>
                    </a>
                    <a href="#" class="flex items-center justify-center py-2.5 px-4 border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
                        <i class="fab fa-facebook-f text-blue-600 mr-2"></i>
                        <span class="text-sm font-medium">Facebook</span>
                    </a>
                </div>
                
                <div class="text-center pt-2">
                    <p class="text-gray-600 text-sm">
                        Já tem uma conta? 
                        <a href="#" class="text-red-600 hover:text-red-500 font-medium" id="fazer-login">
                            Fazer login
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Controle do overlay de cadastro
        const overlay = document.getElementById('cadastro-overlay');
        const openBtn = document.getElementById('open-cadastro');
        const closeBtn = document.getElementById('close-cadastro');
        const formCadastro = document.getElementById('form-cadastro');
        const fazerLoginBtn = document.getElementById('fazer-login');

        // Abrir overlay
        openBtn.addEventListener('click', function(e) {
            e.preventDefault();
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });

        // Fechar overlay
        closeBtn.addEventListener('click', function() {
            overlay.classList.remove('active');
            document.body.style.overflow = 'auto';
        });

        // Fechar ao clicar no link "Fazer login"
        fazerLoginBtn.addEventListener('click', function(e) {
            e.preventDefault();
            overlay.classList.remove('active');
            document.body.style.overflow = 'auto';
        });

        // Fechar ao clicar fora do conteúdo
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                overlay.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        });

        // Fechar com ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && overlay.classList.contains('active')) {
                overlay.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        });

        // Validação do formulário de cadastro
        formCadastro.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const senha = document.getElementById('senha-cadastro').value;
            const confirmarSenha = document.getElementById('confirmar-senha').value;
            
            if (senha !== confirmarSenha) {
                alert('As senhas não coincidem!');
                return;
            }
            
            if (!document.getElementById('termos').checked) {
                alert('Você precisa aceitar os termos e condições!');
                return;
            }
            
            // Simulação de cadastro bem-sucedido
            alert('Conta criada com sucesso! Você já pode fazer login.');
            overlay.classList.remove('active');
            document.body.style.overflow = 'auto';
            formCadastro.reset();
            
            // Em um sistema real, aqui você faria uma requisição AJAX para o backend
            // para processar o cadastro do usuário
        });

        // Funcionalidade de mostrar/ocultar senha
        function setupPasswordToggle(toggleId, inputId) {
            const toggle = document.getElementById(toggleId);
            const input = document.getElementById(inputId);
            
            if (toggle && input) {
                toggle.addEventListener('click', function() {
                    if (input.type === 'password') {
                        input.type = 'text';
                        toggle.innerHTML = '<i class="fas fa-eye"></i>';
                    } else {
                        input.type = 'password';
                        toggle.innerHTML = '<i class="fas fa-eye-slash"></i>';
                    }
                });
            }
        }
        
        // Configurar os toggles de senha
        setupPasswordToggle('togglePassword', 'senha');
        setupPasswordToggle('togglePasswordCadastro', 'senha-cadastro');
        setupPasswordToggle('togglePasswordConfirm', 'confirmar-senha');

        // Adicionar máscaras aos campos (opcional)
        document.getElementById('telefone-cadastro')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            
            if (value.length > 10) {
                value = value.replace(/^(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length > 6) {
                value = value.replace(/^(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else if (value.length > 2) {
                value = value.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
            }
            
            e.target.value = value;
        });
    </script>
</body>
</html>