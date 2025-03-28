<?php
// Incluir o arquivo de autenticação
require_once '../../config/auth.php';
require_once '../../config/database.php';

// Verificar se o usuário está logado
if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Verificar se o ID da vendedora foi passado via GET
if (!isset($_GET['id'])) {
    echo "Vendedora não encontrada!";
    exit();
}

$id = $_GET['id'];

// Preparar a consulta para excluir a vendedora
$sql = "DELETE FROM vendedoras WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id);

// Executar a consulta
if ($stmt->execute()) {
    // Se a exclusão for bem-sucedida, redirecionar para a lista de vendedoras
    header('Location: vendedoras_list.php');
    exit();
} else {
    echo "Erro ao excluir vendedora.";
}
?>
