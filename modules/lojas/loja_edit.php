<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

// Verificar se o usuário está logado e se ele tem o nível de acesso adequado (administrador)
if (!usuarioLogado() || $_SESSION['nivel_acesso'] != 'admin') {
    header('Location: ../index.php'); // Redirecionar para a página inicial se o usuário não for admin
    exit();
}


// Verificar se o ID da loja foi passado
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Consultar a loja pelo ID
    $sql = "SELECT * FROM lojas WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $loja = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $endereco = $_POST['endereco'];
    $telefone = $_POST['telefone'];

    // Atualizar as informações da loja
    $sql = "UPDATE lojas SET nome = :nome, endereco = :endereco, telefone = :telefone WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':endereco', $endereco);
    $stmt->bindParam(':telefone', $telefone);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        echo "<script>
            alert('Loja atualizada com sucesso!');
            window.location.href = 'lojas.php';
        </script>";
    } else {
        echo "<script>
            alert('Erro ao atualizar a loja. Tente novamente.');
            window.history.back();
        </script>";
    }
    
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Loja</title>
</head>
<body>
<?php include('../../templates/header.php'); ?>
<h1>Editar Loja</h1>

<form action="loja_edit.php?id=<?php echo $loja['id']; ?>" method="post">
    <label for="nome">Nome:</label>
    <input type="text" name="nome" value="<?php echo $loja['nome']; ?>" required><br>

    <label for="endereco">Endereço:</label>
    <input type="text" name="endereco" value="<?php echo $loja['endereco']; ?>" required><br>

    <button type="submit">Atualizar</button>
</form>

</body>
</html>
