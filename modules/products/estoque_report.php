<?php
// ... (código de autenticação similar)

// Obter produtos por loja
$sql = "SELECT p.id, p.nome, p.estoque, p.quantidade_atual, p.quantidade_vendida, 
               l.nome AS loja_nome, l.id AS loja_id
        FROM produtos p
        JOIN lojas l ON p.loja = l.id
        ORDER BY l.nome, p.nome";

$produtos = $pdo->query($sql)->fetchAll(PDO::FETCH_GROUP); // Agrupa por loja
?>

<!-- Tabela de relatório -->
<table class="table">
    <?php foreach ($produtos as $loja_id => $produtos_loja): ?>
        <tr class="table-primary">
            <th colspan="5"><?= htmlspecialchars($produtos_loja[0]['loja_nome']) ?></th>
        </tr>
        <tr>
            <th>Produto</th>
            <th>Estoque Total</th>
            <th>Disponível</th>
            <th>Vendido</th>
            <th>Última Mov.</th>
        </tr>
        
        <?php foreach ($produtos_loja as $produto): ?>
            <tr>
                <td><?= htmlspecialchars($produto['nome']) ?></td>
                <td><?= $produto['estoque'] ?></td>
                <td><?= $produto['quantidade_atual'] ?></td>
                <td><?= $produto['quantidade_vendida'] ?></td>
                <td>
                    <?php 
                    $stmt = $pdo->prepare("SELECT data_movimentacao FROM movimentacoes_estoque 
                                          WHERE produto_id = ? AND loja_id = ? 
                                          ORDER BY data_movimentacao DESC LIMIT 1");
                    $stmt->execute([$produto['id'], $loja_id]);
                    $ultima_mov = $stmt->fetchColumn();
                    echo $ultima_mov ? date('d/m/Y', strtotime($ultima_mov)) : '-';
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endforeach; ?>
</table>