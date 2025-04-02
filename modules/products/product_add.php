<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Verificar permissões (apenas admin e gerente podem cadastrar)
if ($_SESSION['nivel_acesso'] !== 'admin' && $_SESSION['nivel_acesso'] !== 'gerente') {
    $_SESSION['error'] = "Acesso negado: Você não tem permissão para cadastrar produtos";
    header('Location: products.php');
    exit();
}

// Variáveis para mensagens
$erro = '';
$sucesso = '';

// Obter lojas ativas para o dropdown
$lojas = $pdo->query("SELECT id, nome FROM lojas WHERE status = 1 ORDER BY nome")->fetchAll();

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $preco = str_replace(['.', ','], ['', '.'], $_POST['preco'] ?? '');
    $quantidade = (int)($_POST['quantidade'] ?? 0);
    $loja_id = (int)($_POST['loja_id'] ?? 0);
    $codigo_barras = trim($_POST['codigo_barras'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    // Validação
    if (empty($nome) || empty($descricao) || $loja_id <= 0) {
        $erro = "Por favor, preencha todos os campos obrigatórios!";
    } elseif ($preco <= 0) {
        $erro = "O preço deve ser maior que zero!";
    } elseif ($quantidade < 0) {
        $erro = "A quantidade não pode ser negativa!";
    } else {
        try {
            // Verificar se código de barras já existe
            if (!empty($codigo_barras)) {
                $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE codigo_barras = ?");
                $stmt_check->execute([$codigo_barras]);
                
                if ($stmt_check->fetchColumn() > 0) {
                    $erro = "Código de barras já cadastrado para outro produto!";
                }
            }

            if (empty($erro)) {
                $sql = "INSERT INTO produtos 
                        (nome, descricao, preco, quantidade_atual, estoque, loja, codigo_barras, categoria, ativo, data_criacao) 
                        VALUES (:nome, :descricao, :preco, :quantidade, :quantidade, :loja, :codigo_barras, :categoria, :ativo, NOW())";
                
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':descricao', $descricao);
                $stmt->bindParam(':preco', $preco);
                $stmt->bindParam(':quantidade', $quantidade);
                $stmt->bindParam(':loja', $loja_id);
                $stmt->bindParam(':codigo_barras', $codigo_barras);
                $stmt->bindParam(':categoria', $categoria);
                $stmt->bindParam(':ativo', $ativo, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    $_SESSION['success'] = "Produto adicionado com sucesso!";
                    header('Location: products.php');
                    exit();
                }
            }
        } catch (PDOException $e) {
            $erro = 'Erro ao adicionar produto: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Produto</title>
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
    
    .input-group-text {
        background-color: #f8f9fa;
    }
    
    .form-check {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
    }
    
    .form-check-input {
        width: 18px;
        height: 18px;
        margin-top: 0;
    }
    
    .form-check-label {
        font-weight: 500;
        color: var(--text-dark);
    }
    
    .price-input {
        max-width: 200px;
    }
    
    @media (max-width: 768px) {
        .main-container {
            padding: 0 10px;
        }
        
        .form-card {
            padding: 15px;
        }
        
        .price-input {
            max-width: 100%;
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
            <i class="bi bi-plus-circle"></i> Adicionar Produto
        </h1>
        
        <div class="form-card">
            <form action="product_add.php" method="POST" id="product-form">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="nome" class="form-label required-field">Nome do Produto</label>
                            <input type="text" id="nome" name="nome" class="form-control" 
                                   value="<?= htmlspecialchars($nome ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="codigo_barras">Código de Barras</label>
                            <input type="text" id="codigo_barras" name="codigo_barras" class="form-control" 
                                   value="<?= htmlspecialchars($codigo_barras ?? '') ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="descricao" class="form-label required-field">Descrição</label>
                    <textarea id="descricao" name="descricao" class="form-control" required><?= htmlspecialchars($descricao ?? '') ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="preco" class="form-label required-field">Preço</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="text" id="preco" name="preco" class="form-control price-input" 
                                       value="<?= htmlspecialchars($preco ?? '') ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="quantidade" class="form-label required-field">Quantidade Inicial</label>
                            <input type="number" id="quantidade" name="quantidade" class="form-control" 
                                   value="<?= htmlspecialchars($quantidade ?? 0) ?>" min="0" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="loja_id" class="form-label required-field">Loja</label>
                            <select id="loja_id" name="loja_id" class="form-control select2" required>
                                <option value="">Selecione uma loja</option>
                                <?php foreach ($lojas as $loja): ?>
                                    <option value="<?= htmlspecialchars($loja['id']) ?>" 
                                        <?= ($loja_id ?? 0) == $loja['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($loja['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="categoria">Categoria</label>
                            <input type="text" id="categoria" name="categoria" class="form-control" 
                                   value="<?= htmlspecialchars($categoria ?? '') ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="ativo" id="ativo" checked>
                    <label class="form-check-label" for="ativo">
                        Produto ativo
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Adicionar Produto
                </button>
                
                <a href="products.php" class="btn-back">
                    <i class="bi bi-arrow-left"></i> Voltar para a lista
                </a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
    $(document).ready(function() {
        // Inicializa Select2
        $('.select2').select2({
            placeholder: "Selecione uma loja",
            width: '100%'
        });
        
        // Máscara para preços
        $('.price-input').mask('#.##0,00', {reverse: true});
        
        // Validação do formulário
        $('#product-form').on('submit', function(e) {
            let isValid = true;
            
            // Converter valores para float
            const preco = parseFloat($('#preco').val().replace('.', '').replace(',', '.'));
            
            // Validar preço de venda
            if (preco <= 0) {
                alert('O preço deve ser maior que zero!');
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