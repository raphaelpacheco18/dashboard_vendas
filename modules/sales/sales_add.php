<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Verificar permissões
if (!in_array($_SESSION['nivel_acesso'], ['admin', 'gerente', 'vendedor'])) {
    $_SESSION['error'] = "Acesso negado: Você não tem permissão para registrar vendas";
    header('Location: ../../index.php');
    exit();
}

// Obter lojas ativas
$lojas = $pdo->query("SELECT id, nome FROM lojas WHERE status = 1 ORDER BY nome")->fetchAll();

// Obter produtos ativos
$produtos = $pdo->query("SELECT id, nome, preco, quantidade_atual FROM produtos WHERE ativo = 1 ORDER BY nome")->fetchAll();

// Função para arredondamento consistente
function roundMoney($value) {
    return round(floatval($value), 2);
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Dados básicos da venda
        $loja_id = $_POST['loja_id'] ?? null;
        $vendedor_id = $_POST['vendedor_id'] ?? $_SESSION['usuario_id'];
        $valor_total = 0;
        $observacoes = $_POST['observacoes'] ?? '';

        // Validar loja e vendedor
        if (!$loja_id) throw new Exception("Selecione uma loja válida");
        if (!$vendedor_id) throw new Exception("Selecione um vendedor válido");

        // Processar itens da venda
        $itens = [];
        
        if (empty($_POST['itens'])) {
            throw new Exception("Adicione pelo menos um item à venda");
        }

        foreach ($_POST['itens'] as $item) {
            if (empty($item['produto_id'])) continue;
            
            $produto_id = $item['produto_id'];
            $quantidade = (int)($item['quantidade'] ?? 0);
            $preco_unitario = roundMoney(str_replace(['R$', '.', ','], ['', '', '.'], $item['preco_unitario'] ?? 0));
            
            // Validar item
            if ($quantidade <= 0) throw new Exception("Quantidade inválida para o produto ID $produto_id");
            if ($preco_unitario <= 0) throw new Exception("Preço inválido para o produto ID $produto_id");
            
            // Verificar estoque
            $stmt = $pdo->prepare("SELECT quantidade_atual FROM produtos WHERE id = ? FOR UPDATE");
            $stmt->execute([$produto_id]);
            $produto = $stmt->fetch();
            
            if (!$produto || $produto['quantidade_atual'] < $quantidade) {
                throw new Exception("Estoque insuficiente para o produto ID $produto_id");
            }
            
            $subtotal = roundMoney($preco_unitario * $quantidade);
            $valor_total = roundMoney($valor_total + $subtotal);
            
            $itens[] = [
                'produto_id' => $produto_id,
                'quantidade' => $quantidade,
                'preco_unitario' => $preco_unitario,
                'subtotal' => $subtotal
            ];
        }
        
        if (count($itens) === 0) {
            throw new Exception("Nenhum item válido foi adicionado à venda");
        }
        
        // Processar formas de pagamento
// Substitua todo o bloco de processamento de pagamentos por:
// Processar formas de pagamento
$formas_pagamento = [];
$total_pagamentos = 0;

if (empty($_POST['pagamentos'])) {
    throw new Exception("Adicione pelo menos uma forma de pagamento");
}

foreach ($_POST['pagamentos'] as $index => $pagamento) {
    if (empty($pagamento['valor'])) continue;
    
    $valor = roundMoney(str_replace(['R$', '.', ','], ['', '', '.'], $pagamento['valor']));
    $tipo = $pagamento['tipo'] ?? 'dinheiro';
    
    if ($valor <= 0) continue;
    
    $formas_pagamento[] = [
        'tipo' => $tipo,
        'valor' => $valor
    ];
    $total_pagamentos = roundMoney($total_pagamentos + $valor);
}

if (count($formas_pagamento) === 0) {
    throw new Exception("Nenhum pagamento válido foi adicionado");
}

// Validar pagamentos com tolerância de 1 centavo
$diferenca = abs(roundMoney($total_pagamentos - $valor_total));
if ($diferenca > 0.01) {
    // Tentar ajustar automaticamente o último pagamento
    if (count($formas_pagamento) > 0) {
        $ajuste = roundMoney($valor_total - $total_pagamentos);
        $formas_pagamento[count($formas_pagamento)-1]['valor'] = roundMoney($formas_pagamento[count($formas_pagamento)-1]['valor'] + $ajuste);
        $total_pagamentos = roundMoney($total_pagamentos + $ajuste);
        
        // Verificar novamente após ajuste
        $diferenca = abs(roundMoney($total_pagamentos - $valor_total));
        if ($diferenca > 0.01) {
            throw new Exception(
                "A soma dos pagamentos (R$ " . number_format($total_pagamentos, 2, ',', '.') . ") " .
                "não confere com o valor total (R$ " . number_format($valor_total, 2, ',', '.') . "). " .
                "Diferença: R$ " . number_format($diferenca, 2, ',', '.')
            );
        }
    } else {
        throw new Exception(
            "A soma dos pagamentos (R$ " . number_format($total_pagamentos, 2, ',', '.') . ") " .
            "não confere com o valor total (R$ " . number_format($valor_total, 2, ',', '.') . "). " .
            "Diferença: R$ " . number_format($diferenca, 2, ',', '.')
        );
    }
}

        // Ajustar o último pagamento para cobrir pequenas diferenças
        if ($diferenca > 0 && $diferenca <= 0.01 && count($formas_pagamento) > 0) {
            $ajuste = roundMoney($valor_total - $total_pagamentos);
            $formas_pagamento[count($formas_pagamento)-1]['valor'] = roundMoney($formas_pagamento[count($formas_pagamento)-1]['valor'] + $ajuste);
            $total_pagamentos = $valor_total;
        }
        
        // Inserir venda principal
        $sql_venda = "INSERT INTO vendas (
            loja_id, vendedora_id, data_venda, valor_total, 
            tipo_pagamento, observacao, usuario_cadastro
        ) VALUES (
            :loja_id, :vendedora_id, NOW(), :valor_total,
            :tipo_pagamento, :observacao, :usuario_cadastro
        )";

        $stmt_venda = $pdo->prepare($sql_venda);
        $stmt_venda->execute([
            ':loja_id' => $loja_id,
            ':vendedora_id' => $vendedor_id,
            ':valor_total' => $valor_total,
            ':tipo_pagamento' => json_encode($formas_pagamento),
            ':observacao' => $observacoes,
            ':usuario_cadastro' => $_SESSION['usuario_id']
        ]);

        $venda_id = $pdo->lastInsertId();

        // Inserir itens da venda
        foreach ($itens as $item) {
            $sql_item = "INSERT INTO venda_itens (
                venda_id, produto_id, quantidade, preco_unitario, subtotal
            ) VALUES (
                :venda_id, :produto_id, :quantidade, :preco_unitario, :subtotal
            )";
            
            $stmt_item = $pdo->prepare($sql_item);
            $stmt_item->execute([
                ':venda_id' => $venda_id,
                ':produto_id' => $item['produto_id'],
                ':quantidade' => $item['quantidade'],
                ':preco_unitario' => $item['preco_unitario'],
                ':subtotal' => $item['subtotal']
            ]);
            
            // Atualizar estoque
            $sql_estoque = "UPDATE produtos SET 
                quantidade_atual = quantidade_atual - :quantidade,
                quantidade_vendida = quantidade_vendida + :quantidade
                WHERE id = :produto_id";
                
            $stmt_estoque = $pdo->prepare($sql_estoque);
            $stmt_estoque->execute([
                ':quantidade' => $item['quantidade'],
                ':produto_id' => $item['produto_id']
            ]);
        }
        
        $pdo->commit();
        
        $_SESSION['success'] = "Venda registrada com sucesso! Nº $venda_id";
        header('Location: sales.php');
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $erro = "Erro ao registrar venda: " . $e->getMessage();
        error_log("Erro na venda: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Venda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
    :root {
    --primary-color: #3498db;
    --success-color: #28a745;
    --danger-color: #dc3545;
    --warning-color: #fd7e14;
    --info-color: #17a2b8;
    --text-dark: #2c3e50;
    --text-muted: #6c757d;
    --border-color: #dee2e6;
    --card-shadow: 0 5px 15px rgba(0,0,0,0.05);
    --card-radius: 10px;
}

/* Estilos Gerais */
body {
    background-color: #f8f9fa;
    padding-top: 20px;
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
}

.main-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 0 15px;
}

