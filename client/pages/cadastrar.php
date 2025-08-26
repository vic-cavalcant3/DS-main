<?php
session_start();
require '../pages/utils/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome-completo'] ?? '');
    $email = trim($_POST['email-cadastro'] ?? '');
    $senha = $_POST['senha-cadastro'] ?? '';
    $confirmarSenha = $_POST['confirmar-senha'] ?? '';

    // Validações básicas
    if (empty($nome) || empty($email) || empty($senha)) {
        $_SESSION['erro'] = "Todos os campos são obrigatórios!";
        header("Location: login.php");
        exit;
    }

    if ($senha !== $confirmarSenha) {
        $_SESSION['erro'] = "As senhas não coincidem!";
        header("Location: login.php");
        exit;
    }

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    try {
        // Verificar se email já existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $_SESSION['erro'] = "E-mail já cadastrado!";
            header("Location: login.php");
            exit;
        }

        // Inserir novo usuário
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', $senhaHash);
        
        if ($stmt->execute()) {
            $_SESSION['sucesso'] = "Cadastro realizado com sucesso!";
        } else {
            $_SESSION['erro'] = "Erro ao realizar cadastro.";
        }

        header("Location: login.php");
        exit;

    } catch (PDOException $e) {
        $_SESSION['erro'] = "Erro no sistema. Tente novamente.";
        header("Location: login.php");
        exit;
    }
}
?>