<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Verificar permissões
if (!in_array($_SESSION['nivel_acesso'], ['admin', 'gerente', 'vendedor'])) {
    $_SESSION['error'] = "Acesso negado: Você não tem permissão para visualizar vendas";
    header('Location: ../../index.php');
    exit();
}

// Obter lojas para filtro
$lojas = $pdo->query("SELECT id, nome FROM lojas WHERE status = 1 ORDER BY nome")->fetchAll();

try {
    // Construir consulta com filtros
    $sql = "SELECT v.id, v.data_venda, v.valor_total, v.tipo_pagamento, 
               l.nome AS nome_loja, ve.nome AS nome_vendedor
        FROM vendas v
        LEFT JOIN lojas l ON v.loja_id = l.id
        LEFT JOIN vendedoras ve ON v.vendedora_id = ve.id
        WHERE 1=1";

    
    $params = [];
    
    // Aplicar filtros
    if (!empty($_GET['data_inicio'])) {
        $sql .= " AND DATE(v.data_venda) >= :data_inicio";
        $params[':data_inicio'] = $_GET['data_inicio'];
    }
    
    if (!empty($_GET['data_fim'])) {
        $sql .= " AND DATE(v.data_venda) <= :data_fim";
        $params[':data_fim'] = $_GET['data_fim'];
    }
    
    if (!empty($_GET['loja_id'])) {
        $sql .= " AND v.loja_id = :loja_id";
        $params[':loja_id'] = $_GET['loja_id'];
    }
    
    $sql .= " ORDER BY v.data_venda DESC";
    
    $stmtVendas = $pdo->prepare($sql);
    $stmtVendas->execute($params);
    
    $vendas = $stmtVendas->fetchAll(PDO::FETCH_ASSOC);

    // Para cada venda, buscar detalhes
    foreach ($vendas as &$venda) {
        // Itens da venda
        $stmtItens = $pdo->prepare("
            SELECT vi.*, p.nome as nome_produto, p.preco as preco_cadastrado
            FROM venda_itens vi
            JOIN produtos p ON vi.produto_id = p.id
            WHERE vi.venda_id = :venda_id
        ");
        $stmtItens->execute([':venda_id' => $venda['id']]);
        $venda['itens'] = $stmtItens->fetchAll(PDO::FETCH_ASSOC);
        
        // Pagamentos da venda
        $stmtPagamentos = $pdo->prepare("
            SELECT forma_pagamento, valor 
            FROM pagamentos 
            WHERE venda_id = :venda_id
        ");
        $stmtPagamentos->execute([':venda_id' => $venda['id']]);
        $venda['pagamentos'] = $stmtPagamentos->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($venda);

} catch (PDOException $e) {
    $_SESSION['error'] = "Erro de banco de dados: " . $e->getMessage();
    header('Location: ../../index.php');
    exit();
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: ../../index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Vendas</title>
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
    --card-radius: 8px;
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

.filters-container {
    background-color: white;
    padding: 20px;
    border-radius: var(--card-radius);
    box-shadow: var(--card-shadow);
    margin-bottom: 20px;
}

.filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.card {
    border-radius: var(--card-radius);
    box-shadow: var(--card-shadow);
    border: none;
    margin-bottom: 20px;
    overflow: hidden;
}

.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table {
    width: 100%;
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
}

.table thead th {
    background-color: var(--primary-color);
    color: white;
    font-weight: 500;
    padding: 12px 15px;
    vertical-align: middle;
    border: none;
    position: sticky;
    top: 0;
}

.table td {
    padding: 12px 15px;
    vertical-align: middle;
    border-top: 1px solid var(--border-color);
    background-color: white;
}

.table-hover tbody tr:hover {
    background-color: var(--primary-light);
}

/* Badges para tipos de pagamento */
.badge {
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
    display: inline-block;
}

.badge-dinheiro {
    background-color: var(--success-color);
    color: white;
}

.badge-pix {
    background-color: var(--info-color);
    color: white;
}

.badge-cartao-credito {
    background-color: #6f42c1;
    color: white;
}

.badge-cartao-debito {
    background-color: var(--warning-color);
    color: white;
}

/* Alertas */
.alert {
    padding: 15px;
    border-radius: var(--card-radius);
    margin-bottom: 20px;
    border: none;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border-left: 4px solid var(--success-color);
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border-left: 4px solid var(--danger-color);
}

/* Botões */
.btn {
    transition: all 0.3s;
}

.btn-primary {
    background-color: var(--primary-color);
    border: none;
}

.btn-outline-primary {
    color: var(--primary-color);
    border-color: var(--primary-color);
}

.btn-outline-primary:hover {
    background-color: var(--primary-color);
    color: white;
}

/* Responsividade */
@media (max-width: 768px) {
    .main-container {
        padding: 0 10px;
    }
    
    .header-actions {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .filter-row {
        flex-direction: column;
        gap: 10px;
    }
    
    .filter-group {
        width: 100%;
        min-width: auto;
    }
    
    /* Tabela responsiva */
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
        padding: 10px 0;
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
    .table td:nth-child(1)::before { content: "Data"; }
    .table td:nth-child(2)::before { content: "Loja"; }
    .table td:nth-child(3)::before { content: "Vendedor"; }
    .table td:nth-child(4)::before { content: "Valor"; }
    .table td:nth-child(5)::before { content: "Itens"; }
    .table td:nth-child(6)::before { content: "Pagamentos"; }
}

/* Melhorias para tablets */
@media (min-width: 769px) and (max-width: 992px) {
    .table td, .table th {
        padding: 10px 12px !important;
    }
}
.filters-container {
    background-color: white;
    padding: 20px;
    border-radius: var(--card-radius);
    box-shadow: var(--card-shadow);
    margin-bottom: 20px;
    width: 100%; /* Garante que ocupa toda a largura */
}

.filter-row {
    width: 100%;
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

/* Ajuste para os selects do Select2 */
.select2-container--default .select2-selection--single {
    height: 38px;
    padding: 5px 10px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}

/* Ajuste para mobile */
@media (max-width: 768px) {
    .filters-container {
        padding: 15px;
    }
    
    .col-md-3, .col-sm-6 {
        margin-bottom: 15px;
    }
    
    .d-flex.gap-2 {
        gap: 10px !important;
    }
}
</style>

</head>
<body>
    <?php include '../../templates/header.php'; ?>
    
    <div class="main-container">
        <div class="header-actions">
            <h1 class="page-title">
                <i class="bi bi-cart-check"></i> Lista de Vendas
            </h1>
            <a href="sales_add.php" class="btn btn-action-primary">
    <i class="bi bi-plus-lg"></i> Nova Venda
</a>
        </div>
        
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="filters-container">
    <form method="GET" class="filter-row">
        <div class="row g-3 align-items-end w-100"> <!-- Adicionei esta div row -->
            <div class="col-md-3 col-sm-6"> <!-- Ajuste as classes col para responsividade -->
                <label class="form-label">Data Inicial</label>
                <input type="date" name="data_inicio" class="form-control" value="<?= htmlspecialchars($_GET['data_inicio'] ?? '') ?>">
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label">Data Final</label>
                <input type="date" name="data_fim" class="form-control" value="<?= htmlspecialchars($_GET['data_fim'] ?? '') ?>">
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label">Loja</label>
                <select name="loja_id" class="form-control select2">
                    <option value="">Todas</option>
                    <?php foreach ($lojas as $loja): ?>
                        <option value="<?= $loja['id'] ?>" <?= ($_GET['loja_id'] ?? '') == $loja['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($loja['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 col-sm-6 d-flex align-items-end gap-2"> <!-- Ajuste para botões -->
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
                <a href="sales.php" class="btn btn-secondary flex-grow-1">
                    <i class="bi bi-arrow-counterclockwise"></i> Limpar
                </a>
            </div>
        </div>
    </form>
</div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Data/Hora</th>
                                <th>Loja</th>
                                <th>Vendedor</th>
                                <th class="text-end">Valor</th>
                                <th>Itens</th>
                                <th>Pagamentos</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($vendas)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        Nenhuma venda encontrada
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($vendas as $venda): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= date('d/m/Y', strtotime($venda['data_venda'])) ?></div>
                                        <div class="text-muted small"><?= date('H:i', strtotime($venda['data_venda'])) ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($venda['nome_loja']) ?></td>
                                    <td><?= htmlspecialchars($venda['nome_vendedor'] ?? 'Desconhecido') ?></td>

                                    <td class="text-end fw-bold">R$ <?= number_format($venda['valor_total'], 2, ',', '.') ?></td>
                                    <td>
                                        <ul class="list-unstyled small">
                                            <?php foreach ($venda['itens'] as $item): ?>
                                            <li>
                                                <?= htmlspecialchars($item['quantidade']) ?> × 
                                                <?= htmlspecialchars($item['nome_produto']) ?>
                                                <span class="text-muted">
                                                    (R$ <?= number_format($item['preco_unitario'], 2, ',', '.') ?>)
                                                </span>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </td>
                                    <td>
    <?php 
    $pagamentosExibir = [];
    
    // Primeiro tenta obter da tabela pagamentos
    if (!empty($venda['pagamentos']) && is_array($venda['pagamentos'])) {
        $pagamentosExibir = $venda['pagamentos'];
    } 
    // Fallback para o campo tipo_pagamento (JSON)
    elseif (!empty($venda['tipo_pagamento']) && is_string($venda['tipo_pagamento'])) {
        $jsonData = json_decode($venda['tipo_pagamento'], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
            $pagamentosExibir = $jsonData;
        }
    }
    
    if (!empty($pagamentosExibir)): ?>
        <ul class="list-unstyled small">
            <?php foreach ($pagamentosExibir as $pagamento): 
                $tipoPagamento = htmlspecialchars((string)($pagamento['forma_pagamento'] ?? $pagamento['tipo'] ?? 'dinheiro'));
                $valorPagamento = number_format((float)($pagamento['valor'] ?? 0), 2, ',', '.');
                $badgeClass = str_replace('_', '-', $tipoPagamento);
            ?>
                <li>
                    <span class="badge badge-<?= $badgeClass ?>">
                        <?= ucfirst(str_replace('_', ' ', $tipoPagamento)) ?>
                    </span>
                    R$ <?= $valorPagamento ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <span class="text-muted small">N/A</span>
    <?php endif; ?>
</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="sales_view.php?id=<?= $venda['id'] ?>" class="btn btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if (in_array($_SESSION['nivel_acesso'], ['admin', 'gerente'])): ?>
                                                <a href="sales_edit.php?id=<?= $venda['id'] ?>" class="btn btn-outline-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="sales_delete.php?id=<?= $venda['id'] ?>" class="btn btn-outline-danger"
                                                   onclick="return confirm('Tem certeza que deseja excluir esta venda?');">
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
        $('.select2').select2();
        
        // Fecha alerts automaticamente após 5 segundos
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    });
    </script>
</body>
</html>