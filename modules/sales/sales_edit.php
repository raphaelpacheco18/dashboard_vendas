<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Verificar permissões
if (!in_array($_SESSION['nivel_acesso'], ['admin', 'gerente'])) {
    $_SESSION['error'] = "Acesso negado: Você não tem permissão para editar vendas";
    header('Location: sales.php');
    exit();
}

// Obter ID da venda
$venda_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($venda_id <= 0) {
    $_SESSION['error'] = "Venda inválida";
    header('Location: sales.php');
    exit();
}

// Carregar dados da venda
$venda = $pdo->prepare("SELECT * FROM vendas WHERE id = ?");
$venda->execute([$venda_id]);
$venda = $venda->fetch(PDO::FETCH_ASSOC);

if (!$venda) {
    $_SESSION['error'] = "Venda não encontrada";
    header('Location: sales.php');
    exit();
}

// Obter dados para os dropdowns
$produtos = $pdo->query("SELECT id, nome, preco, quantidade_atual FROM produtos WHERE ativo = 1 ORDER BY nome")->fetchAll();
$vendedoras = $pdo->query("SELECT id, nome FROM usuarios WHERE ativo = 1 AND nivel_acesso = 'vendedor' ORDER BY nome")->fetchAll();
$lojas = $pdo->query("SELECT id, nome FROM lojas WHERE status = 1 ORDER BY nome")->fetchAll();

// Processar formulário de edição
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Dados da venda (similar ao sales_add.php)
        // Lembre-se de devolver o estoque antigo e subtrair o novo
        
        $pdo->commit();
        
        $_SESSION['success'] = "Venda atualizada com sucesso!";
        header('Location: sales.php');
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $erro = "Erro ao atualizar venda: " . $e->getMessage();
    }
}
?>

<!-- Formulário de edição -->

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Venda #<?= $venda_id ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
    /* Estilos consistentes com os outros módulos */
    </style>
