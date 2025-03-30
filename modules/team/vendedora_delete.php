<?php
require_once '../../config/auth.php';  // Já inicia a sessão
require_once '../../config/database.php';

if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID inválido";
    header('Location: vendedoras_list.php');
    exit();
}

$id = (int)$_GET['id'];

try {
    // Verifica vendas associadas
    $sql_check = "SELECT COUNT(*) as total FROM vendas WHERE vendedora_id = :id";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt_check->execute();
    
    if ($stmt_check->fetchColumn() > 0) {
        $stmt_nome = $pdo->prepare("SELECT nome FROM vendedoras WHERE id = ?");
        $stmt_nome->execute([$id]);
        
        $_SESSION['error'] = [
            'type' => 'has_relations',
            'vendedora_nome' => $stmt_nome->fetchColumn(),
            'total_vendas' => $stmt_check->fetchColumn()
        ];
        header('Location: vendedoras_list.php');
        exit();
    }

    // Exclusão segura
    $stmt_delete = $pdo->prepare("DELETE FROM vendedoras WHERE id = ?");
    if ($stmt_delete->execute([$id])) {
        $_SESSION['success'] = "Vendedora excluída com sucesso!";
    }
} catch (PDOException $e) {
    $_SESSION['error'] = [
        'type' => 'system_error',
        'message' => 'Erro no sistema: ' . $e->getMessage()
    ];
}

header('Location: vendedoras_list.php');
exit();
?>