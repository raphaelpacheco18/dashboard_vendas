<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Parâmetros de filtro
$filtro = $_GET['filtro'] ?? '';
$loja_id = $_GET['loja_id'] ?? '';
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';

// Construção da consulta SQL
$sql = "SELECT v.id, v.data_venda, p.nome AS produto, 
               l.nome AS loja, vd.nome AS vendedora,
               v.valor_total, v.tipo_pagamento, v.varios_pagamentos
        FROM vendas v
        JOIN produtos p ON v.produto_id = p.id
        JOIN lojas l ON v.loja_id = l.id
        JOIN vendedoras vd ON v.vendedora_id = vd.id
        WHERE 1=1";

$params = [];

// Aplicação dos filtros
if (!empty($filtro)) {
    $sql .= " AND (p.nome LIKE :filtro OR l.nome LIKE :filtro OR vd.nome LIKE :filtro OR v.varios_pagamentos LIKE :filtro)";
    $params[':filtro'] = "%$filtro%";
}

if (!empty($loja_id)) {
    $sql .= " AND v.loja_id = :loja_id";
    $params[':loja_id'] = $loja_id;
}

if (!empty($data_inicio) && !empty($data_fim)) {
    $sql .= " AND v.data_venda BETWEEN :data_inicio AND :data_fim";
    $params[':data_inicio'] = $data_inicio;
    $params[':data_fim'] = $data_fim;
}

// Ordenação garantida pela data mais recente primeiro e limitando a 200 registros
$sql .= " ORDER BY v.data_venda DESC, v.id DESC LIMIT 200";

