<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

// Verifica autenticação antes de qualquer output
if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Processa o formulário se for POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
        $nivel = filter_input(INPUT_POST, 'nivel_acesso', FILTER_SANITIZE_STRING);
        $foto = 'default-profile.jpg';

        // Processamento seguro do upload da foto
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

        // Insere no banco de dados
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
        
        // Redireciona após inserção
        header('Location: usuario_list.php');
        exit();
        
    } catch (PDOException $e) {
        // Log do erro (em produção, grave em um arquivo de log)
        error_log('Erro ao cadastrar usuário: ' . $e->getMessage());
        
        // Define mensagem de erro para exibir após o redirecionamento
        $_SESSION['erro'] = 'Erro ao cadastrar usuário. Por favor, tente novamente.';
        header('Location: usuario_add.php');
        exit();
    }
}

// Só carrega o template depois de todo o processamento
require_once '../../templates/header.php';
?>

<div class="container mt-4">
    <h1>Adicionar Usuário</h1>
    
    <?php if (isset($_SESSION['erro'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['erro'] ?></div>
        <?php unset($_SESSION['erro']); ?>
    <?php endif; ?>
    
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Nome:</label>
            <input type="text" name="nome" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email:</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Senha:</label>
            <input type="password" name="senha" class="form-control" required minlength="6">
        </div>
        <div class="mb-3">
            <label class="form-label">Nível de Acesso:</label>
            <select name="nivel_acesso" class="form-select" required>
                <option value="">Selecione...</option>
                <option value="admin">Administrador</option>
                <option value="gerente">Gerente</option>
                <option value="vendedor">Vendedor</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Foto de Perfil:</label>
            <input type="file" name="foto" class="form-control" accept="image/jpeg, image/png">
            <small class="text-muted">Formatos aceitos: JPG, PNG (Máx. 2MB)</small>
        </div>
        <button type="submit" class="btn btn-primary">Cadastrar</button>
        <a href="usuario_list.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php require_once '../../templates/footer.php'; ?>