<?php
session_start();
require '../pages/utils/conexao.php';

// Configurações para login social (substitua com suas próprias credenciais)
define('GOOGLE_CLIENT_ID', 'SEU_GOOGLE_CLIENT_ID');
define('GOOGLE_CLIENT_SECRET', 'SEU_GOOGLE_CLIENT_SECRET');
define('GOOGLE_REDIRECT_URI', 'http://seusite.com/login.php');

define('FACEBOOK_APP_ID', 'SEU_FACEBOOK_APP_ID');
define('FACEBOOK_APP_SECRET', 'SEU_FACEBOOK_APP_SECRET');
define('FACEBOOK_REDIRECT_URI', 'http://seusite.com/login.php');

// Processar formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email']) && isset($_POST['senha'])) {
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
    
    // Processar recuperação de senha
    if (isset($_POST['recuperar_email'])) {
        $email_recuperacao = $_POST['recuperar_email'];
        
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
            $stmt->bindParam(':email', $email_recuperacao);
            $stmt->execute();
            $usuario = $stmt->fetch();
            
            if ($usuario) {
                // Gerar token de recuperação (exemplo simples)
                $token = bin2hex(random_bytes(32));
                $expiracao = date("Y-m-d H:i:s", strtotime('+1 hour'));
                
                // Salvar token no banco de dados
                $stmt = $pdo->prepare("UPDATE usuarios SET token_recuperacao = :token, expiracao_token = :expiracao WHERE email = :email");
                $stmt->bindParam(':token', $token);
                $stmt->bindParam(':expiracao', $expiracao);
                $stmt->bindParam(':email', $email_recuperacao);
                $stmt->execute();
                
                // Em um sistema real, aqui você enviaria um e-mail com o link de recuperação
                $sucesso_recuperacao = "Instruções de recuperação enviadas para seu e-mail!";
            } else {
                $erro_recuperacao = "E-mail não encontrado em nossa base de dados.";
            }
        } catch (PDOException $e) {
            $erro_recuperacao = "Erro ao processar solicitação. Tente novamente.";
        }
    }
}

// Gerar URLs para login social (implementação básica)
$google_login_url = "https://accounts.google.com/o/oauth2/auth?client_id=".GOOGLE_CLIENT_ID."&redirect_uri=".urlencode(GOOGLE_REDIRECT_URI)."&response_type=code&scope=email profile";
$facebook_login_url = "https://www.facebook.com/v12.0/dialog/oauth?client_id=".FACEBOOK_APP_ID."&redirect_uri=".urlencode(FACEBOOK_REDIRECT_URI)."&scope=email";
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Flamma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .input-group {
            position: relative;
            margin-bottom: 1rem;
        }
        
        .input-field {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            background: white;
        }
        
        .input-field:focus {
            outline: none;
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 0.875rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            width: 100%;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }
        
        .btn-social {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            transition: all 0.2s ease;
            text-decoration: none;
            color: #4a5568;
        }
        
        .btn-social:hover {
            border-color: #cbd5e0;
            transform: translateY(-1px);
        }
        
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .modal.active {
            opacity: 1;
            visibility: visible;
        }
        
        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            width: 90%;
            max-width: 400px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }
        
        .modal.active .modal-content {
            transform: scale(1);
        }
        
        .close-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #f7fafc;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        
        .close-btn:hover {
            background: #edf2f7;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: #718096;
            font-size: 0.875rem;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }
        
        .divider::before {
            margin-right: 1rem;
        }
        
        .divider::after {
            margin-left: 1rem;
        }
        
        .error-msg {
            background: #fed7d7;
            color: #c53030;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
        }
        
        .success-msg {
            background: #c6f6d5;
            color: #276749;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
        }
        
        .text-link {
            color: #ef4444;
            text-decoration: none;
            font-weight: 500;
        }
        
        .text-link:hover {
            color: #dc2626;
            text-decoration: underline;
        }
        
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #a0aec0;
            transition: color 0.2s ease;
        }
        
        .password-toggle:hover {
            color: #ef4444;
        }
        
        @media (max-width: 640px) {
            .modal-content {
                padding: 1.5rem;
                margin: 1rem;
            }
        }
    </style>
