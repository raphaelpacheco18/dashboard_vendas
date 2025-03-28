<?php
// Incluir o arquivo de autenticação e banco de dados
require_once '../../config/auth.php';
require_once '../../config/database.php';

// Verificar se o usuário está logado
if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Verificar se o ID da meta foi passado na URL
if (!isset($_GET['id'])) {
    header('Location: metas_list.php'); // Se não, redireciona para a listagem
    exit();
}

// Buscar e excluir a meta
$id = $_GET['id'];
$sql = "DELETE FROM metas WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();

// Redireciona para a listagem após excluir
header('Location: metas_list.php');
exit();
?>
