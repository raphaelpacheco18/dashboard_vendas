<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Consultar todas as lojas
$sql = "SELECT id, nome, endereco, telefone, cep, cidade, estado, status, 
               DATE_FORMAT(data_criacao, '%d/%m/%Y %H:%i') as data_formatada 
        FROM lojas 
        ORDER BY nome";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$lojas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mensagens de feedback
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Lojas</title>
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
    
    .btn-action-primary i {
        margin-right: 8px;
        font-size: 1.1em;
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
    
    .text-truncate {
        max-width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    @media (max-width: 992px) {
        .responsive-table td:nth-child(5), /* Estado */
        .responsive-table th:nth-child(5),
        .responsive-table td:nth-child(6), /* CEP */
        .responsive-table th:nth-child(6) {
            display: none;
        }
    }
    
    @media (max-width: 768px) {
        .responsive-table td:nth-child(4), /* Telefone */
        .responsive-table th:nth-child(4),
        .responsive-table td:nth-child(7), /* Status */
        .responsive-table th:nth-child(7) {
            display: none;
        }
    }
    </style>
</head>
<body>
    <?php include '../../templates/header.php'; ?>
    
    <div class="main-container">
        <!-- Área de Ações no Topo -->
        <div class="header-actions">
            <h2 class="page-title">
                <i class="bi bi-shop"></i> Lista de Lojas
            </h2>
            <div>
                <a href="loja_add.php" class="btn btn-action-primary">
                    <i class="bi bi-plus-lg"></i> Nova Loja
                </a>
            </div>
        </div>

        <!-- Mensagens de feedback -->
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

        <!-- Card de Resultados -->
        <div class="card border-0 shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover responsive-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Endereço</th>
                                <th>Telefone</th>
                                <th>Estado</th>
                                <th>CEP</th>
                                <th>Status</th>
                                <th>Criação</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($lojas)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        Nenhuma loja cadastrada
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($lojas as $loja): ?>
                                <tr>
                                    <td><?= $loja['id'] ?></td>
                                    <td><?= htmlspecialchars($loja['nome']) ?></td>
                                    <td class="text-truncate" title="<?= htmlspecialchars($loja['endereco']) ?>">
                                        <?= htmlspecialchars($loja['endereco']) ?>
                                    </td>
                                    <td><?= htmlspecialchars($loja['telefone']) ?></td>
                                    <td><?= htmlspecialchars($loja['estado']) ?></td>
                                    <td><?= htmlspecialchars($loja['cep']) ?></td>
                                    <td>
                                        <span class="badge-status <?= $loja['status'] ? 'badge-active' : 'badge-inactive' ?>">
                                            <?= $loja['status'] ? 'Ativo' : 'Inativo' ?>
                                        </span>
                                    </td>
                                    <td><?= $loja['data_formatada'] ?></td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="loja_edit.php?id=<?= $loja['id'] ?>" 
                                               class="btn btn-outline-primary"
                                               title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="loja_delete.php?id=<?= $loja['id'] ?>" 
                                               class="btn btn-outline-danger"
                                               title="Excluir"
                                               onclick="return confirm('Tem certeza que deseja excluir esta loja?');">
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