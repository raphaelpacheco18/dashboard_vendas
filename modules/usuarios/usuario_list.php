<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

$sql = "SELECT id, nome, email, nivel_acesso, ativo, foto FROM usuarios";
$stmt = $pdo->query($sql);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
    :root {
        --primary-color: #3498db;
        --success-color: #28a745;
        --danger-color: #dc3545;
        --warning-color: #fd7e14;
        --info-color: #17a2b8;
        --btn-gradient-start: #27ae60;
        --btn-gradient-end: #2ecc71;
        --btn-gradient-hover-start: #219653;
        --btn-gradient-hover-end: #27ae60;
    }
    
    body {
        background-color: #f8f9fa;
        padding-top: 20px;
    }
    
    .main-container {
        max-width: 1200px;
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
        color: #2c3e50;
    }
    
    .btn-novo-usuario {
        background: linear-gradient(135deg, var(--btn-gradient-start), var(--btn-gradient-end));
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
    
    .btn-novo-usuario:hover {
        background: linear-gradient(135deg, var(--btn-gradient-hover-start), var(--btn-gradient-hover-end));
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        color: white !important;
    }
    
    .btn-novo-usuario i {
        margin-right: 8px;
        font-size: 1.1em;
    }
    
    .card {
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        border: none;
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
    
    .badge-status {
        font-size: 0.75rem;
        padding: 5px 10px;
        border-radius: 20px;
        font-weight: 500;
    }
    
    .badge-ativo { background-color: var(--success-color); color: white; }
    .badge-inativo { background-color: var(--danger-color); color: white; }
    
    .profile-img-table {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border: 2px solid rgba(0,0,0,0.1);
    }
    
    /* RESPONSIVIDADE */
    @media (max-width: 768px) {
        .header-actions {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .page-title {
            margin-bottom: 10px;
            font-size: 1.5rem;
        }
        
        .btn-novo-usuario {
            width: 100%;
            justify-content: center;
        }
        
        td:nth-child(1), th:nth-child(1), /* ID */
        td:nth-child(5), th:nth-child(5) { /* Nível */
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
                <i class="bi bi-people-fill"></i> Gerenciamento de Usuários
            </h2>
            <div>
                <a href="usuario_add.php" class="btn btn-novo-usuario">
                    <i class="bi bi-plus-lg"></i> Novo Usuário
                </a>
            </div>
        </div>

        <!-- Card de Resultados -->
        <div class="card border-0 shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Foto</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Nível</th>
                                <th>Status</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($usuarios)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        Nenhum usuário cadastrado
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($usuarios as $user): ?>
                                <tr>
                                    <td><?= $user['id'] ?></td>
                                    <td>
                                        <img src="../../assets/img/profiles/<?= $user['foto'] ?: 'default-profile.jpg' ?>" 
                                             class="profile-img-table rounded-circle"
                                             alt="Foto de <?= htmlspecialchars($user['nome']) ?>"
                                             onerror="this.onerror=null;this.src='../../assets/img/profiles/default-profile.jpg'">
                                    </td>
                                    <td><?= htmlspecialchars($user['nome']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= ucfirst($user['nivel_acesso']) ?></td>
                                    <td>
                                        <span class="badge-status <?= $user['ativo'] ? 'badge-ativo' : 'badge-inativo' ?>">
                                            <?= $user['ativo'] ? 'Ativo' : 'Inativo' ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="usuario_edit.php?id=<?= $user['id'] ?>" 
                                               class="btn btn-outline-primary"
                                               title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="usuario_delete.php?id=<?= $user['id'] ?>" 
                                               class="btn btn-outline-danger"
                                               title="Excluir"
                                               onclick="return confirm('Tem certeza que deseja excluir este usuário?');">
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
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
</body>
</html>