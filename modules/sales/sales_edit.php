<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Obter ID da venda a ser editada
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: sales.php');
    exit();
}

// Buscar dados da venda
$stmt = $pdo->prepare("SELECT * FROM vendas WHERE id = ?");
$stmt->execute([$id]);
$venda = $stmt->fetch();

if (!$venda) {
    $_SESSION['error'] = 'Venda não encontrada';
    header('Location: sales.php');
    exit();
}

// Buscar dados para os dropdowns
$produtos = $pdo->query("SELECT id, nome FROM produtos ORDER BY nome")->fetchAll();
$lojas = $pdo->query("SELECT id, nome FROM lojas ORDER BY nome")->fetchAll();
$vendedoras = $pdo->query("SELECT id, nome FROM vendedoras ORDER BY nome")->fetchAll();

// Verificar se é pagamento múltiplo (pelo campo varios_pagamentos)
$isMultiPayment = (strpos($venda['varios_pagamentos'], 'Múltiplo:') === 0);
$paymentData = [];

if ($isMultiPayment) {
    // Extrair dados de pagamento múltiplo do campo varios_pagamentos
    $parts = explode('Múltiplo: ', $venda['varios_pagamentos']);
    if (isset($parts[1])) {
        $payments = explode(', ', $parts[1]);
        foreach ($payments as $payment) {
            $paymentParts = explode(':', $payment);
            if (count($paymentParts) >= 2) {
                $paymentData[] = [
                    'type' => trim($paymentParts[0]),
                    'value' => (float)trim($paymentParts[1])
                ];
            }
        }
    }
}

// Processar formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $produto_id = $_POST['produto_id'];
        $loja_id = $_POST['loja_id'];
        $vendedora_id = $_POST['vendedora_id'];
        $valor_total = (float)$_POST['valor_total'];
        $tipo_pagamento_form = $_POST['tipo_pagamento'];
        $varios_pagamentos = $_POST['varios_pagamentos'] ?? null;
        $data_venda = $_POST['data_venda'] ?? date('Y-m-d');

        // Mapear valores do formulário para valores do ENUM
        $mapeamento_pagamentos = [
            'Dinheiro' => 'dinheiro',
            'Cartão' => 'cartao',
            'Pix' => 'pix',
            'Débito' => 'debito',
            'Múltiplo' => 'multipla'
        ];

        $tipo_pagamento = $mapeamento_pagamentos[$tipo_pagamento_form] ?? 'dinheiro';

        // Para pagamentos múltiplos, formatar os detalhes para o campo varios_pagamentos
        if ($tipo_pagamento_form === 'Múltiplo') {
            $multi_payments = [];
            $calculated_total = 0;
            
            if (isset($_POST['multi_payment_type'])) {
                foreach ($_POST['multi_payment_type'] as $index => $type) {
                    $value = (float)$_POST['multi_payment_value'][$index];
                    if ($value > 0) {
                        $multi_payments[] = $type . ':' . number_format($value, 2);
                        $calculated_total += $value;
                    }
                }
            }
            
            // Validar total
            if (abs($calculated_total - $valor_total) > 0.01) {
                throw new Exception('A soma dos pagamentos (R$ ' . number_format($calculated_total, 2, ',', '.') . 
                                  ') não corresponde ao valor total da venda (R$ ' . 
                                  number_format($valor_total, 2, ',', '.') . ')');
            }
            
            // Armazenar detalhes no campo varios_pagamentos
            $varios_pagamentos = 'Múltiplo: ' . implode(', ', $multi_payments);
        }

        // Atualizar no banco de dados
        $stmt = $pdo->prepare("UPDATE vendas SET 
                             produto_id = ?, loja_id = ?, vendedora_id = ?, 
                             valor_total = ?, tipo_pagamento = ?, varios_pagamentos = ?, data_venda = ?
                             WHERE id = ?");
        $stmt->execute([$produto_id, $loja_id, $vendedora_id, $valor_total, $tipo_pagamento, $varios_pagamentos, $data_venda, $id]);

        $_SESSION['success'] = 'Venda atualizada com sucesso!';
        header('Location: sales.php');
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = 'Erro ao atualizar venda: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Venda - Dashboard</title>
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
        }
        
        .payment-badge {
            font-size: 0.75rem;
            padding: 5px 10px;
            border-radius: 20px;
            color: white;
            margin-right: 5px;
            margin-bottom: 5px;
            display: inline-block;
        }
        
        .badge-cartao { background-color: var(--primary-color); }
        .badge-pix { background-color: var(--success-color); }
        .badge-debito { background-color: var(--info-color); }
        .badge-dinheiro { background-color: var(--warning-color); }
    </style>
