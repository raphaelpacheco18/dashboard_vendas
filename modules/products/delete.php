<?php
// Incluir o arquivo de autenticação e conexão
require_once '../../config/auth.php';
require_once '../../config/database.php';

// Verificar se o usuário está logado
if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Verificar se o id foi passado pela URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Preparar e executar a query de exclusão
    $sql = "DELETE FROM produtos WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Redirecionar para a lista de produtos após excluir com sucesso
        header('Location: products.php');
        exit();
    } else {
        echo "Erro ao excluir o produto!";
    }
} else {
    // Caso o id não tenha sido passado
    echo "Produto não encontrado!";
}
?>
