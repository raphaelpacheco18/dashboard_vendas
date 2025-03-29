<?php
// Verificar se a sessão já foi iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// GERAR TOKEN CSRF (NOVO)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Incluir a configuração de conexão com o banco de dados
require_once 'database.php'; // Ajuste o caminho se necessário

// Função para verificar se o usuário está logado
function usuarioLogado() {
    return isset($_SESSION['usuario_id']) && isset($_SESSION['nivel_acesso']);
}

// Função para verificar o nível de acesso do usuário
function verificarNivelAcesso($nivel) {
    return isset($_SESSION['nivel_acesso']) && $_SESSION['nivel_acesso'] == $nivel;
}

// Função para garantir que o usuário seja redirecionado para a página de login se não estiver logado
function redirecionarSeNaoLogado() {
    if (!usuarioLogado()) {
        header('Location: auth/login.php');
        exit();
    }
}

// Função para garantir que o usuário tenha um nível de acesso específico
function redirecionarSeSemPermissao($nivel) {
    if (!verificarNivelAcesso($nivel)) {
        echo "Você não tem permissão para acessar esta página.";
        exit();
    }
}

// Função para realizar o login do usuário
function login($email, $senha) {
    global $pdo;

    // Buscar o usuário no banco de dados pelo e-mail
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $usuario = $stmt->fetch();

    // Verificar se o usuário foi encontrado e a senha é válida
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        // Se o login for bem-sucedido, cria a sessão com os dados do usuário
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome']; // Adiciona o nome do usuário na sessão
        $_SESSION['nivel_acesso'] = $usuario['nivel_acesso']; // Armazena o nível de acesso na sessão

        return true;
    } else {
        // Se o login falhar, retorna false
        return false;
    }
}

// Função para realizar o logout do usuário
function realizarLogout() {
    session_unset();
    session_destroy();
}
?>
