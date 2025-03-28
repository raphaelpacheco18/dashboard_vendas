<?php
// Incluir o arquivo de autenticação e conexão
require_once '../../config/auth.php';
require_once '../../config/database.php';  // Incluir a conexão com o banco de dados

// Verificar se o usuário está logado
if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Lógica para filtros de produtos
$nome = isset($_GET['nome']) ? $_GET['nome'] : '';
$loja = isset($_GET['loja']) ? $_GET['loja'] : '';

// Exemplo de consulta ao banco de dados com os filtros
$sql = "SELECT * FROM produtos WHERE 1"; // Adapte a tabela e as condições conforme necessário

// Filtros
if ($nome) {
    $sql .= " AND nome LIKE :nome";
}
if ($loja) {
    $sql .= " AND loja = :loja";
}

// Preparar a consulta com o PDO
$stmt = $pdo->prepare($sql);

// Bind dos parâmetros, se houver
if ($nome) {
    $stmt->bindParam(':nome', "%$nome%");
}
if ($loja) {
    $stmt->bindParam(':loja', $loja);
}

// Executar a consulta
$stmt->execute();

// Obter os resultados
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciamento de Produtos</title>
    <!-- Adicionar links para seus estilos CSS aqui -->
</head>
<body>
<?php include('../../templates/header.php'); ?>
<h1>Gerenciamento de Produtos</h1>

<!-- Exemplo de filtros de nome e loja -->
<form action="products.php" method="get">
    <label for="nome">Nome do Produto:</label>
    <input type="text" name="nome" value="<?php echo $nome; ?>">

    <label for="loja">Loja:</label>
    <input type="text" name="loja" value="<?php echo $loja; ?>">

    <button type="submit">Filtrar</button>
</form>

<h2>Lista de Produtos</h2>
<table border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Descrição</th>
            <th>Preço</th>
            <th>Quantidade</th>
            <th>Loja</th>
            <th>Data de Criação</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($produtos as $produto): ?>
            <tr>
                <td><?php echo $produto['id']; ?></td>
                <td><?php echo $produto['nome']; ?></td>
                <td><?php echo $produto['descricao']; ?></td>
                <td><?php echo $produto['preco']; ?></td>
                <td><?php echo $produto['quantidade']; ?></td>
                <td><?php echo $produto['loja']; ?></td>
                <td><?php echo $produto['data_criacao']; ?></td>
                <td>
                    <a href="product_edit.php?id=<?php echo $produto['id']; ?>">Editar</a>
                    <a href="delete.php?id=<?php echo $produto['id']; ?>" onclick="return confirm('Tem certeza que deseja excluir este produto?');">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
