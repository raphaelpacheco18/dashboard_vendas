<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
require_once '../../templates/header.php';

if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

$id = $_GET['id'];
$sql = "SELECT * FROM usuarios WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $nivel = $_POST['nivel_acesso'];
    $foto = $usuario['foto']; // Mantém a foto atual por padrão

    // Processamento do upload da foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $extensoesPermitidas = ['jpg', 'jpeg', 'png'];
        
        if (in_array($ext, $extensoesPermitidas)) {
            // Remove a foto antiga se não for a padrão
            if ($usuario['foto'] != 'default-profile.jpg') {
                @unlink('../../assets/img/profiles/' . $usuario['foto']);
            }
            
            $nomeUnico = 'user_' . time() . '_' . uniqid() . '.' . $ext;
            $destino = '../../assets/img/profiles/' . $nomeUnico;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
                $foto = $nomeUnico;
            }
        }
    }

    // Se marcar para remover a foto
    if (isset($_POST['remover_foto']) && $_POST['remover_foto'] == 'on') {
        if ($usuario['foto'] != 'default-profile.jpg') {
            @unlink('../../assets/img/profiles/' . $usuario['foto']);
        }
        $foto = 'default-profile.jpg';
    }

    $sql = "UPDATE usuarios SET 
            nome = :nome, 
            email = :email, 
            nivel_acesso = :nivel,
            foto = :foto
            WHERE id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'nome' => $nome,
        'email' => $email,
        'nivel' => $nivel,
        'foto' => $foto,
        'id' => $id
    ]);
    
    header('Location: usuario_list.php');
    exit();
}
?>

<div class="container mt-4">
    <h1>Editar Usuário</h1>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Nome:</label>
            <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email:</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($usuario['email']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Nível de Acesso:</label>
            <select name="nivel_acesso" class="form-select">
                <option value="admin" <?= $usuario['nivel_acesso'] == 'admin' ? 'selected' : '' ?>>Administrador</option>
                <option value="gerente" <?= $usuario['nivel_acesso'] == 'gerente' ? 'selected' : '' ?>>Gerente</option>
                <option value="vendedor" <?= $usuario['nivel_acesso'] == 'vendedor' ? 'selected' : '' ?>>Vendedor</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Foto de Perfil:</label>
            <input type="file" name="foto" class="form-control" accept="image/jpeg, image/png">
            
            <?php if (!empty($usuario['foto'])): ?>
                <div class="mt-2">
                    <img src="../../assets/img/profiles/<?= $usuario['foto'] ?>" width="100" class="img-thumbnail">
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="remover_foto" id="remover_foto">
                        <label class="form-check-label" for="remover_foto">Remover foto atual</label>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        <a href="usuario_list.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php require_once '../../templates/footer.php'; ?>