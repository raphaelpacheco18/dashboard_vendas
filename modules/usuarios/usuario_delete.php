<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

// Verifica se o usuário está logado
if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Verifica se o ID do usuário foi passado via GET
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_usuario = $_GET['id'];

    // Tenta excluir o usuário no banco de dados
    try {
        $sql = "DELETE FROM usuarios WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id_usuario]);

        $_SESSION['sucesso'] = 'Usuário excluído com sucesso!';
        header('Location: usuario_list.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['erro'] = 'Erro ao excluir usuário: ' . $e->getMessage();
        header('Location: usuario_list.php');
        exit();
    }
} else {
    $_SESSION['erro'] = 'ID do usuário inválido.';
    header('Location: usuario_list.php');
    exit();
}
?>