</head>
<body>
    <?php include '../../templates/header.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h2><i class="bi bi-pencil"></i> Editar Venda</h2>
                <hr>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <form method="post" action="sales_edit.php?id=<?= $id ?>">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Produto</label>
                            <select name="produto_id" class="form-select select2" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($produtos as $produto): ?>
                                    <option value="<?= $produto['id'] ?>" <?= $venda['produto_id'] == $produto['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($produto['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Loja</label>
                            <select name="loja_id" class="form-select select2" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($lojas as $loja): ?>
                                    <option value="<?= $loja['id'] ?>" <?= $venda['loja_id'] == $loja['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($loja['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Vendedora</label>
                            <select name="vendedora_id" class="form-select select2" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($vendedoras as $vendedora): ?>
                                    <option value="<?= $vendedora['id'] ?>" <?= $venda['vendedora_id'] == $vendedora['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($vendedora['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Valor Total</label>
                            <input type="number" name="valor_total" class="form-control" step="0.01" min="0" value="<?= number_format($venda['valor_total'], 2, '.', '') ?>" required>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Forma de Pagamento</label>
                            <select name="tipo_pagamento" id="tipoPagamento" class="form-select" required>
                                <option value="">Selecione...</option>
                                <option value="Dinheiro" <?= !$isMultiPayment && $venda['tipo_pagamento'] === 'dinheiro' ? 'selected' : '' ?>>Dinheiro</option>
                                <option value="Cartão" <?= !$isMultiPayment && $venda['tipo_pagamento'] === 'cartao' ? 'selected' : '' ?>>Cartão</option>
                                <option value="Pix" <?= !$isMultiPayment && $venda['tipo_pagamento'] === 'pix' ? 'selected' : '' ?>>Pix</option>
                                <option value="Débito" <?= !$isMultiPayment && $venda['tipo_pagamento'] === 'debito' ? 'selected' : '' ?>>Débito</option>
                                <option value="Múltiplo" <?= $isMultiPayment ? 'selected' : '' ?>>Múltiplas Formas</option>
                            </select>
                        </div>
                        
                        
                        
                        <div class="col-md-3">
                            <label class="form-label">Data da Venda</label>
                            <input type="date" name="data_venda" class="form-control" value="<?= $venda['data_venda'] ?>">
                        </div>
                        
                        <!-- Campos para pagamento múltiplo -->
                        <div class="col-12" id="multiPaymentFields" style="display: <?= $isMultiPayment ? 'block' : 'none' ?>;">
                            <div class="card mt-3">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Pagamento Múltiplo</h5>
                                </div>
                                <div class="card-body">
                                    <?php if ($isMultiPayment && !empty($paymentData)): ?>
                                        <?php foreach ($paymentData as $index => $payment): ?>
                                            <div class="row payment-field <?= $index > 0 ? 'mt-2' : '' ?>">
                                                <div class="col-md-5">
                                                    <select name="multi_payment_type[]" class="form-select mb-2">
                                                        <option value="Dinheiro" <?= $payment['type'] === 'Dinheiro' ? 'selected' : '' ?>>Dinheiro</option>
                                                        <option value="Cartão" <?= $payment['type'] === 'Cartão' ? 'selected' : '' ?>>Cartão</option>
                                                        <option value="Pix" <?= $payment['type'] === 'Pix' ? 'selected' : '' ?>>Pix</option>
                                                        <option value="Débito" <?= $payment['type'] === 'Débito' ? 'selected' : '' ?>>Débito</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="number" name="multi_payment_value[]" class="form-control mb-2 payment-value" 
                                                           placeholder="Valor" step="0.01" min="0" value="<?= $payment['value'] ?>">
                                                </div>
                                                <div class="col-md-2">
                                                    <?php if ($index === 0): ?>
                                                        <button type="button" class="btn btn-success add-payment-field">+</button>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-danger remove-payment-field">-</button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="row">
                                            <div class="col-md-5">
                                                <select name="multi_payment_type[]" class="form-select mb-2">
                                                    <option value="Dinheiro">Dinheiro</option>
                                                    <option value="Cartão">Cartão</option>
                                                    <option value="Pix">Pix</option>
                                                    <option value="Débito">Débito</option>
                                                </select>
                                            </div>
                                            <div class="col-md-5">
                                                <input type="number" name="multi_payment_value[]" class="form-control mb-2 payment-value" placeholder="Valor" step="0.01" min="0">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-success add-payment-field">+</button>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div id="additionalPaymentFields"></div>
                                    <div class="mt-3">
                                        <strong>Total Parciais: R$ <span id="multiPaymentTotal"><?= $isMultiPayment ? number_format(array_sum(array_column($paymentData, 'value')), 2, ',', '.') : '0,00' ?></span></strong>
                                        <div id="paymentValidation" class="text-danger mt-1"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Salvar Alterações
                            </button>
                            <a href="sales.php" class="btn btn-secondary ms-2">
                                <i class="bi bi-x-lg"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </form>
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
            placeholder: "Selecione uma opção",
            width: '100%'
        });

        // Mostrar/ocultar campos de pagamento múltiplo
        $('#tipoPagamento').change(function() {
            if ($(this).val() === 'Múltiplo') {
                $('#multiPaymentFields').show();
            } else {
                $('#multiPaymentFields').hide();
            }
            validatePayment();
        });

        // Adicionar novo campo de pagamento
        $(document).on('click', '.add-payment-field', function() {
            $('#additionalPaymentFields').append(`
                <div class="row payment-field mt-2">
                    <div class="col-md-5">
                        <select name="multi_payment_type[]" class="form-select mb-2">
                            <option value="Dinheiro">Dinheiro</option>
                            <option value="Cartão">Cartão</option>
                            <option value="Pix">Pix</option>
                            <option value="Débito">Débito</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <input type="number" name="multi_payment_value[]" class="form-control mb-2 payment-value" placeholder="Valor" step="0.01" min="0">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-payment-field">-</button>
                    </div>
                </div>
            `);
        });

        // Remover campo de pagamento
        $(document).on('click', '.remove-payment-field', function() {
            $(this).closest('.payment-field').remove();
            calculateMultiPaymentTotal();
            validatePayment();
        });

        // Calcular total dos pagamentos
        $(document).on('input', '.payment-value, input[name="valor_total"]', function() {
            calculateMultiPaymentTotal();
            validatePayment();
        });

        function calculateMultiPaymentTotal() {
            let total = 0;
            $('.payment-value').each(function() {
                const value = parseFloat($(this).val()) || 0;
                total += value;
            });
            $('#multiPaymentTotal').text(total.toFixed(2).replace('.', ','));
            return total;
        }

        function validatePayment() {
            const paymentType = $('#tipoPagamento').val();
            const totalValue = parseFloat($('input[name="valor_total"]').val()) || 0;
            const paymentTotal = calculateMultiPaymentTotal();
            
            if (paymentType === 'Múltiplo') {
                if (Math.abs(paymentTotal - totalValue) > 0.01) {
                    $('#paymentValidation').text('A soma dos pagamentos parciais deve ser igual ao valor total');
                    $('button[type="submit"]').prop('disabled', true);
                } else {
                    $('#paymentValidation').text('');
                    $('button[type="submit"]').prop('disabled', false);
                }
            } else {
                $('button[type="submit"]').prop('disabled', false);
            }
        }
    });
    </script>
</body>
</html>