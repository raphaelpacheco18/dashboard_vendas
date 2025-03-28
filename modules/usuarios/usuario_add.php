/* usuario_add.php - Cadastro de Usuário */
<?php
require_once '../../config/database.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $nivel = $_POST['nivel_acesso'];
    $sql = "INSERT INTO usuarios (nome, email, senha, nivel_acesso, ativo, data_criacao) VALUES (:nome, :email, :senha, :nivel, 1, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['nome' => $nome, 'email' => $email, 'senha' => $senha, 'nivel' => $nivel]);
    header('Location: usuario_list.php'); exit();
}
?>
<h1>Adicionar Usuário</h1>
<form method="post">
    Nome: <input type="text" name="nome" required><br>
    Email: <input type="email" name="email" required><br>
    Senha: <input type="password" name="senha" required><br>
    Nível de Acesso: <select name="nivel_acesso">
        <option value="admin">Administrador</option>
        <option value="gerente">Gerente</option>
        <option value="vendedor">Vendedor</option>
    </select><br>
    <button type="submit">Cadastrar</button>
</form>