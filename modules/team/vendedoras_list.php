<?php
// Incluir o arquivo de autenticação
require_once '../../config/auth.php';
require_once '../../config/database.php';  // Incluir a conexão com o banco de dados

// Verificar se o usuário está logado
if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Consultar as vendedoras
$sql = "SELECT v.*, l.nome AS loja_nome FROM vendedoras v 
        JOIN lojas l ON v.loja_id = l.id";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$vendedoras = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Vendedoras</title>
    <link rel="stylesheet" href="../../assets/css/style.css"> <!-- Ajuste se necessário -->
</head>
<body>
<?php include('../../templates/header.php'); ?>
<h1>Lista de Vendedoras</h1>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>E-mail</th>
            <th>Loja</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($vendedoras as $vendedora): ?>
            <tr>
                <td><?php echo $vendedora['id']; ?></td>
                <td><?php echo $vendedora['nome']; ?></td>
                <td><?php echo $vendedora['email']; ?></td>
                <td><?php echo $vendedora['loja_nome']; ?></td>
                <td>
                    <a href="vendedora_edit.php?id=<?php echo $vendedora['id']; ?>">Editar</a>
                    <a href="vendedora_delete.php?id=<?php echo $vendedora['id']; ?>" onclick="return confirm('Tem certeza que deseja excluir?');">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<a href="vendedora_add.php">Cadastrar nova vendedora</a>

</body>
</html>
