<?php
// Incluir o arquivo de autenticação
require_once '../../config/auth.php';
require_once '../../config/database.php';

// Verificar se o usuário está logado
if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Definir conteúdo de acordo com o nível de acesso
$nivel_acesso = $_SESSION['nivel_acesso'];

// Buscar as vendas no banco de dados
try {
    $sql = "SELECT vendas.id, vendas.data_venda, produtos.nome AS produto, vendas.valor_total, vendas.tipo_pagamento
            FROM vendas
            JOIN produtos ON vendas.produto_id = produtos.id";
    
    $stmt = $pdo->query($sql);
    $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar vendas: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendas - Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php include('../../templates/header.php'); ?>

<!-- Conteúdo Principal -->
<div class="main-content">
    <h1>Vendas</h1>
    <p>Exibindo todas as vendas cadastradas.</p>
    
    <a href="sales_add.php" class="btn btn-primary">Adicionar Nova Venda</a>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Data</th>
                <th>Produto</th>
                <th>Valor</th>
                <th>Pagamento</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($vendas as $venda): ?>
            <tr>
                <td><?= $venda['id'] ?></td>
                <td><?= date('d/m/Y', strtotime($venda['data_venda'])) ?></td>
                <td><?= $venda['produto'] ?></td>
                <td>R$ <?= number_format($venda['valor_total'], 2, ',', '.') ?></td>
                <td><?= ucfirst($venda['tipo_pagamento']) ?></td>
                <td>
                    <a href="sales_edit.php?id=<?= $venda['id'] ?>" class="btn btn-warning">Editar</a>
                    <a href="sales_delete.php?id=<?= $venda['id'] ?>" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta venda?');">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include('../../templates/footer.php'); ?>

</body>
</html>
