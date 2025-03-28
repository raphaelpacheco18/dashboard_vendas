<?php
// Iniciar a sessão
// Verifique se a sessão já foi iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir arquivos de autenticação e conexão com o banco de dados
// No arquivo que está incluindo auth.php
require_once '../../config/auth.php';  // Certifique-se de usar require_once para evitar múltiplas inclusões
require_once '../../config/database.php';

// Verificar se o usuário está logado
if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Definir a variável $nivel_acesso a partir da sessão
$nivel_acesso = $_SESSION['nivel_acesso']; // ou outra forma de definir o nível de acesso

// Verificar se o ID foi passado
if (!isset($_GET['id'])) {
    header('Location: sales.php');
    exit();
}

$id = $_GET['id'];

// Buscar a venda para editar
$sql = "SELECT * FROM vendas WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();
$venda = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar se a venda existe
if (!$venda) {
    header('Location: sales.php');
    exit();
}

// Processar o formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loja_id = $_POST['loja_id'];
    $vendedora_id = $_POST['vendedora_id'];
    $produto_id = $_POST['produto_id'];
    $data_venda = $_POST['data_venda'];
    $boleta = $_POST['boleta'];
    $valor_total = $_POST['valor_total'];
    $tipo_pagamento = $_POST['tipo_pagamento'];

    $sql = "UPDATE vendas SET loja_id = :loja_id, vendedora_id = :vendedora_id, produto_id = :produto_id, 
            data_venda = :data_venda, boleta = :boleta, valor_total = :valor_total, tipo_pagamento = :tipo_pagamento
            WHERE id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':loja_id', $loja_id);
    $stmt->bindParam(':vendedora_id', $vendedora_id);
    $stmt->bindParam(':produto_id', $produto_id);
    $stmt->bindParam(':data_venda', $data_venda);
    $stmt->bindParam(':boleta', $boleta);
    $stmt->bindParam(':valor_total', $valor_total);
    $stmt->bindParam(':tipo_pagamento', $tipo_pagamento);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        header("Location: sales.php");
        exit();
    } else {
        $error = "Erro ao editar a venda.";
    }
}

$lojas = $pdo->query("SELECT * FROM lojas")->fetchAll(PDO::FETCH_ASSOC);
$vendedoras = $pdo->query("SELECT * FROM vendedoras")->fetchAll(PDO::FETCH_ASSOC);
$produtos = $pdo->query("SELECT * FROM produtos")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Venda</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php include('../../templates/header.php'); ?>

<!-- Incluir sidebar.php e passar a variável $nivel_acesso -->
<?php include('../../templates/sidebar.php'); ?>

<div class="main-content">
    <h1>Editar Venda</h1>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form action="sales_edit.php?id=<?php echo $venda['id']; ?>" method="post" class="form-style">
        <label for="loja_id">Loja:</label>
        <select name="loja_id">
            <?php foreach ($lojas as $loja): ?>
                <option value="<?php echo $loja['id']; ?>" <?php echo ($loja['id'] == $venda['loja_id']) ? 'selected' : ''; ?>>
                    <?php echo $loja['nome']; ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <label for="vendedora_id">Vendedora:</label>
        <select name="vendedora_id">
            <?php foreach ($vendedoras as $vendedora): ?>
                <option value="<?php echo $vendedora['id']; ?>" <?php echo ($vendedora['id'] == $venda['vendedora_id']) ? 'selected' : ''; ?>>
                    <?php echo $vendedora['nome']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="produto_id">Produto:</label>
        <select name="produto_id">
            <?php foreach ($produtos as $produto): ?>
                <option value="<?php echo $produto['id']; ?>" <?php echo ($produto['id'] == $venda['produto_id']) ? 'selected' : ''; ?>>
                    <?php echo $produto['nome']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="data_venda">Data da Venda:</label>
        <input type="date" name="data_venda" value="<?php echo date('Y-m-d', strtotime($venda['data_venda'])); ?>">

        <label for="boleta">Boleta:</label>
        <input type="text" name="boleta" value="<?php echo $venda['boleta']; ?>">

        <label for="valor_total">Valor Total:</label>
        <input type="text" name="valor_total" value="<?php echo $venda['valor_total']; ?>">

        <label for="tipo_pagamento">Tipo de Pagamento:</label>
        <select name="tipo_pagamento">
            <option value="cartão" <?php echo ($venda['tipo_pagamento'] == 'cartão') ? 'selected' : ''; ?>>Cartão</option>
            <option value="pix" <?php echo ($venda['tipo_pagamento'] == 'pix') ? 'selected' : ''; ?>>Pix</option>
            <option value="débito" <?php echo ($venda['tipo_pagamento'] == 'débito') ? 'selected' : ''; ?>>Débito</option>
            <option value="dinheiro" <?php echo ($venda['tipo_pagamento'] == 'dinheiro') ? 'selected' : ''; ?>>Dinheiro</option>
            <option value="multiplo" <?php echo ($venda['tipo_pagamento'] == 'multiplo') ? 'selected' : ''; ?>>Mais de uma opção</option>
        </select>

        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
    </form>
</div>

<?php include('../../templates/footer.php'); ?>

</body>
</html>