.page-title {
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.8rem;
    color: var(--text-dark);
    font-weight: 600;
}

.form-card {
    background: white;
    border-radius: var(--card-radius);
    box-shadow: var(--card-shadow);
    padding: 25px;
    margin-bottom: 30px;
}

.required-field::after {
    content: " *";
    color: var(--danger-color);
}

/* Formulários e Controles */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: var(--text-dark);
}

.form-control {
    width: 100%;
    height: 38px;
    padding: 0.375rem 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 5px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.form-control:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

/* Select2 Personalizado */
.select2-container--default .select2-selection--single {
    height: 38px !important;
    border: 1px solid var(--border-color);
    border-radius: 5px;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px !important;
    padding-left: 12px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
}

/* Botões */
.btn {
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-primary {
    background-color: var(--primary-color);
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    color: white;
    font-weight: 500;
}

.btn-primary:hover {
    background-color: #2980b9;
    transform: translateY(-2px);
}

.btn-outline-primary,
.btn-outline-danger {
    padding: 10px 20px;
    border-radius: 5px;
}

.btn-outline-danger {
    color: var(--danger-color);
    border: 1px solid var(--danger-color);
    background: transparent;
}

.btn-outline-danger:hover {
    background-color: var(--danger-color);
    color: white;
}

/* Itens de Venda */
#itens-container {
    margin-bottom: 20px;
}

.item-venda {
    background: #fff;
    border: 1px solid var(--border-color);
    border-radius: var(--card-radius);
    padding: 15px;
    margin-bottom: 15px;
}

.item-venda .row {
    display: flex;
    align-items: center;
    gap: 10px;
}

.item-venda .form-group {
    margin-bottom: 0;
}

.item-venda .card-body {
    padding: 0.5rem !important;
}

.item-venda label {
    font-size: 0.75rem;
    margin-bottom: 0.15rem;
}

.estoque-display {
    background-color: #f8f9fa;
    color: #495057;
    font-weight: 500;
    text-align: center;
    cursor: not-allowed;
}

.btn-remover-item {
    height: 38px;
    min-height: 38px;
    width: 38px;
    padding: 0;
    border-radius: 0.25rem;
}

/* Pagamentos e Totais */
.pagamento-item {
    margin-bottom: 15px;
}

.total-container {
    background-color: #f8f9fa;
    border-radius: var(--card-radius);
    padding: 15px;
    margin-top: 20px;
}

.alert-info {
    background-color: #e7f5fe;
    border-left: 4px solid var(--info-color);
    border-radius: var(--card-radius);
}

.alert-info strong {
    color: var(--text-dark);
    min-width: 120px;
    display: inline-block;
}

#valor-restante {
    font-weight: bold;
    font-size: 1.1em;
}