// Execução da consulta
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obter lojas para o dropdown
    $lojas = $pdo->query("SELECT id, nome FROM lojas ORDER BY nome")->fetchAll();
} catch (PDOException $e) {
    die("Erro ao buscar vendas: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendas - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
    :root {
        --primary-color: #3498db;
        --success-color: #28a745;
        --hover-success: #218838;
        --danger-color: #dc3545;
        --warning-color: #fd7e14;
        --info-color: #17a2b8;
        --sidebar-gradient-start: #2c3e50;
        --sidebar-gradient-end: #34495e;
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
    
    .btn-nova-venda-topo {
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
    
    .btn-nova-venda-topo:hover {
        background: linear-gradient(135deg, var(--btn-gradient-hover-start), var(--btn-gradient-hover-end));
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        color: white !important;
    }
    
    .btn-nova-venda-topo i {
        margin-right: 8px;
        font-size: 1.1em;
        color: white !important;
    }
    
    .filter-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 20px;
        padding: 20px;
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
    
    .badge-pagamento {
        font-size: 0.75rem;
        padding: 5px 10px;
        border-radius: 20px;
        color: white;
        margin-right: 4px;
        margin-bottom: 4px;
        display: inline-block;
    }
    
    .badge-cartao { background-color: var(--primary-color); }
    .badge-pix { background-color: var(--success-color); }
    .badge-debito { background-color: var(--info-color); }
    .badge-dinheiro { background-color: var(--warning-color); }
    .badge-multiplo { background-color: var(--danger-color); }
    
    /* ESTILOS PARA OS FILTROS */
    .filter-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr) auto;
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
    }
    
    /* AJUSTES PARA OS CAMPOS DE FILTRO */
    .filter-group input[type="text"],
    .filter-group input[type="date"],
    .select2-container .select2-selection--single {
        height: 38px !important;
        padding: 6px 12px !important;
        font-size: 1rem !important;
        line-height: 1.5 !important;
        border-radius: 4px !important;
        border: 1px solid #ced4da !important;
    }
    
    /* SELECT2 CUSTOMIZAÇÃO */
    .select2-container {
        width: 100% !important;
    }
    
    .select2-container .select2-selection__rendered {
        line-height: 26px !important;
    }
    
    .select2-container .select2-selection__arrow {
        height: 36px !important;
    }
    
    .filter-actions {
        display: flex;
        gap: 10px;
        align-self: end;
        margin-left: 10px;
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
        
        .btn-nova-venda-topo {
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
            margin-left: 0;
            margin-top: 10px;
        }
        
        td:nth-child(3), th:nth-child(3), /* Loja */
        td:nth-child(4), th:nth-child(4) { /* Vendedor */
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
                <i class="bi bi-receipt"></i> Controle de Vendas
            </h2>
            <div>
                <a href="sales_add.php" class="btn btn-nova-venda-topo">
                    <i class="bi bi-plus-lg"></i> Nova Venda
                </a>
            </div>
        </div>

        <!-- Card de Filtros -->
        <div class="filter-card">
            <form method="get" action="sales.php">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Pesquisa Geral</label>
                        <input type="text" name="filtro" class="form-control" placeholder="Produto, Loja, Vendedor..." value="<?= htmlspecialchars($filtro) ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label>Loja</label>
                        <select name="loja_id" class="form-select select2">
                            <option value="">Todas</option>
                            <?php foreach ($lojas as $loja): ?>
                                <option value="<?= $loja['id'] ?>" <?= $loja_id == $loja['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($loja['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Data Início</label>
                        <input type="date" name="data_inicio" class="form-control" value="<?= $data_inicio ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label>Data Fim</label>
                        <input type="date" name="data_fim" class="form-control" value="<?= $data_fim ?>">
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel"></i> Filtrar
                        </button>
                        <a href="sales.php" class="btn btn-outline-secondary">
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
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Produto</th>
                                <th>Loja</th>
                                <th>Vendedor</th>
                                <th class="text-end">Valor</th>
                                <th>Pagamento</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($vendas)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        Nenhuma venda encontrada com os filtros atuais
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($vendas as $venda): ?>
                                <tr>
                                    <td><?= ($venda['data_venda'] == '0000-00-00') ? 'N/A' : date('d/m/Y', strtotime($venda['data_venda'])) ?></td>
                                    <td><?= htmlspecialchars($venda['produto']) ?></td>
                                    <td><?= htmlspecialchars($venda['loja']) ?></td>
                                    <td><?= htmlspecialchars($venda['vendedora']) ?></td>
                                    <td class="text-end fw-bold">R$ <?= number_format($venda['valor_total'], 2, ',', '.') ?></td>
                                    <td>
                                        <?php
                                        if ($venda['tipo_pagamento'] === 'multipla' && !empty($venda['varios_pagamentos'])) {
                                            $parts = explode('Múltiplo: ', $venda['varios_pagamentos']);
                                            if (isset($parts[1])) {
                                                $payments = explode(', ', $parts[1]);
                                                foreach ($payments as $payment) {
                                                    $paymentParts = explode(':', $payment);
                                                    if (count($paymentParts) >= 2) {
                                                        $type = trim($paymentParts[0]);
                                                        $value = trim($paymentParts[1]);
                                                        
                                                        $badge_class = match(strtolower($type)) {
                                                            'dinheiro' => 'badge-dinheiro',
                                                            'cartão' => 'badge-cartao',
                                                            'pix' => 'badge-pix',
                                                            'débito' => 'badge-debito',
                                                            default => 'badge-multiplo'
                                                        };
                                                        
                                                        echo '<span class="badge-pagamento '.$badge_class.'" title="R$ '.htmlspecialchars($value).'">';
                                                        echo htmlspecialchars($type);
                                                        echo '</span>';
                                                    }
                                                }
                                            }
                                        } else {
                                            $badge_class = match(strtolower($venda['tipo_pagamento'])) {
                                                'cartao' => 'badge-cartao',
                                                'pix' => 'badge-pix',
                                                'debito' => 'badge-debito',
                                                'dinheiro' => 'badge-dinheiro',
                                                default => 'badge-multiplo'
                                            };
                                            echo '<span class="badge-pagamento '.$badge_class.'">';
                                            echo ucfirst($venda['tipo_pagamento']);
                                            echo '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="sales_edit.php?id=<?= $venda['id'] ?>" 
                                               class="btn btn-outline-primary"
                                               title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="sales_delete.php?id=<?= $venda['id'] ?>" 
                                               class="btn btn-outline-danger"
                                               title="Excluir"
                                               onclick="return confirm('Tem certeza que deseja excluir esta venda?');">
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