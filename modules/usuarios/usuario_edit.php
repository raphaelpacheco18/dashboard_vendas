/* usuario_edit.php - Edição de Usuário */
<?php
require_once '../../config/database.php';
$id = $_GET['id'];
$sql = "SELECT * FROM usuarios WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $nivel = $_POST['nivel_acesso'];
    $sql = "UPDATE usuarios SET nome = :nome, email = :email, nivel_acesso = :nivel WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['nome' => $nome, 'email' => $email, 'nivel' => $nivel, 'id' => $id]);
    header('Location: usuario_list.php'); exit();
}
?>
<h1>Editar Usuário</h1>
<form method="post">
    Nome: <input type="text" name="nome" value="<?= $usuario['nome'] ?>" required><br>
    Email: <input type="email" name="email" value="<?= $usuario['email'] ?>" required><br>
    Nível de Acesso: <select name="nivel_acesso">
        <option value="admin" <?= $usuario['nivel_acesso'] == 'admin' ? 'selected' : '' ?>>Administrador</option>
        <option value="gerente" <?= $usuario['nivel_acesso'] == 'gerente' ? 'selected' : '' ?>>Gerente</option>
        <option value="vendedor" <?= $usuario['nivel_acesso'] == 'vendedor' ? 'selected' : '' ?>>Vendedor</option>
    </select><br>
    <button type="submit">Salvar Alterações</button>
</form>