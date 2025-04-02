<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID inválido');
}

$id = (int)$_GET['id'];

$sql = "SELECT p.*, l.nome AS loja_nome 
        FROM produtos p
        LEFT JOIN lojas l ON p.loja = l.id
        WHERE p.id = :id";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    die('Produto não encontrado');
}
?>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <h4><?= htmlspecialchars($produto['nome']) ?></h4>
            <?php if (!$produto['ativo']): ?>
                <span class="badge bg-secondary">Inativo</span>
            <?php endif; ?>
        </div>
        
        <div class="mb-3">
            <h6>Descrição</h6>
            <p><?= nl2br(htmlspecialchars($produto['descricao'])) ?></p>
        </div>
        
        <div class="mb-3">
            <h6>Categoria</h6>
            <p><?= htmlspecialchars($produto['categoria'] ?? 'Não informada') ?></p>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="row mb-3">
            <div class="col-6">
                <h6>Preço de Custo</h6>
                <p><?= $produto['preco_custo'] ? 'R$ ' . number_format($produto['preco_custo'], 2, ',', '.') : '-' ?></p>
            </div>
            <div class="col-6">
                <h6>Preço de Venda</h6>
                <p>R$ <?= number_format($produto['preco_venda'], 2, ',', '.') ?></p>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-6">
                <h6>Margem de Lucro</h6>
                <p>
                    <?php 
                    if ($produto['preco_custo'] > 0) {
                        $margem = (($produto['preco_venda'] - $produto['preco_custo']) / $produto['preco_custo']) * 100;
                        echo number_format($margem, 2, ',', '.') . '%';
                    } else {
                        echo '-';
                    }
                    ?>
                </p>
            </div>
            <div class="col-6">
                <h6>Código de Barras</h6>
                <p><?= htmlspecialchars($produto['codigo_barras'] ?? 'Não informado') ?></p>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-6">
                <h6>Estoque Atual</h6>
                <p>
                    <?php
                    $badge_class = 'badge-em-estoque';
                    if ($produto['quantidade'] <= 0) {
                        $badge_class = 'badge-sem-estoque';
                    } elseif ($produto['quantidade'] < 10) {
                        $badge_class = 'badge-baixo-estoque';
                    }
                    ?>
                    <span class="badge-estoque <?= $badge_class ?>">
                        <?= $produto['quantidade'] ?> un.
                    </span>
                </p>
            </div>
            <div class="col-6">
                <h6>Loja</h6>
                <p><?= htmlspecialchars($produto['loja_nome']) ?></p>
            </div>
        </div>
        
        <div class="mb-3">
            <h6>Data de Cadastro</h6>
            <p><?= date('d/m/Y H:i', strtotime($produto['data_criacao'])) ?></p>
        </div>
    </div>
</div>

<div class="text-end mt-3">
    <a href="product_edit.php?id=<?= $produto['id'] ?>" class="btn btn-primary">
        <i class="bi bi-pencil"></i> Editar Produto
    </a>
</div>