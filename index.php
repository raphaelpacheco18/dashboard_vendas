<?php
require_once 'config/auth.php';
require_once 'config/database.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: auth/login.php');
    exit();
}

// Níveis de acesso
$nivel_acesso = $_SESSION['nivel_acesso'] ?? 'visitante';
$nome_usuario = htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário');
$hoje = date('d/m/Y');

// Simular dados para o dashboard (substitua por consultas reais)
$dados_dashboard = [
    'vendas_hoje' => 8245,
    'lucro' => 2845,
    'novos_clientes' => 24,
    'produtos_estoque_baixo' => 18,
    'meta_mensal' => 65
];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Vendas</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
    :root {
        --primary-color: #4e73df;
        --secondary-color: #224abe;
        --success-color: #1cc88a;
        --info-color: #36b9cc;
        --warning-color: #f6c23e;
        --danger-color: #e74a3b;
        --light-color: #f8f9fc;
        --dark-color: #5a5c69;
        --card-radius: 0.35rem;
        --shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15);
    }
    
    /* Dashboard Header */
    .dashboard-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        padding: 6rem 0 2rem 0;
        margin-top: -56px; /* Compensa a altura do navbar */
        margin-bottom: 2rem;
        box-shadow: var(--shadow);
    }
    
    .welcome-text {
        font-weight: 300;
    }
    
    /* Cards */
    .card {
        border: none;
        border-radius: var(--card-radius);
        box-shadow: var(--shadow);
        transition: all 0.3s;
        height: 100%;
        border-left: 4px solid;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.15);
    }
    
    .card-primary { border-left-color: var(--primary-color); }
    .card-success { border-left-color: var(--success-color); }
    .card-warning { border-left-color: var(--warning-color); }
    .card-danger { border-left-color: var(--danger-color); }
    
    .card-icon {
        font-size: 2rem;
        opacity: 0.7;
    }
    
    .stat-number {
        font-size: 1.75rem;
        font-weight: 700;
    }
    
    /* Quick Actions */
    .quick-actions .btn {
        padding: 1rem;
        border-radius: var(--card-radius);
        transition: all 0.3s;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
    }
    
    .quick-actions .btn i {
        font-size: 1.75rem;
        margin-bottom: 0.5rem;
    }
    
    /* Tables */
    .table-card .table {
        margin-bottom: 0;
    }
    
    /* Activity Feed */
    .activity-feed .feed-item {
        display: flex;
        padding: 0.75rem 0;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    
    .activity-feed .feed-item:last-child {
        border-bottom: none;
    }
    
    .feed-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        color: white;
    }
    
    .feed-content {
        flex: 1;
    }
    
    /* Schedule */
    .schedule-item {
        display: flex;
        padding: 0.75rem 0;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    
    .schedule-item:last-child {
        border-bottom: none;
    }
    
    .schedule-time {
        width: 60px;
        font-weight: 500;
        color: var(--primary-color);
    }
    
    .schedule-content {
        flex: 1;
    }
    
    /* Responsividade */
    @media (max-width: 768px) {
        .dashboard-header {
            padding: 5rem 0 1.5rem 0;
        }
        
        .welcome-text {
            font-size: 1.5rem;
        }
        
        .quick-actions .btn {
            padding: 0.75rem;
            font-size: 0.85rem;
        }
        
        .quick-actions .btn i {
            font-size: 1.5rem;
        }
    }
    </style>
</head>
<body>
    <?php include 'templates/header.php'; ?>
    
    <!-- Header do Dashboard -->
    <div class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="welcome-text">Bem-vindo, <strong><?= $nome_usuario ?></strong></h1>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-dark"><?= ucfirst($nivel_acesso) ?></span>
                        <span><i class="bi bi-calendar-check"></i> <?= $hoje ?></span>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="d-flex justify-content-end gap-2">
                        <button class="btn btn-outline-light btn-notification position-relative">
                            <i class="bi bi-bell"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                3
                            </span>
                        </button>
                        <button class="btn btn-outline-light">
                            <i class="bi bi-question-circle"></i> Ajuda
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Seção Principal -->
    <div class="container mb-5">
        <div class="row g-4">
            <!-- Cards de Métricas -->
            <div class="col-md-6 col-xl-3">
                <div class="card card-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-uppercase text-muted">Vendas Hoje</h6>
                                <h2 class="stat-number mb-0">R$ <?= number_format($dados_dashboard['vendas_hoje'], 2, ',', '.') ?></h2>
                            </div>
                            <i class="bi bi-cart-check card-icon text-primary"></i>
                        </div>
                        <div class="mt-3">
                            <span class="text-success"><i class="bi bi-arrow-up"></i> 12%</span>
                            <span class="text-muted ms-2">vs ontem</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-xl-3">
                <div class="card card-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-uppercase text-muted">Lucro</h6>
                                <h2 class="stat-number mb-0">R$ <?= number_format($dados_dashboard['lucro'], 2, ',', '.') ?></h2>
                            </div>
                            <i class="bi bi-currency-dollar card-icon text-success"></i>
                        </div>
                        <div class="mt-3">
                            <span class="text-success"><i class="bi bi-arrow-up"></i> 8%</span>
                            <span class="text-muted ms-2">vs semana passada</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-xl-3">
                <div class="card card-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-uppercase text-muted">Clientes Novos</h6>
                                <h2 class="stat-number mb-0"><?= $dados_dashboard['novos_clientes'] ?></h2>
                            </div>
                            <i class="bi bi-people card-icon text-warning"></i>
                        </div>
                        <div class="mt-3">
                            <span class="text-success"><i class="bi bi-arrow-up"></i> 5%</span>
                            <span class="text-muted ms-2">vs mês passado</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-xl-3">
                <div class="card card-danger">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-uppercase text-muted">Produtos Baixo Estoque</h6>
                                <h2 class="stat-number mb-0"><?= $dados_dashboard['produtos_estoque_baixo'] ?></h2>
                            </div>
                            <i class="bi bi-exclamation-triangle card-icon text-danger"></i>
                        </div>
                        <a href="modules/products/inventory.php" class="btn btn-sm btn-link text-danger mt-2 p-0">Ver lista</a>
                    </div>
                </div>
            </div>
            
            <!-- Gráficos -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Desempenho de Vendas (30 dias)</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Métodos de Pagamento</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="paymentChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Ações Rápidas -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0"><i class="bi bi-lightning"></i> Ações Rápidas</h5>
                    </div>
                    <div class="card-body">
                        <div class="row quick-actions g-3">
                            <?php if (in_array($nivel_acesso, ['admin', 'gerente', 'vendedor'])): ?>
                            <div class="col-6 col-md-4 col-lg-2">
                                <a href="modules/sales/sales_add.php" class="btn btn-primary">
                                    <i class="bi bi-cart-plus"></i>
                                    <span>Nova Venda</span>
                                </a>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (in_array($nivel_acesso, ['admin', 'gerente'])): ?>
                            <div class="col-6 col-md-4 col-lg-2">
                                <a href="modules/products/product_add.php" class="btn btn-success">
                                    <i class="bi bi-box-seam"></i>
                                    <span>Novo Produto</span>
                                </a>
                            </div>
                            
                            <div class="col-6 col-md-4 col-lg-2">
                                <a href="modules/products/inventory.php" class="btn btn-info">
                                    <i class="bi bi-clipboard2-pulse"></i>
                                    <span>Inventário</span>
                                </a>
                            </div>
                            <?php endif; ?>
                            
                            <div class="col-6 col-md-4 col-lg-2">
                                <a href="modules/clients/clients.php" class="btn btn-warning">
                                    <i class="bi bi-people"></i>
                                    <span>Clientes</span>
                                </a>
                            </div>
                            
                            <div class="col-6 col-md-4 col-lg-2">
                                <a href="modules/reports/" class="btn btn-danger">
                                    <i class="bi bi-graph-up"></i>
                                    <span>Relatórios</span>
                                </a>
                            </div>
                            
                            <div class="col-6 col-md-4 col-lg-2">
                                <a href="modules/calendar/" class="btn btn-secondary">
                                    <i class="bi bi-calendar-event"></i>
                                    <span>Agenda</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabelas de Dados -->
            <div class="col-lg-6">
                <div class="card table-card">
                    <div class="card-header bg-white border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-cart"></i> Últimas Vendas</h5>
                            <a href="modules/sales/sales.php" class="btn btn-sm btn-link">Ver todas</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Valor</th>
                                        <th>Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>#1254</td>
                                        <td>João Silva</td>
                                        <td>R$ 450,00</td>
                                        <td>10/05/2023</td>
                                    </tr>
                                    <tr>
                                        <td>#1253</td>
                                        <td>Maria Souza</td>
                                        <td>R$ 320,50</td>
                                        <td>10/05/2023</td>
                                    </tr>
                                    <tr>
                                        <td>#1252</td>
                                        <td>Empresa XYZ</td>
                                        <td>R$ 1.245,00</td>
                                        <td>09/05/2023</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card table-card">
                    <div class="card-header bg-white border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-box-seam"></i> Produtos Mais Vendidos</h5>
                            <a href="modules/products/products.php" class="btn btn-sm btn-link">Ver todos</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Produto</th>
                                        <th>Vendas</th>
                                        <th>Estoque</th>
                                        <th>Preço</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Notebook Dell</td>
                                        <td>45</td>
                                        <td>12</td>
                                        <td>R$ 3.450</td>
                                    </tr>
                                    <tr>
                                        <td>Mouse Sem Fio</td>
                                        <td>38</td>
                                        <td>24</td>
                                        <td>R$ 89,90</td>
                                    </tr>
                                    <tr>
                                        <td>Teclado Mecânico</td>
                                        <td>32</td>
                                        <td>8</td>
                                        <td>R$ 249,90</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Ferramentas Adicionais -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Atividades Recentes</h5>
                    </div>
                    <div class="card-body">
                        <div class="activity-feed">
                            <div class="feed-item">
                                <div class="feed-icon bg-primary">
                                    <i class="bi bi-cart-check"></i>
                                </div>
                                <div class="feed-content">
                                    <span class="text-primary">Nova venda</span> registrada por você (R$ 450,00)
                                    <small class="text-muted">10 minutos atrás</small>
                                </div>
                            </div>
                            <div class="feed-item">
                                <div class="feed-icon bg-success">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                                <div class="feed-content">
                                    Estoque atualizado para <span class="text-success">Notebook Dell</span>
                                    <small class="text-muted">1 hora atrás</small>
                                </div>
                            </div>
                            <div class="feed-item">
                                <div class="feed-icon bg-info">
                                    <i class="bi bi-person-plus"></i>
                                </div>
                                <div class="feed-content">
                                    Novo cliente <span class="text-info">Empresa ABC</span> cadastrado
                                    <small class="text-muted">3 horas atrás</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Próximos Compromissos</h5>
                    </div>
                    <div class="card-body">
                        <div class="schedule-item">
                            <div class="schedule-time">
                                <span class="time">10:00</span>
                            </div>
                            <div class="schedule-content">
                                <strong>Reunião com cliente</strong>
                                <p>Apresentação de novos produtos</p>
                            </div>
                        </div>
                        <div class="schedule-item">
                            <div class="schedule-time">
                                <span class="time">14:30</span>
                            </div>
                            <div class="schedule-content">
                                <strong>Entrega de pedido</strong>
                                <p>Pedido #1254 para Empresa XYZ</p>
                            </div>
                        </div>
                        <div class="schedule-item">
                            <div class="schedule-time">
                                <span class="time">16:00</span>
                            </div>
                            <div class="schedule-content">
                                <strong>Treinamento de equipe</strong>
                                <p>Novos procedimentos de venda</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rodapé -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Sistema de Vendas Premium</h5>
                    <p class="text-muted">Versão 2.0.0</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        <i class="bi bi-clock"></i> Última atualização: <?= date('H:i') ?>
                    </p>
                    <p class="text-muted mb-0">© <?= date('Y') ?> Todos os direitos reservados</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gráfico de Vendas
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30'],
                datasets: [{
                    label: 'Vendas Diárias',
                    data: [1200, 1900, 1500, 2000, 1800, 2200, 2400, 2100, 2300, 2500, 2700, 3000, 2800, 2600, 2400, 2500, 2700, 2900, 3100, 3000, 3200, 3400, 3300, 3500, 3700, 3600, 3800, 4000, 3900, 4200],
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Gráfico de Métodos de Pagamento
        const paymentCtx = document.getElementById('paymentChart').getContext('2d');
        const paymentChart = new Chart(paymentCtx, {
            type: 'doughnut',
            data: {
                labels: ['Cartão Crédito', 'Cartão Débito', 'PIX', 'Dinheiro', 'Boleto'],
                datasets: [{
                    data: [35, 25, 20, 15, 5],
                    backgroundColor: [
                        'rgba(78, 115, 223, 0.8)',
                        'rgba(28, 200, 138, 0.8)',
                        'rgba(54, 185, 204, 0.8)',
                        'rgba(246, 194, 62, 0.8)',
                        'rgba(231, 74, 59, 0.8)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });
    </script>
</body>
</html>