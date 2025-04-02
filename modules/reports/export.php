<?php
require_once '../config/auth.php';
require_once '../config/database.php';

$type = $_GET['type'] ?? '';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$store_id = $_GET['store_id'] ?? null;
$seller_id = $_GET['seller_id'] ?? null;

switch ($type) {
    case 'sales':
        exportSalesReport($pdo, $start_date, $end_date, $store_id, $seller_id);
        break;
    // Adicionar outros casos para outros tipos de relatório
    default:
        header('Location: reports.php');
        exit;
}

function exportSalesReport($pdo, $start_date, $end_date, $store_id, $seller_id) {
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
    
    // Executar a query e obter os resultados
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC); // Esta linha define a variável $sales
    
    // Configurar headers para download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=relatorio_vendas_' . date('Ymd') . '.csv');
    
    // Criar arquivo CSV
    $output = fopen('php://output', 'w');
    
    // Escrever cabeçalho
    fputcsv($output, ['Data', 'Loja', 'Vendedor', 'Valor', 'Produtos']);
    
    // Escrever dados - agora $sales está definida
    foreach ($sales as $sale) {
        fputcsv($output, [
            date('d/m/Y', strtotime($sale['data_venda'])),
            $sale['loja'],
            $sale['vendedor'],
            $sale['valor_total'],
            $sale['quantidade_itens']
        ]);
    }
    
    fclose($output);
    exit;
}