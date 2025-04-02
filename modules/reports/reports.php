<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

$title = "Relatórios - Dashboard de Vendas";
include '../../templates/header.php';
?>

<div class="container mt-4">
    <h2><i class="fas fa-chart-bar"></i> Relatórios</h2>
    <hr>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-shopping-cart"></i> Relatório de Vendas</h5>
                    <p class="card-text">Relatório detalhado de vendas por período, vendedor, loja ou produto.</p>
                    <a href="sales_report.php" class="btn btn-primary">Acessar</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-boxes"></i> Relatório de Produtos</h5>
                    <p class="card-text">Análise de desempenho de produtos, estoque e movimentação.</p>
                    <a href="products_report.php" class="btn btn-primary">Acessar</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-users"></i> Relatório de Vendedores</h5>
                    <p class="card-text">Desempenho individual e comparativo dos vendedores.</p>
                    <a href="sellers_report.php" class="btn btn-primary">Acessar</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-store"></i> Relatório de Lojas</h5>
                    <p class="card-text">Comparativo de desempenho entre as lojas da rede.</p>
                    <a href="stores_report.php" class="btn btn-primary">Acessar</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-money-bill-wave"></i> Relatório Financeiro</h5>
                    <p class="card-text">Análise financeira, receitas, despesas e lucratividade.</p>
                    <a href="financial_report.php" class="btn btn-primary">Acessar</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>