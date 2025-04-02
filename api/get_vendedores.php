<?php
require_once '../config/database.php';
require_once '../config/auth.php';

header('Content-Type: application/json');

// Verificar se o parâmetro loja_id foi enviado
if (!isset($_GET['loja_id']) || empty($_GET['loja_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Parâmetro loja_id é obrigatório']);
    exit();
}

$loja_id = (int)$_GET['loja_id'];

try {
    // Consulta para obter vendedoras ativas da loja selecionada
    $stmt = $pdo->prepare("
        SELECT id, nome 
        FROM vendedoras 
        WHERE loja_id = :loja_id 
        AND status = 1
        ORDER BY nome
    ");
    
    $stmt->execute([':loja_id' => $loja_id]);
    $vendedoras = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($vendedoras);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao consultar vendedoras: ' . $e->getMessage()]);
}