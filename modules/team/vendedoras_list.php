<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Consulta com todos os campos necessários
$sql = "SELECT v.*, l.nome AS loja_nome, l.status AS loja_status 
        FROM vendedoras v 
        JOIN lojas l ON v.loja_id = l.id
        ORDER BY v.nome";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$vendedoras = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mensagens de feedback
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Vendedoras</title>
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
    
    .badge-status {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .badge-active {
        background-color: #d4edda;
        color: #155724;
    }
    
    .badge-inactive {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    .badge-loja-inactive {
        background-color: #fff3cd;
        color: #856404;
    }
    
    .text-truncate {
        max-width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .cpf-mask {
        font-family: monospace;
    }
    
    .comissao-badge {
        background-color: #e2e3e5;
        color: #383d41;
        padding: 3px 8px;
        border-radius: 10px;
        font-size: 0.8rem;
    }
    
    @media (max-width: 1200px) {
        .responsive-table td:nth-child(6), /* CPF */
        .responsive-table th:nth-child(6) {
            display: none;
        }
    }
    
    @media (max-width: 992px) {
        .responsive-table td:nth-child(5), /* Comissão */
        .responsive-table th:nth-child(5),
        .responsive-table td:nth-child(7), /* Status */
        .responsive-table th:nth-child(7) {
            display: none;
        }
    }
    
    @media (max-width: 768px) {
        .responsive-table td:nth-child(4), /* Telefone */
        .responsive-table th:nth-child(4) {
            display: none;
        }
        
        .header-actions {
            flex-direction: column;
            align-items: flex-start;
        }
    }
    </style>
</head>
<body>
    <?php include '../../templates/header.php'; ?>
    
    <div class="main-container">
        <div class="header-actions">
            <h2 class="page-title">
                <i class="bi bi-people-fill"></i> Lista de Vendedoras
            </h2>
            <div>
                <?php if ($_SESSION['nivel_acesso'] === 'admin' || $_SESSION['nivel_acesso'] === 'gerente'): ?>
                <a href="vendedora_add.php" class="btn btn-action-primary">
                    <i class="bi bi-plus-lg"></i> Nova Vendedora
                </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <?php if (is_array($error) && $error['type'] === 'has_relations'): ?>
            <div class="alert alert-warning">
                <h5><i class="bi bi-exclamation-triangle"></i> Não foi possível excluir</h5>
                <p>A vendedora <strong><?= htmlspecialchars($error['vendedora_nome']) ?></strong> possui 
                <strong><?= $error['total_vendas'] ?> venda(s)</strong> registrada(s).</p>
                <hr>
                <p class="mb-0">Para excluir, primeiro transfira as vendas para outra vendedora.</p>
            </div>
        <?php elseif (is_array($error) && $error['type'] === 'system_error'): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error['message']) ?>
            </div>
        <?php elseif ($error): ?>
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
                                <th>ID</th>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Telefone</th>
                                <th>Comissão</th>
                                <th>CPF</th>
                                <th>Status</th>
                                <th>Loja</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($vendedoras)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        Nenhuma vendedora cadastrada
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($vendedoras as $vendedora): ?>
                                <tr>
                                    <td><?= $vendedora['id'] ?></td>
                                    <td>
                                        <?= htmlspecialchars($vendedora['nome']) ?>
                                        <?php if ($vendedora['data_nascimento']): ?>
                                            <br>
                                            <small class="text-muted">
                                                <?= date('d/m/Y', strtotime($vendedora['data_nascimento'])) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-truncate" title="<?= htmlspecialchars($vendedora['email']) ?>">
                                        <?= htmlspecialchars($vendedora['email']) ?>
                                    </td>
                                    <td><?= htmlspecialchars($vendedora['telefone']) ?></td>
                                    <td>
                                        <span class="comissao-badge">
                                            <?= number_format($vendedora['comissao'], 2, ',', '.') ?>%
                                        </span>
                                    </td>
                                    <td class="cpf-mask">
                                        <?= $vendedora['cpf'] ? substr($vendedora['cpf'], 0, 3) . '.' . 
                                            substr($vendedora['cpf'], 3, 3) . '.' . 
                                            substr($vendedora['cpf'], 6, 3) . '-' . 
                                            substr($vendedora['cpf'], 9, 2) : '' ?>
                                    </td>
                                    <td>
                                        <span class="badge-status <?= $vendedora['status'] ? 'badge-active' : 'badge-inactive' ?>">
                                            <?= $vendedora['status'] ? 'Ativa' : 'Inativa' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($vendedora['loja_nome']) ?>
                                        <?php if (!$vendedora['loja_status']): ?>
                                            <span class="badge-loja-inactive badge-status">Loja Inativa</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="vendedora_edit.php?id=<?= $vendedora['id'] ?>" 
                                               class="btn btn-outline-primary"
                                               title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php if ($_SESSION['nivel_acesso'] === 'admin' || $_SESSION['nivel_acesso'] === 'gerente'): ?>
                                            <a href="vendedora_delete.php?id=<?= $vendedora['id'] ?>" 
                                               class="btn btn-outline-danger"
                                               title="Excluir"
                                               onclick="return confirm('Tem certeza que deseja excluir esta vendedora?');">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                            <?php endif; ?>
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