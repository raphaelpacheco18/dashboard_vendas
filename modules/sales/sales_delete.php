<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Verificar permissões
if (!in_array($_SESSION['nivel_acesso'], ['admin', 'gerente'])) {
    $_SESSION['error'] = "Acesso negado: Você não tem permissão para excluir vendas";
    header('Location: sales.php');
    exit();
}

// Obter ID da venda
$venda_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($venda_id <= 0) {
    $_SESSION['error'] = "Venda inválida";
    header('Location: sales.php');
    exit();
}

try {
    $pdo->beginTransaction();
    
    // 1. Obter dados da venda para devolver ao estoque
    $venda = $pdo->prepare("SELECT produto_id, quantidade FROM vendas WHERE id = ?");
    $venda->execute([$venda_id]);
    $venda = $venda->fetch();
    
    // 2. Devolver ao estoque
    $sql_devolver = "UPDATE produtos SET 
        quantidade_atual = quantidade_atual + :quantidade
        WHERE id = :produto_id";
        
    $stmt_devolver = $pdo->prepare($sql_devolver);
    $stmt_devolver->execute([
        ':quantidade' => $venda['quantidade'],
        ':produto_id' => $venda['produto_id']
    ]);
    
    // 3. Remover a venda
    $pdo->prepare("DELETE FROM vendas WHERE id = ?")->execute([$venda_id]);
    
    $pdo->commit();
    
    $_SESSION['success'] = "Venda #$venda_id excluída com sucesso!";
    
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Erro ao excluir venda: " . $e->getMessage();
}

header('Location: sales.php');
exit();