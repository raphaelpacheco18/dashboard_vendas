<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

// Verificar se o usuário está logado
if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Obter lojas e vendedoras
$lojas = $pdo->query("SELECT * FROM lojas")->fetchAll(PDO::FETCH_ASSOC);
$vendedoras = $pdo->query("SELECT * FROM vendedoras")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loja_id = $_POST['loja_id'];
    $vendedora_id = $_POST['vendedora_id'];
    $periodo = $_POST['periodo'];
    $meta_valor = $_POST['meta_valor'];
    $meta_quantidade = $_POST['meta_quantidade'];
    $bonificacao = $_POST['bonificacao'];
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];

    // Inserir os dados na tabela metas
    $sql = "INSERT INTO metas (loja_id, vendedora_id, periodo, meta_valor, meta_quantidade, bonificacao, data_inicio, data_fim) 
            VALUES (:loja_id, :vendedora_id, :periodo, :meta_valor, :meta_quantidade, :bonificacao, :data_inicio, :data_fim)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':loja_id', $loja_id);
    $stmt->bindParam(':vendedora_id', $vendedora_id);
    $stmt->bindParam(':periodo', $periodo);
    $stmt->bindParam(':meta_valor', $meta_valor);
    $stmt->bindParam(':meta_quantidade', $meta_quantidade);
    $stmt->bindParam(':bonificacao', $bonificacao);
    $stmt->bindParam(':data_inicio', $data_inicio);
    $stmt->bindParam(':data_fim', $data_fim);

    if ($stmt->execute()) {
        echo "Meta definida com sucesso!";
    } else {
        echo "Erro ao definir a meta.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Definir Meta</title>
</head>
<body>
<?php include('../../templates/header.php'); ?>
<h1>Definir Meta de Vendas</h1>

<form action="metas_add.php" method="post">
    <label for="loja_id">Loja:</label>
    <select name="loja_id" required>
        <option value="">Selecione uma Loja</option>
        <?php foreach ($lojas as $loja): ?>
            <option value="<?php echo $loja['id']; ?>"><?php echo $loja['nome']; ?></option>
        <?php endforeach; ?>
    </select>

    <label for="vendedora_id">Vendedora:</label>
    <select name="vendedora_id" required>
        <option value="">Selecione uma Vendedora</option>
        <?php foreach ($vendedoras as $vendedora): ?>
            <option value="<?php echo $vendedora['id']; ?>"><?php echo $vendedora['nome']; ?></option>
        <?php endforeach; ?>
    </select>

    <label for="periodo">Período:</label>
    <select name="periodo" required>
        <option value="diario">Diário</option>
        <option value="mensal">Mensal</option>
        <option value="trimestral">Trimestral</option>
    </select>

    <label for="meta_valor">Valor da Meta:</label>
    <input type="number" name="meta_valor" step="0.01" required>

    <label for="meta_quantidade">Quantidade da Meta:</label>
    <input type="number" name="meta_quantidade" required>

    <label for="bonificacao">Bonificação:</label>
    <input type="number" name="bonificacao" step="0.01" required>

    <label for="data_inicio">Data de Início:</label>
    <input type="date" name="data_inicio" required>

    <label for="data_fim">Data de Fim:</label>
    <input type="date" name="data_fim" required>

    <button type="submit">Definir Meta</button>
</form>

<!-- Botão para voltar para a lista de metas -->
<br>
<a href="metas_list.php"><button type="button">Voltar para a Lista de Metas</button></a>

</body>
</html>
