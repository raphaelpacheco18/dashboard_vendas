<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Variáveis para mensagens
$erro = '';
$sucesso = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $preco = $_POST['preco'] ?? '';
    $quantidade = $_POST['quantidade'] ?? '';
    $loja_id = $_POST['loja_id'] ?? '';

    // Validação básica
    if (empty($nome) || empty($descricao) || empty($preco) || empty($quantidade) || empty($loja_id)) {
        $erro = "Por favor, preencha todos os campos!";
    } else {
        try {
            $sql = "INSERT INTO produtos (nome, descricao, preco, quantidade, loja, data_criacao) 
                VALUES (:nome, :descricao, :preco, :quantidade, :loja, NOW())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':descricao', $descricao);
            $stmt->bindParam(':preco', $preco);
            $stmt->bindParam(':quantidade', $quantidade);
            $stmt->bindParam(':loja', $loja_id);

            if ($stmt->execute()) {
                $sucesso = "Produto adicionado com sucesso!";
                // Limpar campos após sucesso
                $nome = $descricao = $preco = $quantidade = $loja_id = '';
            }
        } catch (PDOException $e) {
            $erro = 'Erro ao adicionar produto: ' . $e->getMessage();
        }
    }
}

// Obter lojas para o dropdown
$lojas = $pdo->query("SELECT id, nome FROM lojas ORDER BY nome")->fetchAll();
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
        max-width: 800px;
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
        transition: background-color 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-primary:hover {
        background-color: #2980b9;
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
        <?php if ($sucesso): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($sucesso) ?>
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
            <form action="product_add.php" method="POST">
                <div class="form-group">
                    <label for="nome">Nome do Produto</label>
                    <input type="text" id="nome" name="nome" class="form-control" 
                           value="<?= htmlspecialchars($nome ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <textarea id="descricao" name="descricao" class="form-control" required><?= htmlspecialchars($descricao ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="preco">Preço (R$)</label>
                    <input type="number" id="preco" name="preco" class="form-control" 
                           value="<?= htmlspecialchars($preco ?? '') ?>" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="quantidade">Quantidade em Estoque</label>
                    <input type="number" id="quantidade" name="quantidade" class="form-control" 
                           value="<?= htmlspecialchars($quantidade ?? '') ?>" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="loja_id">Loja</label>
                    <select id="loja_id" name="loja_id" class="form-control select2" required>
                        <option value="">Selecione uma loja</option>
                        <?php foreach ($lojas as $loja): ?>
                            <option value="<?= htmlspecialchars($loja['id']) ?>" 
                                <?= ($loja_id ?? '') == $loja['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($loja['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
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
    <script>
    $(document).ready(function() {
        // Inicializa Select2
        $('.select2').select2({
            placeholder: "Selecione uma loja",
            width: '100%'
        });
    });
    </script>
</body>
</html>