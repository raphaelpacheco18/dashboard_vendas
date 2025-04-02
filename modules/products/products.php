<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Verificar permissões
if (!in_array($_SESSION['nivel_acesso'], ['admin', 'gerente', 'vendedor'])) {
    $_SESSION['error'] = "Acesso negado: Você não tem permissão para acessar esta página";
    header('Location: ../../index.php');
    exit();
}

// Variáveis para filtros
$filtro_nome = $_GET['nome'] ?? '';
$filtro_loja = $_GET['loja'] ?? '';

// Consulta com JOIN para obter nome da loja
// Consulta com JOIN para obter nome da loja
$sql = "SELECT p.id, p.nome, p.descricao, p.preco, 
               p.quantidade_atual, p.estoque, p.ativo,
               l.nome AS loja_nome
        FROM produtos p
        LEFT JOIN lojas l ON p.loja_id = l.id
        WHERE 1=1";

$params = [];

// Aplicar filtros
if (!empty($filtro_nome)) {
    $sql .= " AND p.nome LIKE :nome";
    $params[':nome'] = "%$filtro_nome%";
}

if (!empty($filtro_loja) && is_numeric($filtro_loja)) {
    $sql .= " AND p.loja_id = :loja";
    $params[':loja'] = $filtro_loja;
}

// Ordenação padrão
$sql .= " ORDER BY p.nome ASC";