</head>
<body class="p-4">
   <div class="card w-full max-w-md p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                Bem-vindo de volta
            </h1>
            <p class="text-gray-600">Entre na sua conta Flamma</p>
        </div>
        
        <form method="POST" class="space-y-4">
            <?php if (isset($erro)): ?>
                <div class="error-msg">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo $erro; ?>
                </div>
            <?php endif; ?>
            
            <div class="input-group">
                <input type="email" name="email" class="input-field" placeholder="E-mail" required>
            </div>
            
            <div class="input-group">
                <input type="password" id="password" name="senha" class="input-field" placeholder="Senha" required>
                <span class="password-toggle" onclick="togglePassword('password')">
                    <i class="fas fa-eye-slash"></i>
                </span>
            </div>
            
            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center">
                    <input type="checkbox" class="h-4 w-4 text-indigo-600 rounded">
                    <span class="ml-2 text-gray-600">Lembrar-me</span>
                </label>
                <a href="#" onclick="openModal('forgot-modal')" class="text-link">Esqueceu a senha?</a>
            </div>

            <button type="submit" class="btn-primary">
                Entrar
            </button>
            
            <div class="divider">ou</div>
            
            <div class="grid grid-cols-2 gap-3">
                <a href="#" class="btn-social">
                    <i class="fab fa-google text-red-500 mr-2"></i>
                    Google
                </a>
                <a href="#" class="btn-social">
                    <i class="fab fa-facebook-f text-blue-600 mr-2"></i>
                    Facebook
                </a>
            </div>
            
            <div class="text-center pt-4">
                <p class="text-gray-600 text-sm">
                    Não tem uma conta? 
                    <a href="#" onclick="openModal('register-modal')" class="text-link">Cadastre-se</a>
                </p>
            </div>
        </form>
    </div>

    <!-- Modal de Cadastro -->
    <div id="register-modal" class="modal">
        <div class="modal-content">
            <button class="close-btn" onclick="closeModal('register-modal')">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold text-gray-900">Criar Conta</h2>
                <p class="text-gray-600 text-sm">Junte-se à Flamma</p>
            </div>
            
            <form action="cadastrar.php" method="POST" class="space-y-4">
                <div class="input-group">
                    <input type="text" name="nome-completo" class="input-field" placeholder="Nome completo" required>
                </div>
                
                <div class="input-group">
                    <input type="email" name="email-cadastro" class="input-field" placeholder="E-mail" required>
                </div>
                
                <div class="input-group">
                    <input type="password" id="password-register" name="senha-cadastro" class="input-field" placeholder="Senha" required>
                    <span class="password-toggle" onclick="togglePassword('password-register')">
                        <i class="fas fa-eye-slash"></i>
                    </span>
                </div>
                
                <div class="input-group">
                    <input type="password" id="password-confirm" name="confirmar-senha" class="input-field" placeholder="Confirmar senha" required>
                    <span class="password-toggle" onclick="togglePassword('password-confirm')">
                        <i class="fas fa-eye-slash"></i>
                    </span>
                </div>
                
                <label class="flex items-start text-sm">
                    <input type="checkbox" name="termos" required class="h-4 w-4 text-indigo-600 rounded mr-2 mt-0.5">
                    <span class="text-gray-700">
                        Concordo com os <a href="#" class="text-link">termos</a> e <a href="#" class="text-link">privacidade</a>
                    </span>
                </label>
                
                <button type="submit" class="btn-primary">
                    Criar Conta
                </button>
                
                <div class="text-center">
                    <p class="text-gray-600 text-sm">
                        Já tem conta? 
                        <a href="#" onclick="closeModal('register-modal')" class="text-link">Fazer login</a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Recuperação -->
    <div id="forgot-modal" class="modal">
        <div class="modal-content">
            <button class="close-btn" onclick="closeModal('forgot-modal')">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold text-gray-900">Recuperar Senha</h2>
                <p class="text-gray-600 text-sm">Digite seu e-mail para receber as instruções</p>
            </div>
            
            <form method="POST" class="space-y-4">
                <?php if (isset($erro_recuperacao)): ?>
                    <div class="error-msg">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo $erro_recuperacao; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($sucesso_recuperacao)): ?>
                    <div class="success-msg">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo $sucesso_recuperacao; ?>
                    </div>
                <?php endif; ?>
                
                <div class="input-group">
                    <input type="email" name="recuperar_email" class="input-field" placeholder="Seu e-mail" required>
                </div>
                
                <button type="submit" class="btn-primary">
                    Enviar Instruções
                </button>
                
                <div class="text-center">
                    <p class="text-gray-600 text-sm">
                        Lembrou sua senha? 
                        <a href="#" onclick="closeModal('forgot-modal')" class="text-link">Fazer login</a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
            document.body.style.overflow = 'auto';
        }
        
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye-slash';
            }
        }
        
        // Fechar modal clicando fora
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal(modal.id);
                }
            });
        });
        
        // Fechar modal com ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal.active').forEach(modal => {
                    closeModal(modal.id);
                });
            }
        });
        
        // Validação simples do formulário de cadastro
        document.querySelector('#register-modal form').addEventListener('submit', function(e) {
            const senha = document.querySelector('input[name="senha-cadastro"]').value;
            const confirmar = document.querySelector('input[name="confirmar-senha"]').value;
            
            if (senha !== confirmar) {
                e.preventDefault();
                alert('As senhas não coincidem!');
            }
        });
    </script>
</body>
</html>