<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

// Verificar se o usuário está logado
if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Verificar se o ID da meta foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "ID da meta não fornecido!";
    exit();
}

$id = $_GET['id'];

// Consultar a meta pelo ID
$sql = "SELECT * FROM metas WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$meta = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar se a meta foi encontrada
if (!$meta) {
    echo "Meta não encontrada!";
    exit();
}

// Consultar lojas e vendedoras para preencher os selects
$lojas = $pdo->query("SELECT * FROM lojas")->fetchAll(PDO::FETCH_ASSOC);
$vendedoras = $pdo->query("SELECT * FROM vendedoras")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loja_id = $_POST['loja_id'];
    $vendedora_id = $_POST['vendedora_id'];
    $periodo = $_POST['periodo'];
    $meta_valor = $_POST['meta_valor'];
    $meta_quantidade = $_POST['meta_quantidade'];
    $bonificacao = $_POST['bonificacao'];
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];

    // Atualizar a meta no banco de dados
    $sql = "UPDATE metas SET loja_id = :loja_id, vendedora_id = :vendedora_id, periodo = :periodo, meta_valor = :meta_valor, 
            meta_quantidade = :meta_quantidade, bonificacao = :bonificacao, data_inicio = :data_inicio, data_fim = :data_fim 
            WHERE id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':loja_id', $loja_id);
    $stmt->bindParam(':vendedora_id', $vendedora_id);
    $stmt->bindParam(':periodo', $periodo);
    $stmt->bindParam(':meta_valor', $meta_valor);
    $stmt->bindParam(':meta_quantidade', $meta_quantidade);
    $stmt->bindParam(':bonificacao', $bonificacao);
    $stmt->bindParam(':data_inicio', $data_inicio);
    $stmt->bindParam(':data_fim', $data_fim);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        // Redirecionar para a lista de metas após a atualização
        header('Location: metas_list.php');
        exit();
    } else {
        echo "Erro ao atualizar a meta.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Meta</title>
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
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 15px;
    }
    
    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .page-title {
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.8rem;
        color: var(--text-dark);
        font-weight: 600;
    }
    
    .btn-action-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--info-color));
        border: none;
        padding: 10px 20px;
        font-weight: 500;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        color: white !important;
        border-radius: 5px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        text-decoration: none;
    }
    
    .btn-action-primary:hover {
        background: linear-gradient(135deg, var(--info-color), var(--primary-color));
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        color: white !important;
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
        transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
    }
    
    .btn-submit {
        background: linear-gradient(135deg, var(--primary-color), var(--info-color));
        border: none;
        padding: 10px 25px;
        font-weight: 500;
        color: white;
        border-radius: 5px;
        transition: all 0.3s ease;
    }
    
    .btn-submit:hover {
        background: linear-gradient(135deg, var(--info-color), var(--primary-color));
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        color: white;
    }
    
    .text-currency {
        font-family: 'Courier New', monospace;
        font-weight: bold;
    }
    
    @media (max-width: 768px) {
        .header-actions {
            flex-direction: column;
            align-items: flex-start;
        }
    }
    </style>
</head>
<body>
    <?php include '../../templates/header.php'; ?>
    
    <div class="main-container">
        <div class="header-actions">
            <h2 class="page-title">
                <i class="bi bi-pencil-square"></i> Editar Meta
            </h2>
            <div>
                <a href="metas_list.php" class="btn btn-action-primary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="metas_edit.php?id=<?php echo $meta['id']; ?>" method="post">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="loja_id" class="form-label">Loja</label>
                            <select name="loja_id" class="form-select" required>
                                <option value="">Selecione uma Loja</option>
                                <?php foreach ($lojas as $loja): ?>
                                    <option value="<?php echo $loja['id']; ?>" <?php echo ($loja['id'] == $meta['loja_id']) ? 'selected' : ''; ?>>
                                        <?php echo $loja['nome']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="vendedora_id" class="form-label">Vendedora</label>
                            <select name="vendedora_id" class="form-select" required>
                                <option value="">Selecione uma Vendedora</option>
                                <?php foreach ($vendedoras as $vendedora): ?>
                                    <option value="<?php echo $vendedora['id']; ?>" <?php echo ($vendedora['id'] == $meta['vendedora_id']) ? 'selected' : ''; ?>>
                                        <?php echo $vendedora['nome']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="periodo" class="form-label">Período</label>
                            <select name="periodo" class="form-select" required>
                                <option value="diario" <?php echo ($meta['periodo'] == 'diario') ? 'selected' : ''; ?>>Diário</option>
                                <option value="mensal" <?php echo ($meta['periodo'] == 'mensal') ? 'selected' : ''; ?>>Mensal</option>
                                <option value="trimestral" <?php echo ($meta['periodo'] == 'trimestral') ? 'selected' : ''; ?>>Trimestral</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="meta_valor" class="form-label">Valor da Meta (R$)</label>
                            <input type="number" name="meta_valor" class="form-control" step="0.01" value="<?php echo $meta['meta_valor']; ?>" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="meta_quantidade" class="form-label">Quantidade da Meta</label>
                            <input type="number" name="meta_quantidade" class="form-control" value="<?php echo $meta['meta_quantidade']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="bonificacao" class="form-label">Bonificação (R$)</label>
                            <input type="number" name="bonificacao" class="form-control" step="0.01" value="<?php echo $meta['bonificacao']; ?>" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="data_inicio" class="form-label">Data de Início</label>
                            <input type="date" name="data_inicio" class="form-control" value="<?php echo $meta['data_inicio']; ?>" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="data_fim" class="form-label">Data de Fim</label>
                            <input type="date" name="data_fim" class="form-control" value="<?php echo $meta['data_fim']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-submit">
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