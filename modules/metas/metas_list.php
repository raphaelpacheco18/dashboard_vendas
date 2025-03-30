<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Consulta com JOIN para pegar os nomes da loja e vendedora
$sql = "SELECT m.*, l.nome AS loja_nome, v.nome AS vendedora_nome 
        FROM metas m
        LEFT JOIN lojas l ON m.loja_id = l.id
        LEFT JOIN vendedoras v ON m.vendedora_id = v.id
        ORDER BY m.data_inicio DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$metas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Metas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
    :root {
        --primary-color: #3498db;
        --success-color: #28a745;
        --danger-color: #dc3545;
        --warning-color: #fd7e14;
        --info-color: #17a2b8;
        --text-dark: #2c3e50;
        --text-muted: #6c757d;
        --border-color: #dee2e6;
        --card-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    
    body {
        background-color: #f8f9fa;
        padding-top: 20px;
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }
    
    .main-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 15px;
    }
    
    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .page-title {
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.8rem;
        color: var(--text-dark);
        font-weight: 600;
    }
    
    .btn-action-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--info-color));
        border: none;
        padding: 10px 20px;
        font-weight: 500;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        color: white !important;
        border-radius: 5px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        text-decoration: none;
    }
    
    .btn-action-primary:hover {
        background: linear-gradient(135deg, var(--info-color), var(--primary-color));
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        color: white !important;
    }
    
    .card {
        border-radius: 10px;
        box-shadow: var(--card-shadow);
        border: none;
        margin-bottom: 20px;
    }
    
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .table thead {
        background-color: var(--primary-color);
        color: white;
        position: sticky;
        top: 0;
    }
    
    .table th {
        font-weight: 500;
        padding: 12px 15px !important;
        white-space: nowrap;
    }
    
    .table td {
        padding: 10px 15px !important;
        vertical-align: middle;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(52, 152, 219, 0.05);
    }
    
    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        border-radius: 0.25rem;
    }
    
    .btn-outline-primary {
        color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .btn-outline-primary:hover {
        background-color: var(--primary-color);
        color: white;
    }
    
    .btn-outline-danger {
        color: var(--danger-color);
        border-color: var(--danger-color);
    }
    
    .btn-outline-danger:hover {
        background-color: var(--danger-color);
        color: white;
    }
    
    .alert {
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .badge-periodo {
        background-color: var(--warning-color);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.8rem;
    }
    
    .text-currency {
        font-family: 'Courier New', monospace;
        font-weight: bold;
    }
    
    @media (max-width: 1200px) {
        .responsive-table td:nth-child(6),
        .responsive-table th:nth-child(6),
        .responsive-table td:nth-child(7),
        .responsive-table th:nth-child(7) {
            display: none;
        }
    }
    
    @media (max-width: 992px) {
        .responsive-table td:nth-child(5),
        .responsive-table th:nth-child(5),
        .responsive-table td:nth-child(8),
        .responsive-table th:nth-child(8) {
            display: none;
        }
    }
    
    @media (max-width: 768px) {
        .header-actions {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .responsive-table td:nth-child(4),
        .responsive-table th:nth-child(4) {
            display: none;
        }
    }
    </style>
</head>
<body>
    <?php include '../../templates/header.php'; ?>
    
    <div class="main-container">
        <div class="header-actions">
            <h2 class="page-title">
                <i class="bi bi-bullseye"></i> Metas de Vendas
            </h2>
            <div>
                <a href="metas_add.php" class="btn btn-action-primary">
                    <i class="bi bi-plus-lg"></i> Nova Meta
                </a>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover responsive-table">
                        <thead>
                            <tr>
                                <th>Loja</th>
                                <th>Vendedora</th>
                                <th>Período</th>
                                <th>Meta (R$)</th>
                                <th>Meta (Qtd)</th>
                                <th>Bonificação</th>
                                <th>Início</th>
                                <th>Término</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($metas)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        Nenhuma meta cadastrada
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($metas as $meta): ?>
                                <tr>
                                    <td><?= htmlspecialchars($meta['loja_nome'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($meta['vendedora_nome'] ?? 'Todas') ?></td>
                                    <td>
                                        <span class="badge-periodo"><?= htmlspecialchars($meta['periodo']) ?></span>
                                    </td>
                                    <td class="text-currency">R$ <?= number_format($meta['meta_valor'], 2, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($meta['meta_quantidade']) ?></td>
                                    <td class="text-currency">R$ <?= number_format($meta['bonificacao'], 2, ',', '.') ?></td>
                                    <td><?= date('d/m/Y', strtotime($meta['data_inicio'])) ?></td>
                                    <td><?= date('d/m/Y', strtotime($meta['data_fim'])) ?></td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="metas_edit.php?id=<?= $meta['id'] ?>" 
                                               class="btn btn-outline-primary"
                                               title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="metas_delete.php?id=<?= $meta['id'] ?>" 
                                               class="btn btn-outline-danger"
                                               title="Excluir"
                                               onclick="return confirm('Tem certeza que deseja excluir esta meta?');">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>