/* Responsividade */
@media (max-width: 768px) {
    .main-container {
        padding: 0 10px;
    }
    
    .form-card {
        padding: 15px;
    }
    
    .row {
        flex-direction: column;
    }
    
    .col-md-6, 
    .col-md-4,
    .col-md-5,
    .col-md-2,
    .col-md-1 {
        width: 100%;
        padding: 0;
    }
    
    .select2-container {
        width: 100% !important;
    }
    
    .item-venda .row {
        flex-wrap: wrap;
    }
    
    .item-venda .row > div {
        margin-bottom: 10px;
    }
    
    .btn-remover-item {
        margin-top: 0;
        margin-left: auto;
    }
}

/* Estilo para os itens de venda */
.item-venda .row {
    display: flex;
    align-items: flex-end; /* Alinhar na base */
}

.item-venda .form-group {
    margin-bottom: 0;
}

.item-venda .form-control {
    height: 38px;
    padding: 0.375rem 0.75rem;
}

.btn-remover-item {
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}

/* Garantir que o Select2 tenha a mesma altura */
.select2-container .select2-selection--single {
    height: 38px !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 38px !important;
}

/* Ajuste para mobile */
@media (max-width: 768px) {
    .item-venda .row {
        flex-wrap: wrap;
    }
    
    .item-venda .col-md-1,
    .item-venda .col-md-2,
    .item-venda .col-md-5 {
        width: 100%;
        margin-bottom: 10px;
    }
    
    .btn-remover-item {
        margin-left: auto;
        margin-right: auto;
        width: 38px;
    }
}
/* Adicione ao seu CSS */
#valor-total {
    background-color: #f0f8ff; /* Azul bem clarinho */
    font-weight: bold;
}

