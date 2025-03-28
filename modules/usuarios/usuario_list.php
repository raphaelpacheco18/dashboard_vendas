<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
if (!usuarioLogado()) { header('Location: ../../auth/login.php'); exit(); }
$sql = "SELECT id, nome, email, nivel_acesso, ativo FROM usuarios";
$stmt = $pdo->query($sql);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include 'templates/header.php'; ?>
<h1>Usuários</h1>
<a href="usuario_add.php">Adicionar Usuário</a>
<table border="1">
    <tr><th>ID</th><th>Nome</th><th>Email</th><th>Nível</th><th>Status</th><th>Ações</th></tr>
    <?php foreach ($usuarios as $user): ?>
        <tr>
            <td><?= $user['id'] ?></td>
            <td><?= $user['nome'] ?></td>
            <td><?= $user['email'] ?></td>
            <td><?= $user['nivel_acesso'] ?></td>
            <td><?= $user['ativo'] ? 'Ativo' : 'Inativo' ?></td>
            <td>
                <a href="usuario_edit.php?id=<?= $user['id'] ?>">Editar</a>
                <a href="usuario_delete.php?id=<?= $user['id'] ?>" onclick="return confirm('Tem certeza?')">Excluir</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>