<?php
// Incluir o arquivo de autenticação e conexão
require_once '../../config/auth.php';
require_once '../../config/database.php';

// Verificar se o usuário está logado
if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Verificar se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obter os dados do formulário
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $quantidade = $_POST['quantidade'];
    $loja = $_POST['loja'];

    // Atualizar o produto no banco de dados
    $sql = "UPDATE produtos SET nome = :nome, descricao = :descricao, preco = :preco, quantidade = :quantidade, loja = :loja WHERE id = :id";
    $stmt = $pdo->prepare($sql);

    // Bind dos parâmetros
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':descricao', $descricao);
    $stmt->bindParam(':preco', $preco);
    $stmt->bindParam(':quantidade', $quantidade);
    $stmt->bindParam(':loja', $loja);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Executar a consulta
    if ($stmt->execute()) {
        header('Location: products.php'); // Redireciona para a lista de produtos
        exit();
    } else {
        echo "Erro ao atualizar produto.";
    }
}

// Obter o ID do produto para editar
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: products.php'); // Se o ID não for fornecido, redireciona para a lista de produtos
    exit();
}

// Buscar os dados do produto para preenchimento no formulário
$sql = "SELECT * FROM produtos WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    echo "Produto não encontrado!";
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Produto</title>
    <!-- Aqui você pode adicionar seu CSS -->
</head>
<body>
<?php include('../../templates/header.php'); ?>
<h1>Editar Produto</h1>

<form action="product_edit.php" method="post">
    <input type="hidden" name="id" value="<?php echo $produto['id']; ?>">

    <label for="nome">Nome:</label>
    <input type="text" name="nome" value="<?php echo $produto['nome']; ?>" required><br><br>

    <label for="descricao">Descrição:</label>
    <input type="text" name="descricao" value="<?php echo $produto['descricao']; ?>" required><br><br>

    <label for="preco">Preço:</label>
    <input type="text" name="preco" value="<?php echo $produto['preco']; ?>" required><br><br>

    <label for="quantidade">Quantidade:</label>
    <input type="number" name="quantidade" value="<?php echo $produto['quantidade']; ?>" required><br><br>

    <label for="loja">Loja:</label>
    <input type="text" name="loja" value="<?php echo $produto['loja']; ?>" required><br><br>

    <button type="submit">Atualizar Produto</button>
</form>

</body>
</html>
