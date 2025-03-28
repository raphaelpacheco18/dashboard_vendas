<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

// Verificar se o usuário está logado
if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Consultar as metas cadastradas
$sql = "SELECT * FROM metas";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$metas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Metas</title>
</head>
<body>
<?php include('../../templates/header.php'); ?>
<h1>Lista de Metas de Vendas</h1>

<a href="metas_add.php">Adicionar Nova Meta</a>

<?php if (count($metas) > 0): ?>
    <table border="1">
        <thead>
            <tr>
                <th>Loja</th>
                <th>Vendedora</th>
                <th>Período</th>
                <th>Meta Valor</th>
                <th>Meta Quantidade</th>
                <th>Bonificação</th>
                <th>Data Início</th>
                <th>Data Fim</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($metas as $meta): ?>
                <tr>
                    <td><?php echo $meta['loja_id']; ?></td> <!-- Você pode fazer JOIN para mostrar o nome da loja -->
                    <td><?php echo $meta['vendedora_id']; ?></td> <!-- O mesmo vale para a vendedora -->
                    <td><?php echo $meta['periodo']; ?></td>
                    <td><?php echo $meta['meta_valor']; ?></td>
                    <td><?php echo $meta['meta_quantidade']; ?></td>
                    <td><?php echo $meta['bonificacao']; ?></td>
                    <td><?php echo $meta['data_inicio']; ?></td>
                    <td><?php echo $meta['data_fim']; ?></td>
                    <td>
                        <a href="metas_edit.php?id=<?php echo $meta['id']; ?>">Editar</a> |
                        <a href="metas_delete.php?id=<?php echo $meta['id']; ?>">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Nenhuma meta cadastrada.</p>
<?php endif; ?>

</body>
</html>
