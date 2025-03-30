<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Verificar se o ID foi passado e é válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID inválido";
    header('Location: vendedoras_list.php');
    exit();
}

$id = (int)$_GET['id'];

// Consultar os dados da vendedora
$sql = "SELECT * FROM vendedoras WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$vendedora = $stmt->fetch(PDO::FETCH_ASSOC);

// Se a vendedora não for encontrada
if (!$vendedora) {
    $_SESSION['error'] = "Vendedora não encontrada";
    header('Location: vendedoras_list.php');
    exit();
}

// Consultar lojas para o select
$sql = "SELECT * FROM lojas ORDER BY nome";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$lojas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $loja_id = (int)$_POST['loja_id'];

    // Validação básica
    if (empty($nome) || empty($email) || empty($telefone) || empty($loja_id)) {
        $_SESSION['error'] = "Preencha todos os campos obrigatórios";
    } else {
        try {
            $sql = "UPDATE vendedoras SET 
                    nome = :nome, 
                    email = :email, 
                    telefone = :telefone, 
                    loja_id = :loja_id 
                    WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':loja_id', $loja_id, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Vendedora atualizada com sucesso";
                header('Location: vendedoras_list.php');
                exit();
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erro ao atualizar: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Vendedora</title>
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
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
    }
    
    .page-title {
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.8rem;
        color: var(--text-dark);
        font-weight: 600;
        margin-bottom: 20px;
    }
    
    .card {
        border-radius: 10px;
        box-shadow: var(--card-shadow);
        border: none;
        margin-bottom: 20px;
    }
    
    .card-body {
        padding: 25px;
    }
    
    .form-label {
        font-weight: 500;
        color: var(--text-dark);
        margin-bottom: 8px;
    }
    
    .form-control, .form-select {
        border-radius: 5px;
        padding: 10px 15px;
        border: 1px solid var(--border-color);
        height: auto;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }
    
    .btn {
        padding: 10px 20px;
        font-weight: 500;
        border-radius: 5px;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .btn-primary:hover {
        background-color: #2980b9;
        border-color: #2980b9;
        transform: translateY(-2px);
    }
    
    .btn-secondary {
        color: var(--text-dark);
        background-color: white;
        border: 1px solid var(--border-color);
    }
    
    .btn-secondary:hover {
        background-color: #f8f9fa;
    }
    
    .actions-footer {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid var(--border-color);
    }
    
    .alert {
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    @media (max-width: 768px) {
        .main-container {
            padding: 0 10px;
        }
        
        .card-body {
            padding: 15px;
        }
        
        .actions-footer {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
            justify-content: center;
        }
    }
    </style>
</head>
<body>
    <?php include('../../templates/header.php'); ?>
    
    <div class="main-container">
        <h1 class="page-title">
            <i class="bi bi-person-lines-fill"></i> Editar Vendedora
        </h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form action="vendedora_edit.php?id=<?= $id ?>" method="post">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome *</label>
                        <input type="text" class="form-control" name="nome" 
                               value="<?= htmlspecialchars($vendedora['nome'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail *</label>
                        <input type="email" class="form-control" name="email" 
                               value="<?= htmlspecialchars($vendedora['email'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="telefone" class="form-label">Telefone *</label>
                        <input type="text" class="form-control" name="telefone" 
                               value="<?= htmlspecialchars($vendedora['telefone'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="loja_id" class="form-label">Loja *</label>
                        <select class="form-select" name="loja_id" required>
                            <?php foreach ($lojas as $loja): ?>
                                <option value="<?= $loja['id'] ?>" 
                                    <?= ($vendedora['loja_id'] == $loja['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($loja['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="actions-footer">
                        <a href="vendedoras_list.php" class="btn btn-secondary">
                            <i class="bi bi-x-lg"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>