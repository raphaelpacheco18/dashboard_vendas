<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';  // Incluir a conexão com o banco de dados

// Verificar se o usuário está logado e se ele tem o nível de acesso adequado (administrador)
if (!usuarioLogado() || $_SESSION['nivel_acesso'] != 'admin') {
    header('Location: ../index.php'); // Redirecionar para a página inicial se o usuário não for admin
    exit();
}


// Verificar se o ID foi passado na URL
if (isset($_GET['id'])) {
    $id_loja = $_GET['id'];

    // Verificar se há funcionárias cadastradas para essa loja
    $sql_check_funcionarias = "SELECT COUNT(*) FROM vendedoras WHERE loja_id = :id";
    $stmt_check_funcionarias = $pdo->prepare($sql_check_funcionarias);
    $stmt_check_funcionarias->bindParam(':id', $id_loja, PDO::PARAM_INT);
    $stmt_check_funcionarias->execute();
    $result = $stmt_check_funcionarias->fetchColumn();

    // Se houver funcionárias cadastradas, não permitir exclusão
    if ($result > 0) {
        // Redirecionar de volta para a página de listagem e mostrar a mensagem
        header('Location: lojas.php?erro=funcionarias'); 
        exit();
    }

    // Preparar o comando SQL para excluir a loja
    $sql_loja = "DELETE FROM lojas WHERE id = :id";
    $stmt_loja = $pdo->prepare($sql_loja);
    $stmt_loja->bindParam(':id', $id_loja, PDO::PARAM_INT);

    // Executar a exclusão da loja
    if ($stmt_loja->execute()) {
        header('Location: lojas.php'); // Redirecionar de volta para a lista de lojas
        exit();
    } else {
        echo "Erro ao excluir a loja.";
    }
} else {
    echo "ID da loja não especificado.";
}
?>