</head>
<body>
    <?php include '../../templates/header.php'; ?>
    
    <div class="main-container">
        <h1 class="page-title">
            <i class="bi bi-cart-check"></i> Editar Venda #<?= $venda_id ?>
        </h1>
        
        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <form id="form-venda" method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cliente_id">Cliente</label>
                            <select id="cliente_id" name="cliente_id" class="form-control select2">
                                <option value="">Selecione um cliente</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?= $cliente['id'] ?>" <?= $venda['cliente_id'] == $cliente['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cliente['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="loja_id" class="required-field">Loja</label>
                            <select id="loja_id" name="loja_id" class="form-control select2" required>
                                <option value="">Selecione uma loja</option>
                                <?php foreach ($lojas as $loja): ?>
                                    <option value="<?= $loja['id'] ?>" <?= $venda['loja_id'] == $loja['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($loja['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="vendedor_id">Vendedor</label>
                    <select id="vendedor_id" name="vendedor_id" class="form-control select2">
                        <option value="">Selecione um vendedor</option>
                        <?php foreach ($vendedores as $vendedor): ?>
                            <option value="<?= $vendedor['id'] ?>" <?= $venda['vendedor_id'] == $vendedor['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($vendedor['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="forma_pagamento" class="required-field">Forma de Pagamento</label>
                    <select id="forma_pagamento" name="forma_pagamento" class="form-control" required>
                        <option value="">Selecione</option>
                        <option value="dinheiro" <?= $venda['forma_pagamento'] == 'dinheiro' ? 'selected' : '' ?>>Dinheiro</option>
                        <option value="cartao_credito" <?= $venda['forma_pagamento'] == 'cartao_credito' ? 'selected' : '' ?>>Cartão de Crédito</option>
                        <option value="cartao_debito" <?= $venda['forma_pagamento'] == 'cartao_debito' ? 'selected' : '' ?>>Cartão de Débito</option>
                        <option value="pix" <?= $venda['forma_pagamento'] == 'pix' ? 'selected' : '' ?>>PIX</option>
                        <option value="boleto" <?= $venda['forma_pagamento'] == 'boleto' ? 'selected' : '' ?>>Boleto</option>
                        <option value="transferencia" <?= $venda['forma_pagamento'] == 'transferencia' ? 'selected' : '' ?>>Transferência Bancária</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="finalizada" <?= $venda['status'] == 'finalizada' ? 'selected' : '' ?>>Finalizada</option>
                        <option value="cancelada" <?= $venda['status'] == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                        <option value="pendente" <?= $venda['status'] == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="observacoes">Observações</label>
                    <textarea id="observacoes" name="observacoes" class="form-control" rows="2"><?= htmlspecialchars($venda['observacoes']) ?></textarea>
                </div>
                
                <hr>
                
                <h5>Itens da Venda</h5>
                
                <div id="itens-container">
                    <!-- Itens serão adicionados aqui via JavaScript -->
                </div>
                
                <button type="button" id="btn-adicionar-item" class="btn btn-outline-primary mb-3">
                    <i class="bi bi-plus-lg"></i> Adicionar Item
                </button>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="desconto">Desconto (R$)</label>
                            <input type="text" id="desconto" name="desconto" class="form-control money-mask" 
                                   value="<?= number_format($venda['desconto'], 2, ',', '.') ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="total-container bg-light p-3 rounded">
                            <h5>Total: <span id="total-venda">R$ <?= number_format($venda['valor_total'], 2, ',', '.') ?></span></h5>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="sales.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>

        <!-- Template para novos itens (hidden) -->
        <template id="item-template">
            <div class="item-venda card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Produto</label>
                                <select name="itens[][produto_id]" class="form-control select-produto" required>
                                    <option value="">Selecione um produto</option>
                                    <?php foreach ($produtos as $produto): ?>
                                        <option value="<?= $produto['id'] ?>" 
                                            data-preco="<?= number_format($produto['preco'], 2, '.', '') ?>"
                                            data-estoque="<?= $produto['quantidade_atual'] ?>">
                                            <?= htmlspecialchars($produto['nome']) ?> - 
                                            R$ <?= number_format($produto['preco'], 2, ',', '.') ?> 
                                            (Estoque: <?= $produto['quantidade_atual'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Quantidade</label>
                                <input type="number" name="itens[][quantidade]" class="form-control quantidade" min="1" value="1" required>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Preço Unitário</label>
                                <input type="text" name="itens[][preco_unitario]" class="form-control money-mask preco-unitario" required>
                            </div>
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-danger btn-remover-item">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="subtotal text-end">
                        Subtotal: <span class="valor-subtotal">R$ 0,00</span>
                    </div>
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
            
            // Máscaras
            $('.money-mask').mask('#.##0,00', {reverse: true});
            
            // Contador para índices dos itens
            let itemCount = 0;
            
            // Adicionar novo item
            function adicionarItem(produto_id = '', quantidade = 1, preco_unitario = 0) {
                const template = $('#item-template').html();
                const newItem = $(template.replace(/\[\]/g, `[${itemCount}]`));
                $('#itens-container').append(newItem);
                
                // Inicializar select2 e máscara para o novo item
                newItem.find('.select-produto').select2();
                newItem.find('.money-mask').mask('#.##0,00', {reverse: true});
                
                // Preencher valores se for edição
                if (produto_id) {
                    newItem.find('.select-produto').val(produto_id).trigger('change');
                    newItem.find('.quantidade').val(quantidade);
                    newItem.find('.preco-unitario').val(preco_unitario.toFixed(2).replace('.', ',')).trigger('input');
                }
                
                // Configurar eventos para o novo item
                configurarEventosItem(newItem);
                
                itemCount++;
            }
            
            // Configurar eventos para um item
            function configurarEventosItem(item) {
                // Quando selecionar um produto
                item.find('.select-produto').change(function() {
                    const selectedOption = $(this).find('option:selected');
                    const preco = selectedOption.data('preco');
                    const estoque = selectedOption.data('estoque');
                    
                    item.find('.preco-unitario').val(preco.toFixed(2).replace('.', ',')).trigger('input');
                    item.find('.quantidade').attr('max', estoque).trigger('input');
                });
                
                // Quando alterar quantidade ou preço
                item.find('.quantidade, .preco-unitario').on('input', function() {
                    calcularSubtotal(item);
                    calcularTotal();
                });
                
                // Remover item
                item.find('.btn-remover-item').click(function() {
                    item.remove();
                    calcularTotal();
                });
            }
            
            // Calcular subtotal de um item
            function calcularSubtotal(item) {
                const quantidade = parseFloat(item.find('.quantidade').val()) || 0;
                const preco = parseFloat(item.find('.preco-unitario').val().replace('.', '').replace(',', '.')) || 0;
                const subtotal = quantidade * preco;
                
                item.find('.valor-subtotal').text('R$ ' + subtotal.toFixed(2).replace('.', ','));
            }
            
            // Calcular total da venda
            function calcularTotal() {
                let total = 0;
                
                $('.item-venda').each(function() {
                    const quantidade = parseFloat($(this).find('.quantidade').val()) || 0;
                    const preco = parseFloat($(this).find('.preco-unitario').val().replace('.', '').replace(',', '.')) || 0;
                    total += quantidade * preco;
                });
                
                const desconto = parseFloat($('#desconto').val().replace('.', '').replace(',', '.')) || 0;
                const totalComDesconto = total - desconto;
                
                $('#total-venda').text('R$ ' + totalComDesconto.toFixed(2).replace('.', ','));
            }
            
            // Calcular total quando alterar desconto
            $('#desconto').on('input', calcularTotal);
            
            // Adicionar botão para novo item
            $('#btn-adicionar-item').click(function() {
                adicionarItem();
            });
            
            // Adicionar itens existentes
            <?php foreach ($itens as $item): ?>
                adicionarItem(
                    '<?= $item['produto_id'] ?>',
                    <?= $item['quantidade'] ?>,
                    <?= $item['preco_unitario'] ?>
                );
            <?php endforeach; ?>
        });
        </script>
    </body>
    </html>