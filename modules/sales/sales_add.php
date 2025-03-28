<?php
// Incluir arquivos de autenticação e conexão com o banco de dados
require_once '../../config/auth.php';
require_once '../../config/database.php';

// Verificar se o usuário está logado
if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Processar o formulário de adição de venda
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coletar dados do formulário
    $loja_id = $_POST['loja_id'];
    $vendedora_id = $_POST['vendedora_id'];
    $produto_id = $_POST['produto_id'];
    $data_venda = $_POST['data_venda'];
    $boleta = $_POST['boleta'];
    $valor_total = $_POST['valor_total'];
    $tipo_pagamento = $_POST['tipo_pagamento'];

    // Inserir no banco de dados
    $sql = "INSERT INTO vendas (loja_id, vendedora_id, produto_id, data_venda, boleta, valor_total, tipo_pagamento) 
            VALUES (:loja_id, :vendedora_id, :produto_id, :data_venda, :boleta, :valor_total, :tipo_pagamento)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':loja_id', $loja_id);
    $stmt->bindParam(':vendedora_id', $vendedora_id);
    $stmt->bindParam(':produto_id', $produto_id);
    $stmt->bindParam(':data_venda', $data_venda);
    $stmt->bindParam(':boleta', $boleta);
    $stmt->bindParam(':valor_total', $valor_total);
    $stmt->bindParam(':tipo_pagamento', $tipo_pagamento);
    
    if ($stmt->execute()) {
        header('Location: sales.php');
        exit();
    } else {
        $error = "Erro ao adicionar venda.";
    }
}

// Consultar lojas, vendedoras e produtos para preencher os selects
$lojas = $pdo->query("SELECT * FROM lojas")->fetchAll(PDO::FETCH_ASSOC);
$vendedoras = $pdo->query("SELECT * FROM vendedoras")->fetchAll(PDO::FETCH_ASSOC);
$produtos = $pdo->query("SELECT * FROM produtos")->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../../templates/header.php'; ?>

<div class="content">
    <h1>Adicionar Nova Venda</h1>

    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form action="sales_add.php" method="post">
        <label for="loja_id">Loja:</label>
        <select name="loja_id">
            <?php foreach ($lojas as $loja): ?>
                <option value="<?php echo $loja['id']; ?>"><?php echo $loja['nome']; ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="vendedora_id">Vendedora:</label>
        <select name="vendedora_id">
            <?php foreach ($vendedoras as $vendedora): ?>
                <option value="<?php echo $vendedora['id']; ?>"><?php echo $vendedora['nome']; ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="produto_id">Produto:</label>
        <select name="produto_id">
            <?php foreach ($produtos as $produto): ?>
                <option value="<?php echo $produto['id']; ?>"><?php echo $produto['nome']; ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="data_venda">Data da Venda:</label>
        <input type="date" name="data_venda"><br><br>

        <label for="boleta">Boleta:</label>
        <input type="text" name="boleta"><br><br>

        <label for="valor_total">Valor Total:</label>
        <input type="text" name="valor_total"><br><br>

        <label for="tipo_pagamento">Tipo de Pagamento:</label>
        <select name="tipo_pagamento">
            <option value="cartão">Cartão</option>
            <option value="pix">Pix</option>
            <option value="débito">Débito</option>
            <option value="dinheiro">Dinheiro</option>
            <option value="multiplo">Mais de uma opção</option>
        </select><br><br>

        <button type="submit">Salvar Venda</button>
    </form>
</div>

<?php include '../../templates/footer.php'; ?>
