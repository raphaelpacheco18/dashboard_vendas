<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

$title = "Relatório de Vendas - Dashboard de Vendas";
include '../../templates/header.php';

// Obter parâmetros de filtro
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$store_id = $_GET['store_id'] ?? null;
$seller_id = $_GET['seller_id'] ?? null;
?>

<div class="container mt-4">
    <h2><i class="fas fa-shopping-cart"></i> Relatório de Vendas</h2>
    <hr>
    
    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="form-inline">
                <div class="form-group mr-3">
                    <label for="start_date" class="mr-2">De:</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $start_date ?>">
                </div>
                <div class="form-group mr-3">
                    <label for="end_date" class="mr-2">Até:</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $end_date ?>">
                </div>
                <div class="form-group mr-3">
                    <label for="store_id" class="mr-2">Loja:</label>
                    <select class="form-control" id="store_id" name="store_id">
                        <option value="">Todas</option>
                        <?php
                        $stores = $pdo->query("SELECT id, nome FROM lojas WHERE ativo = 1 ORDER BY nome");
                        while ($store = $stores->fetch(PDO::FETCH_ASSOC)) {
                            $selected = $store_id == $store['id'] ? 'selected' : '';
                            echo "<option value='{$store['id']}' $selected>{$store['nome']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mr-2">Filtrar</button>
                <a href="export.php?type=sales&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&store_id=<?= $store_id ?>" 
                   class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Exportar
                </a>
            </form>
        </div>
    </div>
    
    <!-- Resultados -->
    <div class="card">
        <div class="card-body">
            <?php
            // Construir query com filtros
            $query = "SELECT v.*, l.nome as loja, u.nome as vendedor 
                      FROM vendas v
                      LEFT JOIN lojas l ON v.loja_id = l.id
                      LEFT JOIN usuarios u ON v.vendedor_id = u.id
                      WHERE v.data_venda BETWEEN :start_date AND :end_date";
            
            $params = [':start_date' => $start_date, ':end_date' => $end_date];
            
            if ($store_id) {
                $query .= " AND v.loja_id = :store_id";
                $params[':store_id'] = $store_id;
            }
            
            if ($seller_id) {
                $query .= " AND v.vendedor_id = :seller_id";
                $params[':seller_id'] = $seller_id;
            }
            
            $query .= " ORDER BY v.data_venda DESC";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Loja</th>
                            <th>Vendedor</th>
                            <th>Valor</th>
                            <th>Produtos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sales as $sale): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($sale['data_venda'])) ?></td>
                            <td><?= $sale['loja'] ?></td>
                            <td><?= $sale['vendedor'] ?></td>
                            <td>R$ <?= number_format($sale['valor_total'], 2, ',', '.') ?></td>
                            <td><?= $sale['quantidade_itens'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Resumo -->
            <div class="mt-4">
                <h5>Resumo do Período</h5>
                <?php
                $total_sales = array_sum(array_column($sales, 'valor_total'));
                $avg_sale = count($sales) > 0 ? $total_sales / count($sales) : 0;
                ?>
                <p>Total de Vendas: <strong>R$ <?= number_format($total_sales, 2, ',', '.') ?></strong></p>
                <p>Número de Vendas: <strong><?= count($sales) ?></strong></p>
                <p>Ticket Médio: <strong>R$ <?= number_format($avg_sale, 2, ',', '.') ?></strong></p>
            </div>
        </div>
    </div>
</div>
<!-- Gráfico -->
<div class="card mt-4">
    <div class="card-body">
        <h5 class="card-title">Vendas por Dia</h5>
        <canvas id="salesChart" height="100"></canvas>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Preparar dados para o gráfico
const salesByDay = <?= json_encode($salesDataForChart) ?>;

// Criar gráfico
const ctx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: Object.keys(salesByDay),
        datasets: [{
            label: 'Vendas por Dia',
            data: Object.values(salesByDay),
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
<?php include '../../templates/footer.php'; ?>