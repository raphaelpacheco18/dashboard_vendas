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
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'] ?? '';
    $endereco = $_POST['endereco'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $cep = $_POST['cep'] ?? '';
    $cidade = $_POST['cidade'] ?? '';
    $estado = $_POST['estado'] ?? '';
    $status = isset($_POST['status']) ? 1 : 0;

    // Validação básica
    if (empty($nome) || empty($endereco)) {
        $erro = "Por favor, preencha todos os campos obrigatórios!";
    } else {
        try {
            $sql = "INSERT INTO lojas (nome, endereco, telefone, cep, cidade, estado, status) 
                    VALUES (:nome, :endereco, :telefone, :cep, :cidade, :estado, :status)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':endereco', $endereco);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':cep', $cep);
            $stmt->bindParam(':cidade', $cidade);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':status', $status);

            if ($stmt->execute()) {
                $sucesso = "Loja cadastrada com sucesso!";
                // Limpar campos após sucesso
                $nome = $endereco = $telefone = $cep = $cidade = $estado = '';
            }
        } catch (PDOException $e) {
            $erro = 'Erro ao cadastrar loja: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Loja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
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
    
    select.form-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 16px 12px;
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
            <i class="bi bi-shop"></i> Cadastrar Loja
        </h1>
        
        <div class="form-card">
            <form action="loja_add.php" method="POST">
                <div class="form-group">
                    <label for="nome">Nome da Loja <span class="text-danger">*</span></label>
                    <input type="text" id="nome" name="nome" class="form-control" 
                           value="<?= htmlspecialchars($nome ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="endereco">Endereço <span class="text-danger">*</span></label>
                    <textarea id="endereco" name="endereco" class="form-control" required><?= htmlspecialchars($endereco ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <input type="text" id="telefone" name="telefone" class="form-control" 
                           value="<?= htmlspecialchars($telefone ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="cep">CEP</label>
                    <input type="text" id="cep" name="cep" class="form-control" 
                           value="<?= htmlspecialchars($cep ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="cidade">Cidade</label>
                    <input type="text" id="cidade" name="cidade" class="form-control" 
                           value="<?= htmlspecialchars($cidade ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado" class="form-control">
                        <option value="">Selecione</option>
                        <?php
                        $estados = [
                            'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO',
                            'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI',
                            'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'
                        ];
                        
                        foreach ($estados as $uf) {
                            $selected = (isset($estado) && $estado == $uf) ? 'selected' : '';
                            echo "<option value=\"$uf\" $selected>$uf</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="status" id="status" checked>
                    <label class="form-check-label" for="status">
                        Loja ativa
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Cadastrar Loja
                </button>
                
                <a href="lojas.php" class="btn-back">
                    <i class="bi bi-arrow-left"></i> Voltar para a lista
                </a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>