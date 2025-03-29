<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
require_once '../../templates/header.php';

if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

$sql = "SELECT id, nome, email, nivel_acesso, ativo, foto FROM usuarios";
$stmt = $pdo->query($sql);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h1>Usuários</h1>
    <a href="usuario_add.php" class="btn btn-primary mb-3">Adicionar Usuário</a>
    
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Foto</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Nível</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td>
                        <img src="../../assets/img/profiles/<?= $user['foto'] ?>" width="40" height="40" class="rounded-circle">
                    </td>
                    <td><?= htmlspecialchars($user['nome']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= ucfirst($user['nivel_acesso']) ?></td>
                    <td><?= $user['ativo'] ? 'Ativo' : 'Inativo' ?></td>
                    <td>
                        <a href="usuario_edit.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                        <a href="usuario_delete.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../../templates/footer.php'; ?>