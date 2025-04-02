<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

if (!in_array($_SESSION['nivel_acesso'], ['admin'])) {
    die("Acesso negado! Este script só pode ser executado por administradores.");
}

set_time_limit(0);

// Verifica se a tabela pagamentos existe
$tabelaExiste = $pdo->query("SHOW TABLES LIKE 'pagamentos'")->rowCount() > 0;

if (!$tabelaExiste) {
    die("Erro: A tabela 'pagamentos' não existe no banco de dados.");
}

try {
    $pdo->beginTransaction();
    $total_migrados = 0;
    
    // Consulta melhorada para detectar formatos diferentes
    $vendas = $pdo->query("
        SELECT id, tipo_pagamento 
        FROM vendas 
        WHERE tipo_pagamento IS NOT NULL
        AND tipo_pagamento != ''
        AND tipo_pagamento != 'null'
    ")->fetchAll();

    echo "<h2>Processando ".count($vendas)." vendas...</h2>";

    foreach ($vendas as $venda) {
        echo "<hr><h3>Processando venda #{$venda['id']}</h3>";
        echo "<p>Conteúdo original: <pre>{$venda['tipo_pagamento']}</pre></p>";
        
        $pagamentos = [];
        $json_data = $venda['tipo_pagamento'];
        
        // Tentativa 1: Decodificar como JSON
        $decoded = json_decode($json_data, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            $pagamentos = $decoded;
        } 
        // Tentativa 2: Verificar se já está em formato array serializado
        elseif (strpos($json_data, 'a:') === 0) {
            $pagamentos = unserialize($json_data);
        }
        // Tentativa 3: Formato customizado (como string simples)
        else {
            // Tenta extrair manualmente os valores
            if (preg_match('/([a-z_]+):([0-9.]+)/i', $json_data, $matches)) {
                $pagamentos = [['tipo' => $matches[1], 'valor' => $matches[2]]];
            }
        }

        if (!empty($pagamentos) && is_array($pagamentos)) {
            echo "<p>Pagamentos detectados: <pre>".print_r($pagamentos, true)."</pre></p>";
            
            foreach ($pagamentos as $pagamento) {
                // Normaliza os nomes dos campos
                $tipo = $pagamento['tipo'] ?? $pagamento['forma_pagamento'] ?? null;
                $valor = $pagamento['valor'] ?? $pagamento['value'] ?? null;
                
                if ($tipo && $valor) {
                    $stmt = $pdo->prepare("INSERT INTO pagamentos 
                                        (venda_id, forma_pagamento, valor, data_cadastro) 
                                        VALUES (?, ?, ?, NOW())");
                    $stmt->execute([$venda['id'], $tipo, $valor]);
                    $total_migrados++;
                    echo "<p style='color:green'>Migrado: {$tipo} - R$ {$valor}</p>";
                }
            }
        } else {
            echo "<p style='color:red'>Formato inválido ou não reconhecido</p>";
        }
    }
    
    $pdo->commit();
    
    echo "<h2 style='color:blue'>Migração concluída!</h2>";
    echo "<p>Total de vendas processadas: " . count($vendas) . "</p>";
    echo "<p>Total de pagamentos migrados: $total_migrados</p>";
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "<h2 style='color:red'>Erro durante a migração</h2>";
    echo "<p>{$e->getMessage()}</p>";
    echo "<pre>{$e->getTraceAsString()}</pre>";
}
?>