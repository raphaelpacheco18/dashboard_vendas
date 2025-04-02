<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Verificar permissões
if (!in_array($_SESSION['nivel_acesso'], ['admin', 'gerente'])) {
    $_SESSION['error'] = "Acesso negado: Você não tem permissão para atualizar estoque";
    header('Location: ../products/products.php');
    exit();
}

// Obter produtos e lojas
$produtos = $pdo->query("SELECT id, nome FROM produtos ORDER BY nome")->fetchAll();
$lojas = $pdo->query("SELECT id, nome FROM lojas WHERE status = 1 ORDER BY nome")->fetchAll();

// Variáveis para mensagens
$erro = '';
$sucesso = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $produto_id = (int)$_POST['produto_id'];
    $loja_id = (int)$_POST['loja_id'];
    $quantidade = (int)$_POST['quantidade'];
    $tipo = $_POST['tipo']; // 'entrada' ou 'ajuste'
    $observacao = trim($_POST['observacao'] ?? '');

    // Validações
    if ($produto_id <= 0 || $loja_id <= 0) {
        $erro = "Selecione um produto e uma loja válidos!";
    } elseif ($quantidade <= 0) {
        $erro = "A quantidade deve ser maior que zero!";
    } else {
        try {
            $pdo->beginTransaction();

            // Verificar se o produto existe na loja selecionada
            $stmt_check = $pdo->prepare("SELECT id FROM produtos WHERE id = ? AND loja = ?");
            $stmt_check->execute([$produto_id, $loja_id]);
            
            if (!$stmt_check->fetch()) {
                $erro = "Este produto não está cadastrado na loja selecionada!";
            } else {
                // 1. Atualizar estoque na tabela produtos
                if ($tipo === 'entrada') {
                    $sql = "UPDATE produtos 
                            SET estoque = estoque + :quantidade,
                                quantidade_atual = quantidade_atual + :quantidade
                            WHERE id = :produto_id AND loja = :loja_id";
                } else { // ajuste
                    $sql = "UPDATE produtos 
                            SET estoque = :quantidade,
                                quantidade_atual = :quantidade
                            WHERE id = :produto_id AND loja = :loja_id";
                }

                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':quantidade', $quantidade);
                $stmt->bindParam(':produto_id', $produto_id);
                $stmt->bindParam(':loja_id', $loja_id);
                $stmt->execute();

                // 2. Registrar a movimentação
                $sql = "INSERT INTO movimentacoes_estoque 
                        (produto_id, loja_id, quantidade, tipo, observacao, usuario_id)
                        VALUES (:produto_id, :loja_id, :quantidade, :tipo, :observacao, :usuario_id)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':produto_id', $produto_id);
                $stmt->bindParam(':loja_id', $loja_id);
                $stmt->bindParam(':quantidade', $quantidade);
                $stmt->bindParam(':tipo', $tipo);
                $stmt->bindParam(':observacao', $observacao);
                $stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
                $stmt->execute();

                $pdo->commit();
                
                $_SESSION['success'] = "Estoque atualizado com sucesso!";
                header('Location: ../products/products.php');
                exit();
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $erro = 'Erro ao atualizar estoque: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar Estoque</title>
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
    }
    
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
        border-radius: 10px;
        box-shadow: var(--card-shadow);
        padding: 25px;
        margin-bottom: 30px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: var(--text-dark);
    }
    
    .required-field::after {
        content: " *";
        color: var(--danger-color);
    }
    
    .form-control {
        width: 100%;
        padding: 10px 15px;
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
    
    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }
    
    .btn-primary {
        background-color: var(--primary-color);
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        color: white;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-primary:hover {
        background-color: #2980b9;
        transform: translateY(-2px);
    }
    
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-top: 15px;
        color: var(--primary-color);
        text-decoration: none;
    }
    
    .btn-back:hover {
        text-decoration: underline;
    }
    
    .alert {
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    /* SELECT2 Customização */
    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid var(--border-color) !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
    
    /* Estilo para cards de informação */
    .info-card {
        background: white;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        box-shadow: var(--card-shadow);
    }
    
    .info-card h5 {
        color: var(--primary-color);
        margin-bottom: 10px;
    }
    
    @media (max-width: 768px) {
        .main-container {
            padding: 0 10px;
        }
        
        .form-card {
            padding: 15px;
        }
    }
    </style>
</head>
<body>
    <?php include '../../templates/header.php'; ?>
    
    <div class="main-container">
        <!-- Mensagens de feedback -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <h1 class="page-title">
            <i class="bi bi-box-arrow-in-down"></i> Atualizar Estoque
        </h1>
        
        <div class="info-card">
            <h5><i class="bi bi-info-circle"></i> Como funciona</h5>
            <p><strong>Entrada de Mercadoria:</strong> Adiciona a quantidade informada ao estoque existente.</p>
            <p><strong>Ajuste de Estoque:</strong> Define o estoque para a quantidade informada (substitui o valor atual).</p>
        </div>
        
        <div class="form-card">
            <form action="atualizar_estoque.php" method="POST" id="stock-form">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="produto_id" class="required-field">Produto</label>
                            <select id="produto_id" name="produto_id" class="form-control select2" required>
                                <option value="">Selecione um produto</option>
                                <?php foreach ($produtos as $produto): ?>
                                    <option value="<?= $produto['id'] ?>" <?= ($produto_id ?? '') == $produto['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($produto['nome']) ?>
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
                                    <option value="<?= $loja['id'] ?>" <?= ($loja_id ?? '') == $loja['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($loja['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="tipo" class="required-field">Tipo de Movimentação</label>
                            <select id="tipo" name="tipo" class="form-control" required>
                                <option value="entrada" <?= ($tipo ?? '') == 'entrada' ? 'selected' : '' ?>>Entrada de Mercadoria</option>
                                <option value="ajuste" <?= ($tipo ?? '') == 'ajuste' ? 'selected' : '' ?>>Ajuste de Estoque</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="quantidade" class="required-field">Quantidade</label>
                            <input type="number" id="quantidade" name="quantidade" class="form-control" 
                                   value="<?= htmlspecialchars($quantidade ?? '') ?>" min="1" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="observacao">Observação</label>
                    <textarea id="observacao" name="observacao" class="form-control"><?= htmlspecialchars($observacao ?? '') ?></textarea>
                    <small class="text-muted">Ex: "Entrada de nova remessa" ou "Ajuste de inventário"</small>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Atualizar Estoque
                </button>
                
                <a href="../products/products.php" class="btn-back">
                    <i class="bi bi-arrow-left"></i> Voltar para a lista de produtos
                </a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    $(document).ready(function() {
        // Inicializa Select2
        $('.select2').select2({
            placeholder: "Selecione",
            width: '100%'
        });
        
        // Validação do formulário
        $('#stock-form').on('submit', function(e) {
            let isValid = true;
            
            if (parseInt($('#quantidade').val()) <= 0) {
                alert('A quantidade deve ser maior que zero!');
                isValid = false;
            }
            
            if ($('#produto_id').val() === '' || $('#loja_id').val() === '') {
                alert('Selecione um produto e uma loja!');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
    </script>
</body>
</html>