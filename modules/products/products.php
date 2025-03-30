<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Filtros
$nome = $_GET['nome'] ?? '';
$loja = $_GET['loja'] ?? '';

// Obter lista de lojas distintas para o dropdown
$lojas_distintas = $pdo->query("SELECT DISTINCT loja FROM produtos WHERE loja IS NOT NULL ORDER BY loja")->fetchAll();

// Consulta base
$sql = "SELECT * FROM produtos WHERE 1=1";

// Aplicar filtros
if (!empty($nome)) {
    $sql .= " AND nome LIKE :nome";
}
if (!empty($loja)) {
    $sql .= " AND loja = :loja";
}

$sql .= " ORDER BY nome";

// Preparar e executar
$stmt = $pdo->prepare($sql);

if (!empty($nome)) {
    $stmt->bindValue(':nome', "%$nome%");
}
if (!empty($loja)) {
    $stmt->bindValue(':loja', $loja);
}

$stmt->execute();
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Produtos</title>
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
        --text-dark: #2c3e50;
        --text-muted: #6c757d;
        --border-color: #dee2e6;
        --card-shadow: 0 5px 15px rgba(0,0,0,0.05);
        --filter-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    body {
        background-color: #f8f9fa;
        padding-top: 20px;
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
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
        color: var(--text-dark);
        font-weight: 600;
    }
    
    .btn-action-primary {
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
    
    .btn-action-primary:hover {
        background: linear-gradient(135deg, var(--btn-gradient-hover-start), var(--btn-gradient-hover-end));
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        color: white !important;
    }
    
    .btn-action-primary i {
        margin-right: 8px;
        font-size: 1.1em;
        color: white !important;
    }
    
    /* CARDS E ESTRUTURAS */
    .card {
        border-radius: 10px;
        box-shadow: var(--card-shadow);
        border: none;
        margin-bottom: 20px;
    }
    
    .card-header {
        background-color: white;
        border-bottom: 1px solid var(--border-color);
        font-weight: 600;
        padding: 15px 20px;
    }
    
    .card-body {
        padding: 20px;
    }
    
    .filter-card {
        background: white;
        border-radius: 10px;
        box-shadow: var(--filter-shadow);
        margin-bottom: 20px;
        padding: 20px;
    }
    
    /* TABELAS */
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
    
    /* BADGES PARA ESTOQUE */
    .badge-estoque {
        font-size: 0.75rem;
        padding: 5px 10px;
        border-radius: 20px;
        font-weight: 500;
    }
    
    .badge-em-estoque {
        background-color: #d4edda;
        color: #155724;
    }
    
    .badge-baixo-estoque {
        background-color: #fff3cd;
        color: #856404;
    }
    
    .badge-sem-estoque {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    /* FORMULÁRIOS E FILTROS */
    .filter-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr) auto;
        gap: 15px;
        align-items: end;
    }
    
    .filter-group {
        display: flex;
        flex-direction: column;
    }
    
    .filter-group label {
        margin-bottom: 5px;
        font-weight: 500;
        font-size: 0.9rem;
        color: var(--text-dark);
    }
    
    .filter-group input,
    .filter-group select,
    .select2-container .select2-selection--single {
        height: 38px !important;
        padding: 6px 12px !important;
        font-size: 1rem !important;
        line-height: 1.5 !important;
        border-radius: 4px !important;
        border: 1px solid var(--border-color) !important;
    }
    
    /* SELECT2 CUSTOMIZAÇÃO */
    .select2-container {
        width: 100% !important;
    }
    
    .select2-container .select2-selection__rendered {
        line-height: 26px !important;
        color: #495057 !important;
    }
    
    .select2-container .select2-selection__arrow {
        height: 36px !important;
    }
    
    .filter-actions {
        display: flex;
        gap: 10px;
        align-self: end;
    }
    
    /* AÇÕES */
    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        border-radius: 0.25rem;
    }
    
    /* RESPONSIVIDADE */
    @media (max-width: 992px) {
        .filter-row {
            grid-template-columns: 1fr 1fr;
        }
        
        .filter-actions {
            grid-column: span 2;
            justify-content: flex-end;
        }
    }
    
    @media (max-width: 768px) {
        .header-actions {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .page-title {
            margin-bottom: 10px;
        }
        
        .btn-action-primary {
            width: 100%;
            justify-content: center;
        }
        
        .filter-row {
            grid-template-columns: 1fr;
        }
        
        .filter-actions {
            grid-column: span 1;
            width: 100%;
            justify-content: space-between;
        }
        
        /* Esconder colunas menos importantes em mobile */
        .responsive-table td:nth-child(3), /* Descrição */
        .responsive-table th:nth-child(3),
        .responsive-table td:nth-child(7), /* Data */
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
                <i class="bi bi-box-seam"></i> Gerenciamento de Produtos
            </h2>
            <div>
                <a href="product_add.php" class="btn btn-action-primary">
                    <i class="bi bi-plus-lg"></i> Novo Produto
                </a>
            </div>
        </div>

        <!-- Card de Filtros -->
        <div class="filter-card">
            <form method="get" action="products.php">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Nome do Produto</label>
                        <input type="text" name="nome" class="form-control" placeholder="Pesquisar..." value="<?= htmlspecialchars($nome) ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label>Loja</label>
                        <select name="loja" class="form-select select2">
                            <option value="">Todas as Lojas</option>
                            <?php foreach ($lojas_distintas as $l): ?>
                                <option value="<?= htmlspecialchars($l['loja']) ?>" <?= $loja == $l['loja'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($l['loja']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel"></i> Filtrar
                        </button>
                        <a href="products.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i> Limpar
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Card de Resultados -->
        <div class="card border-0 shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover responsive-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Descrição</th>
                                <th class="text-end">Preço</th>
                                <th class="text-center">Estoque</th>
                                <th>Loja</th>
                                <th>Criação</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($produtos)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        Nenhum produto encontrado com os filtros atuais
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($produtos as $produto): ?>
                                <tr>
                                    <td><?= $produto['id'] ?></td>
                                    <td><?= htmlspecialchars($produto['nome']) ?></td>
                                    <td><?= htmlspecialchars($produto['descricao']) ?></td>
                                    <td class="text-end fw-bold">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></td>
                                    <td class="text-center">
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
                                    </td>
                                    <td><?= htmlspecialchars($produto['loja']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($produto['data_criacao'])) ?></td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="product_edit.php?id=<?= $produto['id'] ?>" 
                                               class="btn btn-outline-primary"
                                               title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="delete.php?id=<?= $produto['id'] ?>" 
                                               class="btn btn-outline-danger"
                                               title="Excluir"
                                               onclick="return confirm('Tem certeza que deseja excluir este produto?');">
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    $(document).ready(function() {
        // Inicializa Select2
        $('.select2').select2({
            placeholder: "Selecione uma loja",
            width: '100%'
        });
    });
    </script>
</body>
</html>