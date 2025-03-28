<?php
// Incluir o arquivo de autenticação
require_once '../../config/auth.php';
require_once '../../config/database.php';  // Incluir a conexão com o banco de dados

// Verificar se o usuário está logado
if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_loja = $_POST['nome_loja'];
    $endereco_loja = $_POST['endereco_loja'];

    // Inserir a nova loja no banco de dados
    $sql = "INSERT INTO lojas (nome, endereco) VALUES (:nome, :endereco)";
    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':nome', $nome_loja);
    $stmt->bindParam(':endereco', $endereco_loja);

    if ($stmt->execute()) {
        // Redireciona para a página de listagem de lojas após o cadastro
        header('Location: lojas.php');
        exit();
    } else {
        echo "Erro ao cadastrar a loja.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Loja</title>
</head>
<body>
<?php include('../../templates/header.php'); ?>
<h1>Cadastrar Loja</h1>

<form action="loja_add.php" method="post">
    <label for="nome_loja">Nome da Loja:</label>
    <input type="text" name="nome_loja" required>

    <label for="endereco_loja">Endereço da Loja:</label>
    <input type="text" name="endereco_loja" required>

    <button type="submit">Cadastrar Loja</button>
</form>

</body>
</html>
