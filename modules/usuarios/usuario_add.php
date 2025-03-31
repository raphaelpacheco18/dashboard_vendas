<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

// Verifica se o usuário está logado
if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Processa o formulário de adição de usuário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Coleta e sanitiza os dados do formulário
    $dados = [
        'nome' => filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING),
        'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
        'senha' => password_hash(filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_STRING), PASSWORD_DEFAULT),
        'nivel_acesso' => filter_input(INPUT_POST, 'nivel_acesso', FILTER_SANITIZE_STRING),
        'ativo' => filter_input(INPUT_POST, 'ativo', FILTER_VALIDATE_INT) ?: 1, // 1 por padrão se não for fornecido
        'foto' => 'default-profile.jpg' // Foto padrão
    ];

    // Tenta inserir o novo usuário no banco de dados
    try {
        $sql = "INSERT INTO usuarios (nome, email, senha, nivel_acesso, ativo, foto) 
                VALUES (:nome, :email, :senha, :nivel_acesso, :ativo, :foto)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'senha' => $dados['senha'],
            'nivel_acesso' => $dados['nivel_acesso'],
            'ativo' => $dados['ativo'],
            'foto' => $dados['foto']
        ]);

        $_SESSION['sucesso'] = 'Usuário adicionado com sucesso!';
        header('Location: usuario_list.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['erro'] = 'Erro ao adicionar usuário: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Usuário - Dashboard</title>
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

    .card {
        border-radius: 10px;
        box-shadow: var(--card-shadow);
        border: none;
        margin-bottom: 20px;
        padding: 20px;
        background-color: white;
    }

    .page-title {
        margin-bottom: 20px;
        font-size: 2rem;
        color: var(--text-dark);
        font-weight: 600;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        font-weight: 500;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
    }

    .form-control {
        border-radius: 0.5rem;
        border: 1px solid var(--border-color);
        padding: 0.75rem 1rem;
        font-size: 1rem;
        width: 100%;
        background-color: #f8f9fa;
        transition: border-color 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }

    .btn-primary {
        background-color: var(--primary-color);
        border: none;
        padding: 10px 20px;
        font-weight: 500;
        letter-spacing: 0.5px;
        border-radius: 0.5rem;
        color: white;
        text-transform: uppercase;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #2980b9;
    }

    .btn-secondary {
        background-color: #ccc;
        border: none;
        padding: 10px 20px;
        font-weight: 500;
        letter-spacing: 0.5px;
        border-radius: 0.5rem;
        color: var(--text-dark);
        text-transform: uppercase;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .btn-secondary:hover {
        background-color: #b6b6b6;
    }

    .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }

    .form-row .form-group {
        flex: 1;
        min-width: 250px;
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

    .alert-warning {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }

    /* Responsividade */
    @media (max-width: 768px) {
        .form-row {
            flex-direction: column;
        }
    }
</style>

</head>
<body>
    <?php include '../../templates/header.php'; ?>
    
    <div class="main-container">
        <div class="header-actions">
            <h2 class="page-title">
                <i class="bi bi-person-plus"></i> Adicionar Novo Usuário
            </h2>
            <div>
                <a href="usuario_list.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
        </div>

        <div class="card-form">
            <?php if (isset($_SESSION['erro'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['erro'] ?></div>
                <?php unset($_SESSION['erro']); ?>
            <?php endif; ?>
            
            <form method="post">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-4">
                            <label class="form-label">Nome Completo:</label>
                            <input type="text" name="nome" class="form-control" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Email:</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Senha:</label>
                            <input type="password" name="senha" class="form-control" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Nível de Acesso:</label>
                            <select name="nivel_acesso" class="form-select" required>
                                <option value="admin">Administrador</option>
                                <option value="gerente">Gerente</option>
                                <option value="vendedor">Vendedor</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Ativo:</label>
                            <select name="ativo" class="form-select" required>
                                <option value="1">Ativo</option>
                                <option value="0">Inativo</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-4">
                            <label class="form-label">Foto do Usuário:</label>
                            <img src="../../img/usuarios/default-profile.jpg" alt="Foto do Usuário" class="current-photo">
                            <div class="photo-actions">
                                <label for="foto" class="btn btn-primary">
                                    <i class="bi bi-upload"></i> Alterar Foto
                                </label>
                                <input type="file" id="foto" name="foto" class="d-none" accept="image/*">
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Adicionar Usuário
                </button>
            </form>
        </div>
    </div>

    <?php include '../../templates/footer.php'; ?>
</body>
</html>
