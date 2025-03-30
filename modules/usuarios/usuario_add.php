<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
        $nivel = filter_input(INPUT_POST, 'nivel_acesso', FILTER_SANITIZE_STRING);
        $foto = 'default-profile.jpg';

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $extensoesPermitidas = ['jpg', 'jpeg', 'png'];
            
            if (in_array($ext, $extensoesPermitidas)) {
                $nomeUnico = 'user_' . time() . '_' . uniqid() . '.' . $ext;
                $destino = '../../assets/img/profiles/' . $nomeUnico;
                
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
                    $foto = $nomeUnico;
                }
            }
        }

        $sql = "INSERT INTO usuarios (nome, email, senha, nivel_acesso, ativo, data_criacao, foto) 
                VALUES (:nome, :email, :senha, :nivel, 1, NOW(), :foto)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nome' => $nome,
            'email' => $email,
            'senha' => $senha,
            'nivel' => $nivel,
            'foto' => $foto
        ]);
        
        header('Location: usuario_list.php');
        exit();
        
    } catch (PDOException $e) {
        error_log('Erro ao cadastrar usuário: ' . $e->getMessage());
        $_SESSION['erro'] = 'Erro ao cadastrar usuário. Por favor, tente novamente.';
        header('Location: usuario_add.php');
        exit();
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
        max-width: 1200px;
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
        color: #2c3e50;
    }
    
    .card-form {
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        border: none;
        padding: 25px;
    }
    
    .form-label {
        font-weight: 500;
        margin-bottom: 5px;
    }
    
    .form-control, .form-select {
        border-radius: 8px;
        padding: 10px 15px;
        border: 1px solid #ced4da;
        transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), #2c3e50);
        border: none;
        padding: 10px 25px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #2c3e50, var(--primary-color));
        transform: translateY(-2px);
    }
    
    .btn-secondary {
        background: #6c757d;
        border: none;
        padding: 10px 25px;
        font-weight: 500;
    }
    
    .alert {
        border-radius: 8px;
    }
    
    /* Preview da foto */
    .photo-preview {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #e9ecef;
        display: none;
        margin: 15px auto;
    }
    
    @media (max-width: 768px) {
        .page-title {
            font-size: 1.5rem;
        }
        
        .card-form {
            padding: 20px 15px;
        }
    }
    </style>
</head>
<body>
    <?php include '../../templates/header.php'; ?>
    
    <div class="main-container">
        <!-- Área de Ações no Topo -->
        <div class="header-actions">
            <h2 class="page-title">
                <i class="bi bi-person-plus"></i> Adicionar Usuário
            </h2>
            <div>
                <a href="usuario_list.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
        </div>

        <!-- Card do Formulário -->
        <div class="card card-form">
            <div class="card-body">
                <?php if (isset($_SESSION['erro'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['erro'] ?></div>
                    <?php unset($_SESSION['erro']); ?>
                <?php endif; ?>
                
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label class="form-label">Nome Completo:</label>
                        <input type="text" name="nome" class="form-control form-control-lg" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Email:</label>
                        <input type="email" name="email" class="form-control form-control-lg" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Senha:</label>
                        <input type="password" name="senha" class="form-control form-control-lg" required minlength="6">
                        <small class="text-muted">Mínimo 6 caracteres</small>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Nível de Acesso:</label>
                        <select name="nivel_acesso" class="form-select form-select-lg" required>
                            <option value="">Selecione um nível...</option>
                            <option value="admin">Administrador</option>
                            <option value="gerente">Gerente</option>
                            <option value="vendedor">Vendedor</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Foto de Perfil:</label>
                        <img id="photoPreview" class="photo-preview" src="#" alt="Pré-visualização">
                        <input type="file" name="foto" id="fotoInput" class="form-control form-control-lg" accept="image/jpeg, image/png">
                        <small class="text-muted">Formatos aceitos: JPG, PNG (Máx. 2MB)</small>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-3 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Cadastrar Usuário
                        </button>
                        <a href="usuario_list.php" class="btn btn-secondary">
                            <i class="bi bi-x-lg"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Preview da foto selecionada
    document.getElementById('fotoInput').addEventListener('change', function(e) {
        const preview = document.getElementById('photoPreview');
        const file = e.target.files[0];
        
        if (file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            
            reader.readAsDataURL(file);
        }
    });
    </script>
</body>
</html>