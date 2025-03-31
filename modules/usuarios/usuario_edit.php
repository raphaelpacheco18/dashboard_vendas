<?php
// Incluir o cabeçalho
include '../../templates/header.php';

// Incluir a conexão com o banco de dados
require_once '../../config/database.php';

// Obter o ID do usuário a ser editado
if (isset($_GET['id'])) {
    $id_usuario = $_GET['id'];

    // Consultar os dados do usuário
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
    $stmt->bindParam(':id', $id_usuario);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo "Usuário não encontrado.";
        exit;
    }
} else {
    echo "ID do usuário não informado.";
    exit;
}

// Atualizar os dados se o formulário for enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $nivel_acesso = $_POST['nivel_acesso'];
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    // Atualizar os dados no banco de dados
    $stmt = $pdo->prepare("UPDATE usuarios SET nome = :nome, email = :email, nivel_acesso = :nivel_acesso, ativo = :ativo WHERE id = :id");
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':nivel_acesso', $nivel_acesso);
    $stmt->bindParam(':ativo', $ativo);
    $stmt->bindParam(':id', $id_usuario);

    if ($stmt->execute()) {
        echo "Usuário atualizado com sucesso.";
    } else {
        echo "Erro ao atualizar o usuário.";
    }
}
?>

<!-- Formulário de edição do usuário -->
<div class="container">
    <h2>Editar Usuário</h2>
    <form method="POST">
        <div class="form-group">
            <label for="nome">Nome:</label>
            <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
        </div>
        <div class="form-group">
            <label for="nivel_acesso">Nível de Acesso:</label>
            <select class="form-control" id="nivel_acesso" name="nivel_acesso">
                <option value="admin" <?php echo ($usuario['nivel_acesso'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                <option value="gerente" <?php echo ($usuario['nivel_acesso'] == 'gerente') ? 'selected' : ''; ?>>Gerente</option>
                <option value="vendedor" <?php echo ($usuario['nivel_acesso'] == 'vendedor') ? 'selected' : ''; ?>>Vendedor</option>
            </select>
        </div>
        <div class="form-check">
            <label class="form-check-label">
                <input type="checkbox" class="form-check-input" name="ativo" <?php echo ($usuario['ativo'] == 1) ? 'checked' : ''; ?>> Ativo
            </label>
        </div>
        <button type="submit" class="btn btn-primary">Atualizar</button>
    </form>

    <!-- Botão para voltar para a lista -->
    <a href="usuario_list.php" class="btn btn-secondary mt-3">Voltar para a lista</a>
</div>

<?php
// Incluir o rodapé
include '../../templates/footer.php';
?>
