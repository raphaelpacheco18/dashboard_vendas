<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

if (!in_array($_SESSION['nivel_acesso'], ['admin'])) {
    die("Acesso negado! Este script só pode ser executado por administradores.");
}

set_time_limit(0);

try {
    $pdo->beginTransaction();
    
    echo "<h2>Iniciando correção de pagamentos...</h2>";
    
    // 1. Atualiza os pagamentos existentes
    $update = $pdo->exec("UPDATE pagamentos SET 
        forma_pagamento = CASE 
            WHEN valor > 1000 THEN 'cartao_credito'
            WHEN valor BETWEEN 100 AND 1000 THEN 'pix'
            ELSE 'dinheiro'
        END");
    
    echo "<p>Atualizados $update registros na tabela pagamentos</p>";
    
    // 2. Processa vendas sem pagamentos registrados
    $vendas_sem_pagamento = $pdo->query("
        SELECT v.id, v.tipo_pagamento, v.valor_total
        FROM vendas v
        LEFT JOIN pagamentos p ON v.id = p.venda_id
        WHERE p.id IS NULL
        ORDER BY v.id
    ")->fetchAll();

    echo "<p>Encontradas ".count($vendas_sem_pagamento)." vendas sem pagamentos</p>";
    
    foreach ($vendas_sem_pagamento as $venda) {
        $tipo_pagamento = 'dinheiro'; // Padrão
        
        // Define o tipo baseado no valor (ajuste conforme sua regra)
        if ($venda['valor_total'] > 1000) {
            $tipo_pagamento = 'cartao_credito';
        } elseif ($venda['valor_total'] > 100) {
            $tipo_pagamento = 'pix';
        }
        
        $stmt = $pdo->prepare("INSERT INTO pagamentos 
            (venda_id, forma_pagamento, valor, data_cadastro)
            VALUES (?, ?, ?, NOW())");
        $stmt->execute([
            $venda['id'],
            $tipo_pagamento,
            $venda['valor_total']
        ]);
        
        echo "<p>Inserido pagamento para venda #{$venda['id']}: $tipo_pagamento - R$ {$venda['valor_total']}</p>";
    }
    
    $pdo->commit();
    echo "<h2 style='color:green'>Correção concluída com sucesso!</h2>";
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "<h2 style='color:red'>Erro durante a correção</h2>";
    echo "<p>{$e->getMessage()}</p>";
}
?>