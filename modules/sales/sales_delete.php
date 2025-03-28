<?php
// Incluir arquivos de autenticação e conexão com o banco de dados
require_once '../../config/auth.php';
require_once '../../config/database.php';

// Verificar se o usuário está logado
if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Verificar se o ID foi passado
if (!isset($_GET['id'])) {
    header('Location: sales.php');
    exit();
}

$id = $_GET['id'];

// Deletar a venda
$sql = "DELETE FROM vendas WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id);

if ($stmt->execute()) {
    header('Location: sales.php');
    exit();
} else {
    echo "Erro ao excluir a venda.";
}
?>
