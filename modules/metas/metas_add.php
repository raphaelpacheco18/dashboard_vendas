<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Obter lojas e vendedoras
$lojas = $pdo->query("SELECT id, nome FROM lojas ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$vendedoras = $pdo->query("SELECT id, nome FROM vendedoras ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// Mensagens de feedback
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loja_id = $_POST['loja_id'];
    $vendedora_id = $_POST['vendedora_id'] ?? null;
    $periodo = $_POST['periodo'];
    $meta_valor = (float) str_replace(['.', ','], ['', '.'], $_POST['meta_valor']);
    $meta_quantidade = (int) $_POST['meta_quantidade'];
    $bonificacao = (float) str_replace(['.', ','], ['', '.'], $_POST['bonificacao']);
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];

    try {
        // Validar datas
        if (strtotime($data_fim) < strtotime($data_inicio)) {
            throw new Exception("A data final deve ser maior que a data inicial");
        }

        // Inserir os dados
        $sql = "INSERT INTO metas (loja_id, vendedora_id, periodo, meta_valor, meta_quantidade, bonificacao, data_inicio, data_fim) 
                VALUES (:loja_id, :vendedora_id, :periodo, :meta_valor, :meta_quantidade, :bonificacao, :data_inicio, :data_fim)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':loja_id', $loja_id, PDO::PARAM_INT);
        $stmt->bindParam(':vendedora_id', $vendedora_id, PDO::PARAM_INT);
        $stmt->bindParam(':periodo', $periodo);
        $stmt->bindParam(':meta_valor', $meta_valor);
        $stmt->bindParam(':meta_quantidade', $meta_quantidade, PDO::PARAM_INT);
        $stmt->bindParam(':bonificacao', $bonificacao);
        $stmt->bindParam(':data_inicio', $data_inicio);
        $stmt->bindParam(':data_fim', $data_fim);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Meta cadastrada com sucesso!";
            header('Location: metas_list.php');
            exit();
        }
    } catch (PDOException $e) {
        $error = "Erro ao cadastrar meta: " . $e->getMessage();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Definir Meta</title>
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
        max-width: 1000px;
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
    
    .form-label {
        font-weight: 500;
        color: var(--text-dark);
        margin-bottom: 8px;
    }
    
    .form-control, .form-select {
        border-radius: 5px;
        padding: 10px 15px;
        border: 1px solid var(--border-color);
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }
    
    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        padding: 10px 20px;
        font-weight: 500;
    }
    
    .btn-primary:hover {
        background-color: #2980b9;
        border-color: #2980b9;
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
    }
    
    .alert {
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .currency-input {
        position: relative;
    }
    
    .currency-input::before {
        content: "R$";
        position: absolute;
        left: 10px;
        top: 10px;
        z-index: 1;
        font-weight: 500;
    }
    
    .currency-input input {
        padding-left: 35px !important;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .row {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -10px;
    }
    
    .col-md-6 {
        width: 50%;
        padding: 0 10px;
        box-sizing: border-box;
    }
    
    @media (max-width: 768px) {
        .col-md-6 {
            width: 100%;
        }
    }
    </style>
</head>
<body>
    <?php include('../../templates/header.php'); ?>
    
    <div class="main-container">
        <h1 class="page-title">
            <i class="bi bi-bullseye"></i> Definir Meta
        </h1>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form action="metas_add.php" method="post">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="loja_id" class="form-label">Loja *</label>
                                <select class="form-select" name="loja_id" required>
                                    <option value="">Selecione uma Loja</option>
                                    <?php foreach ($lojas as $loja): ?>
                                        <option value="<?= $loja['id'] ?>">
                                            <?= htmlspecialchars($loja['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vendedora_id" class="form-label">Vendedora</label>
                                <select class="form-select" name="vendedora_id">
                                    <option value="">Todas as Vendedoras</option>
                                    <?php foreach ($vendedoras as $vendedora): ?>
                                        <option value="<?= $vendedora['id'] ?>">
                                            <?= htmlspecialchars($vendedora['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="periodo" class="form-label">Período *</label>
                                <select class="form-select" name="periodo" required>
                                    <option value="diario">Diário</option>
                                    <option value="semanal">Semanal</option>
                                    <option value="mensal" selected>Mensal</option>
                                    <option value="trimestral">Trimestral</option>
                                    <option value="semestral">Semestral</option>
                                    <option value="anual">Anual</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="meta_quantidade" class="form-label">Meta de Quantidade *</label>
                                <input type="number" class="form-control" name="meta_quantidade" min="1" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group currency-input">
                                <label for="meta_valor" class="form-label">Meta em Valor (R$) *</label>
                                <input type="text" class="form-control" name="meta_valor" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group currency-input">
                                <label for="bonificacao" class="form-label">Bonificação (R$)</label>
                                <input type="text" class="form-control" name="bonificacao">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="data_inicio" class="form-label">Data de Início *</label>
                                <input type="date" class="form-control" name="data_inicio" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="data_fim" class="form-label">Data de Término *</label>
                                <input type="date" class="form-control" name="data_fim" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="actions-footer">
                        <a href="metas_list.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Salvar Meta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Formatação de campos monetários
    document.querySelectorAll('input[name="meta_valor"], input[name="bonificacao"]').forEach(input => {
        input.addEventListener('blur', function(e) {
            let value = this.value.replace(/\D/g, '');
            value = (value/100).toFixed(2) + '';
            value = value.replace(".", ",");
            value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
            this.value = value;
        });
    });

    // Validação de datas
    document.querySelector('form').addEventListener('submit', function(e) {
        const inicio = new Date(document.querySelector('input[name="data_inicio"]').value);
        const fim = new Date(document.querySelector('input[name="data_fim"]').value);
        
        if (fim < inicio) {
            alert('A data final deve ser maior que a data inicial');
            e.preventDefault();
        }
    });
    </script>
</body>
</html>