<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

// Verificar se o usuário está logado
if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Verificar se foi passado um parâmetro de erro na URL
if (isset($_GET['erro']) && $_GET['erro'] == 'funcionarias') {
    echo "<p style='color: red;'>Não é possível excluir a loja, pois ela possui funcionárias cadastradas.</p>";
}

// Consultar todas as lojas
$sql = "SELECT * FROM lojas";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$lojas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Lojas</title>
</head>
<body>
<?php include('../../templates/header.php'); ?>
<h1>Lista de Lojas</h1>
<a href="loja_add.php">Cadastrar Nova Loja</a>

<table border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Endereço</th>
            <th>Data de Criação</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($lojas as $loja): ?>
            <tr>
                <td><?php echo $loja['id']; ?></td>
                <td><?php echo $loja['nome']; ?></td>
                <td><?php echo $loja['endereco']; ?></td>
                <td><?php echo $loja['data_criacao']; ?></td>
                <td>
                    <a href="loja_edit.php?id=<?php echo $loja['id']; ?>">Editar</a> | 
                    <a href="loja_delete.php?id=<?php echo $loja['id']; ?>" onclick="return confirm('Tem certeza que deseja excluir esta loja?');">Excluir</a>

                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
