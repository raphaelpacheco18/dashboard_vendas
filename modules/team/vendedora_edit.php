<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Verificar permissões (apenas admin e gerente podem editar)
if ($_SESSION['nivel_acesso'] !== 'admin' && $_SESSION['nivel_acesso'] !== 'gerente') {
    $_SESSION['error'] = "Acesso negado: Você não tem permissão para editar vendedoras";
    header('Location: vendedoras_list.php');
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

// Consultar lojas ativas para o select
$sql = "SELECT id, nome FROM lojas WHERE status = 1 ORDER BY nome";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$lojas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
    $loja_id = (int)$_POST['loja_id'];
    $comissao = floatval(str_replace(',', '.', $_POST['comissao']));
    $data_nascimento = $_POST['data_nascimento'] ?: null;
    $status = isset($_POST['status']) ? 1 : 0;

    // Validações
    if (empty($nome) || empty($email) || empty($telefone) || empty($cpf)) {
        $_SESSION['error'] = "Preencha todos os campos obrigatórios!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "E-mail inválido!";
    } elseif (strlen($cpf) !== 11) {
        $_SESSION['error'] = "CPF inválido!";
    } else {
        try {
            // Verificar se email ou CPF já existem em outras vendedoras
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM vendedoras 
                                        WHERE (email = ? OR cpf = ?) AND id != ?");
            $stmt_check->execute([$email, $cpf, $id]);
            
            if ($stmt_check->fetchColumn() > 0) {
                $_SESSION['error'] = "E-mail ou CPF já cadastrado para outra vendedora";
            } else {
                // Atualizar vendedora
                $sql = "UPDATE vendedoras SET 
                        nome = :nome, 
                        email = :email, 
                        telefone = :telefone,
                        cpf = :cpf,
                        loja_id = :loja_id,
                        comissao = :comissao,
                        data_nascimento = :data_nascimento,
                        status = :status
                        WHERE id = :id";
                
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':telefone', $telefone);
                $stmt->bindParam(':cpf', $cpf);
                $stmt->bindParam(':loja_id', $loja_id, PDO::PARAM_INT);
                $stmt->bindParam(':comissao', $comissao);
                $stmt->bindParam(':data_nascimento', $data_nascimento);
                $stmt->bindParam(':status', $status, PDO::PARAM_INT);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Vendedora atualizada com sucesso!";
                    header('Location: vendedoras_list.php');
                    exit();
                }
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
    
    .form-control, .form-select, .select2-selection {
        border-radius: 5px;
        padding: 10px 15px;
        border: 1px solid var(--border-color);
        height: auto;
    }
    
    .form-control:focus, .form-select:focus, .select2-selection:focus {
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
    
    .required-field::after {
        content: " *";
        color: var(--danger-color);
    }
    
    .select2-container--default .select2-selection--single {
        height: 45px;
        display: flex;
        align-items: center;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
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
    
    .input-group-text {
        background-color: #f8f9fa;
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
                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form action="vendedora_edit.php?id=<?= $id ?>" method="post" id="form-vendedora">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nome" class="form-label required-field">Nome Completo</label>
                                <input type="text" class="form-control" name="nome" required
                                       value="<?= htmlspecialchars($vendedora['nome']) ?>"
                                       placeholder="Digite o nome completo">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="form-label required-field">E-mail</label>
                                <input type="email" class="form-control" name="email" required
                                       value="<?= htmlspecialchars($vendedora['email']) ?>"
                                       placeholder="exemplo@dominio.com">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="cpf" class="form-label required-field">CPF</label>
                                <input type="text" class="form-control cpf-mask" name="cpf" required
                                       value="<?= htmlspecialchars($vendedora['cpf']) ?>"
                                       placeholder="000.000.000-00">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="telefone" class="form-label required-field">Telefone</label>
                                <input type="text" class="form-control phone-mask" name="telefone" required
                                       value="<?= htmlspecialchars($vendedora['telefone']) ?>"
                                       placeholder="(00) 00000-0000">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                                <input type="date" class="form-control" name="data_nascimento"
                                       value="<?= htmlspecialchars($vendedora['data_nascimento']) ?>"
                                       max="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="loja_id" class="form-label required-field">Loja</label>
                                <select class="form-select select2" name="loja_id" required>
                                    <option value="">Selecione uma loja</option>
                                    <?php foreach ($lojas as $loja): ?>
                                        <option value="<?= $loja['id'] ?>" 
                                            <?= ($vendedora['loja_id'] == $loja['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($loja['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="comissao" class="form-label">Comissão (%)</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="comissao" 
                                           value="<?= number_format($vendedora['comissao'], 2, '.', '') ?>"
                                           placeholder="5.00">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="status" id="status" 
                               <?= $vendedora['status'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="status">
                            Vendedora ativa
                        </label>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
    $(document).ready(function() {
        // Inicializar Select2
        $('.select2').select2({
            placeholder: "Selecione uma loja",
            allowClear: true
        });
        
        // Máscaras para os campos
        $('.cpf-mask').mask('000.000.000-00', {reverse: true});
        $('.phone-mask').mask('(00) 00000-0000');
        
        // Validação do formulário
        $('#form-vendedora').on('submit', function(e) {
            let isValid = true;
            
            // Validar CPF
            const cpf = $('.cpf-mask').cleanVal();
            if (cpf.length !== 11) {
                alert('CPF deve conter 11 dígitos');
                isValid = false;
            }
            
            // Validar telefone
            const telefone = $('.phone-mask').cleanVal();
            if (telefone.length < 10) {
                alert('Telefone inválido');
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