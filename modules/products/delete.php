<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: products.php?error=ID do produto não fornecido');
    exit();
}

$id = $_GET['id'];

try {
    // Marcar o produto como inativo em vez de excluir
    $sql = "UPDATE produtos SET ativo = 0 WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Redirecionar com mensagem de sucesso
        header('Location: products.php?success=Produto desativado com sucesso');
    } else {
        header('Location: products.php?error=Erro ao desativar o produto');
    }
    exit();
    
} catch (PDOException $e) {
    header('Location: products.php?error=Erro no sistema: ' . urlencode($e->getMessage()));
    exit();
}
?>