// Executar consulta
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obter todas as lojas ativas para o dropdown
$lojas = $pdo->query("SELECT id, nome FROM lojas WHERE status = 1 ORDER BY nome")->fetchAll();

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
    <title>Lista de Produtos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
    :root {
        --primary-color: #3498db;
        --primary-light: #e3f2fd;
        --success-color: #28a745;
        --danger-color: #dc3545;
        --warning-color: #fd7e14;
        --info-color: #17a2b8;
        --text-dark: #2c3e50;
        --text-muted: #6c757d;
        --border-color: #dee2e6;
        --card-shadow: 0 5px 15px rgba(0,0,0,0.05);
        --card-radius: 10px;
    }
    
    body {
        background-color: #f8f9fa;
        padding-top: 20px;
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        line-height: 1.5;
    }
    
    .main-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 15px;
    }
    
    /* ===== HEADER E BOTÕES ===== */
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
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: white !important;
        border-radius: var(--card-radius);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        text-decoration: none;
    }
    
    .btn-action-primary:hover {
        background: linear-gradient(135deg, var(--info-color), var(--primary-color));
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        color: white !important;
    }
    
    /* ===== FILTROS - AJUSTADO ===== */
    .filters-container {
        background-color: white;
        padding: 20px;
        border-radius: var(--card-radius);
        box-shadow: var(--card-shadow);
        margin-bottom: 20px;
    }
    
    .filter-row {
        display: flex;
        flex-wrap: nowrap; /* Alterado para nowrap */
        gap: 15px;
        align-items: flex-end;
    }
    
    .filter-group {
        flex: 1;
        min-width: 200px;
    }
    
    .filter-label {
        font-size: 0.9rem;
        font-weight: 500;
        margin-bottom: 5px;
        display: block;
        color: var(--text-dark);
    }
    
    .filter-button-group {
        display: flex;
        gap: 10px;
        align-items: center;
        flex: 0 0 auto;
    }
    
    .btn-reset-filter {
        white-space: nowrap;
    }
    
    /* ===== TABELA ===== */
    .card {
        border-radius: var(--card-radius);
        box-shadow: var(--card-shadow);
        border: none;
        margin-bottom: 20px;
        overflow: hidden;
    }
    
    .table-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .table {
        width: 100%;
        margin-bottom: 0;
        min-width: 600px;
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
        vertical-align: middle;
        white-space: nowrap;
    }
    
    .table td {
        padding: 12px 15px !important;
        vertical-align: middle;
        border-top: 1px solid var(--border-color);
    }
    
    .table-hover tbody tr:hover {
        background-color: var(--primary-light);
    }
    
    /* ===== BADGES E STATUS ===== */
    .badge-status {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        display: inline-block;
    }
    
    .badge-active {
        background-color: var(--success-color);
        color: white;
    }
    
    .badge-inactive {
        background-color: var(--danger-color);
        color: white;
    }
    
    /* ===== BOTÕES DE AÇÃO ===== */
    .btn-group-sm > .btn {
        padding: 0.3rem 0.5rem;
        font-size: 0.875rem;
        border-radius: 4px;
    }
    
    .btn-outline-primary {
        color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .btn-outline-primary:hover {
        background-color: var(--primary-color);
        color: white;
    }
    
    .btn-outline-warning {
        color: var(--warning-color);
        border-color: var(--warning-color);
    }
    
    .btn-outline-warning:hover {
        background-color: var(--warning-color);
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
    
    /* ===== ALERTAS ===== */
    .alert {
        padding: 15px;
        border-radius: var(--card-radius);
        margin-bottom: 20px;
        border: none;
    }
    
    .alert-success {
        background-color: #d4edda;
        color: #155724;
    }
    
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    /* ===== SELECT2 CUSTOMIZADO ===== */
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid var(--border-color);
        border-radius: 4px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        padding-left: 12px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
    
    /* ===== RESPONSIVIDADE - MOBILE ===== */
    @media (max-width: 768px) {
        .main-container {
            padding: 0 10px;
        }
        
        .header-actions {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .page-title {
            font-size: 1.5rem;
        }
        
        .filter-row {
            flex-wrap: wrap;
        }
        
        .filter-group {
            width: 100%;
            min-width: auto;
        }
        
        .filter-button-group {
            width: 100%;
            justify-content: flex-end;
        }
        
        /* TABELA MOBILE */
        .table-container {
            overflow-x: visible;
        }
        
        .table {
            min-width: 100%;
        }
        
        .table thead {
            display: none;
        }
        
        .table tbody tr {
            display: block;
            margin-bottom: 15px;
            border: 1px solid var(--border-color);
            border-radius: var(--card-radius);
            padding: 15px;
            background: white;
            box-shadow: var(--card-shadow);
        }
        
        .table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0 !important;
            border: none !important;
            border-bottom: 1px solid #f5f5f5 !important;
        }
        
        .table td:last-child {
            border-bottom: none !important;
            justify-content: flex-end;
            gap: 8px;
            padding-top: 15px !important;
        }
        
        .table td::before {
            content: attr(data-label);
            font-weight: 600;
            margin-right: auto;
            padding-right: 15px;
            text-align: left;
            color: var(--primary-color);
            font-size: 0.85rem;
        }
        
        /* Labels específicas para cada coluna */
        .table td:nth-child(1)::before { content: "Produto"; }
        .table td:nth-child(2)::before { content: "Preço"; }
        .table td:nth-child(3)::before { content: "Disponível"; }
        .table td:nth-child(4)::before { content: "Estoque"; }
        .table td:nth-child(5)::before { content: "Loja"; }
        .table td:nth-child(6)::before { content: "Status"; }
        
        /* Esconde colunas menos importantes em mobile */
        .table td:nth-child(4),
        .table td:nth-child(5) {
            display: none;
        }
        
        /* Ajusta o badge de status */
        .badge-status {
            padding: 4px 8px;
            font-size: 0.75rem;
        }
    }
    
    /* ===== MELHORIAS PARA TABLETS ===== */
    @media (min-width: 769px) and (max-width: 992px) {
        .table td, .table th {
            padding: 10px 12px !important;
        }
        
        /* Esconde coluna de estoque total */
        .table td:nth-child(4),
        .table th:nth-child(4) {
            display: none;
        }
    }
    </style>
</head>
<body>
    <?php include '../../templates/header.php'; ?>
    
    <div class="main-container">
        <div class="header-actions">
            <h1 class="page-title">
                <i class="bi bi-box-seam"></i> Lista de Produtos
            </h1>
            <?php if (in_array($_SESSION['nivel_acesso'], ['admin', 'gerente'])): ?>
                <a href="product_add.php" class="btn btn-action-primary">
                    <i class="bi bi-plus-lg"></i> Adicionar Produto
                </a>
            <?php endif; ?>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="filters-container">
            <form method="GET" action="products.php">
                <div class="filter-row">
                    <div class="filter-group">
                        <label class="filter-label">Nome do Produto</label>
                        <input type="text" name="nome" class="form-control" 
                               value="<?= htmlspecialchars($filtro_nome) ?>" 
                               placeholder="Digite para filtrar...">
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Loja</label>
                        <select name="loja" class="form-control select2">
                            <option value="">Todas as lojas</option>
                            <?php foreach ($lojas as $loja): ?>
                                <option value="<?= $loja['id'] ?>" 
                                    <?= $filtro_loja == $loja['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($loja['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-button-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel"></i> Filtrar
                        </button>
                        <a href="products.php" class="btn btn-outline-secondary btn-sm btn-reset-filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Tabela de Produtos -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-container">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Preço</th>
                                <th>Disponível</th>
                                <th>Estoque</th>
                                <th>Loja</th>
                                <th>Status</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($produtos)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        Nenhum produto encontrado
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($produtos as $produto): ?>
                                    <tr>
                                        <td data-label="Nome"><?= htmlspecialchars($produto['nome']) ?></td>
                                        <td data-label="Preço">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></td>
                                        <td data-label="Disponível"><?= $produto['quantidade_atual'] ?></td>
                                        <td data-label="Estoque"><?= $produto['estoque'] ?></td>
                                        <td data-label="Loja"><?= htmlspecialchars($produto['loja_nome']) ?></td>
                                        <td data-label="Status">
                                            <span class="badge-status <?= $produto['ativo'] ? 'badge-active' : 'badge-inactive' ?>">
                                                <?= $produto['ativo'] ? 'Ativo' : 'Inativo' ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="product_details.php?id=<?= $produto['id'] ?>" 
                                                   class="btn btn-outline-primary"
                                                   title="Detalhes">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <?php if (in_array($_SESSION['nivel_acesso'], ['admin', 'gerente'])): ?>
                                                    <a href="product_edit.php?id=<?= $produto['id'] ?>" 
                                                       class="btn btn-outline-warning"
                                                       title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="delete.php?id=<?= $produto['id'] ?>" 
                                                       class="btn btn-outline-danger"
                                                       title="Excluir"
                                                       onclick="return confirm('Tem certeza que deseja excluir este produto?')">
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

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    $(document).ready(function() {
        // Inicializa Select2
        $('.select2').select2({
            placeholder: "Selecione uma loja",
            width: '100%'
        });
        
        // Fecha automaticamente os alerts após 5 segundos
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
        
        // Configura labels para mobile
        function setupMobileLabels() {
            if (window.innerWidth <= 768) {
                $('table td').each(function() {
                    const headerIndex = $(this).index() + 1;
                    const headerText = $(`table th:nth-child(${headerIndex})`).text();
                    $(this).attr('data-label', headerText);
                });
            } else {
                $('table td').removeAttr('data-label');
            }
        }
        
        // Executa ao carregar e redimensionar
        setupMobileLabels();
        window.addEventListener('resize', setupMobileLabels);
    });
    </script>
</body>
</html>