<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

// Verifica se o usuário está logado
if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Obtém o ID do usuário a ser editado
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Verifica se o ID é válido
if (!$id) {
    $_SESSION['erro'] = 'ID de usuário inválido';
    header('Location: usuario_list.php');
    exit();
}

// Busca os dados do usuário
$sql = "SELECT * FROM usuarios WHERE id = :id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);

try {
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        $_SESSION['erro'] = 'Usuário não encontrado';
        header('Location: usuario_list.php');
        exit();
    }
    
    // Define valores padrão
    $usuario['nivel_acesso'] = $usuario['nivel_acesso'] ?? 'vendedor';
    $usuario['foto'] = $usuario['foto'] ?? 'default-profile.jpg';
    $usuario['ativo'] = isset($usuario['ativo']) ? (int)$usuario['ativo'] : 1;

} catch (PDOException $e) {
    $_SESSION['erro'] = 'Erro ao buscar usuário: ' . $e->getMessage();
    header('Location: usuario_list.php');
    exit();
}

// Processa o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Coleta e sanitiza os dados do formulário
    $dados = [
        'nome' => filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING),
        'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
        'nivel_acesso' => filter_input(INPUT_POST, 'nivel_acesso', FILTER_SANITIZE_STRING),
        'ativo' => isset($_POST['ativo']) ? 1 : 0,
        'foto' => $usuario['foto']
    ];

    // Processa o upload da foto (o resto do código de processamento permanece igual)
    // ... (manter o código existente de upload de foto)

    try {
        // Atualiza o usuário no banco de dados
        $sql = "UPDATE usuarios SET 
                nome = :nome, 
                email = :email, 
                nivel_acesso = :nivel,
                ativo = :ativo,
                foto = :foto
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'nivel' => $dados['nivel_acesso'],
            'ativo' => $dados['ativo'],
            'foto' => $dados['foto'],
            'id' => $id
        ]);
        
        if ($success) {
            $_SESSION['sucesso'] = 'Usuário atualizado com sucesso!';
            header('Location: usuario_list.php');
            exit();
        }
        
    } catch (PDOException $e) {
        $_SESSION['erro'] = 'Erro ao atualizar usuário: ' . $e->getMessage();
    }
}
?>

<!-- O restante do HTML permanece igual -->

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
    :root {
        --primary-color: #3498db;
        --success-color: #28a745;
        --hover-success: #218838;
        --danger-color: #dc3545;
        --warning-color: #fd7e14;
        --info-color: #17a2b8;
        --btn-gradient-start: #27ae60;
        --btn-gradient-end: #2ecc71;
        --btn-gradient-hover-start: #219653;
        --btn-gradient-hover-end: #27ae60;
    }
    
    body {
        background-color: #f8f9fa;
        padding-top: 20px;
    }
    
    .main-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
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
        color: #2c3e50;
    }
    
    .card-form {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 20px;
        padding: 25px;
    }
    
    .form-label {
        font-weight: 500;
        margin-bottom: 8px;
    }
    
    .form-control, .form-select {
        border-radius: 8px;
        padding: 10px 15px;
        border: 1px solid #ced4da;
        transition: all 0.3s;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, var(--btn-gradient-start), var(--btn-gradient-end));
        border: none;
        padding: 10px 25px;
        font-weight: 500;
        transition: all 0.3s;
        color: white;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, var(--btn-gradient-hover-start), var(--btn-gradient-hover-end));
        transform: translateY(-2px);
        color: white;
    }
    
    .current-photo {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #e9ecef;
        margin: 15px auto;
        display: block;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .photo-actions {
        text-align: center;
        margin-top: 10px;
    }
    
    @media (max-width: 768px) {
        .header-actions {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .page-title {
            margin-bottom: 10px;
            font-size: 1.5rem;
        }
        
        .current-photo {
            width: 120px;
            height: 120px;
        }
    }
    </style>
</head>
<body>
    <?php include '../../templates/header.php'; ?>
    
    <div class="main-container">
        <div class="header-actions">
            <h2 class="page-title">
                <i class="bi bi-person-gear"></i> Editar Usuário: <?= htmlspecialchars($usuario['nome']) ?>
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
            
            <form method="post" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-4">
                            <label class="form-label">Nome Completo:</label>
                            <input type="text" name="nome" class="form-control" 
                                   value="<?= htmlspecialchars($usuario['nome']) ?>" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Email:</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?= htmlspecialchars($usuario['email']) ?>" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Nível de Acesso:</label>
                            <select name="nivel_acesso" class="form-select" required>
                                <option value="admin" <?= $usuario['nivel_acesso'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                                <option value="gerente" <?= $usuario['nivel_acesso'] === 'gerente' ? 'selected' : '' ?>>Gerente</option>
                                <option value="vendedor" <?= $usuario['nivel_acesso'] === 'vendedor' ? 'selected' : '' ?>>Vendedor</option>
                            </select>
                        </div>
                        
                        <div class="mb-4 form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="ativo" id="ativo" 
                                   <?= $usuario['ativo'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="ativo">Usuário Ativo</label>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-4">
                            <label class="form-label">Foto de Perfil:</label>
                            <input type="file" name="foto" class="form-control" accept="image/jpeg, image/png">
                            
                            <?php 
                            $fotoPath = '../../assets/img/profiles/'.$usuario['foto'];
                            if (file_exists($fotoPath) && !is_dir($fotoPath)): ?>
                                <img src="<?= $fotoPath.'?'.time() ?>" 
                                     class="current-photo"
                                     onerror="this.src='../../assets/img/profiles/default-profile.jpg'">
                                <div class="photo-actions">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remover_foto" id="remover_foto">
                                        <label class="form-check-label" for="remover_foto">Remover foto atual</label>
                                    </div>
                                </div>
                            <?php else: ?>
                                <img src="../../assets/img/profiles/default-profile.jpg" class="current-photo">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-3 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Salvar Alterações
                    </button>
                    <a href="usuario_list.php" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Preview da foto selecionada
    document.querySelector('input[name="foto"]')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.querySelector('.current-photo');
        
        if (file && preview) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                document.querySelector('#remover_foto').checked = false;
            }
            
            reader.readAsDataURL(file);
        }
    });
    </script>
</body>
</html>