#valor_venda {
    background-color: #fff8dc; /* Amarelo claro para destacar que é editável */
    font-weight: bold;
}

.diferenca-desconto {
    font-size: 0.85rem;
    margin-top: 5px;
}
    </style>
</head>
<body>
    <?php include '../../templates/header.php'; ?>
    
    <div class="main-container">
        <h1 class="page-title">
            <i class="bi bi-cart-plus"></i> Nova Venda
        </h1>
        
        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <form id="form-venda" method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="form-group">
                            <label for="loja_id" class="required-field">Loja</label>
                            <select id="loja_id" name="loja_id" class="form-control select2" required>
                                <option value="">Selecione uma loja</option>
                                <?php foreach ($lojas as $loja): ?>
                                    <option value="<?= $loja['id'] ?>"><?= htmlspecialchars($loja['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="vendedor_id" class="required-field">Vendedor</label>
                            <select id="vendedor_id" name="vendedor_id" class="form-control select2" required>
                                <option value="">Selecione a loja primeiro</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <h5>Itens da Venda</h5>
                
                <div id="itens-container">
                    <!-- Itens serão adicionados aqui via JavaScript -->
                </div>
                
                <button type="button" id="btn-adicionar-item" class="btn btn-outline-primary mb-3">
                    <i class="bi bi-plus-lg"></i> Adicionar Item
                </button>
                
                <div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Valor Total Calculado</label>
            <div class="input-group">
                <span class="input-group-text">R$</span>
                <input type="text" id="valor-total" class="form-control" readonly>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label class="required-field">Valor da Venda (com desconto)</label>
            <div class="input-group">
                <span class="input-group-text">R$</span>
                <input type="text" id="valor_venda" name="valor_venda" class="form-control money-mask" required>
            </div>
            <small class="text-muted">Insira o valor final com desconto, se necessário</small>
        </div>
    </div>
</div>
                
                <hr>
                
                <h5>Formas de Pagamento</h5>
                
                <div id="pagamentos-container">
                    <div class="pagamento-item row mb-3">
                        <div class="col-md-4">
                            <select name="pagamentos[][tipo]" class="form-control" required>
                                <option value="dinheiro">Dinheiro</option>
                                <option value="pix">PIX</option>
                                <option value="cartao_credito">Cartão de Crédito</option>
                                <option value="cartao_debito">Cartão de Débito</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="pagamentos[][valor]" class="form-control money-mask valor-pagamento" placeholder="Valor" required>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-danger btn-remover-pagamento">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info mt-3">
                    <strong>Valor da Venda:</strong> <span id="valor-venda-display">R$ 0,00</span><br>
                    <strong>Total Pagamentos:</strong> <span id="total-pagamentos">R$ 0,00</span><br>
                    <strong>Restante:</strong> <span id="valor-restante">R$ 0,00</span>
                </div>
                
                <button type="button" id="btn-adicionar-pagamento" class="btn btn-outline-primary mb-3">
                    <i class="bi bi-plus-lg"></i> Adicionar Forma de Pagamento
                </button>
                
                <div class="form-group">
                    <label for="observacoes">Observações</label>
                    <textarea id="observacoes" name="observacoes" class="form-control" rows="2"></textarea>
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="sales.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Finalizar Venda
                    </button>
                </div>
            </form>
        </div>

        <!-- Template para novos itens - MODIFICADO -->
        <template id="item-template">
    <div class="item-venda card mb-3">
        <div class="card-body p-2">
            <div class="row g-2 align-items-end"> <!-- Mudamos para align-items-end -->
                <!-- Produto -->
                <div class="col-md-5">
                    <div class="form-group mb-0">
                        <label class="required-field small mb-1 d-block">Produto</label>
                        <select name="itens[][produto_id]" class="form-control select-produto" required style="width: 100%">
                            <option value="">Selecione um produto</option>
                            <?php foreach ($produtos as $produto): ?>
                                <option value="<?= $produto['id'] ?>" 
                                    data-preco="<?= number_format($produto['preco'], 2, '.', '') ?>"
                                    data-estoque="<?= $produto['quantidade_atual'] ?>">
                                    <?= htmlspecialchars($produto['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Estoque -->
                <div class="col-md-1">
                    <div class="form-group mb-0">
                        <label class="small mb-1 d-block">Estoque</label>
                        <input type="text" class="form-control estoque-display px-1 text-center py-2" readonly>
                    </div>
                </div>
                
                <!-- Quantidade -->
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label class="required-field small mb-1 d-block">Qtd</label>
                        <input type="number" name="itens[][quantidade]" class="form-control quantidade px-1 py-2" min="1" value="1" required>
                    </div>
                </div>
                
                <!-- Preço Unitário -->
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label class="required-field small mb-1 d-block">P. Unitário</label>
                        <input type="text" name="itens[][preco_unitario]" class="form-control money-mask preco-unitario px-1 py-2" required>
                    </div>
                </div>
                
                <!-- Botão Remover -->
                <div class="col-md-1 d-flex align-items-center"> <!-- Simplificado -->
                    <button type="button" class="btn btn-outline-danger btn-remover-item w-100 py-2">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

        <!-- Template para novos pagamentos -->
        <!-- Substitua o template existente por: -->
        <template id="pagamento-template">
    <div class="pagamento-item row mb-3">
        <div class="col-md-4">
            <select name="pagamentos[0][tipo]" class="form-control" required>
                <option value="dinheiro">Dinheiro</option>
                <option value="pix">PIX</option>
                <option value="cartao_credito">Cartão de Crédito</option>
                <option value="cartao_debito">Cartão de Débito</option>
            </select>
        </div>
        <div class="col-md-6">
            <input type="text" name="pagamentos[0][valor]" class="form-control money-mask valor-pagamento" placeholder="Valor" required>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-outline-danger btn-remover-pagamento">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>
</template>

        <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
        <script>
$(document).ready(function() {
    // Inicializa Select2
    $('.select2').select2();
    
    // Foco automático no campo de busca do Select2
    $(document).on('select2:open', () => {
        document.querySelector('.select2-container--open .select2-search__field').focus();
    });

    // Máscaras monetárias
    $('.money-mask').mask('#.##0,00', {reverse: true});
    
    // Contadores
    let itemCount = 0;
    
    // Carregar vendedores quando selecionar loja
    $('#loja_id').change(function() {
        const lojaId = $(this).val();
        
        if (lojaId) {
            $.ajax({
                url: '../../api/get_vendedores.php',
                type: 'GET',
                data: { loja_id: lojaId },
                dataType: 'json',
                success: function(data) {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    
                    $('#vendedor_id').empty().append('<option value="">Selecione um vendedor</option>');
                    
                    if (data.length > 0) {
                        data.forEach(function(vendedor) {
                            $('#vendedor_id').append(
                                $('<option>', {
                                    value: vendedor.id,
                                    text: vendedor.nome
                                })
                            );
                        });
                    } else {
                        $('#vendedor_id').append('<option value="">Nenhum vendedor encontrado</option>');
                    }
                    
                    $('#vendedor_id').trigger('change');
                },
                error: function(xhr, status, error) {
                    console.error('Erro na requisição:', status, error);
                    alert('Erro ao carregar vendedores. Verifique o console para detalhes.');
                }
            });
        } else {
            $('#vendedor_id').empty().append('<option value="">Selecione a loja primeiro</option>');
        }
    });
    
    // Adicionar novo item de venda
    $('#btn-adicionar-item').click(function() {
        const template = $('#item-template').html();
        const newItem = $(template.replace(/\[\]/g, `[${itemCount}]`));
        $('#itens-container').append(newItem);
        
        // Inicializar Select2
        newItem.find('.select-produto').select2({
            dropdownParent: newItem.closest('.form-card'),
            width: '100%'
        });
        
        // Aplicar máscara monetária
        newItem.find('.money-mask').mask('#.##0,00', {reverse: true});
        
        // Configurar eventos do item
        configurarEventosItem(newItem);
        
        itemCount++;
    });
    
    // Configurar eventos para um item de venda
    function configurarEventosItem(item) {
        // Quando selecionar um produto
        item.find('.select-produto').change(function() {
            const selectedOption = $(this).find('option:selected');
            const preco = parseFloat(selectedOption.data('preco')) || 0;
            const estoque = parseInt(selectedOption.data('estoque')) || 0;
            
            item.find('.preco-unitario').val(preco.toFixed(2).replace('.', ',')).trigger('input');
            item.find('.quantidade').attr({
                'max': estoque,
                'value': 1
            }).trigger('input');
            
            // Atualizar o campo de estoque
            item.find('.estoque-display').val(estoque);
            
            calcularSubtotal(item);
            calcularTotal();
        });
        
        // Quando alterar quantidade ou preço
        item.find('.quantidade, .preco-unitario').on('input', function() {
            calcularSubtotal(item);
            calcularTotal();
        });
        
        // Remover item
        item.find('.btn-remover-item').click(function() {
            $(this).closest('.item-venda').remove();
            calcularTotal();
        });
    }
    
    // Calcular subtotal de um item
    function calcularSubtotal(item) {
        const quantidade = parseInt(item.find('.quantidade').val()) || 0;
        const precoStr = item.find('.preco-unitario').val().replace(/[^\d,-]/g, '').replace(',', '.');
        const preco = parseFloat(precoStr) || 0;
        const subtotal = quantidade * preco;
        
        item.find('.valor-subtotal').text('R$ ' + subtotal.toFixed(2).replace('.', ','));
    }
    
    // Calcular total da venda
    function calcularTotal() {
        let total = 0;
        
        $('.item-venda').each(function() {
            const quantidade = parseInt($(this).find('.quantidade').val()) || 0;
            const precoStr = $(this).find('.preco-unitario').val().replace(/[^\d,-]/g, '').replace(',', '.');
            const preco = parseFloat(precoStr) || 0;
            total = parseFloat((total + (quantidade * preco)).toFixed(2));
        });
        
        $('#valor-total').val(total.toFixed(2).replace('.', ','));
        
        const valorAtualVenda = $('#valor_venda').val().replace(/[^\d,-]/g, '');
        if (!valorAtualVenda || parseFloat(valorAtualVenda.replace(',', '.')) ){
            $('#valor_venda').val(total.toFixed(2).replace('.', ',')).trigger('input');
        }
        
        $('#valor-venda-display').text('R$ ' + $('#valor_venda').val());
        calcularPagamentos();
    }
    
    // Adicionar forma de pagamento (VERSÃO CORRIGIDA)
    $('#btn-adicionar-pagamento').click(function() {
        const nextIndex = $('.pagamento-item').length;
        const template = $('#pagamento-template').html();
        const newPagamento = $(template.replace(/\[0\]/g, `[${nextIndex}]`));
        $('#pagamentos-container').append(newPagamento);
        
        newPagamento.find('.money-mask').mask('#.##0,00', {reverse: true});
        
        newPagamento.find('.btn-remover-pagamento').click(function() {
            $(this).closest('.pagamento-item').remove();
            calcularPagamentos();
        });
        
        newPagamento.find('.valor-pagamento').focus();
    });
    
    // Calcular totais de pagamento
    function calcularPagamentos() {
        let totalPagamentos = 0;
        const valorVendaStr = $('#valor_venda').val().replace(/[^\d,-]/g, '').replace(',', '.');
        const valorVenda = parseFloat(valorVendaStr) || 0;
        const pagamentos = $('.valor-pagamento');
        
        pagamentos.each(function() {
            const valorStr = $(this).val().replace(/[^\d,-]/g, '').replace(',', '.');
            const valor = parseFloat(valorStr) || 0;
            totalPagamentos = parseFloat((totalPagamentos + valor).toFixed(2));
        });

        const restante = parseFloat((valorVenda - totalPagamentos).toFixed(2));
        
        $('#total-pagamentos').text('R$ ' + totalPagamentos.toFixed(2).replace('.', ','));
        $('#valor-restante').text('R$ ' + restante.toFixed(2).replace('.', ','));
        
        if (restante > 0.01) {
            $('#valor-restante').css('color', 'var(--danger-color)');
        } else if (restante < -0.01) {
            $('#valor-restante').css('color', 'var(--warning-color)');
        } else {
            $('#valor-restante').css('color', 'var(--success-color)');
        }
        
        // Auto-ajustar o último pagamento
        if (pagamentos.length > 0 && restante !== 0) {
            const ultimoPagamento = pagamentos.last();
            const valorAtualStr = ultimoPagamento.val().replace(/[^\d,-]/g, '').replace(',', '.');
            const valorAtual = parseFloat(valorAtualStr) || 0;
            
            if (valorAtual === 0 || ultimoPagamento.val() === '') {
                const novoValor = parseFloat((valorAtual + restante).toFixed(2));
                ultimoPagamento.val(novoValor.toFixed(2).replace('.', ',')).trigger('input');
            }
        }
    }
    
    // Mostrar diferença quando editar valor da venda
    $(document).on('input', '#valor_venda', function() {
        const totalCalculado = parseFloat($('#valor-total').val().replace(/[^\d,-]/g, '').replace(',', '.')) || 0;
        const valorVenda = parseFloat($(this).val().replace(/[^\d,-]/g, '').replace(',', '.')) || 0;
        const diferenca = totalCalculado - valorVenda;
        
        $('.diferenca-desconto').remove();
        
        if (diferenca > 0) {
            $(this).parent().after(
                `<div class="diferenca-desconto text-success">
                    <i class="bi bi-arrow-down"></i> Desconto aplicado: R$ ${diferenca.toFixed(2).replace('.', ',')}
                </div>`
            );
        } else if (diferenca < 0) {
            $(this).parent().after(
                `<div class="diferenca-desconto text-danger">
                    <i class="bi bi-exclamation-triangle"></i> Valor acima do total!
                </div>`
            );
        }
        
        $('#valor-venda-display').text('R$ ' + $(this).val());
        calcularPagamentos();
    });
    
    // Atualizar sempre que um pagamento for alterado
    $(document).on('input', '.valor-pagamento', calcularPagamentos);
    
    // Auto-completar valor restante
    $(document).on('focus', '.valor-pagamento', function() {
        const restanteStr = $('#valor-restante').text().replace(/[^\d,-]/g, '').replace(',', '.');
        const restante = parseFloat(restanteStr) || 0;
        const valorAtualStr = $(this).val().replace(/[^\d,-]/g, '').replace(',', '.');
        const valorAtual = parseFloat(valorAtualStr) || 0;
        
        if ((valorAtual === 0 || $(this).val() === '') && restante > 0) {
            $(this).val(restante.toFixed(2).replace('.', ',')).trigger('input');
        }
    });
    
    // Validação no envio do formulário
    $('#form-venda').submit(function(e) {
        const restanteStr = $('#valor-restante').text().replace(/[^\d,-]/g, '').replace(',', '.');
        const restante = parseFloat(restanteStr) || 0;
        
        if (restante > 0.01) {
            e.preventDefault();
            alert('A soma dos pagamentos não confere com o valor total da venda. Faltam R$ ' + restante.toFixed(2).replace('.', ','));
            return false;
        }
        
        const totalCalculado = parseFloat($('#valor-total').val().replace(/[^\d,-]/g, '').replace(',', '.')) || 0;
        const valorVenda = parseFloat($('#valor_venda').val().replace(/[^\d,-]/g, '').replace(',', '.')) || 0;
        
        if (valorVenda > totalCalculado) {
            e.preventDefault();
            alert('O valor da venda não pode ser maior que o valor total calculado!');
            return false;
        }
        
        return true;
    });
    
    // Adicionar primeiro item automaticamente
    $('#btn-adicionar-item').trigger('click');
});
</script>
    </body>
</html>