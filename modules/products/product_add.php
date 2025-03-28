<?php
// Incluir o arquivo de autenticação e conexão
require_once '../../config/auth.php';
require_once '../../config/database.php';

// Verificar se o usuário está logado
if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Variáveis para erros
$erro = '';

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Coletar os dados do formulário
    $nome = isset($_POST['nome']) ? $_POST['nome'] : '';
    $descricao = isset($_POST['descricao']) ? $_POST['descricao'] : '';
    $preco = isset($_POST['preco']) ? $_POST['preco'] : '';
    $quantidade = isset($_POST['quantidade']) ? $_POST['quantidade'] : '';
    $loja = isset($_POST['loja']) ? $_POST['loja'] : '';

    // Verificar se todos os campos obrigatórios foram preenchidos
    if (empty($nome) || empty($descricao) || empty($preco) || empty($quantidade) || empty($loja)) {
        $erro = "Por favor, preencha todos os campos!";
    } else {
        // Inserir o produto no banco de dados
        $sql = "INSERT INTO produtos (nome, descricao, preco, quantidade, loja, data_criacao) 
                VALUES (:nome, :descricao, :preco, :quantidade, :loja, NOW())";

        // Preparar a consulta SQL
        $stmt = $pdo->prepare($sql);

        // Bind dos parâmetros
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':preco', $preco);
        $stmt->bindParam(':quantidade', $quantidade);
        $stmt->bindParam(':loja', $loja);

        // Executar a consulta
        if ($stmt->execute()) {
            echo "Produto adicionado com sucesso!";
        } else {
            $erro = 'Erro ao adicionar produto: ' . $stmt->errorInfo()[2];
        }
    }
}

// Buscar as lojas do banco de dados
$lojas_sql = "SELECT id, nome FROM lojas";
$lojas_stmt = $pdo->prepare($lojas_sql);
$lojas_stmt->execute();
$lojas = $lojas_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Produto</title>
</head>
<body>
<?php include('../../templates/header.php'); ?>
<h1>Adicionar Produto</h1>

<!-- Exibir erro, se houver -->
<?php if ($erro): ?>
    <div style="color: red;"><?php echo $erro; ?></div>
<?php endif; ?>

<form action="product_add.php" method="POST">
    <label for="nome">Nome:</label>
    <input type="text" name="nome" id="nome" required><br>

    <label for="descricao">Descrição:</label>
    <textarea name="descricao" id="descricao" required></textarea><br>

    <label for="preco">Preço:</label>
    <input type="number" name="preco" id="preco" step="0.01" required><br>

    <label for="quantidade">Quantidade:</label>
    <input type="number" name="quantidade" id="quantidade" required><br>

    <label for="loja">Loja:</label>
    <select name="loja" id="loja" required>
        <option value="">Selecione uma loja</option>
        <?php foreach ($lojas as $loja): ?>
            <option value="<?php echo $loja['id']; ?>"><?php echo $loja['nome']; ?></option>
        <?php endforeach; ?>
    </select><br>

    <button type="submit">Adicionar Produto</button>
</form>

<!-- Botão para voltar para a lista de produtos -->
<br>
<a href="products.php"><button type="button">Voltar para a Lista de Produtos</button></a>

</body>
</html>
